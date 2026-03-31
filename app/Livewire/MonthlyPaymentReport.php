<?php

namespace App\Livewire;

use App\Models\Payment;
use App\Support\ArabicMonth;
use Carbon\Carbon;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

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

            $this->years = Payment::whereHas('client', function ($query) {
                $query->where('user_id', Auth::id());
            })
                ->selectRaw("strftime('%Y', paid_at) as year")
                ->distinct()
                ->orderBy('year', 'desc')
                ->pluck('year')
                ->toArray();

            if (empty($this->years)) {
                $this->years = [$this->selectedYear];
            }

            $this->months = ArabicMonth::all(true);
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
