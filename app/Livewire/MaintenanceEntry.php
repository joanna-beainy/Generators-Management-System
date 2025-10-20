<?php

namespace App\Livewire;

use App\Models\Client;
use Livewire\Component;
use App\Models\Maintenance;
use App\Models\MeterReading;
use Illuminate\Support\Facades\Auth;

class MaintenanceEntry extends Component
{
    public $search = '';
    public $selectedClientId = null;
    public $amount = '';
    public $description = '';
    
    public $successMessage = null;
    public $errorMessage = null;

    protected $rules = [
        'selectedClientId' => 'required|exists:clients,id',
        'amount' => 'required|numeric|min:0.01',
        'description' => 'nullable|string|max:200',
    ];

    protected $messages = [
        'selectedClientId.required' => 'يرجى اختيار المشترك',
        'amount.required' => 'يرجى إدخال مبلغ الصيانة',
        'amount.numeric' => 'يجب أن يكون المبلغ رقمًا',
        'amount.min' => 'يجب أن يكون المبلغ أكبر من الصفر',
    ];

    public function loadClientsForSearch()
    {
        return Client::where('user_id', Auth::id())
            ->where('is_offered', false)
            ->active()
            ->search($this->search)
            ->orderBy('id')
            ->get();
    }

    public function getSelectedClient()
    {
        if (!$this->selectedClientId) {
            return null;
        }

        $client = Client::where('user_id', Auth::id())
            ->find($this->selectedClientId);

        if ($client) {
            $client->latest_meter_reading = MeterReading::latestForClient($client->id);
        }

        return $client;
    }

    public function handleSearch()
    {
        $this->resetValidation();
        
        // Auto-select if only one result
        $clients = $this->loadClientsForSearch();
        if ($clients->count() === 1) {
            $this->selectedClientId = $clients->first()->id;
        } else {
            $this->selectedClientId = null;
        }
    }

    public function updatedSelectedClientId($value)
    {
        if ($value) {
            $this->resetValidation();
            $this->clearMessages();
        }
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->selectedClientId = null;
        $this->resetValidation();
        $this->clearMessages();
    }

    public function clearMessages()
    {
        $this->successMessage = null;
        $this->errorMessage = null;
    }

    public function save()
    {
        $this->validate();

        try {
            $client = $this->getSelectedClient();
            
            if (!$client) {
                $this->errorMessage = 'المشترك غير موجود';
                return;
            }

            // Check if client is offered
            if ($client->is_offered) {
                $this->errorMessage = 'لا يمكن إدخال مصاريف صيانة للمشتركين المقدمين كتقدمة';
                return;
            }

            // Create maintenance record using the model's method
            $maintenance = Maintenance::addWithAutoHandling(
                $this->selectedClientId,
                $this->amount,
                $this->description
            );

            // Success message
            $this->successMessage = "✅ تم إدخال مصاريف الصيانة بنجاح للمشترك {$client->full_name}";

            // Reset form
            $this->amount = '';
            $this->description = '';

        } catch (\Exception $e) {
            $this->errorMessage = 'حدث خطأ أثناء حفظ البيانات: ' . $e->getMessage();
        }
    }

    public function render()
    {
        $clients = $this->loadClientsForSearch();
        $selectedClient = $this->getSelectedClient();

        return view('livewire.maintenance-entry', [
            'clients' => $clients,
            'selectedClient' => $selectedClient,
        ]);
    }
}