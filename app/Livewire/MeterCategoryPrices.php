<?php

namespace App\Livewire;

use Exception;
use Livewire\Component;
use App\Models\MeterCategory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;

class MeterCategoryPrices extends Component
{
    public $categories = [];
    public $newCategoryName = '';
    public $newCategoryPrice = '';
    public $showAddForm = false;
    public $alertMessage = null;
    public $alertType = null;

     protected $listeners = ['category-deleted' => 'loadCategories'];

    public function mount()
    {
        try {
            // Check if user can view meter categories
            $this->authorize('viewAny', MeterCategory::class);
            $this->loadCategories();
        } catch (AuthorizationException $e) {
            $this->setAlert('ليس لديك صلاحية لعرض فئات العدادات', 'danger');
        }
    }

    private function setAlert($message, $type = 'success')
    {
        $this->alertMessage = $message;
        $this->alertType = $type;
    }

    public function loadCategories()
    {
        try {
            $this->categories = Auth::user()->meterCategories->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->category,
                    'price' => $category->price,
                ];
            })->toArray();
        } catch (Exception $e) {
            $this->setAlert('حدث خطأ أثناء تحميل فئات العدادات', 'danger');
            $this->categories = [];
        }
    }

    public function toggleAddForm()
    {
        $this->showAddForm = !$this->showAddForm;
        $this->alertMessage = null;
        $this->alertType = null;
    }

    public function addCategory()
    {
        try {
            // Check if user can create meter categories
            $this->authorize('create', MeterCategory::class);

            $this->validate([
                'newCategoryName' => 'required|string|max:255|unique:meter_categories,category,NULL,id,user_id,' . Auth::id(),
                'newCategoryPrice' => 'required|numeric|min:0',
            ], [
                'newCategoryName.required' => 'يرجى ادخال اسم الفئة.',
                'newCategoryName.unique' => 'اسم الفئة موجود.',
                'newCategoryPrice.required' => 'يرجى ادخال سعر الفئة.',
                'newCategoryPrice.numeric' => 'يجب أن يكون سعر الفئة رقمًا.',
                'newCategoryPrice.min' => 'لا يمكن أن يكون سعر الفئة أقل من صفر.',
            ]);

            $this->newCategoryName = strip_tags(trim($this->newCategoryName));

            MeterCategory::create([
                'user_id' => Auth::id(),
                'category' => $this->newCategoryName,
                'price' => $this->newCategoryPrice,
            ]);

            $this->reset(['newCategoryName', 'newCategoryPrice', 'showAddForm']);
            $this->setAlert('تمت إضافة الفئة بنجاح.', 'success');
            $this->loadCategories();
        } catch (ValidationException $e) {
            throw $e;
        } catch (AuthorizationException $e) {
            $this->setAlert('ليس لديك صلاحية لإضافة فئات عدادات', 'danger');
        } catch (Exception $e) {
            $this->setAlert('حدث خطأ أثناء إضافة الفئة. يرجى المحاولة لاحقاً.', 'danger');
        }
    }

    public function updatePrices()
    {
        try {
            $this->validate([
                'categories.*.price' => 'required|numeric|min:0',
            ], [
                'categories.*.price.required' => 'يرجى ادخال سعر الفئة.',
                'categories.*.price.numeric' => 'يجب أن يكون سعر الفئة رقمًا.',
                'categories.*.price.min' => 'لا يمكن أن يكون سعر الفئة أقل من صفر.',
            ]);

            foreach ($this->categories as $data) {
                $category = MeterCategory::where('id', $data['id'])
                    ->where('user_id', Auth::id())
                    ->first();

                if ($category) {
                    $this->authorize('update', $category);
                    $category->update(['price' => $data['price']]);
                }
            }

            $this->setAlert('تم تحديث أسعار الفئات بنجاح.', 'success');
            $this->loadCategories();
        } catch (ValidationException $e) {
            throw $e;
        } catch (AuthorizationException $e) {
            $this->setAlert('ليس لديك صلاحية لتحديث هذه الفئة.', 'danger');
        } catch (Exception $e) {
            $this->setAlert('حدث خطأ أثناء تحديث الأسعار. يرجى المحاولة لاحقاً.', 'danger');
        }
    }

    public function deleteCategory($id)
    {
        try {
            $category = MeterCategory::where('id', $id)
                ->where('user_id', Auth::id())
                ->first();

            if ($category) {
                $this->authorize('delete', $category);
                $category->delete();
                $this->setAlert('تم حذف الفئة بنجاح.', 'success');
            } else {
                $this->setAlert('الفئة غير موجودة.', 'danger');
            }

            $this->dispatch('category-deleted');

        } catch (AuthorizationException $e) {
            $this->setAlert('لا يمكن حذف هذه الفئة لأنها مرتبطة بمشتركين.', 'danger');
        } catch (Exception $e) {
            $this->setAlert('حدث خطأ أثناء حذف الفئة. يرجى المحاولة لاحقاً.', 'danger');
        }
    }

    public function render()
    {
        return view('livewire.meter-category-prices');
    }
}