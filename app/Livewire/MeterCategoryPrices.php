<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\MeterCategory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Access\AuthorizationException;

class MeterCategoryPrices extends Component
{
    public $categories = [];
    public $newCategoryName = '';
    public $newCategoryPrice = '';
    public $showAddForm = false;

    public function mount()
    {
        $this->loadCategories();
    }

    public function loadCategories()
    {
        $this->categories = Auth::user()->meterCategories->map(function ($category) {
            return [
                'id' => $category->id,
                'name' => $category->category,
                'price' => $category->price,
            ];
        })->toArray();
    }

    public function toggleAddForm()
    {
        $this->showAddForm = !$this->showAddForm;
    }

    public function addCategory()
    {
        $this->validate([
            'newCategoryName' => 'required|string|max:255',
            'newCategoryPrice' => 'required|numeric|min:0',
        ]);

        $this->newCategoryName = strip_tags(trim($this->newCategoryName));

        $category = MeterCategory::create([
            'user_id' => Auth::id(),
            'category' => $this->newCategoryName,
            'price' => $this->newCategoryPrice,
        ]);

        $this->newCategoryName = '';
        $this->newCategoryPrice = '';
        $this->showAddForm = false;
        session()->flash('success_category', 'تمت إضافة الفئة بنجاح.');
        $this->loadCategories();
    }

    public function updatePrices()
    {
        $this->validate([
            'categories.*.price' => 'required|numeric|min:0',
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

        session()->flash('success_category', 'تم تحديث أسعار الفئات بنجاح.');
        $this->loadCategories();
    }

    public function deleteCategory($id)
    {
        $category = MeterCategory::where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if ($category) {
            try {
                $this->authorize('delete', $category);
                $category->delete();
                session()->flash('success_category', 'تم حذف الفئة بنجاح.');
            } catch (AuthorizationException $e) {
                session()->flash('error_category', 'لا يمكن حذف هذه الفئة لأنها مرتبطة بمشتركين.');
            }

            $this->loadCategories();
        }
    }

    public function render()
    {
        return view('livewire.meter-category-prices');
    }
}
