<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\MeterReading;
use App\Models\Client;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Access\AuthorizationException;
use Exception;

class MeterReadings extends Component
{
    public $search = '';
    public $selectedClientId = null;
    public $savedReadings = [];
    public $focusMeterId = null;
    
    public $allReadings = [];
    public $displayReadings = [];
    
    public $alertMessage = null;
    public $alertType = null;
    public $fieldErrors = [];

    public function mount()
    {
        $this->allReadings = collect();
        $this->displayReadings = collect();
        $this->loadAllReadings();
    }

    private function setAlert($message, $type = 'success')
    {
        $this->alertMessage = $message;
        $this->alertType = $type;
    }

    public function clearAlert()
    {
        $this->alertMessage = null;
        $this->alertType = null;
    }

    private function setFieldError($readingId, $message)
    {
        $this->fieldErrors[$readingId] = $message;
    }

    private function clearFieldError($readingId)
    {
        unset($this->fieldErrors[$readingId]);
    }

    public function loadAllReadings()
    {
        $userId = Auth::id();

        try {
            // Check if user can view meter readings
            $this->authorize('viewAny', MeterReading::class);

            $this->allReadings = MeterReading::latestPerActiveClient($userId)
                ->load(['client.meterCategory', 'client.user.kilowattPrice', 'payments']);

            // Apply filters
            if ($this->selectedClientId) {
                $this->allReadings = $this->allReadings
                    ->where('client_id', $this->selectedClientId);
            } elseif (trim($this->search) !== '') {
                $this->allReadings = $this->allReadings->filter(
                    fn ($r) => stripos($r->client->full_name, $this->search) !== false
                );
            }

            $this->updateDisplayReadings();

        } catch (AuthorizationException $e) {
            $this->setAlert('ليس لديك صلاحية لعرض قراءات العدادات', 'danger');
            $this->allReadings = collect();
            $this->displayReadings = collect();
        } catch (Exception $e) {
            $this->setAlert('حدث خطأ أثناء تحميل قراءات العدادات', 'danger');
            $this->allReadings = collect();
            $this->displayReadings = collect();
        }
    }

    public function loadClientsForSearch()
    {
        try {
            return Client::where('user_id', Auth::id())
                ->active()
                ->search($this->search)
                ->orderBy('id')
                ->get();
        } catch (Exception $e) {
            $this->setAlert('حدث خطأ أثناء تحميل بيانات المشتركين', 'danger');
            return collect();
        }
    }

    public function handleSearch()
    {
        $this->resetValidation();
        $this->clearAlert();
        $this->fieldErrors = [];
        
        // Auto-select if only one result
        $clients = $this->loadClientsForSearch();
        $this->selectedClientId = $clients->count() === 1 ? $clients->first()->id : null;
        
        $this->loadAllReadings();
    }

    public function updatedSelectedClientId($value)
    {
        if ($value) {
            $this->resetValidation();
            $this->clearAlert();
            $this->fieldErrors = [];
        }
        $this->updateDisplayReadings();
    }

    public function updateDisplayReadings()
    {
        $this->displayReadings = $this->selectedClientId
            ? $this->allReadings->where('client_id', $this->selectedClientId)
            : $this->allReadings;
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->selectedClientId = null;
        $this->resetValidation();
        $this->clearAlert();
        $this->fieldErrors = [];
        $this->loadAllReadings();
    }

    public function updateCurrentMeter($readingId, $value, $moveFocus = null)
    {
        try {
            $reading = MeterReading::with(['client.meterCategory', 'client.user.kilowattPrice', 'payments'])->findOrFail($readingId);

            $value = (int) $value;
            // Check authorization using Policy
            $this->authorize('update', $reading);

            // Clear any previous field error for this reading
            $this->clearFieldError($readingId);

            if ($value < $reading->previous_meter) {
                $this->setFieldError($readingId, 'العداد الحالي يجب أن يكون أكبر من العداد السابق.');
                return;
            }

             /**
             * FIRST TIME ENTRY → update directly
             */
            if (is_null($reading->reading_date)) {
                $this->applyMeterUpdate($reading, $value);

                if ($moveFocus === 'next') {
                    $this->focusMeterId = $this->getNextMeterId($readingId);
                } elseif ($moveFocus === 'prev') {
                    $this->focusMeterId = $this->getPrevMeterId($readingId);
                }
                
                // Reload readings to reflect changes
                $this->loadAllReadings();
                return;
            }
            
            /**
             * SECOND TIME → ask for confirmation
             */

            if ((int)$reading->current_meter === (int)$value) {
                return;
            }

            // notify client to open modal (Alpine listens for 'show-confirm-modal')
            $this->dispatchBrowserEvent('show-confirm-modal', [
                'readingId' => $readingId,
                'oldMeter'   => (int) $reading->current_meter,
                'value'      => $value,
            ]);
            

        } catch (AuthorizationException $e) {
            $this->setAlert('ليس لديك صلاحية لتحديث قراءة العداد هذه', 'danger');
        } catch (Exception $e) {
            $this->setAlert('حدث خطأ أثناء تحديث قراءة العداد.', 'danger');
        }
    }

     /** =========================
     *  Confirm modal actions
     *  ========================= */
    public function confirmMeterUpdate($readingId, $newMeter)
    {
        $reading = MeterReading::findOrFail($readingId);

        $this->authorize('update', $reading);

        $this->applyMeterUpdate($reading, (int)$newMeter);

        // refresh minimal data; consider replacing loadAllReadings with a targeted update for speed
        $this->loadAllReadings();

        $this->setAlert('تم تحديث قراءة العداد بنجاح.', 'success');
    }


     /** =========================
     *  Actual save logic
     *  ========================= */
    private function applyMeterUpdate(MeterReading $reading, int $value)
    {
        $client = $reading->client;
        $totalPaid = $reading->totalPaid();

        if ($client->is_offered) {
            $reading->update([
                'current_meter' => $value,
                'amount' => 0,
                'maintenance_cost' => 0,
                'previous_balance' => 0,
                'remaining_amount' => 0,
                'reading_date' => now(),
            ]);
        } else {
            $previousBalance = $this->calculateActualPreviousBalance(
                $client->id,
                $reading->reading_for_month
            );

            $consumption = $value - $reading->previous_meter;
            $kilowatt = $client->user->kilowattPrice->price ?? 0;
            $category = $client->meterCategory->price ?? 0;

            $amount = round((($consumption * $kilowatt) + $category) * 2) / 2;
            $totalDue = $amount + $reading->maintenance_cost + $previousBalance;

            $reading->update([
                'current_meter' => $value,
                'amount' => $amount,
                'previous_balance' => $previousBalance,
                'remaining_amount' => $totalDue - $totalPaid,
                'reading_date' => now(),
            ]);
        }

        $this->savedReadings[$reading->id] = true;

        //clear any field error after successful save
        $this->clearFieldError($reading->id);
        
    }


    // Calculate the actual previous balance from the latest completed reading BEFORE the current month
    private function calculateActualPreviousBalance($clientId, $currentMonth)
    {
        $previousReading = MeterReading::where('client_id', $clientId)
            ->beforeMonth($currentMonth)
            ->completed()
            ->latest()
            ->first();

        // Return the remaining_amount from the previous completed reading (can be negative for credit)
        return $previousReading?->remaining_amount ?? 0;
    }


    public function getNextMeterId($currentId)
    {
        $ids = $this->displayReadings->pluck('id')->values();
        $index = $ids->search($currentId);
        return $ids[$index + 1] ?? null;
    }

    public function getPrevMeterId($currentId)
    {
        $ids = $this->displayReadings->pluck('id')->values();
        $index = $ids->search($currentId);
        return $ids[$index - 1] ?? null;
    }

    private function getArabicMonthName($date)
    {
        $months = [
            1 => 'كانون الثاني', 2 => 'شباط', 3 => 'آذار', 4 => 'نيسان',
            5 => 'أيار', 6 => 'حزيران', 7 => 'تموز', 8 => 'آب',
            9 => 'أيلول', 10 => 'تشرين الأول', 11 => 'تشرين الثاني', 12 => 'كانون الأول',
        ];
        
        return $months[$date->month] . ' ' . $date->year;
    }

    public function render()
    {
        $clients = $this->loadClientsForSearch();
        $latestMonth = $this->allReadings->first()?->reading_for_month;
        $arabicMonthName = $latestMonth ? $this->getArabicMonthName($latestMonth) : null;

        return view('livewire.meter-readings', [
            'clients' => $clients,
            'arabicMonthName' => $arabicMonthName,
        ]);
    }
}