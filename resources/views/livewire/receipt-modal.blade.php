<div>
    @if ($show)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);" wire:ignore.self>
            <div class="modal-dialog modal-lg" dir="rtl">
                <div class="modal-content">
                    <div class="modal-header bg-light">
                        <h5 class="modal-title">
                            <i class="bi bi-receipt-cutoff text-success me-2"></i>
                            {{ $mode === 'bulk' ? 'إيصالات المشتركين غير المسددين' : 'إيصال الدفع' }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>

                    <div class="modal-body" style="max-height:75vh; overflow-y:auto;">
                        
                        {{-- SINGLE RECEIPT MODE --}}
                        @if ($mode === 'single')
                            <div id="receipts-container">
                                <x-combined-payment-receipt :receiptData="$receiptData" />
                            </div>

                        {{-- BULK RECEIPTS MODE --}}
                        @elseif ($mode === 'bulk')

                            <!-- Search and Client Selection -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">ابحث عن المشترك</label>
                                    <div class="input-group">
                                        <input type="text" 
                                            wire:model="search" 
                                            wire:keydown.enter="handleSearch"
                                            class="form-control" 
                                            placeholder="اكتب اسم المشترك أو رقمه..."
                                            wire:loading.attr="disabled"
                                            style="text-align: right;">
                                        <button class="btn btn-outline-secondary" type="button" wire:click="handleSearch">
                                            <i class="bi bi-search"></i>
                                        </button>
                                    </div>
                                    <div wire:loading wire:target="handleSearch" class="small text-muted mt-1">
                                        <i class="bi bi-arrow-repeat spinner me-1"></i> جاري البحث...
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">المشترك المحدد</label>
                                    <select wire:model.live="selectedClientId" class="form-select" style="text-align: right;">
                                        <option value="">-- اختر المشترك --</option>
                                        @foreach($unpaidClients as $client)
                                            <option value="{{ $client->id }}" wire:key="client-{{ $client->id }}">
                                                {{ $client->id }} - {{ $client->full_name }} 
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            @if($search || $selectedClientId)
                                <div class="row mb-3">
                                    <div class="col-12">
                                        <button wire:click="resetFilters" class="btn btn-outline-primary btn-sm">
                                            <i class="bi bi-arrow-clockwise me-1"></i> عرض جميع الإيصالات
                                        </button>
                                    </div>
                                </div>
                            @endif

                            {{-- Receipts display --}}
                            @if (empty($receiptsData) || count($receiptsData) === 0)
                                @if($search || $selectedClientId)
                                    {{-- No results for search --}}
                                    <div class="alert alert-warning text-center">
                                        <i class="bi bi-search me-2"></i>
                                        @if($search && $selectedClientId)
                                            لا توجد نتائج للبحث "{{ $search }}" للمشترك المحدد
                                        @elseif($search)
                                            لا توجد نتائج للبحث "{{ $search }}"
                                        @elseif($selectedClientId)
                                            لا توجد نتائج للمشترك المحدد
                                        @endif
                                    </div>
                                @else
                                    {{-- No unpaid clients at all --}}
                                    <div class="alert alert-info text-center">
                                         ا يوجد مشتركين لديهم مبالغ مستحقة
                                    </div>
                                @endif
                            @else
                                <div id="receipts-container">
                                    @foreach ($receiptsData as $index => $receipt)
                                        <div class="receipt-wrapper">
                                            <x-combined-payment-receipt :receiptData="$receipt" />

                                            {{-- Separator --}}
                                            @if (!$loop->last)
                                                <div class="receipt-separator my-4">
                                                    <div class="d-flex align-items-center">
                                                        <div class="flex-grow-1 border-top border-2 border-primary"></div>
                                                        <div class="mx-3 text-primary fw-bold">
                                                            <i class="bi bi-receipt me-2"></i>
                                                            إيصال {{ $index + 2 }}
                                                        </div>
                                                        <div class="flex-grow-1 border-top border-2 border-primary"></div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        @endif
                    </div>

                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-secondary" wire:click="closeModal">
                            <i class="bi bi-x me-1"></i> إغلاق
                        </button>

                        @if (
                            ($mode === 'single' && !empty($receiptData)) ||
                            ($mode === 'bulk' && !empty($receiptsData) && count($receiptsData) > 0)
                        )
                            <button type="button" onclick="printReceipts()" class="btn btn-success">
                                <i class="bi bi-printer me-2"></i>
                                {{ $mode === 'bulk' ? 'طباعة جميع الإيصالات' : 'طباعة الإيصال' }}
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
    function printReceipts() {
        const receipts = document.querySelectorAll('.combined-receipt');
        if (!receipts.length) {
            alert('❌ لم يتم العثور على إيصالات للطباعة');
            return;
        }

        let printContent = '';
        receipts.forEach((receipt) => {
            const clone = receipt.cloneNode(true);
            clone.style.width = '18cm';
            clone.style.height = '18cm';
            clone.style.background = 'white';
            clone.style.display = 'flex';
            clone.style.flexDirection = 'column';
            clone.style.margin = '0 auto';

            printContent += `
                <div class="page-break" style="page-break-after: always; width: 18cm; height: 18cm;">
                    ${clone.outerHTML}
                </div>
            `;
        });

        const win = window.open('', '_blank');
        win.document.write(`
            <!DOCTYPE html>
            <html dir="rtl">
            <head>
                <meta charset="UTF-8">
                <title>طباعة الإيصالات</title>
                <style>
                    @media print {
                        @page { size: 18cm 18cm; margin: 0; }
                        body { margin: 0; padding: 0; font-family: Arial, sans-serif; }
                        .page-break { width: 18cm; height: 18cm; page-break-after: always; }
                    }
                </style>
            </head>
            <body>
                ${printContent}
                <script>
                    window.addEventListener('load', function() {
                        window.print();
                        setTimeout(() => window.close(), 100);
                    });
                <\/script>
            </body>
            </html>
        `);
        win.document.close(); 
    }
</script>
@endpush