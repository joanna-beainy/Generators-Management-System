<?php

namespace App\Livewire;

use App\Models\Client;
use App\Models\MeterReading;
use App\Support\ArabicMonth;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ClientMeterReadings extends Component
{
    public $clientId;
    public $client;
    public $selectedMonth = null;
    public $selectedYear;
    public $months;
    public $years = [];
    public $readings = [];
    public $alertMessage = null;
    public $alertType = null;

    public function mount($clientId)
    {
        try {
            $this->readings = collect();

            $this->client = Client::where('user_id', Auth::id())->find($clientId);

            if (!$this->client) {
                $this->setAlert('المشترك غير موجود أو ليس لديك صلاحية للوصول إليه', 'danger');
                return;
            }

            $this->authorize('viewAny', MeterReading::class);

            $this->initializeFilters();
            $this->loadReadings();
        } catch (AuthorizationException $e) {
            $this->setAlert('ليس لديك صلاحية لعرض قراءات العدادات', 'danger');
            $this->readings = collect();
        } catch (Exception $e) {
            $this->setAlert('حدث خطأ أثناء تحميل بيانات المشترك', 'danger');
            $this->readings = collect();
        }
    }

    private function setAlert($message, $type = 'success')
    {
        $this->alertMessage = $message;
        $this->alertType = $type;
    }

    private function clearAlert()
    {
        $this->alertMessage = null;
        $this->alertType = null;
    }

    private function initializeFilters()
    {
        $this->months = ArabicMonth::all();

        $this->years = MeterReading::whereHas('client', function ($query) {
            $query->where('user_id', Auth::id());
        })
            ->selectRaw("strftime('%Y', reading_for_month) as year")
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();

        $this->selectedYear = in_array(now()->year, $this->years)
            ? now()->year
            : ($this->years[0] ?? null);
    }

    public function updatedSelectedMonth()
    {
        $this->clearAlert();
        $this->loadReadings();
    }

    public function updatedSelectedYear()
    {
        $this->clearAlert();
        $this->loadReadings();
    }

    public function loadReadings()
    {
        try {
            if (!$this->client) {
                $this->readings = collect();
                return;
            }

            $query = MeterReading::where('client_id', $this->client->id)
                ->whereNotNull('reading_date')
                ->orderBy('reading_for_month', 'desc');

            if ($this->selectedYear) {
                $query->whereYear('reading_for_month', $this->selectedYear);
            }

            if ($this->selectedMonth) {
                $query->whereMonth('reading_for_month', $this->selectedMonth);
            }

            $this->readings = $query->get();
        } catch (Exception $e) {
            $this->setAlert('حدث خطأ أثناء تحميل قراءات العدادات', 'danger');
            $this->readings = collect();
        }
    }

    public function render()
    {
        return view('livewire.client-meter-readings');
    }
}
