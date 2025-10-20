<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Client;
use App\Models\MeterReading;
use App\Models\Payment;
use App\Models\ExchangeRate;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ReceiptModal extends Component
{
    public $show = false;
    public $receiptData = [];
    public $receiptsData = [];
    public $mode = 'single'; // 'single' or 'bulk'

    public $search = ''; 
    public $unpaidClients = [];
    public $selectedClientId = null; 
    public $errorMessage = null;

    protected $listeners = [
        'showReceipt' => 'openSingleModal',
        'showBulkReceipts' => 'openBulkModal',
    ];

    public function mount()
    {
        $this->unpaidClients = collect();
    }

    /** ✅ Get the current exchange rate dynamically */
    public function getExchangeRateProperty()
    {
        return ExchangeRate::getCurrentRate(Auth::id());
    }

    /** ✅ Open single receipt modal after payment */
    public function openSingleModal($clientId)
    {
        $payment = Payment::where('client_id', $clientId)
            ->orderByDesc('paid_at')
            ->first();

        if (!$payment) {
            return;
        }

        $this->receiptData = $this->generateReceiptData($clientId, $payment);
        $this->mode = 'single';
        $this->show = true;    
    }

    /** ✅ Open bulk receipts modal */
    public function openBulkModal()
    {
        $this->reset(['search', 'selectedClientId']);
        $this->mode = 'bulk';
        $this->loadUnpaidClients();
        $this->loadBulkReceipts();
        $this->show = true;
    }

    /** ✅ Handle search with Enter key */
    public function handleSearch()
    {
        $this->loadUnpaidClients();
        $this->loadBulkReceipts();

        // ✅ Auto-select if only one result
        if ($this->unpaidClients->count() === 1) {
            $this->selectedClientId = $this->unpaidClients->first()->id;
            $this->loadBulkReceipts(); 
        } else {
            $this->selectedClientId = null;
        }
    }

    public function updatedSelectedClientId()
    {
        $this->loadBulkReceipts();
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->selectedClientId = null;
        $this->loadUnpaidClients();
        $this->loadBulkReceipts();
    }

    /** ✅ Load unpaid clients with optional search - direct implementation */
    public function loadUnpaidClients()
    {
        $unpaidClientIds = MeterReading::whereIn('id', function ($query) {
            $query->selectRaw('MAX(id)')
                ->from('meter_readings')
                ->whereIn('client_id', function ($sub) {
                    $sub->select('id')
                        ->from('clients')
                        ->where('user_id', Auth::id())
                        ->where('is_offered', false);
                })
                ->groupBy('client_id');
        })
        ->where('remaining_amount', '>', 0)
        ->pluck('client_id');

        $this->unpaidClients = Client::whereIn('id', $unpaidClientIds)
            ->where('is_offered', false)
            ->with(['meterCategory', 'user.kilowattPrice', 'user.phoneNumbers'])
            ->when($this->search, function ($query, $term) {
                $query->search($term);
            })
            ->get();
    }

    /** ✅ Load receipts for all or filtered unpaid clients */
    private function loadBulkReceipts()
    {
        $clients = collect($this->unpaidClients);

        if ($this->selectedClientId) {
            $clients = $clients->filter(function ($client) {
                return $client->id == $this->selectedClientId;
            });
        }

        $this->receiptsData = $clients->map(function ($client) {
            $latestReading = MeterReading::latestForClient($client->id);
            if (!$latestReading) return null;

            $kilowattPrice = $client->user->kilowattPrice->price ?? 0;
            $consumptionAmount = $latestReading->consumption * $kilowattPrice;
            $readingMonth = $latestReading->reading_for_month ?? now();
            $arabicMonth = $this->getArabicMonthName($readingMonth);

            return [
                'client_id' => $client->id,
                'client_full_name' => $client->full_name,
                'payment_date' => now()->format('d/m/Y'),
                'reading_for_month_arabic' => $arabicMonth,
                'kilowatt_price' => $kilowattPrice,
                'meter_category' => $client->meterCategory->category ?? 'N/A',
                'meter_category_price' => $client->meterCategory->price ?? 0,
                'previous_meter' => $latestReading->previous_meter ?? 0,
                'current_meter' => $latestReading->current_meter ?? 0,
                'consumption' => $latestReading->consumption ?? 0,
                'consumption_amount' => $consumptionAmount,
                'maintenance_cost' => $latestReading->maintenance_cost ?? 0,
                'previous_balance' => $latestReading->previous_balance ?? 0,
                'total_due' => $latestReading->total_due ?? 0,
                'total_due_lbp' => $latestReading ? $latestReading->total_due * $this->exchangeRate : 0,
                'amount_paid' => 0,
                'remaining_after_payment' => $latestReading->remaining_amount ?? 0,
                'payment_id' => 'BULK-' . $client->id . '-' . time(),
                'user_name' => $client->user->name,
                'user_phones' => $client->user->phoneNumbers->pluck('phone_number')->implode(' - '),
            ];
        })->filter()->values()->toArray();
    }

    /** ✅ Generate data for a single receipt */
    private function generateReceiptData($clientId, $payment)
    {
        $client = Client::with(['meterCategory', 'user.kilowattPrice', 'user.phoneNumbers'])->find($clientId);
        if (!$client) return [];

        $latestReading = MeterReading::latestForClient($clientId);
        $kilowattPrice = $client->user->kilowattPrice->price ?? 0;
        $consumptionAmount = $latestReading ? $latestReading->consumption * $kilowattPrice : 0;
        $readingMonth = $latestReading ? $latestReading->reading_for_month : now();
        $arabicMonth = $this->getArabicMonthName($readingMonth);
        $amountPaid = $payment->amount + $payment->discount;
        $remainingAfterPayment = $latestReading->remaining_amount ?? 0;

        return [
            'client_id' => $client->id,
            'client_full_name' => $client->full_name,
            'payment_date' => $payment->paid_at->format('d/m/Y'),
            'reading_for_month_arabic' => $arabicMonth,
            'kilowatt_price' => $kilowattPrice,
            'meter_category' => $client->meterCategory->category ?? 'N/A',
            'meter_category_price' => $client->meterCategory->price ?? 0,
            'previous_meter' => $latestReading?->previous_meter ?? 0,
            'current_meter' => $latestReading?->current_meter ?? 0,
            'consumption' => $latestReading?->consumption ?? 0,
            'consumption_amount' => $consumptionAmount,
            'maintenance_cost' => $latestReading?->maintenance_cost ?? 0,
            'previous_balance' => $latestReading?->previous_balance ?? 0,
            'total_due' => $latestReading?->total_due ?? 0,
            'total_due_lbp' => $latestReading ? $latestReading->total_due * $this->exchangeRate : 0,
            'amount_paid' => $amountPaid,
            'remaining_after_payment' => $remainingAfterPayment,
            'payment_id' => $payment->id,
            'user_name' => $client->user->name,
            'user_phones' => $client->user->phoneNumbers->pluck('phone_number')->implode(' - '),
        ];
    }

    /** ✅ Arabic month translation */
    private function getArabicMonthName($date)
    {
        $months = [
            1 => 'كانون الثاني', 2 => 'شباط', 3 => 'آذار', 4 => 'نيسان',
            5 => 'أيار', 6 => 'حزيران', 7 => 'تموز', 8 => 'آب',
            9 => 'أيلول', 10 => 'تشرين الأول', 11 => 'تشرين الثاني', 12 => 'كانون الأول',
        ];
        return $months[$date->month] . ' ' . $date->year;
    }

    public function closeModal()
    {
        $this->show = false;
        $this->receiptData = [];
        $this->receiptsData = [];
        $this->errorMessage = null;
        $this->reset(['search', 'selectedClientId']);
        $this->dispatch('resetPaymentEntryFilters');
    }

    public function render()
    {
        return view('livewire.receipt-modal');
    }
}