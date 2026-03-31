<?php

namespace App\Livewire;

use App\Models\MeterReading;
use App\Support\ArabicMonth;
use Carbon\Carbon;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class MonthlyMeterReadingsReport extends Component
{
    public $selectedYear;
    public $selectedMonth;
    public $readings = [];
    public $years = [];
    public $months = [];
    public $alertMessage = null;
    public $alertType = null;

    protected $listeners = ['refreshReport' => 'loadReadings'];

    public function mount()
    {
        try {
            $this->authorize('viewAny', MeterReading::class);

            $this->initializeFilters();
            $this->loadReadings();
        } catch (AuthorizationException $e) {
            $this->setAlert('ليس لديك صلاحية لعرض تقارير قراءات العدادات', 'danger');
        } catch (Exception $e) {
            $this->setAlert('حدث خطأ أثناء تحميل بيانات التقرير', 'danger');
        }
    }

    private function setAlert($message, $type = 'success')
    {
        $this->alertMessage = $message;
        $this->alertType = $type;
    }

    private function clearAlert()
    {
        $this->alertMessage = null;
        $this->alertType = null;
    }

    private function initializeFilters()
    {
        try {
            $this->years = MeterReading::whereHas('client', function ($query) {
                $query->where('user_id', Auth::id());
            })
                ->whereNotNull('reading_date')
                ->selectRaw("strftime('%Y', reading_for_month) as year")
                ->distinct()
                ->orderBy('year', 'desc')
                ->pluck('year')
                ->toArray();

            if (empty($this->years)) {
                $this->years = [Carbon::now()->year];
            }

            $latestReading = MeterReading::whereHas('client', function ($query) {
                $query->where('user_id', Auth::id());
            })
                ->whereNotNull('reading_date')
                ->orderBy('reading_for_month', 'desc')
                ->first();

            if ($latestReading) {
                $this->selectedYear = $latestReading->reading_for_month->year;
                $this->selectedMonth = $latestReading->reading_for_month->month;
            } else {
                $this->selectedYear = Carbon::now()->year;
                $this->selectedMonth = Carbon::now()->month;
            }

            $this->months = ArabicMonth::all(true);
        } catch (Exception $e) {
            $this->setAlert('حدث خطأ أثناء تهيئة الفلاتر', 'danger');
        }
    }

    public function loadReadings()
    {
        try {
            $this->readings = MeterReading::with('client')
                ->whereHas('client', function ($query) {
                    $query->where('user_id', Auth::id());
                })
                ->whereNotNull('reading_date')
                ->whereYear('reading_for_month', $this->selectedYear)
                ->whereMonth('reading_for_month', $this->selectedMonth)
                ->orderBy('client_id')
                ->get();
        } catch (Exception $e) {
            $this->setAlert('حدث خطأ أثناء تحميل بيانات القراءات الشهرية', 'danger');
        }
    }

    public function updatedSelectedYear()
    {
        $this->clearAlert();
        $this->loadReadings();
    }

    public function updatedSelectedMonth()
    {
        $this->clearAlert();
        $this->loadReadings();
    }

    public function getStatistics()
    {
        $regularClients = $this->readings->filter(fn ($r) => !$r->client->is_offered);
        $offeredClients = $this->readings->filter(fn ($r) => $r->client->is_offered);

        return [
            'consumption_offered' => $offeredClients->sum('consumption'),
            'consumption_regular' => $regularClients->sum('consumption'),
            'total_amount' => $regularClients->sum('amount'),
            'total_previous_balance' => $regularClients->sum('previous_balance'),
            'total_maintenance_cost' => $regularClients->sum('maintenance_cost'),
            'total_due' => $regularClients->sum('total_due'),
        ];
    }

    public function render()
    {
        return view('livewire.monthly-meter-readings-report', [
            'statistics' => $this->getStatistics(),
            'arabicMonthName' => ArabicMonth::label($this->selectedMonth, (int) $this->selectedYear),
        ]);
    }
}
