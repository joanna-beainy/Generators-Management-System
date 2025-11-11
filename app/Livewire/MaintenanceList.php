<?php
namespace App\Livewire;

use Exception;
use App\Models\Client;
use Livewire\Component;
use App\Models\Maintenance;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Access\AuthorizationException;

class MaintenanceList extends Component
{
    public $clientId;
    public $client;
    public $maintenances;
    public $alertMessage = null;
    public $alertType = null;

    public function mount($clientId = null)
    {
        if ($clientId) {
            $this->clientId = $clientId;
            $this->loadClientData();
        }
    }

    private function setAlert($message, $type = 'success')
    {
        $this->alertMessage = $message;
        $this->alertType = $type;
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
        try {
            $maintenance = Maintenance::find($maintenanceId);
            
            if (!$maintenance) {
                $this->setAlert('لم يتم العثور على مصاريف الصيانة', 'danger');
                return;
            }

            // Check authorization using Policy with $this->authorize()
            $this->authorize('delete', $maintenance);

            $maintenance->deleteWithAutoHandling();
            $this->setAlert('تم حذف مصاريف الصيانة بنجاح', 'success');
            $this->loadClientData(); // Refresh the list

        } catch (AuthorizationException $e) {
            $this->setAlert('ليس لديك صلاحية لحذف مصاريف الصيانة هذه', 'danger');
        } catch (Exception $e) {
            $this->setAlert('حدث خطأ أثناء حذف مصاريف الصيانة', 'danger');
        }
    }

    public function render()
    {
        return view('livewire.maintenance-list');
    }
}