<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Client;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PaymentHistory extends Component
{
    public $clientId;
    public $client;
    public $payments;
    public $selectedYear;
    public $selectedMonth;
    public $years = [];
    public $months = [];

    public function mount($clientId = null)
    {
        if ($clientId) {
            $this->clientId = $clientId;
            $this->loadClientData();
            $this->initializeFilters();
            $this->loadPayments();
        }
    }

    private function loadClientData()
    {
        $this->client = Client::where('user_id', Auth::id())
            ->find($this->clientId);
    }

    private function initializeFilters()
    {
        // Set current year as default
        $this->selectedYear = Carbon::now()->year;
        
        // Get available years from payments
        $this->years = Payment::where('client_id', $this->clientId)
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
    }

    public function loadPayments()
    {
        $this->payments = Payment::with(['meterReading'])
            ->where('client_id', $this->clientId)
            ->when($this->selectedYear, function($query) {
                $query->whereYear('paid_at', $this->selectedYear);
            })
            ->when($this->selectedMonth, function($query) {
                $query->whereMonth('paid_at', $this->selectedMonth);
            })
            ->orderBy('paid_at', 'desc')
            ->get();
    }

    public function updatedSelectedYear()
    {
        $this->loadPayments();
    }

    public function updatedSelectedMonth()
    {
        $this->loadPayments();
    }

    public function render()
    {
        return view('livewire.payment-history');
    }
}