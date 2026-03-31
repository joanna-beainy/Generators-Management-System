<?php

namespace App\Livewire;

use App\Models\Client;
use App\Models\MeterReading;
use App\Models\Payment;
use App\Support\ArabicMonth;
use Carbon\Carbon;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Native\Desktop\Facades\Alert;

class PaymentHistory extends Component
{
    public $clientId;
    public $client;
    public $payments;
    public $selectedYear;
    public $selectedMonth;
    public $years = [];
    public $months = [];
    public $alertMessage = null;
    public $alertType = null;

    public function mount($clientId = null)
    {
        if ($clientId) {
            $this->clientId = $clientId;
            $this->loadClientData();
            $this->initializeFilters();
            $this->loadPayments();
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

    private function loadClientData()
    {
        try {
            $this->client = Client::where('user_id', Auth::id())
                ->find($this->clientId);

            if (!$this->client) {
                $this->setAlert('المشترك غير موجود أو ليس لديك صلاحية للوصول إليه', 'danger');
                return;
            }

            $this->authorize('viewAny', Payment::class);
        } catch (AuthorizationException $e) {
            $this->setAlert('ليس لديك صلاحية لعرض دفعات هذا المشترك', 'danger');
            $this->client = null;
        } catch (Exception $e) {
            $this->setAlert('حدث خطأ أثناء تحميل بيانات المشترك', 'danger');
            $this->client = null;
        }
    }

    private function initializeFilters()
    {
        try {
            if (!$this->client) {
                return;
            }

            $defaultYear = $this->selectedYear ?: Carbon::now()->year;

            $this->years = Payment::whereHas('client', function ($query) {
                $query->where('user_id', Auth::id());
            })
                ->withTrashed()
                ->where('client_id', $this->clientId)
                ->selectRaw("strftime('%Y', paid_at) as year")
                ->distinct()
                ->orderBy('year', 'desc')
                ->pluck('year')
                ->toArray();

            if (empty($this->years)) {
                $this->years = [$defaultYear];
            }

            $availableYears = array_map('strval', $this->years);
            $this->selectedYear = in_array((string) $defaultYear, $availableYears, true)
                ? $defaultYear
                : $this->years[0];

            $this->months = ArabicMonth::all(true, true);
            $this->selectedMonth = $this->selectedMonth ?? '';
        } catch (Exception $e) {
            $this->setAlert('حدث خطأ أثناء تهيئة الفلاتر', 'danger');
        }
    }

    public function loadPayments()
    {
        try {
            if (!$this->client) {
                $this->payments = collect();
                return;
            }

            $this->payments = Payment::withTrashed()
                ->with(['meterReading'])
                ->whereHas('client', function ($query) {
                    $query->where('user_id', Auth::id());
                })
                ->where('client_id', $this->clientId)
                ->when($this->selectedYear, function ($query) {
                    $query->whereYear('paid_at', $this->selectedYear);
                })
                ->when($this->selectedMonth, function ($query) {
                    $query->whereMonth('paid_at', $this->selectedMonth);
                })
                ->orderBy('paid_at', 'desc')
                ->get();
        } catch (Exception $e) {
            $this->setAlert('حدث خطأ أثناء تحميل الدفعات', 'danger');
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

    public function confirmDelete($id)
    {
        $buttonIndex = Alert::title('تأكيد الحذف')
            ->buttons(['إلغاء', 'نعم'])
            ->show('هل أنت متأكد من حذف هذه الدفعة؟');

        if ($buttonIndex === 1) {
            $this->deletePayment($id);
        }
    }

    public function deletePayment($paymentId)
    {
        try {
            $payment = Payment::whereHas('client', function ($query) {
                $query->where('user_id', Auth::id());
            })
                ->where('client_id', $this->clientId)
                ->with('meterReading')
                ->findOrFail($paymentId);

            if (!$payment->belongsToLatestCompletedReading()) {
                $this->setAlert('يمكن حذف دفعات آخر قراءة مكتملة فقط.', 'danger');
                return;
            }

            $this->authorize('delete', $payment);

            $payment->deleteWithAutoHandling();

            $this->clearAlert();
            $this->initializeFilters();
            $this->loadPayments();
            $this->setAlert('تم حذف الدفعة بنجاح.', 'success');
        } catch (AuthorizationException $e) {
            $this->setAlert('ليس لديك صلاحية لحذف هذه الدفعة', 'danger');
        } catch (Exception $e) {
            $this->setAlert('حدث خطأ أثناء حذف الدفعة', 'danger');
        }
    }

    public function getLatestCompletedReadingIdProperty(): ?int
    {
        if (!$this->clientId) {
            return null;
        }

        return MeterReading::latestCompletedForClient($this->clientId)?->id;
    }

    public function render()
    {
        return view('livewire.payment-history');
    }
}
