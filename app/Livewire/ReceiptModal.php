<?php

namespace App\Livewire;

use App\Models\Client;
use App\Models\ExchangeRate;
use App\Models\MeterReading;
use App\Models\Payment;
use App\Support\ArabicMonth;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ReceiptModal extends Component
{
    public $show = false;
    public $receiptData = [];
    public $receiptsData = [];
    public $mode = 'single';

    public $search = '';
    public $unpaidClients = [];
    public $selectedClientId = null;
    public $showSearchResults = false;
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

    public function getExchangeRateProperty()
    {
        return ExchangeRate::getCurrentRate(Auth::id());
    }

    public function openSingleModal($clientId)
    {
        try {
            $client = Client::forUser(Auth::id())
                ->where('id', $clientId)
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

    public function openBulkModal()
    {
        try {
            $this->authorize('viewAny', Client::class);

            $this->reset(['search', 'selectedClientId']);
            $this->showSearchResults = false;
            $this->mode = 'bulk';
            $this->loadUnpaidClients();
            $this->loadBulkReceipts();
            $this->show = true;
            $this->dispatch('focus-receipt-search');
        } catch (AuthorizationException $e) {
            $this->setAlert('ليس لديك صلاحية لعرض الإيصالات', 'danger');
        } catch (Exception $e) {
            $this->setAlert('حدث خطأ أثناء فتح الإيصالات', 'danger');
        }
    }

    public function handleSearch()
    {
        try {
            $this->refreshBulkReceiptSearch(true);
            $this->dispatch('focus-receipt-search');
        } catch (Exception $e) {
            $this->setAlert('حدث خطأ أثناء البحث', 'danger');
        }
    }

    public function updatedSearch()
    {
        try {
            $this->refreshBulkReceiptSearch(false);
            $this->dispatch('focus-receipt-search');
        } catch (Exception $e) {
            $this->setAlert('حدث خطأ أثناء البحث', 'danger');
        }
    }

    public function selectClient($clientId)
    {
        $client = collect($this->unpaidClients)->firstWhere('id', (int) $clientId);
        if (!$client) {
            return;
        }

        $this->selectedClientId = $client->id;
        $this->showSearchResults = false;
        $this->loadBulkReceipts();
        $this->dispatch('focus-receipt-search');
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->selectedClientId = null;
        $this->showSearchResults = false;
        $this->loadUnpaidClients();
        $this->loadBulkReceipts();
        $this->dispatch('focus-receipt-search');
    }

    private function refreshBulkReceiptSearch(bool $allowAutoSelect): void
    {
        $this->selectedClientId = null;
        $this->showSearchResults = filled(trim($this->search));
        $this->loadUnpaidClients();

        if ($allowAutoSelect && $this->unpaidClients->count() === 1) {
            $this->selectedClientId = $this->unpaidClients->first()->id;
            $this->showSearchResults = false;
        }

        $this->loadBulkReceipts();
    }

    private function loadUnpaidClients()
    {
        try {
            $clientsQuery = Client::forUser(Auth::id())
                ->notOffered()
                ->with([
                    'meterReadings' => function ($q) {
                        $q->whereNotNull('reading_date')
                            ->orderByDesc('reading_for_month')
                            ->limit(1);
                    },
                    'user.kilowattPrice',
                    'meterCategory',
                    'user.phoneNumbers',
                ]);

            if (!empty($this->search)) {
                $clientsQuery->search($this->search);
            }

            $clients = $clientsQuery->get()
                ->filter(function ($client) {
                    $latest = $client->meterReadings->first();
                    return $latest && ((float) $latest->remaining_amount > 0);
                })
                ->map(function ($client) {
                    $latest = $client->meterReadings->first();
                    $client->setRelation('latestReading', $latest);
                    $client->setAttribute('remaining_amount', (float) $latest->remaining_amount);
                    return $client;
                })
                ->values();

            $this->unpaidClients = $clients;
        } catch (Exception $e) {
            $this->setAlert('حدث خطأ أثناء تحميل بيانات المشتركين', 'danger');
            $this->unpaidClients = collect();
        }
    }

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
                try {
                    $this->authorize('view', $client);
                } catch (AuthorizationException $e) {
                    return null;
                }

                $latestReading = MeterReading::latestCompletedForClient($client->id);
                if (!$latestReading) {
                    return null;
                }

                $kilowattPrice = $client->user->kilowattPrice->price ?? 0;
                $consumptionAmount = round($latestReading->consumption * $kilowattPrice * 2) / 2;

                return [
                    'client_id' => $client->id,
                    'client_full_name' => $client->full_name,
                    'payment_date' => now()->format('d/m/Y'),
                    'reading_for_month_arabic' => ArabicMonth::label($latestReading->reading_for_month ?? now()),
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
                    'amount_paid' => 0,
                    'remaining_after_payment' => $latestReading->remaining_amount ?? 0,
                    'user_name' => $client->user->name,
                    'user_phones' => $client->user->phoneNumbers->pluck('phone_number')->implode(' - '),
                ];
            })->filter()->values()->toArray();
        } catch (Exception $e) {
            $this->setAlert('حدث خطأ أثناء تحميل بيانات الإيصالات', 'danger');
            $this->receiptsData = [];
        }
    }

    private function generateReceiptData($clientId, $payment)
    {
        try {
            $client = Client::with(['meterCategory', 'user.kilowattPrice', 'user.phoneNumbers'])->find($clientId);
            if (!$client) {
                return [];
            }

            $this->authorize('view', $client);

            $meterReading = $payment->meterReading;
            if (!$meterReading) {
                $this->setAlert('لا توجد قراءة للمشترك', 'danger');
                return [];
            }

            $kilowattPrice = $client->user->kilowattPrice->price ?? 0;
            $consumptionAmount = round($meterReading->consumption * $kilowattPrice * 2) / 2;
            $amountPaid = $payment->amount + $payment->discount;

            return [
                'client_id' => $client->id,
                'client_full_name' => $client->full_name,
                'payment_date' => $payment->paid_at->format('d/m/Y'),
                'reading_for_month_arabic' => ArabicMonth::label($meterReading->reading_for_month),
                'kilowatt_price' => $kilowattPrice,
                'meter_category' => $client->meterCategory->category ?? 'N/A',
                'meter_category_price' => $client->meterCategory->price ?? 0,
                'previous_meter' => $meterReading->previous_meter ?? 0,
                'current_meter' => $meterReading->current_meter ?? 0,
                'consumption' => $meterReading->consumption ?? 0,
                'consumption_amount' => $consumptionAmount,
                'maintenance_cost' => $meterReading->maintenance_cost ?? 0,
                'previous_balance' => $meterReading->previous_balance ?? 0,
                'total_due' => $meterReading->total_due ?? 0,
                'amount_paid' => $amountPaid,
                'remaining_after_payment' => $meterReading->remaining_amount,
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

    public function closeModal()
    {
        $this->show = false;
        $this->receiptData = [];
        $this->receiptsData = [];
        $this->clearAlert();
        $this->reset(['search', 'selectedClientId']);
        $this->showSearchResults = false;
        $this->dispatch('resetPaymentEntryFilters')->to(PaymentEntry::class);
        $this->dispatch('clear-payment-entry-search');
    }

    public function render()
    {
        return view('livewire.receipt-modal');
    }
}
