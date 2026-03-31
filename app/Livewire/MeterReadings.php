<?php

namespace App\Livewire;

use App\Models\Client;
use App\Models\MeterReading;
use App\Support\ArabicMonth;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class MeterReadings extends Component
{
    public $search = '';
    public $selectedClientId = null;
    public $showSearchResults = false;
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
            $this->authorize('viewAny', MeterReading::class);

            $this->allReadings = MeterReading::latestMonthReadings($userId)
                ->load(['client.meterCategory', 'client.user.kilowattPrice', 'payments']);

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
            return Client::forUser(Auth::id())
                ->active()
                ->search($this->search)
                ->ordered()
                ->get();
        } catch (Exception $e) {
            $this->setAlert('حدث خطأ أثناء تحميل بيانات المشتركين', 'danger');

            return collect();
        }
    }

    public function handleSearch()
    {
        $this->refreshSearchState(true);
    }

    public function updatedSearch()
    {
        $this->refreshSearchState(false);
    }

    public function selectClient($clientId)
    {
        $client = $this->loadClientsForSearch()->firstWhere('id', (int) $clientId);
        if (!$client) {
            return;
        }

        $this->selectedClientId = $client->id;
        $this->showSearchResults = false;
        $this->resetValidation();
        $this->clearAlert();
        $this->fieldErrors = [];
        $this->loadAllReadings();
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
        $this->showSearchResults = false;
        $this->resetValidation();
        $this->clearAlert();
        $this->fieldErrors = [];
        $this->loadAllReadings();
    }

    private function refreshSearchState(bool $allowAutoSelect): void
    {
        $this->selectedClientId = null;
        $this->resetValidation();
        $this->clearAlert();
        $this->fieldErrors = [];
        $this->showSearchResults = filled(trim($this->search));

        $clients = $this->loadClientsForSearch();

        if ($allowAutoSelect && $clients->count() === 1) {
            $this->selectedClientId = $clients->first()->id;
            $this->showSearchResults = false;
        }

        $this->loadAllReadings();
    }

    public function updateCurrentMeter($readingId, $value, $moveFocus = null)
    {
        try {
            $reading = MeterReading::with(['client.meterCategory', 'client.user.kilowattPrice', 'payments'])->findOrFail($readingId);

            $value = (int) $value;
            $this->authorize('update', $reading);

            $this->clearFieldError($readingId);

            if ($value < $reading->previous_meter) {
                $this->setFieldError($readingId, 'العداد الحالي يجب أن يكون أكبر من العداد السابق.');
                return;
            }

            if (is_null($reading->reading_date)) {
                $this->applyMeterUpdate($reading, $value);

                if ($moveFocus === 'next') {
                    $this->focusMeterId = $this->getNextMeterId($readingId);
                } elseif ($moveFocus === 'prev') {
                    $this->focusMeterId = $this->getPrevMeterId($readingId);
                }

                $this->loadAllReadings();
                return;
            }

            if ((int) $reading->current_meter === (int) $value) {
                return;
            }

            $this->dispatchBrowserEvent('show-confirm-modal', [
                'readingId' => $readingId,
                'oldMeter' => (int) $reading->current_meter,
                'value' => $value,
            ]);
        } catch (AuthorizationException $e) {
            $this->setAlert('ليس لديك صلاحية لتحديث قراءة العداد هذه', 'danger');
        } catch (Exception $e) {
            $this->setAlert('حدث خطأ أثناء تحديث قراءة العداد.', 'danger');
        }
    }

    public function confirmMeterUpdate($readingId, $newMeter)
    {
        $reading = MeterReading::findOrFail($readingId);

        $this->authorize('update', $reading);

        $this->applyMeterUpdate($reading, (int) $newMeter);
        $this->loadAllReadings();

        $this->setAlert('تم تحديث قراءة العداد بنجاح.', 'success');
    }

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
            $previousBalance = MeterReading::actualPreviousBalanceForClientBeforeMonth(
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

        if ((int) $client->initial_meter !== $value) {
            $client->update(['initial_meter' => $value]);
        }

        $this->savedReadings[$reading->id] = true;
        $this->clearFieldError($reading->id);
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

    public function render()
    {
        return view('livewire.meter-readings', [
            'clients' => $this->loadClientsForSearch(),
            'arabicMonthName' => $this->allReadings->first()?->reading_for_month
                ? ArabicMonth::label($this->allReadings->first()->reading_for_month)
                : null,
        ]);
    }
}
