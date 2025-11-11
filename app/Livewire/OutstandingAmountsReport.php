<?php

namespace App\Livewire;

use Exception;
use App\Models\Client;
use Livewire\Component;
use App\Models\MeterReading;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Access\AuthorizationException;

class OutstandingAmountsReport extends Component
{
    public $unpaidClients = [];
    public $alertMessage = null;
    public $alertType = null;

    public function mount()
    {
        try {
            // Check if user can view meter readings
            $this->authorize('viewAny', MeterReading::class);

            $this->loadUnpaidClients();

        } catch (AuthorizationException $e) {
            $this->setAlert('ليس لديك صلاحية لعرض تقارير المبالغ المستحقة', 'danger');
        } catch (Exception $e) {
            $this->setAlert('حدث خطأ أثناء تحميل بيانات التقرير', 'danger');
        }
    }

    private function setAlert($message, $type = 'success')
    {
        $this->alertMessage = $message;
        $this->alertType = $type;
    }

     private function loadUnpaidClients()
    {
        try {
            // Eager-load only the latest *completed* reading per client (reading_date NOT NULL)
            $clients = Client::where('user_id', Auth::id())
                ->where('is_offered', false)
                ->with(['meterReadings' => function ($q) {
                    $q->whereNotNull('reading_date')
                      ->orderByDesc('reading_for_month')
                      ->limit(1); // only the latest completed reading
                }])
                ->get()
                // keep only clients whose latest completed reading exists and has remaining_amount > 0
                ->filter(function ($client) {
                    $latest = $client->meterReadings->first();
                    return $latest && ((float) $latest->remaining_amount > 0);
                })
                // attach the latest reading and remaining_amount to each client for easy view usage
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

    public function render()
    {
        return view('livewire.outstanding-amounts-report');
    }
}
