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
    public $errorMessage = null;
    public $actionType = null;

    protected $listeners = ['openClientSearch' => 'openModal'];

    public function mount()
    {
        $this->clients = collect();
    }

    public function openModal($actionType)
    {
        $this->show = true;
        $this->reset(['search', 'selectedClientId', 'errorMessage']);
        $this->actionType = $actionType;
        $this->loadClients();
    }

    public function loadClients()
    {
        $query = Client::where('user_id', Auth::id())
            ->search($this->search)
            ->orderBy('id');

        // ✅ Exclude offered clients for payment and maintenance actions
        if (in_array($this->actionType, ['print-receipt', 'view-maintenance', 'view-payments'])) {
            $query->where('is_offered', false);
        }

        $this->clients = $query->get();
    }

    public function updatedSearch()
    {
        $this->loadClients();
        $this->selectedClientId = null;
        $this->errorMessage = null;
        
        if ($this->clients->count() === 1) {
            $this->selectedClientId = $this->clients->first()->id;
        }
    }

    public function updatedSelectedClientId()
    {
        if ($this->selectedClientId) {
            $this->resetValidation();
            $this->errorMessage = null;
            $this->loadClients();
        }
    }

    public function handleSelection()
    {
        if (!$this->selectedClientId) {
            $this->errorMessage = '❌ يرجى اختيار مشترك.';
            return;
        }

        $client = Client::find($this->selectedClientId);
        
        if (!$client) {
            $this->errorMessage = '❌ المشترك غير موجود.';
            return;
        }

        // Emit different events depending on the action type
        switch ($this->actionType) {
            case 'print-receipt':
                if ($client->is_offered) {
                    $this->errorMessage = '❌ لا يمكن طباعة إيصال للمشتركين المقدمين كتقدمة.';
                    return;
                }
                $this->dispatch('showReceipt', clientId: $this->selectedClientId);
                break;

            case 'view-maintenance':
                if ($client->is_offered) {
                    $this->errorMessage = '❌ المشترك مقدم كتقدمة ولا توجد مصاريف صيانة مسجلة له.';
                    return;
                }

                $this->redirect(route('maintenance.list', ['clientId' => $this->selectedClientId]), navigate: true);
                break;
            case 'view-payments':
                if ($client->is_offered) {
                    $this->errorMessage = '❌ المشترك مقدم كتقدمة ولا توجد دفعات مسجلة له.';
                    return;
                }
                $this->redirect(route('payment.history', ['clientId' => $this->selectedClientId]), navigate: true);
                break;

            default:
                $this->errorMessage = '❌ نوع العملية غير معروف.';
                return;
        }

        $this->closeModal();
    }

    public function closeModal()
    {
        $this->show = false;
        $this->reset(['search', 'selectedClientId', 'errorMessage', 'actionType']);
    }

    public function render()
    {
        return view('livewire.search-client-modal');
    }
}
