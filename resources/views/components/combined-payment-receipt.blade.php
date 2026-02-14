<div class="combined-receipt" dir="rtl"
     style="width: 18cm; height: 18cm; background: white; display: flex; flex-direction: column; margin: 0; padding: 0.3cm; box-sizing: border-box;">

    <!-- Client Copy - Top Half -->
    <div class="receipt-copy client-copy" style="flex: 1; height: 9cm; padding: 0.4cm; overflow: hidden; box-sizing: border-box; border-bottom: 1px dashed #ccc;">
        @include('components.partials.single-receipt')
    </div>

    <!-- Owner Copy - Bottom Half -->
    <div class="receipt-copy owner-copy" style="flex: 1; height: 9cm; padding: 0.4cm; overflow: hidden; box-sizing: border-box;">
        @include('components.partials.single-receipt')
    </div>
</div>
