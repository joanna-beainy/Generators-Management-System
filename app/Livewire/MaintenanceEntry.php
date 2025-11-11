<?php

namespace App\Livewire;

use Exception;
use App\Models\Client;
use Livewire\Component;
use App\Models\Maintenance;
use App\Models\MeterReading;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;

class MaintenanceEntry extends Component
{
    public $search = '';
    public $selectedClientId = null;
    public $amount = '';
    public $description = '';
    
    public $alertMessage = null;
    public $alertType = null;

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

    private function setAlert($message, $type = 'success')
    {
        $this->alertMessage = $message;
        $this->alertType = $type;
    }

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
        $this->clearAlert();
        
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
            $this->clearAlert();
        }
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->selectedClientId = null;
        $this->resetValidation();
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
            // Check authorization using Policy with $this->authorize()
            $this->authorize('create', [Maintenance::class, $this->selectedClientId]);

            $client = $this->getSelectedClient();
            
            if (!$client) {
                $this->setAlert('المشترك غير موجود', 'danger');
                return;
            }

            // Create maintenance record
            $maintenance = Maintenance::addWithAutoHandling(
                $this->selectedClientId,
                $this->amount,
                $this->description
            );

            // Success message
            $this->setAlert(" تم إدخال مصاريف الصيانة بنجاح للمشترك {$client->full_name}", 'success');

            // Reset form
            $this->amount = '';
            $this->description = '';

        } catch (ValidationException $e) {
            throw $e;
        } catch (AuthorizationException $e) {
            $this->setAlert('ليس لديك صلاحية لإضافة مصاريف صيانة لهذا المشترك', 'danger');
        } catch (Exception $e) {
            $this->setAlert('حدث خطأ أثناء حفظ البيانات: ' . $e->getMessage(), 'danger');
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