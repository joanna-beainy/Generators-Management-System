<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\MeterReading;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\MeterReadingsExport;

class MeterReadings extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;
    public $readings = [];

    protected $paginationTheme = 'bootstrap';

    protected $listeners = [
        'blurCurrentMeter' => 'updateCurrentMeter',
        'blurMaintenanceCost' => 'updateMaintenanceCost',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function loadMeterReadings()
    {
        $readingMonth = Carbon::now()->day <= 5
            ? Carbon::now()->subMonth()->startOfMonth()
            : Carbon::now()->startOfMonth();

        return MeterReading::with('client.MeterCategory', 'client.user.kilowatt', 'payments')
            ->whereHas('client', fn($q) => $q->where('user_id', Auth::id())
                ->where(function ($q) {
                    $q->where('first_name', 'like', "%{$this->search}%")
                      ->orWhere('father_name', 'like', "%{$this->search}%")
                      ->orWhere('last_name', 'like', "%{$this->search}%");
                }))
            ->forMonth($readingMonth)
            ->paginate($this->perPage);
    }

    public function updateCurrentMeter($readingId, $value)
    {
        $reading = MeterReading::with('client.MeterCategory', 'client.user.kilowatt')->findOrFail($readingId);
        $client = $reading->client;

        $this->authorize('update', $reading);

        $value = (int) $value;

        if ($value < $reading->previous_meter) {
            session()->flash('error', 'العداد الحالي يجب أن يكون أكبر من العداد السابق.');
            return;
        }

        $kilowattPrice = $client->user->kilowatt->price;
        $categoryPrice = $client->MeterCategory->price;
        $newAmount = ($value - $reading->previous_meter) * $kilowattPrice + $categoryPrice;

        $reading->amount = $newAmount;
        $reading->current_meter = $value;
        $reading->reading_date = Carbon::now();

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
                'maintenance_cost' => 0,
                'reading_for_month' => $nextMonth,
                'reading_date' => null,
                'status' => 'unpaid',
            ]);
        }

        // Recalculate status dynamically
        $reading->status = $reading->remaining_amount <= 0 ? 'paid' : 'unpaid';
        $reading->save();

        session()->flash("saved_{$readingId}", true);
    }

    public function updateMaintenanceCost($readingId, $value)
    {
        $reading = MeterReading::findOrFail($readingId);
        $this->authorize('update', $reading);

        $value = (int) $value;
        $reading->maintenance_cost = $value;
        $reading->status = $reading->remaining_amount <= 0 ? 'paid' : 'unpaid';
        $reading->save();

        session()->flash("saved_{$readingId}", true);
    }

    public function export()
    {
        return Excel::download(new MeterReadingsExport($this->search), 'meter_readings.xlsx');
    }

    public function render()
    {
        $readings = $this->loadMeterReadings();
        return view('livewire.meter-readings', compact('readings'));
    }
}
