<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Exception;

class MonthlyPaymentReport extends Component
{
    public $payments;
    public $selectedYear;
    public $selectedMonth;
    public $years = [];
    public $months = [];
    public $alertMessage = null;
    public $alertType = null;

    public function mount()
    {
        try {
            // Check if user can view payments
            $this->authorize('viewAny', Payment::class);

            $this->initializeFilters();
            $this->loadPayments();

        } catch (AuthorizationException $e) {
            $this->setAlert('ليس لديك صلاحية لعرض تقارير الدفعات', 'danger');
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
            $this->selectedYear = Carbon::now()->year;
            $this->selectedMonth = Carbon::now()->month;

            // Get available years from the user's clients' payments
            $this->years = Payment::whereHas('client', function ($query) {
                    $query->where('user_id', Auth::id());
                })
                ->selectRaw('YEAR(paid_at) as year')
                ->distinct()
                ->orderBy('year', 'desc')
                ->pluck('year')
                ->toArray();

            if (empty($this->years)) {
                $this->years = [$this->selectedYear];
            }

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

    public function loadPayments()
    {
        try {
            $this->payments = Payment::with(['client', 'meterReading'])
                ->whereHas('client', function ($query) {
                    $query->where('user_id', Auth::id());
                })
                ->when($this->selectedYear, function ($query) {
                    $query->whereYear('paid_at', $this->selectedYear);
                })
                ->when($this->selectedMonth, function ($query) {
                    $query->whereMonth('paid_at', $this->selectedMonth);
                })
                ->orderBy('paid_at', 'desc')
                ->get();

        } catch (Exception $e) {
            $this->setAlert('حدث خطأ أثناء تحميل بيانات الدفعات', 'danger');
            $this->payments = collect();
        }
    }

    public function updatedSelectedYear()
    {
        $this->clearAlert();
        $this->loadPayments();
    }

    public function updatedSelectedMonth()
    {
        $this->clearAlert();
        $this->loadPayments();
    }

    public function render()
    {   
        return view('livewire.monthly-payment-report');
    }
}