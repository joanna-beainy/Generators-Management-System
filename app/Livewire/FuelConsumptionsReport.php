<?php

namespace App\Livewire;

use App\Models\FuelConsumption;
use App\Models\Generator;
use App\Support\ArabicMonth;
use Carbon\Carbon;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Native\Desktop\Facades\Alert;

class FuelConsumptionsReport extends Component
{
    public $consumptions;
    public $selectedYear;
    public $selectedMonth;
    public $selectedGenerator;
    public $years = [];
    public $months = [];
    public $generators = [];
    public $alertMessage = null;
    public $alertType = null;
    public $showConsumptionModal = false;

    public $generator_id;
    public $liters_consumed;
    public $notes;

    protected $rules = [
        'generator_id' => 'required|exists:generators,id',
        'liters_consumed' => 'required|integer|min:1',
        'notes' => 'nullable|string',
    ];

    protected $messages = [
        'generator_id.required' => 'يرجى اختيار المولد.',
        'generator_id.exists' => 'المولد المحدد غير صالح.',
        'liters_consumed.required' => 'يرجى إدخال اللترات المستهلكة.',
        'liters_consumed.numeric' => 'يرجى إدخال رقم صالح للترات.',
        'liters_consumed.min' => 'يجب أن تكون اللترات المستهلكة على الأقل 1.',
    ];

    public function mount()
    {
        try {
            $this->authorize('viewAny', FuelConsumption::class);

            $this->generators = Generator::where('user_id', Auth::id())->get();
            $this->initializeFilters();
            $this->loadConsumptions();
        } catch (AuthorizationException $e) {
            $this->setAlert('ليس لديك صلاحية لعرض استهلاك الوقود', 'danger');
        } catch (Exception $e) {
            $this->setAlert('حدث خطأ أثناء تحميل بيانات الاستهلاك', 'danger');
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
        try {
            $this->selectedYear = Carbon::now()->year;
            $this->selectedMonth = Carbon::now()->month;
            $this->selectedGenerator = 'all';

            $this->years = FuelConsumption::where('user_id', Auth::id())
                ->selectRaw("strftime('%Y', created_at) as year")
                ->distinct()
                ->orderBy('year', 'desc')
                ->pluck('year')
                ->toArray();

            if (empty($this->years)) {
                $this->years = [$this->selectedYear];
            }

            $this->months = ArabicMonth::all(true);
        } catch (Exception $e) {
            $this->setAlert('حدث خطأ أثناء تهيئة الفلاتر', 'danger');
        }
    }

    public function loadConsumptions()
    {
        try {
            $this->consumptions = FuelConsumption::with(['generator', 'purchases'])
                ->where('user_id', Auth::id())
                ->when($this->selectedYear, function ($query) {
                    $query->whereYear('created_at', $this->selectedYear);
                })
                ->when($this->selectedMonth, function ($query) {
                    $query->whereMonth('created_at', $this->selectedMonth);
                })
                ->when($this->selectedGenerator && $this->selectedGenerator !== 'all', function ($query) {
                    $query->where('generator_id', $this->selectedGenerator);
                })
                ->orderBy('created_at', 'desc')
                ->get();
        } catch (Exception $e) {
            $this->setAlert('حدث خطأ أثناء تحميل بيانات الاستهلاك', 'danger');
            $this->consumptions = collect();
        }
    }

    public function updatedSelectedYear()
    {
        $this->clearAlert();
        $this->loadConsumptions();
    }

    public function updatedSelectedMonth()
    {
        $this->clearAlert();
        $this->loadConsumptions();
    }

    public function updatedSelectedGenerator()
    {
        $this->clearAlert();
        $this->loadConsumptions();
    }

    public function openConsumptionModal()
    {
        try {
            $this->authorize('create', FuelConsumption::class);
            $this->showConsumptionModal = true;
            $this->clearForm();
        } catch (AuthorizationException $e) {
            $this->setAlert('ليس لديك صلاحية لإضافة استهلاك وقود', 'danger');
        }
    }

    public function closeConsumptionModal()
    {
        $this->showConsumptionModal = false;
        $this->clearForm();
        $this->clearValidation();
    }

    private function clearForm()
    {
        $this->generator_id = '';
        $this->liters_consumed = '';
        $this->notes = '';
    }

    public function saveConsumption()
    {
        $this->validate();

        try {
            $this->authorize('create', FuelConsumption::class);

            FuelConsumption::recordConsumption(
                Auth::id(),
                $this->generator_id,
                $this->liters_consumed,
                $this->notes
            );

            $this->closeConsumptionModal();
            $this->loadConsumptions();
            $this->setAlert('تم إضافة استهلاك الوقود بنجاح', 'success');
        } catch (AuthorizationException $e) {
            $this->setAlert('ليس لديك صلاحية لإضافة استهلاك وقود', 'danger');
        } catch (Exception $e) {
            $this->setAlert($e->getMessage(), 'danger');
        }
    }

    public function confirmDelete($id)
    {
        $buttonIndex = Alert::title('تأكيد الحذف')
            ->buttons(['الغاء', 'نعم'])
            ->show('هل أنت متأكد من حذف استهلاك الوقود هذا؟');

        if ($buttonIndex === 1) {
            $this->deleteConsumption($id);
        }
    }

    public function deleteConsumption($consumptionId)
    {
        try {
            $consumption = FuelConsumption::where('user_id', Auth::id())
                ->findOrFail($consumptionId);

            $this->authorize('delete', $consumption);

            $consumption->delete();

            $this->loadConsumptions();
            $this->setAlert('تم حذف استهلاك الوقود بنجاح.', 'success');
        } catch (AuthorizationException $e) {
            $this->setAlert('لا يمكنك حذف استهلاك الوقود', 'danger');
        } catch (Exception $e) {
            $this->setAlert('حدث خطأ أثناء حذف استهلاك الوقود.', 'danger');
        }
    }

    public function render()
    {
        return view('livewire.fuel-consumptions-report');
    }
}
