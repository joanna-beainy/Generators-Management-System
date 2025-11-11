<?php

namespace App\Livewire;

use Carbon\Carbon;
use App\Models\Client;
use Livewire\Component;
use App\Models\MeterReading;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Access\AuthorizationException;
use Exception;

class MeterReadingFormReport extends Component
{
    public $clients = [];
    public $arabicMonthName;
    public $selectedMonth;
    public $selectedYear;
    public $alertMessage = null;
    public $alertType = null;

    public function mount()
    {
        try {
            $user = Auth::user();
            
            // Check if user can view meter readings
            $this->authorize('viewAny', MeterReading::class);

            $latestReading = MeterReading::whereHas('client', function ($q) use ($user) {
                $q->where('user_id', $user->id)
                ->where('is_active', true);
            })
            ->orderByDesc('reading_for_month')
            ->first();

            $this->selectedMonth = $latestReading
                ? Carbon::parse($latestReading->reading_for_month)->month
                : Carbon::now()->month;

            $this->selectedYear = $latestReading
                ? Carbon::parse($latestReading->reading_for_month)->year
                : Carbon::now()->year;

            $arabicMonths = [
                1 => 'كانون الثاني', 2 => 'شباط', 3 => 'آذار', 4 => 'نيسان',
                5 => 'أيار', 6 => 'حزيران', 7 => 'تموز', 8 => 'آب',
                9 => 'أيلول', 10 => 'تشرين الأول', 11 => 'تشرين الثاني', 12 => 'كانون الأول'
            ];
            $this->arabicMonthName = $arabicMonths[$this->selectedMonth] ?? '';

            // Pre-process the clients data with previous meter readings
            $this->clients = Client::where('user_id', $user->id)
                ->where('is_active', true)
                ->whereHas('meterReadings')
                ->with(['meterReadings' => function ($query) {
                    $query->latest('reading_for_month')->limit(1);
                }])
                ->get()
                ->map(function ($client) {
                    $latestReading = $client->meterReadings->first();
                    
                    return [
                        'id' => $client->id,
                        'full_name' => $client->full_name,
                        'previous_meter' => $latestReading ? $latestReading->previous_meter : 0,
                    ];
                })
                ->toArray();

        } catch (AuthorizationException $e) {
            $this->setAlert('ليس لديك صلاحية لعرض تقرير قراءات العدادات', 'danger');
        } catch (Exception $e) {
            $this->setAlert('حدث خطأ أثناء تحميل بيانات التقرير', 'danger');
        }
    }

    private function setAlert($message, $type = 'success')
    {
        $this->alertMessage = $message;
        $this->alertType = $type;
    }

    public function render()
    {
        return view('livewire.meter-reading-form-report');
    }
}