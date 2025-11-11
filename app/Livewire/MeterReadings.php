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
                $this->allReadings = $this->allReadings->filter(
                    fn($reading) => $reading->client_id == $this->selectedClientId
                );
            } elseif (trim($this->search) !== '') {
                $this->allReadings = $this->allReadings->filter(
                    fn($reading) => stripos($reading->client->full_name, $this->search) !== false
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
        if ($clients->count() === 1) {
            $this->selectedClientId = $clients->first()->id;
        } else {
            $this->selectedClientId = null;
        }
        
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
        if ($this->selectedClientId) {
            $this->displayReadings = $this->allReadings->filter(
                fn($reading) => $reading->client_id == $this->selectedClientId
            );
        } else {
            $this->displayReadings = $this->allReadings;
        }
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

    public function clearAlert()
    {
        $this->alertMessage = null;
        $this->alertType = null;
    }

    public function updateCurrentMeter($readingId, $value, $moveFocus = null)
    {
        try {
            $reading = MeterReading::with(['client.meterCategory', 'client.user.kilowattPrice', 'payments'])->findOrFail($readingId);
            $client = $reading->client;

            // Check authorization using Policy
            $this->authorize('update', $reading);

            $value = (int) $value;

            $totalPaid = $reading->totalPaid();

            // Clear any previous field error for this reading
            $this->clearFieldError($readingId);

            if ($value < $reading->previous_meter) {
                $this->setFieldError($readingId, 'العداد الحالي يجب أن يكون أكبر من العداد السابق.');
                return;
            }

            // Check if client is offered
            if ($client->is_offered) {
            
                // For offered clients, set everything to 0
                $reading->current_meter = $value;
                $reading->amount = 0;
                $reading->maintenance_cost = 0;
                $reading->previous_balance = 0;
                $reading->remaining_amount = 0;
                $reading->reading_date = now();
                
                $reading->save();

                $this->savedReadings[$readingId] = true;
                $this->setAlert('تم تحديث قراءة العداد للمشترك.', 'success');
                
            } else {
                $actualPreviousBalance = $this->calculateActualPreviousBalance($client->id, $reading->reading_for_month);

                $reading->current_meter = $value;
                $kilowattPrice = $client->user->kilowattPrice->price ?? 0;
                $categoryPrice = $client->meterCategory->price ?? 0;
                $consumption = $value - $reading->previous_meter;
                
                $reading->amount = ($consumption * $kilowattPrice) + $categoryPrice;
                $reading->previous_balance = $actualPreviousBalance;
                
                // Calculate remaining_amount correctly considering payments and actual previous balance
                $newTotalDue = $reading->amount + $reading->maintenance_cost + $actualPreviousBalance;
                $reading->remaining_amount = $newTotalDue - $totalPaid;
                $reading->reading_date = now(); 
                
                $reading->save(); 

                $this->savedReadings[$readingId] = true;
                
                $this->setAlert('تم تحديث قراءة العداد للمشترك.', 'success');
            }

            if ($moveFocus === 'next') {
                $this->focusMeterId = $this->getNextMeterId($readingId);
            } elseif ($moveFocus === 'prev') {
                $this->focusMeterId = $this->getPrevMeterId($readingId);
            }
            
            // Reload readings to reflect changes
            $this->loadAllReadings();

        } catch (AuthorizationException $e) {
            $this->setAlert('ليس لديك صلاحية لتحديث قراءة العداد هذه', 'danger');
        } catch (Exception $e) {
            $this->setAlert('حدث خطأ أثناء تحديث قراءة العداد.', 'danger');
        }
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
        return $previousReading ? $previousReading->remaining_amount : 0;
    }

    public function handleEnterKey($readingId, $value)
    {
        $this->updateCurrentMeter($readingId, $value, 'next');
    }

    public function handleArrowDown($readingId, $value)
    {
        $this->updateCurrentMeter($readingId, $value, 'next');
    }

    public function handleArrowUp($readingId, $value)
    {
        $this->updateCurrentMeter($readingId, $value, 'prev');
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