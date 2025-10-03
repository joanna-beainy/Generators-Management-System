<?php

namespace App\Livewire;

use App\Models\Client;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class TrashedClients extends Component
{
     public $search = '';
    public $clients = [];

    public function mount()
    {
        $this->loadClients();
    }

    public function updatedSearch()
    {
        $this->loadClients();
    }

    public function loadClients()
    {
        $this->clients = Client::onlyTrashed()
            ->where('user_id', Auth::id())
            ->where(function ($q) {
                $q->where('first_name', 'like', "%{$this->search}%")
                  ->orWhere('father_name', 'like', "%{$this->search}%")
                  ->orWhere('last_name', 'like', "%{$this->search}%");
            })
            ->with(['generator', 'meterCategory'])
            ->get();
    }

    public function restoreClient($id)
    {
        $client = Client::onlyTrashed()
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $this->authorize('restore', $client);
        $client->restore();

        session()->flash('success', 'تم استعادة المشترك.');
        $this->loadClients();
    }
    
    public function render()
    {
        return view('livewire.trashed-clients');
    }
}
