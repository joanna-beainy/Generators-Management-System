<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\MeterReading;
use App\Models\Client;
use Illuminate\Support\Facades\Auth;

class MeterReadings extends Component
{
    public $search = '';
    public $selectedClientId = null;
    public $savedReadings = [];
    public $focusMeterId = null;

    public function loadMeterReadings()
    {
        $userId = Auth::id();

        // Subquery to get the latest reading ID per client
        $latestReadingIds = MeterReading::selectRaw('MAX(id) as id')
            ->whereHas('client', fn ($q) => 
                $q->where('user_id', $userId)
                ->active()
            )
            ->groupBy('client_id');

        $query = MeterReading::with(['client.meterCategory', 'client.user.kilowattPrice'])
            ->whereIn('id', $latestReadingIds);

        // Apply filters
        if ($this->selectedClientId) {
            $query->where('client_id', $this->selectedClientId);
        } elseif (trim($this->search) !== '') {
            $query->whereHas('client', fn ($q) => $q->search($this->search));
        }

        return $query->orderBy('client_id')->get();
    }


    public function loadClientsForSearch()
    {
        return Client::where('user_id', Auth::id())
            ->active()
            ->search($this->search)
            ->orderBy('id')
            ->get();
    }

    public function handleSearch()
    {
        $this->resetValidation();
        
        // Auto-select if only one result
        $clients = $this->loadClientsForSearch();
        if ($clients->count() === 1) {
            $this->selectedClientId = $clients->first()->id;
        } else {
            $this->selectedClientId = null;
        }
    }

    public function updatedSelectedClientId($value)
    {
        if ($value) {
            $this->resetValidation();
        }
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->selectedClientId = null;
        $this->resetValidation();
    }

    public function updateCurrentMeter($readingId, $value, $moveFocus = null)
    {
        $reading = MeterReading::with(['client.meterCategory', 'client.user.kilowattPrice', 'payments'])->findOrFail($readingId);
        $client = $reading->client;

        $this->authorize('update', $reading);

        $value = (int) $value;
        $hasPayments = $reading->payments->count() > 0;
        $totalPaid = $reading->payments->sum('amount');

        if ($value < $reading->previous_meter) {
            session()->flash("error_{$readingId}", 'العداد الحالي يجب أن يكون أكبر من العداد السابق.');
            return;
        }

        // If payments exist, show warning but allow correction
        if ($hasPayments && $reading->current_meter !== $value) {
            $originalTotalDue = $reading->total_due;
            
            // Calculate new total due after correction
            $kilowattPrice = $client->user->kilowattPrice->price ?? 0;
            $categoryPrice = $client->meterCategory->price ?? 0;
            $newConsumption = $value - $reading->previous_meter;
            $newAmount = ($newConsumption * $kilowattPrice) + $categoryPrice;
            $newTotalDue = $newAmount + $reading->maintenance_cost + $reading->previous_balance;
            
            $balanceChange = $newTotalDue - $originalTotalDue;
            
            if ($balanceChange > 0) {
                session()->flash("warning_{$readingId}", 
                    "⚠️ تحذير: العميل سدد {$totalPaid} د.أ بالفعل. التصحيح سيزيد المستحق بمقدار {$balanceChange} د.أ."
                );
            } else {
                session()->flash("warning_{$readingId}", 
                    "ℹ️ العميل سدد {$totalPaid} د.أ. التصحيح سيقلل المستحق بمقدار " . abs($balanceChange) . " د.أ."
                );
            }
        }

        // Check if client is offered
        if ($client->is_offered) {
            if ($reading->current_meter !== $value) {
                $reading->current_meter = $value;
                $reading->amount = 0;
                $reading->maintenance_cost = 0;
                $reading->remaining_amount = 0;
                $reading->reading_date = now();
                $reading->save();

                $this->savedReadings[$readingId] = true;
            }
        } else {
            if ($reading->current_meter !== $value) {
                $reading->current_meter = $value;
                $kilowattPrice = $client->user->kilowattPrice->price ?? 0;
                $categoryPrice = $client->meterCategory->price ?? 0;
                $consumption = $value - $reading->previous_meter;
                
                $reading->amount = ($consumption * $kilowattPrice) + $categoryPrice;
                
                // ✅ FIXED: Calculate remaining_amount correctly considering payments
                $newTotalDue = $reading->amount + $reading->maintenance_cost + $reading->previous_balance;
                $reading->remaining_amount = $newTotalDue - $totalPaid;

                $reading->reading_date = now();
                $reading->save();

                $this->savedReadings[$readingId] = true;
                
                // Show appropriate message
                if ($hasPayments) {
                    $newRemaining = $reading->remaining_amount;
                    session()->flash("success_{$readingId}", 
                        "✅ تم تصحيح العداد. الرصيد المتبقي بعد السداد: {$newRemaining} د.أ"
                    );
                } else {
                    session()->flash("success_{$readingId}", 
                        "✅ تم تحديث قراءة العداد. الاستهلاك: {$consumption} ك.و"
                    );
                }
            }
        }

        session()->forget("error_{$readingId}");

        if ($moveFocus === 'next') {
            $this->focusMeterId = $this->getNextMeterId($readingId);
        } elseif ($moveFocus === 'prev') {
            $this->focusMeterId = $this->getPrevMeterId($readingId);
        }
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
        $readings = $this->loadMeterReadings();
        $ids = $readings->pluck('id')->values();
        $index = $ids->search($currentId);
        return $ids[$index + 1] ?? null;
    }

    public function getPrevMeterId($currentId)
    {
        $readings = $this->loadMeterReadings();
        $ids = $readings->pluck('id')->values();
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
        $readings = $this->loadMeterReadings();
        $clients = $this->loadClientsForSearch();
        $latestMonth = $readings->first()?->reading_for_month;
        $arabicMonthName = $latestMonth ? $this->getArabicMonthName($latestMonth) : null;

        return view('livewire.meter-readings', [
            'readings' => $readings,
            'clients' => $clients,
            'arabicMonthName' => $arabicMonthName
        ]);
    }
}