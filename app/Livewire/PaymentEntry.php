<?php

namespace App\Livewire;

use Exception;
use App\Models\Client;
use App\Models\Payment;
use Livewire\Component;
use App\Models\ExchangeRate;
use App\Models\MeterReading;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;

class PaymentEntry extends Component
{
    public $search = '';
    public $selectedClientId = null;
    public $amount = '';
    public $discount = '';
    public $alertMessage = null;
    public $alertType = null;
    public $clients;
    public $showConfirmationModal = false;
    public $pendingPaymentData = null;

    protected $rules = [
        'selectedClientId' => 'required|exists:clients,id',
        'amount' => 'required|numeric|min:0.01',
        'discount' => 'nullable|numeric|min:0',
    ];

    protected $messages = [
        'selectedClientId.required' => 'يرجى اختيار المشترك',
        'amount.required' => 'يرجى ادخال المبلغ المدفوع',
        'amount.min' => 'المبلغ يجب أن يكون أكبر من الصفر',
        'amount.numeric' => 'المبلغ يجب أن يكون رقمًا صالحًا',
        'discount.min' => 'الخصم يجب أن يكون صفر على الأقل',
        'discount.numeric' => 'الخصم يجب أن يكون رقمًا صالحًا',
    ];

    protected $listeners = ['resetPaymentEntryFilters' => 'resetFilters'];

    private function setAlert($message, $type = 'success')
    {
        $this->alertMessage = $message;
        $this->alertType = $type;
    }

    // Get exchange rate from database
    public function getExchangeRateProperty()
    {
        return ExchangeRate::getCurrentRate(Auth::id());
    }

    // Computed properties for reactive calculations
    public function getAmountLbpProperty()
    {
        $amount = $this->amount ? (float)$this->amount * $this->exchangeRate : 0;
        return number_format($amount, 0);
    }

    public function getDiscountLbpProperty()
    {
        $discount = $this->discount ? (float)$this->discount * $this->exchangeRate : 0;
        return number_format($discount, 0);
    }

    public function getTotalUsdProperty()
    {
        $amount = $this->amount ? (float)$this->amount : 0;
        $discount = $this->discount ? (float)$this->discount : 0;
        return $amount + $discount;
    }

    public function getTotalLbpProperty()
    {
        $total = $this->total_usd * $this->exchangeRate;
        return number_format($total, 0);
    }

    // Get current user info
    public function getUserProperty()
    {
        return Auth::user();
    }

    public function mount()
    {
        $this->clients = collect();
        $this->loadClients();
    }

    public function loadClients()
    {
        $this->clients = Client::where('user_id', Auth::id())
            ->where('is_offered', false)
            ->when($this->search, function ($query, $search) {
                $query->search($search);
            })
            ->orderBy('id')
            ->get()
            ->map(function ($client) {
                $client->current_remaining_usd = $client->total_remaining_amount;
                $client->current_remaining_lbp = $client->total_remaining_amount * $this->exchangeRate;
                return $client;
            });
    }

    public function handleSearch()
    {
        $this->loadClients();
        $this->clearAlert();
        
        // Auto-select if only one result
        if ($this->clients->count() === 1) {
            $this->selectedClientId = $this->clients->first()->id;
        } else {
            $this->selectedClientId = null;
        }
    }

    public function updatedSelectedClientId($value)
    {
        if ($value) {
            $this->resetValidation();
            $this->amount = '';
            $this->discount = '';
            $this->clearAlert();
            $this->loadClients();
        }
    }

    public function updatedAmount()
    {
        $this->clearAlert();
    }

    public function updatedDiscount()
    {
        $this->clearAlert();
    }

    public function clearAlert()
    {
        $this->alertMessage = null;
        $this->alertType = null;
    }

    public function save()
    {
        $this->validate();

        try {
            // Check authorization using Policy
            $this->authorize('create', [Payment::class, $this->selectedClientId]);

            $client = Client::find($this->selectedClientId);
            
            if (!$client) {
                $this->setAlert('المشترك المحدد غير موجود', 'danger');
                return;
            }

            // Prevent payments for offered clients
            if ($client->is_offered) {
                $this->setAlert('لا يمكن تسجيل دفعة للمشتركين المعفيين', 'danger');
                return;
            }

            $currentRemainingUSD = $client->total_remaining_amount;

            // Show confirmation for overpayment
            if ($this->total_usd > $currentRemainingUSD && $currentRemainingUSD > 0) {
                $overpaymentAmount = $this->total_usd - $currentRemainingUSD;
                $this->pendingPaymentData = [
                    'client_id' => $this->selectedClientId,
                    'amount' => (float)$this->amount,
                    'discount' => (float)$this->discount,
                    'total_payment' => $this->total_usd,
                    'current_remaining' => $currentRemainingUSD,
                    'overpayment' => $overpaymentAmount,
                ];
                $this->showConfirmationModal = true;
                return;
            }

            $this->processPayment($this->selectedClientId, (float)$this->amount, (float)$this->discount);

        } catch (ValidationException $e) {
            throw $e;
        } catch (AuthorizationException $e) {
            $this->setAlert('ليس لديك صلاحية لإضافة دفعة لهذا المشترك', 'danger');
        } catch (Exception $e) {
            $this->setAlert('حدث خطأ أثناء حفظ البيانات', 'danger');
        }
    }

    public function confirmPayment()
    {
        if (!$this->pendingPaymentData) return;

        $this->processPayment(
            $this->pendingPaymentData['client_id'],
            $this->pendingPaymentData['amount'],
            $this->pendingPaymentData['discount']
        );
        
        $this->showConfirmationModal = false;
        $this->pendingPaymentData = null;
    }

    public function cancelPayment()
    {
        $this->showConfirmationModal = false;
        $this->pendingPaymentData = null;
    }

    public function resetFilters()
    {
        $this->reset(['search', 'selectedClientId', 'amount', 'discount']);
        $this->clearAlert();
        $this->loadClients();
    }
    
    private function processPayment($clientId, $amount, $discount)
    {
        try {
            $latestCompletedReading = MeterReading::latestCompletedForClient($clientId);
            
            if (!$latestCompletedReading) {
                $this->setAlert('لا يوجد فاتورة سابقة للمشترك يمكن السداد لها', 'danger');
                return;
            }

            $payment = Payment::create([
                'client_id' => $clientId,
                'meter_reading_id' => $latestCompletedReading->id,
                'amount' => $amount,
                'discount' => $discount,
                'paid_at' => now(),
            ]);

            $payment->applyToReading();
            
            $this->reset(['search', 'selectedClientId', 'amount', 'discount']);
            $this->loadClients();
            
            // Emit event to show receipt modal
            $this->dispatch('showReceipt', clientId: $clientId);
            
            $successMessage = "تم تسجيل الدفعة بنجاح! المبلغ: {$amount} $ + خصم: {$discount} $";
            $this->setAlert($successMessage, 'success');

        } catch (Exception $e) {
            $this->setAlert('حدث خطأ أثناء تسجيل الدفعة', 'danger');
        }
    }

    public function getSelectedClient()
    {
        if (!$this->selectedClientId) {
            return null;
        }
        
        $client = Client::find($this->selectedClientId);
        if ($client) {
            $client->current_remaining_usd = $client->total_remaining_amount;
            $client->current_remaining_lbp = $client->total_remaining_amount * $this->exchangeRate;
        }
        
        return $client;
    }

    public function render()
    {
        return view('livewire.payment-entry', [
            'selectedClient' => $this->getSelectedClient(),
        ]);
    }
}