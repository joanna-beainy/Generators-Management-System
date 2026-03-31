<?php

namespace App\Livewire;

use App\Models\FuelPurchase;
use App\Support\ArabicMonth;
use Carbon\Carbon;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Native\Desktop\Facades\Alert;

class FuelPurchasesReport extends Component
{
    public $purchases;
    public $selectedYear;
    public $selectedMonth;
    public $years = [];
    public $months = [];
    public $alertMessage = null;
    public $alertType = null;
    public $showPurchaseModal = false;

    public $totalAvailableLiters;

    public $company;
    public $liters_purchased;
    public $total_price;
    public $description;

    protected $rules = [
        'company' => 'required|string|max:255',
        'liters_purchased' => 'required|integer|min:1',
        'total_price' => 'required|numeric|min:0.50',
        'description' => 'nullable|string',
    ];

    protected $messages = [
        'company.required' => 'يرجى إدخال اسم الشركة.',
        'liters_purchased.required' => 'يرجى إدخال اللترات المشتراة.',
        'liters_purchased.numeric' => 'يرجى إدخال رقم صالح للترات .',
        'liters_purchased.min' => 'يجب أن تكون اللترات المشتراة على الأقل 1.',
        'total_price.required' => 'يرجى إدخال السعر الإجمالي.',
        'total_price.numeric' => 'يرجى إدخال رقم صالح للسعر الإجمالي.',
        'total_price.min' => 'يجب أن يكون السعر الإجمالي على الأقل 0.50.',
    ];

    public function mount()
    {
        try {
            $this->authorize('viewAny', FuelPurchase::class);
            $this->initializeFilters();
            $this->loadPurchases();
            $this->loadTotalStatistics();
        } catch (AuthorizationException $e) {
            $this->setAlert('ليس لديك صلاحية لعرض مشتريات الوقود', 'danger');
        } catch (Exception $e) {
            $this->setAlert('حدث خطأ أثناء تحميل بيانات المشتريات', 'danger');
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

            $this->years = FuelPurchase::where('user_id', Auth::id())
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

    public function loadPurchases()
    {
        try {
            $this->purchases = FuelPurchase::where('user_id', Auth::id())
                ->when($this->selectedYear, function ($query) {
                    $query->whereYear('created_at', $this->selectedYear);
                })
                ->when($this->selectedMonth, function ($query) {
                    $query->whereMonth('created_at', $this->selectedMonth);
                })
                ->orderBy('created_at', 'desc')
                ->get();
        } catch (Exception $e) {
            $this->setAlert('حدث خطأ أثناء تحميل بيانات المشتريات', 'danger');
            $this->purchases = collect();
        }
    }

    public function loadTotalStatistics()
    {
        try {
            $this->totalAvailableLiters = FuelPurchase::where('user_id', Auth::id())
                ->sum('remaining_liters');
        } catch (Exception $e) {
            $this->totalAvailableLiters = 0;
        }
    }

    public function updatedSelectedYear()
    {
        $this->clearAlert();
        $this->loadPurchases();
    }

    public function updatedSelectedMonth()
    {
        $this->clearAlert();
        $this->loadPurchases();
    }

    public function openPurchaseModal()
    {
        try {
            $this->authorize('create', FuelPurchase::class);
            $this->showPurchaseModal = true;
            $this->clearForm();
        } catch (AuthorizationException $e) {
            $this->setAlert('ليس لديك صلاحية لإضافة مشتريات وقود', 'danger');
        }
    }

    public function closePurchaseModal()
    {
        $this->showPurchaseModal = false;
        $this->clearForm();
        $this->clearValidation();
    }

    private function clearForm()
    {
        $this->company = '';
        $this->liters_purchased = '';
        $this->total_price = '';
        $this->description = '';
    }

    public function savePurchase()
    {
        $this->validate();

        try {
            $this->authorize('create', FuelPurchase::class);

            FuelPurchase::create([
                'user_id' => Auth::id(),
                'company' => $this->company,
                'liters_purchased' => $this->liters_purchased,
                'remaining_liters' => $this->liters_purchased,
                'total_price' => $this->total_price,
                'description' => $this->description,
            ]);

            $this->closePurchaseModal();
            $this->loadPurchases();
            $this->loadTotalStatistics();
            $this->setAlert('تم إضافة شراء الوقود بنجاح', 'success');
        } catch (AuthorizationException $e) {
            $this->setAlert('ليس لديك صلاحية لإضافة مشتريات وقود', 'danger');
        } catch (Exception $e) {
            $this->setAlert('حدث خطأ أثناء حفظ بيانات الشراء', 'danger');
        }
    }

    public function confirmDelete($id)
    {
        $buttonIndex = Alert::title('تأكيد الحذف')
            ->buttons(['الغاء', 'نعم'])
            ->show('هل أنت متأكد من حذف شراء الوقود هذا؟');

        if ($buttonIndex === 1) {
            $this->deletePurchase($id);
        }
    }

    public function deletePurchase($purchaseId)
    {
        try {
            $purchase = FuelPurchase::where('user_id', Auth::id())
                ->findOrFail($purchaseId);

            $this->authorize('delete', $purchase);

            $purchase->delete();

            $this->loadPurchases();
            $this->loadTotalStatistics();
            $this->setAlert('تم حذف شراء الوقود بنجاح.', 'success');
        } catch (AuthorizationException $e) {
            $this->setAlert('لا يمكنك حذف شراء الوقود', 'danger');
        } catch (Exception $e) {
            $this->setAlert('حدث خطأ أثناء حذف شراء الوقود.', 'danger');
        }
    }

    public function render()
    {
        return view('livewire.fuel-purchases-report');
    }
}
