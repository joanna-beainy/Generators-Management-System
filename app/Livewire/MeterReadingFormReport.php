<?php

namespace App\Livewire;

use App\Models\Client;
use App\Models\MeterReading;
use App\Support\ArabicMonth;
use Carbon\Carbon;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

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

            $this->arabicMonthName = ArabicMonth::name($this->selectedMonth);

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
