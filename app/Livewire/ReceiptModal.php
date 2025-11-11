<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Client;
use App\Models\MeterReading;
use App\Models\Payment;
use App\Models\ExchangeRate;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Access\AuthorizationException;
use Exception;

class ReceiptModal extends Component
{
    public $show = false;
    public $receiptData = [];
    public $receiptsData = [];
    public $mode = 'single'; // 'single' or 'bulk'

    public $search = ''; 
    public $unpaidClients = [];
    public $selectedClientId = null; 
    public $alertMessage = null;
    public $alertType = null;

    protected $listeners = [
        'showReceipt' => 'openSingleModal',
        'showBulkReceipts' => 'openBulkModal',
    ];

    public function mount()
    {
        $this->unpaidClients = collect();
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

    // Get the current exchange rate dynamically
    public function getExchangeRateProperty()
    {
        return ExchangeRate::getCurrentRate(Auth::id());
    }

    // Open single receipt modal after payment 
    public function openSingleModal($clientId)
    {
        try {
            // Check if user can view the client 
            $client = Client::where('id', $clientId)
                ->where('user_id', Auth::id())
                ->firstOrFail();
            
            $this->authorize('view', $client);

            $payment = Payment::where('client_id', $clientId)
                ->orderByDesc('paid_at')
                ->first();

            if (!$payment) {
                $this->setAlert('لم يتم العثور على بيانات الدفع', 'danger');
                return;
            }

            $this->receiptData = $this->generateReceiptData($clientId, $payment);
            $this->mode = 'single';
            $this->show = true;

        } catch (AuthorizationException $e) {
            $this->setAlert('ليس لديك صلاحية لعرض إيصال هذا المشترك', 'danger');
        } catch (Exception $e) {
            $this->setAlert('حدث خطأ أثناء فتح الإيصال', 'danger');
        }
    }

    // Open bulk receipts modal 
    public function openBulkModal()
    {
        try {
            // Check if user can view clients in general
            $this->authorize('viewAny', Client::class);

            $this->reset(['search', 'selectedClientId']);
            $this->mode = 'bulk';
            $this->loadUnpaidClients();
            $this->loadBulkReceipts();
            $this->show = true;

        } catch (AuthorizationException $e) {
            $this->setAlert('ليس لديك صلاحية لعرض الإيصالات', 'danger');
        } catch (Exception $e) {
            $this->setAlert('حدث خطأ أثناء فتح الإيصالات', 'danger');
        }
    }

    public function handleSearch()
    {
        try {
            $this->loadUnpaidClients();
            $this->loadBulkReceipts();

            // Auto-select if only one result
            if ($this->unpaidClients->count() === 1) {
                $this->selectedClientId = $this->unpaidClients->first()->id;
                $this->loadBulkReceipts(); 
            } else {
                $this->selectedClientId = null;
            }
        } catch (Exception $e) {
            $this->setAlert('حدث خطأ أثناء البحث', 'danger');
        }
    }

    public function updatedSelectedClientId()
    {
        try {
            $this->loadBulkReceipts();
        } catch (Exception $e) {
            $this->setAlert('حدث خطأ أثناء تحميل بيانات الإيصالات', 'danger');
        }
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->selectedClientId = null;
        $this->loadUnpaidClients();
        $this->loadBulkReceipts();
    }

    // Load unpaid clients - Ensuring we get latest COMPLETED reading 
    private function loadUnpaidClients()
    {
        try {
            $clientsQuery = Client::where('user_id', Auth::id())
                ->where('is_offered', false)
                ->with([
                    'meterReadings' => function ($q) {
                        $q->whereNotNull('reading_date')
                        ->orderByDesc('reading_for_month')
                        ->limit(1);
                    },
                    'user.kilowattPrice',
                    'meterCategory',
                    'user.phoneNumbers'
                ]);

            if (!empty($this->search)) {
                $clientsQuery->search($this->search);
            }

            $clients = $clientsQuery->get()
                ->filter(function ($client) {
                    $latest = $client->meterReadings->first();
                    return $latest && ((float)$latest->remaining_amount > 0);
                })
                ->map(function ($client) {
                    $latest = $client->meterReadings->first();
                    $client->setRelation('latestReading', $latest);
                    $client->setAttribute('remaining_amount', (float)$latest->remaining_amount);
                    return $client;
                })
                ->values();

            $this->unpaidClients = $clients;

        } catch (\Exception $e) {
            $this->setAlert('حدث خطأ أثناء تحميل بيانات المشتركين', 'danger');
            $this->unpaidClients = collect();
        }
    }



    // Load receipts for all or filtered unpaid clients 
    private function loadBulkReceipts()
    {
        try {
            $clients = $this->unpaidClients;

            if ($this->selectedClientId) {
                $clients = $clients->filter(function ($client) {
                    return $client->id == $this->selectedClientId;
                });
            }

            $this->receiptsData = $clients->map(function ($client) {
                // Check if user can view this client's data
                try {
                    $this->authorize('view', $client);
                } catch (AuthorizationException $e) {
                    return null; 
                }

                // Get the latest COMPLETED reading
                $latestReading = MeterReading::latestCompletedForClient($client->id);
                    
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
                    'amount_paid' => 0, // Bulk receipts show current due, not payments
                    'remaining_after_payment' => $latestReading->remaining_amount ?? 0,
                    'payment_id' => 'BULK-' . $client->id . '-' . time(),
                    'user_name' => $client->user->name,
                    'user_phones' => $client->user->phoneNumbers->pluck('phone_number')->implode(' - '),
                ];
            })->filter()->values()->toArray();

        } catch (Exception $e) {
            $this->setAlert('حدث خطأ أثناء تحميل بيانات الإيصالات', 'danger');
            $this->receiptsData = [];
        }
    }

    // Generate data for a single receipt 
    private function generateReceiptData($clientId, $payment)
    {
        try {
            $client = Client::with(['meterCategory', 'user.kilowattPrice', 'user.phoneNumbers'])->find($clientId);
            if (!$client) return [];

            // Check if user can view this client's data
            $this->authorize('view', $client);

            $latestReading = MeterReading::latestCompletedForClient($client->id);

            if (!$latestReading) {
                $this->setAlert('لا توجد قراءة للمشترك', 'danger');
                return [];
            }

            $kilowattPrice = $client->user->kilowattPrice->price ?? 0;
            $consumptionAmount = $latestReading->consumption * $kilowattPrice;
            $readingMonth = $latestReading->reading_for_month;
            $arabicMonth = $this->getArabicMonthName($readingMonth);
            $amountPaid = $payment->amount + $payment->discount;
            $remainingAfterPayment = $latestReading->remaining_amount;

            return [
                'client_id' => $client->id,
                'client_full_name' => $client->full_name,
                'payment_date' => $payment->paid_at->format('d/m/Y'),
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
                'amount_paid' => $amountPaid,
                'remaining_after_payment' => $remainingAfterPayment,
                'payment_id' => $payment->id,
                'user_name' => $client->user->name,
                'user_phones' => $client->user->phoneNumbers->pluck('phone_number')->implode(' - '),
            ];
        } catch (AuthorizationException $e) {
            $this->setAlert('ليس لديك صلاحية لعرض بيانات هذا المشترك', 'danger');
            return [];
        } catch (Exception $e) {
            $this->setAlert('حدث خطأ أثناء إنشاء بيانات الإيصال', 'danger');
            return [];
        }
    }

    // Arabic month translation 
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
        $this->clearAlert();
        $this->reset(['search', 'selectedClientId']);
        $this->dispatch('resetPaymentEntryFilters');
    }

    public function render()
    {
        return view('livewire.receipt-modal');
    }
}