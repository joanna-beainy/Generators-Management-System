<?php

namespace App\View\Components;

use Illuminate\View\Component;

class CombinedPaymentReceipt extends Component
{
    public array $receiptData;

    /**
     * Create a new component instance.
     */
    public function __construct(array $receiptData)
    {
        $this->receiptData = $receiptData;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render()
    {
        return view('components.combined-payment-receipt');
    }
}
