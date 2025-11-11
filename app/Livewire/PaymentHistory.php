<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Client;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Exception;

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
            // Only load client if it belongs to the current user
            $this->client = Client::where('user_id', Auth::id())
                ->find($this->clientId);

            if (!$this->client) {
                $this->setAlert('المشترك غير موجود أو ليس لديك صلاحية للوصول إليه', 'danger');
                return;
            }

            // Check if user can view payments for this client
            $this->authorize('viewAny', Payment::class);

        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
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

            // Set current year as default
            $this->selectedYear = Carbon::now()->year;
            
            // Get available years from payments - only for this user's clients
            $this->years = Payment::whereHas('client', function($query) {
                    $query->where('user_id', Auth::id());
                })
                ->where('client_id', $this->clientId)
                ->selectRaw('YEAR(paid_at) as year')
                ->distinct()
                ->orderBy('year', 'desc')
                ->pluck('year')
                ->toArray();

            // If no payments found, set current year
            if (empty($this->years)) {
                $this->years = [$this->selectedYear];
            }

            // Initialize months
            $this->months = [
                '' => 'كل الأشهر',
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

            $this->selectedMonth = ''; // All months by default
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

            // Load payments only for this client and ensure client belongs to user
            $this->payments = Payment::with(['meterReading'])
                ->whereHas('client', function($query) {
                    $query->where('user_id', Auth::id());
                })
                ->where('client_id', $this->clientId)
                ->when($this->selectedYear, function($query) {
                    $query->whereYear('paid_at', $this->selectedYear);
                })
                ->when($this->selectedMonth, function($query) {
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

    public function render()
    {
        return view('livewire.payment-history');
    }
}