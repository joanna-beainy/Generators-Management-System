<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\GeneratorMaintenance;
use App\Models\Generator;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Exception;

class GeneratorsMaintenanceReport extends Component
{
    public $maintenances;
    public $selectedYear;
    public $selectedMonth;
    public $selectedGenerator;
    public $years = [];
    public $months = [];
    public $generators = [];
    public $alertMessage = null;
    public $alertType = null;
    public $showMaintenanceModal = false;

    // Form fields
    public $generator_id;
    public $amount;
    public $description;

    protected $rules = [
        'generator_id' => 'required|exists:generators,id',
        'amount' => 'required|numeric|min:0.50',
        'description' => 'nullable|string',
    ];

    protected $messages = [
        'generator_id.required' => 'يرجى اختيار المولد.',
        'generator_id.exists' => 'المولد المحدد غير صالح.',
        'amount.required' => 'يرجى إدخال المبلغ.',
        'amount.numeric' => 'يرجى إدخال رقم صحيح للمبلغ.',
        'amount.min' => 'يجب أن يكون المبلغ على الأقل 0.50.',
    ];

    public function mount()
    {
        try {
            $this->authorize('viewAny', GeneratorMaintenance::class);

            $this->generators = Generator::where('user_id', Auth::id())->get();
            $this->initializeFilters();
            $this->loadMaintenances();

        } catch (AuthorizationException $e) {
            $this->setAlert('ليس لديك صلاحية لعرض مصاريف الصيانة', 'danger');
        } catch (Exception $e) {
            $this->setAlert('حدث خطأ أثناء تحميل بيانات الصيانة', 'danger');
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

            // Get available years from user's maintenances
            $this->years = GeneratorMaintenance::where('user_id', Auth::id())
                ->selectRaw('YEAR(created_at) as year')
                ->distinct()
                ->orderBy('year', 'desc')
                ->pluck('year')
                ->toArray();

            if (empty($this->years)) {
                $this->years = [$this->selectedYear];
            }

            $this->months = [
                '1' => 'كانون الثاني',
                '2' => 'شباط',
                '3' => 'آذار',
                '4' => 'نيسان',
                '5' => 'أيار',
                '6' => 'حزيران',
                '7' => 'تموز',
                '8' => 'آب',
                '9' => 'أيلول',
                '10' => 'تشرين الأول',
                '11' => 'تشرين الثاني',
                '12' => 'كانون الأول',
            ];

        } catch (Exception $e) {
            $this->setAlert('حدث خطأ أثناء تهيئة الفلاتر', 'danger');
        }
    }

    public function loadMaintenances()
    {
        try {
            $this->maintenances = GeneratorMaintenance::with(['generator'])
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
            $this->setAlert('حدث خطأ أثناء تحميل بيانات الصيانة', 'danger');
            $this->maintenances = collect();
        }
    }

    public function updatedSelectedYear()
    {
        $this->clearAlert();
        $this->loadMaintenances();
    }

    public function updatedSelectedMonth()
    {
        $this->clearAlert();
        $this->loadMaintenances();
    }

    public function updatedSelectedGenerator()
    {
        $this->clearAlert();
        $this->loadMaintenances();
    }

    public function openMaintenanceModal()
    {
        try {
            $this->authorize('create', GeneratorMaintenance::class);
            $this->showMaintenanceModal = true;
            $this->clearForm();
        } catch (AuthorizationException $e) {
            $this->setAlert('ليس لديك صلاحية لإضافة مصاريف صيانة', 'danger');
        }
    }

    public function closeMaintenanceModal()
    {
        $this->showMaintenanceModal = false;
        $this->clearForm();
        $this->clearValidation();
    }

    private function clearForm()
    {
        $this->generator_id = '';
        $this->amount = '';
        $this->description = '';
    }

    public function saveMaintenance()
    {
        $this->validate();

        try {
            $this->authorize('create', GeneratorMaintenance::class);

            GeneratorMaintenance::create([
                'user_id' => Auth::id(),
                'generator_id' => $this->generator_id,
                'amount' => $this->amount,
                'description' => $this->description,
            ]);

            $this->closeMaintenanceModal();
            $this->loadMaintenances();
            $this->setAlert('تم إضافة مصاريف الصيانة بنجاح', 'success');

        } catch (AuthorizationException $e) {
            $this->setAlert('ليس لديك صلاحية لإضافة مصاريف صيانة', 'danger');
        } catch (Exception $e) {
            $this->setAlert('حدث خطأ أثناء حفظ بيانات الصيانة', 'danger');
        }
    }

    public function deleteMaintenance($maintenanceId)
    {
        try {
            $maintenance = GeneratorMaintenance::where('user_id', Auth::id())
                ->findOrFail($maintenanceId);

            $this->authorize('delete', $maintenance);

            $maintenance->delete();

            $this->loadMaintenances();
            $this->setAlert('تم حذف مصاريف الصيانة بنجاح.', 'success');

        } catch (AuthorizationException $e) {
            $this->setAlert('ليس لديك صلاحية لحذف مصاريف الصيانة هذه.', 'danger');
        } catch (Exception $e) {
            $this->setAlert('حدث خطأ أثناء حذف مصاريف الصيانة.', 'danger');
        }
    }

    public function render()
    {
        return view('livewire.generators-maintenance-report');
    }
}