<div class="combined-receipt" dir="rtl"
     style="width: 18cm; height: 18cm; background: white; display: flex; flex-direction: column; margin: 0 auto;">

    <!-- Client Copy - Top Half -->
    <div class="receipt-copy client-copy" style="height: 9cm; padding: 0.3cm; overflow: hidden;">
        @include('components.partials.single-receipt', [
            'receiptData' => $receiptData,
            'copyType' => 'CLIENT'
        ])
    </div>

    <!-- Owner Copy - Bottom Half -->
    <div class="receipt-copy owner-copy" style="height: 9cm; padding: 0.3cm; overflow: hidden;">
        @include('components.partials.single-receipt', [
            'receiptData' => $receiptData,
            'copyType' => 'OWNER'
        ])
    </div>
</div>
