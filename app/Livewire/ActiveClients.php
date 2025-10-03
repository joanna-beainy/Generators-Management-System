<?php
namespace App\Livewire;

use App\Models\Client;
use App\Models\Generator;
use App\Models\MeterCategory;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class ActiveClients extends Component
{
    public $search = '';
    public $clients = [];

    public $showEditModal = false;
    public $editingClient;

    public $first_name, $father_name, $last_name, $phone_number, $address, $generator_id, $meter_category_id;

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
        $this->clients = Client::where('user_id', Auth::id())
            ->where(function ($q) {
                $q->where('first_name', 'like', "%{$this->search}%")
                  ->orWhere('father_name', 'like', "%{$this->search}%")
                  ->orWhere('last_name', 'like', "%{$this->search}%");
            })
            ->with(['generator', 'meterCategory'])
            ->get();
    }

    public function deleteClient($id)
    {
        $client = Client::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $this->authorize('delete', $client);
        $client->delete();

        session()->flash('success', 'تم حذف المشترك.');
        $this->loadClients();
    }

    public function editClient($id)
    {
        $client = Client::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $this->authorize('update', $client);

        $this->editingClient = $client;
        $this->first_name = $client->first_name;
        $this->father_name = $client->father_name;
        $this->last_name = $client->last_name;
        $this->phone_number = $client->phone_number;
        $this->address = $client->address;
        $this->generator_id = $client->generator_id;
        $this->meter_category_id = $client->meter_category_id;

        $this->showEditModal = true;
    }

    public function updateClient()
    {
        $this->validate([
            'first_name' => 'required|string|max:255',
            'father_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone_number' => 'required|string|regex:/^(\+?\d{1,4})?\d{8,15}$/',
            'address' => 'nullable|string|max:255',
            'generator_id' => 'required|exists:generators,id',
            'meter_category_id' => 'required|exists:meter_categories,id',
        ]);

        $this->editingClient->update([
            'first_name' => $this->first_name,
            'father_name' => $this->father_name,
            'last_name' => $this->last_name,
            'phone_number' => $this->phone_number,
            'address' => $this->address,
            'generator_id' => $this->generator_id,
            'meter_category_id' => $this->meter_category_id,
        ]);

        $this->showEditModal = false;
        session()->flash('success', 'تم تحديث بيانات المشترك.');
        $this->loadClients();
    }

    public function render()
    {
        return view('livewire.active-clients');
    }
}
