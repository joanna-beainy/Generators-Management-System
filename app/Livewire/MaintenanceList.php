<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Client;
use App\Models\Maintenance;
use Illuminate\Support\Facades\Auth;

class MaintenanceList extends Component
{
    public $clientId;
    public $client;
    public $maintenances;

    public function mount($clientId = null)
    {
        if ($clientId) {
            $this->clientId = $clientId;
            $this->loadClientData();
        }
    }

    public function loadClientMaintenance($clientId)
    {
        $this->clientId = $clientId;
        $this->loadClientData();
    }

    private function loadClientData()
    {
        $this->client = Client::where('user_id', Auth::id())
            ->with(['maintenances' => function($query) {
                $query->orderByDesc('created_at');
            }])
            ->find($this->clientId);

        if ($this->client) {
            $this->maintenances = $this->client->maintenances;
        }
    }

    public function deleteMaintenance($maintenanceId)
    {
        $maintenance = Maintenance::find($maintenanceId);
        
        if ($maintenance && $maintenance->belongsToUser(Auth::id())) {
            try {
                $maintenance->deleteWithAutoHandling();
                session()->flash('success', 'تم حذف مصاريف الصيانة بنجاح');
                $this->loadClientData(); // Refresh the list
            } catch (\Exception $e) {
                session()->flash('error', 'حدث خطأ أثناء الحذف: ' . $e->getMessage());
            }
        }
    }

    public function render()
    {
        return view('livewire.maintenance-list');
    }
}