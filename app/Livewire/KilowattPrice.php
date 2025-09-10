<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class KilowattPrice extends Component
{
    public $price;

    public function mount()
    {
        $this->price = Auth::user()->kilowattPrice->price ?? 0;
    }

    public function updatePrice()
    {
        $this->validate([
            'price' => 'required|numeric|min:0',
        ]);

        Auth::user()->kilowattPrice->update(['price' => $this->price]);

        session()->flash('success_kilowatt', 'تم تحديث سعر الكيلووات بنجاح.');
    }

    public function render()
    {
        return view('livewire.kilowatt-price');
    }
}
