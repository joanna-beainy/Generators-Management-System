<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\MeterReading;
use App\Models\Client;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Exception;

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
            // Check if user can view meter readings
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
            // Get available years from meter readings
            $this->years = MeterReading::whereHas('client', function($query) {
                    $query->where('user_id', Auth::id());
                })
                ->whereNotNull('reading_date')
                ->selectRaw('YEAR(reading_for_month) as year')
                ->distinct()
                ->orderBy('year', 'desc')
                ->pluck('year')
                ->toArray();

            // If no readings found, set current year
            if (empty($this->years)) {
                $this->years = [Carbon::now()->year];
            }

            // Set default to latest available month
            $latestReading = MeterReading::whereHas('client', function($query) {
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

            // Initialize months
            $this->months = [
                '1' => 'كانون الثاني',
                '2' => 'شباط',
                '3' => 'آذار',
                '4' => 'نيسان',
                '5' => 'أيار',
                '6' => 'حزيران',
                '7' => 'تموز',
                '8' => 'آب',
                '9' => 'أيلول',
                '10' => 'تشرين الأول',
                '11' => 'تشرين الثاني',
                '12' => 'كانون الأول',
            ];

        } catch (Exception $e) {
            $this->setAlert('حدث خطأ أثناء تهيئة الفلاتر', 'danger');
        }
    }

    public function loadReadings()
    {
        try {
            $this->readings = MeterReading::with('client')
                ->whereHas('client', function($query) {
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
        $regularClients = $this->readings->filter(fn($r) => !$r->client->is_offered);
        
        return [
            'total_consumption' => $this->readings->sum('consumption'),
            'total_amount' => $regularClients->sum('amount'),
            'total_previous_balance' => $regularClients->sum('previous_balance'),
            'total_maintenance_cost' => $regularClients->sum('maintenance_cost'),
            'total_due' => $regularClients->sum('total_due'),
        ];
    }

    public function getArabicMonthName()
    {
        $months = [
            1 => 'كانون الثاني', 2 => 'شباط', 3 => 'آذار', 4 => 'نيسان',
            5 => 'أيار', 6 => 'حزيران', 7 => 'تموز', 8 => 'آب',
            9 => 'أيلول', 10 => 'تشرين الأول', 11 => 'تشرين الثاني', 12 => 'كانون الأول',
        ];
        
        return $months[$this->selectedMonth] . ' ' . $this->selectedYear;
    }

    public function render()
    {
        $statistics = $this->getStatistics();
        $arabicMonthName = $this->getArabicMonthName();
        
        return view('livewire.monthly-meter-readings-report', [
            'statistics' => $statistics,
            'arabicMonthName' => $arabicMonthName,
        ]);
    }
}