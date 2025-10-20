<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Client;
use App\Models\Payment;
use App\Models\ExchangeRate;
use App\Models\MeterReading;
use Illuminate\Support\Facades\Auth;

class PaymentEntry extends Component
{
    public $search = '';
    public $selectedClientId = null;
    public $amount = '';
    public $discount = '';
    public $successMessage = null;
    public $errorMessage = null;
    public $clients;
    public $showConfirmationModal = false;
    public $pendingPaymentData = null;

    protected $rules = [
        'selectedClientId' => 'required|exists:clients,id',
        'amount' => 'required|numeric|min:0.01',
        'discount' => 'nullable|numeric|min:0',
    ];

    protected $messages = [
        'selectedClientId.required' => 'يرجى اختيار العميل',
        'amount.required' => 'المبلغ المدفوع مطلوب',
        'amount.min' => 'المبلغ يجب أن يكون أكبر من الصفر',
    ];

    protected $listeners = ['resetPaymentEntryFilters' => 'resetFilters'];

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
        $this->errorMessage = null;
        
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
            $this->errorMessage = null;
            $this->loadClients();
        }
    }

    public function updatedAmount()
    {
        $this->errorMessage = null;
    }

    public function updatedDiscount()
    {
        $this->errorMessage = null;
    }

    public function save()
    {
        $this->validate();
        $this->errorMessage = null;

        $client = Client::find($this->selectedClientId);
        
        if (!$client) {
            $this->errorMessage = '❌ العميل المحدد غير موجود';
            return;
        }

        $currentRemainingUSD = $client->total_remaining_amount;

        // Check if payment exceeds remaining amount, show confirmation
        if ($this->total_usd > $currentRemainingUSD) {
            $this->pendingPaymentData = [
                'client_id' => $this->selectedClientId,
                'amount' => (float)$this->amount,
                'discount' => (float)$this->discount,
                'total_payment' => $this->total_usd,
                'current_remaining' => $currentRemainingUSD,
                'overpayment' => $this->total_usd - $currentRemainingUSD,
            ];
            $this->showConfirmationModal = true;
            return;
        }

        $this->processPayment($this->selectedClientId, (float)$this->amount, (float)$this->discount);
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
        $this->reset(['search', 'selectedClientId', 'amount', 'discount', 'errorMessage', 'successMessage']);
        $this->loadClients();
    }
    
    private function processPayment($clientId, $amount, $discount)
    {
        try {
            $latestReading = MeterReading::latestForClient($clientId);
            
            if (!$latestReading) {
                $this->errorMessage = '❌ لا يوجد قراءة عداد للعميل';
                return;
            }

            $payment = Payment::create([
                'client_id' => $clientId,
                'meter_reading_id' => $latestReading->id,
                'amount' => $amount,
                'discount' => $discount,
                'paid_at' => now(),
            ]);

            $payment->applyToReading();
            
            $this->reset(['search', 'selectedClientId', 'amount', 'discount', 'errorMessage']);
            $this->loadClients();
            
            // Emit event to show receipt modal
            $this->dispatch('showReceipt', clientId: $clientId);
            
            $successMessage = "✅ تم تسجيل الدفعة بنجاح! المبلغ: {$amount} $ + خصم: {$discount} $";
            $this->successMessage = $successMessage;

        } catch (\Exception $e) {
            $this->errorMessage = '❌ حدث خطأ أثناء تسجيل الدفعة';
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