<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Client;
use Illuminate\Support\Facades\Auth;

class SearchClientModal extends Component
{
    public $show = false;
    public $search = '';
    public $clients;
    public $selectedClientId = null;
    public $alertMessage = null;
    public $alertType = null;
    public $actionType = null;

    protected $listeners = ['openClientSearch' => 'openModal'];

    public function mount()
    {
        $this->clients = collect();
    }

    public function openModal($actionType)
    {
        $this->show = true;
        $this->reset(['search', 'selectedClientId', 'alertMessage', 'alertType']);
        $this->actionType = $actionType;
        $this->loadClients();
    }

    public function loadClients()
    {
        $query = Client::where('user_id', Auth::id())
            ->search($this->search)
            ->orderBy('id');

        // Exclude offered clients for payment and maintenance actions
        if (in_array($this->actionType, ['print-receipt', 'view-maintenance', 'view-payments'])) {
            $query->where('is_offered', false);
        }

        $this->clients = $query->get();
    }

    public function updatedSearch()
    {
        $this->loadClients();
        $this->selectedClientId = null;
        $this->alertMessage = null;
        $this->alertType = null;
        
        if ($this->clients->count() === 1) {
            $this->selectedClientId = $this->clients->first()->id;
        }
    }

    public function updatedSelectedClientId()
    {
        if ($this->selectedClientId) {
            $this->resetValidation();
            $this->alertMessage = null;
            $this->alertType = null;
            $this->loadClients();
        }
    }

    private function setAlert($message, $type = 'danger')
    {
        $this->alertMessage = $message;
        $this->alertType = $type;
    }

    public function handleSelection()
    {
        if (!$this->selectedClientId) {
            $this->setAlert('يرجى اختيار مشترك.');
            return;
        }

        $client = Client::find($this->selectedClientId);
        
        if (!$client) {
            $this->setAlert('المشترك غير موجود.');
            return;
        }

        // Emit different events depending on the action type
        switch ($this->actionType) {
            case 'print-receipt':
                if ($client->is_offered) {
                    $this->setAlert(' لا يمكن طباعة إيصال للمشتركين المعفيين من الدفع.');
                    return;
                }
                $this->dispatch('showReceipt', clientId: $this->selectedClientId);
                break;

            case 'view-maintenance':
                if ($client->is_offered) {
                    $this->setAlert('المشترك معفى من الدفع ولا توجد مصاريف صيانة مسجلة له.');
                    return;
                }

                $this->redirect(route('maintenance.list', ['clientId' => $this->selectedClientId]), navigate: true);
                break;

            case 'view-meter':
                $this->redirect(route('client.meter.readings', ['clientId' => $this->selectedClientId]), navigate: true);
                break;    

            default:
                $this->setAlert('نوع العملية غير معروف.');
                return;
        }

        $this->closeModal();
    }

    public function closeModal()
    {
        $this->show = false;
        $this->reset(['search', 'selectedClientId', 'alertMessage', 'alertType', 'actionType']);
    }

    public function render()
    {
        return view('livewire.search-client-modal');
    }
}