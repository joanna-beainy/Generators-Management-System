<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\MeterReading;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class MeterReadings extends Component
{
    public $search = '';

    public function loadMeterReadings()
    {
        $readingMonth = Carbon::now()->day <= 3
            ? Carbon::now()->subMonth()->startOfMonth()
            : Carbon::now()->startOfMonth();

        $query = MeterReading::with('client.MeterCategory', 'client.user.kilowattPrice')
            ->whereHas('client', function($q) {
                $q->where('user_id', Auth::id());
                
                if (!empty(trim($this->search))) {
                    $q->where(function ($query) {
                        $searchTerms = explode(' ', trim($this->search));
                        $termCount = count($searchTerms);
                        
                        // Single term search - could be ID or name
                        if ($termCount === 1) {
                            $term = $searchTerms[0];
                            $query->where('id', $term)
                                ->orWhere('first_name', 'like', "%{$term}%");
                        }
                        // Multiple terms - name search only (not ID)
                        else {
                            // Two terms - first term in first_name, second term in father_name or last_name
                            if ($termCount === 2) {
                                $query->where('first_name', 'like', "%{$searchTerms[0]}%")
                                    ->where(function ($q) use ($searchTerms) {
                                        $q->where('father_name', 'like', "%{$searchTerms[1]}%")
                                            ->orWhere('last_name', 'like', "%{$searchTerms[1]}%");
                                    });
                            }
                            // Three terms - exact match for first_name, father_name, last_name
                            elseif ($termCount === 3) {
                                $query->where('first_name', 'like', "%{$searchTerms[0]}%")
                                    ->where('father_name', 'like', "%{$searchTerms[1]}%")
                                    ->where('last_name', 'like', "%{$searchTerms[2]}%");
                            }
                        }
                    });
                }
            })
            ->forMonth($readingMonth)
            ->orderBy('client_id')
            ->get();

        return $query;
    }

    public function updateCurrentMeter($readingId, $value, $moveFocus = null)
    {
        $reading = MeterReading::with('client.MeterCategory', 'client.user.kilowattPrice')->findOrFail($readingId);
        $client = $reading->client;

        $this->authorize('update', $reading);

        $value = (int) $value;

        if ($value < $reading->previous_meter) {
            session()->flash("error_{$readingId}", 'العداد الحالي يجب أن يكون أكبر من العداد السابق.');
            return;
        }

        $kilowattPrice = $client->user->kilowattPrice->price;
        $categoryPrice = $client->MeterCategory->price;
        $newAmount = ($value - $reading->previous_meter) * $kilowattPrice + $categoryPrice;

        $reading->amount = $newAmount;
        $reading->current_meter = $value;
        $reading->reading_date = Carbon::now();
        $reading->remaining_amount = $reading->amount + $reading->maintenance_cost;

        $wasFirstEntry = is_null($reading->reading_date);

        $nextMonth = Carbon::parse($reading->reading_for_month)->addMonth()->startOfMonth();
        $nextReading = MeterReading::where('client_id', $client->id)
            ->where('reading_for_month', $nextMonth)
            ->first();

        if ($nextReading) {
            $nextReading->previous_meter = $value;
            $nextReading->save();
        } elseif ($wasFirstEntry) {
            MeterReading::create([
                'client_id' => $client->id,
                'previous_meter' => $value,
                'current_meter' => 0,
                'amount' => 0,
                'remaining_amount' => 0,
                'maintenance_cost' => 0,
                'reading_for_month' => $nextMonth,
                'reading_date' => null,
                'status' => 'unpaid',
            ]);
        }

        $reading->updateStatus();
        session()->forget("error_{$readingId}");
        session()->flash("saved_{$readingId}", true);

        if ($moveFocus === 'next') {
            $this->dispatch('focus-next-meter', currentId: $readingId);
        } elseif ($moveFocus === 'prev') {
            $this->dispatch('focus-prev-meter', currentId: $readingId);
        }
    }

    public function updateMaintenanceCost($readingId, $value)
    {
        $reading = MeterReading::findOrFail($readingId);
        $this->authorize('update', $reading);

        $value = (float) $value;
        $reading->maintenance_cost = $value;
        $reading->remaining_amount = $reading->amount + $value;
        $reading->updateStatus();

        session()->flash("saved_{$readingId}", true);
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

    public function checkAndFocusNext($readingId)
    {
        if (session()->has("error_{$readingId}")) {
            return;
        }
        $this->dispatch('focus-next-meter', currentId: $readingId);
    }

    public function checkAndFocusPrev($readingId)
    {
        if (session()->has("error_{$readingId}")) {
            return;
        }
        $this->dispatch('focus-prev-meter', currentId: $readingId);
    }

    public function render()
    {
        $readings = $this->loadMeterReadings();
        
        return view('livewire.meter-readings', [
            'readings' => $readings
        ]);
    }
}