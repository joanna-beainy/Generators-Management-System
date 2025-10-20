<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\ExchangeRate;
use Illuminate\Support\Facades\Auth;

class ExchangeRateModal extends Component
{
    public $showModal = false;
    public $exchangeRate;

    protected $listeners = ['openExchangeRateModal' => 'openModal'];

    public function mount()
    {
        $this->exchangeRate = ExchangeRate::where('user_id', Auth::id())->value('exchange_rate') ?? 89500;
    }

    public function openModal()
    {
        $this->showModal = true;
    }

    public function updateRate()
    {
        $this->validate([
            'exchangeRate' => 'required|integer|min:1',
        ], [
            'exchangeRate.required' => 'سعر الصرف مطلوب',
            'exchangeRate.min' => 'سعر الصرف يجب أن يكون أكبر من الصفر',
        ]);

        ExchangeRate::updateOrCreate(
            ['user_id' => Auth::id()],
            ['exchange_rate' => $this->exchangeRate]
        );

        return redirect()->route('users.dashboard')->with('success', '✅ تم تحديث سعر الصرف بنجاح!');
    }

    public function render()
    {
        return view('livewire.exchange-rate-modal');
    }
}