<div>
    @if ($show)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.4); backdrop-filter: blur(4px);" wire:ignore.self>
            <div class="modal-dialog modal-lg modal-dialog-centered" dir="rtl">
                <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
                    <div class="modal-header bg-success bg-opacity-10 border-bottom border-success border-opacity-25 pb-3">
                        <h5 class="modal-title fw-bold text-success">
                            <i class="bi bi-receipt-cutoff me-2"></i>
                            {{ $mode === 'bulk' ? 'إيصالات المشتركين غير المسددين' : 'إيصال الدفع' }}
                        </h5>
                        <button type="button" class="btn-close shadow-none" wire:click="closeModal"></button>
                    </div>

                    <div class="modal-body p-4" style="max-height:75vh; overflow-y:auto;">
                        
                        {{-- SINGLE RECEIPT MODE --}}
                        @if ($mode === 'single')
                            <div id="receipts-container">
                                <x-combined-payment-receipt :receiptData="$receiptData" />
                            </div>

                        {{-- BULK RECEIPTS MODE --}}
                        @elseif ($mode === 'bulk')

                            <!-- Search and Client Selection -->
                            <div class="card border-0 shadow-sm rounded-4 mb-4 bg-light">
                                <div class="card-body p-3" x-data x-on:focus-receipt-search.window="$nextTick(() => $refs.receiptSearch && $refs.receiptSearch.focus())">
                                    <div class="row g-3">
                                        <div class="col-md-8">
                                            <label class="form-label fw-bold medium"><i class="bi bi-search me-1"></i>ابحث عن المشترك</label>
                                            <div class="input-group shadow-sm rounded-pill overflow-hidden">
                                                <input type="text" 
                                                    x-ref="receiptSearch"
                                                    autofocus
                                                    wire:model.live.debounce.300ms="search" 
                                                    wire:keydown.enter="handleSearch"
                                                    class="form-control border-0" 
                                                    placeholder="اكتب اسم المشترك أو رقمه..."
                                                    wire:loading.attr="disabled"
                                                    style="text-align: right; box-shadow: none;">
                                                <button class="btn btn-white border-0" type="button" wire:click="handleSearch">
                                                    <i class="bi bi-search text-secondary"></i>
                                                </button>
                                            </div>
                                            @if($showSearchResults && $search)
                                                <div class="list-group w-100 shadow-sm border rounded-3 mt-1 overflow-auto bg-white" style="max-height: 260px;">
                                                    @forelse($unpaidClients as $client)
                                                        <button type="button"
                                                                class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
                                                                wire:click="selectClient({{ $client->id }})">
                                                            <span>{{ $client->id }} - {{ $client->full_name }}</span>
                                                            <i class="bi bi-person text-muted"></i>
                                                        </button>
                                                    @empty
                                                        <div class="list-group-item text-muted small">
                                                            لا توجد نتائج
                                                        </div>
                                                    @endforelse
                                                </div>
                                            @endif
                                            <div wire:loading wire:target="handleSearch,search" class="small text-muted mt-2">
                                                <i class="bi bi-arrow-repeat spinner me-1"></i> جاري البحث...
                                            </div>
                                        </div>
                                        @if($search || $selectedClientId)
                                            <div class="col-md-4 d-flex align-items-end">
                                                <button wire:click="resetFilters" class="btn btn-outline-success btn-sm rounded-pill w-100">
                                                    <i class="bi bi-arrow-clockwise me-1"></i> عرض جميع الإيصالات
                                                </button>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Receipts display --}}
                            @if (empty($receiptsData) || count($receiptsData) === 0)
                                @if($search || $selectedClientId)
                                    {{-- No results for search --}}
                                    <div class="alert alert-warning text-center rounded-3 shadow-sm border-0">
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
                                    <div class="text-center py-5">
                                        <i class="bi bi-check-circle display-4 text-success mb-3 opacity-50"></i>
                                        <h5 class="text-muted fw-bold">لا يوجد مشتركين لديهم مبالغ مستحقة</h5>
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
                                                        <div class="flex-grow-1 border-top border-2 border-primary border-opacity-25"></div>
                                                        <div class="mx-3 text-primary fw-bold badge bg-primary bg-opacity-10 rounded-pill px-3 py-2">
                                                            <i class="bi bi-receipt me-2"></i>
                                                            إيصال {{ $index + 2 }}
                                                        </div>
                                                        <div class="flex-grow-1 border-top border-2 border-primary border-opacity-25"></div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        @endif
                    </div>

                    <div class="modal-footer border-0 p-3 bg-light bg-opacity-50 justify-content-between">
                        <button type="button" class="btn btn-outline-danger rounded-pill px-4" wire:click="closeModal">
                            <i class="bi bi-x me-1"></i> إغلاق
                        </button>

                        @if (
                            ($mode === 'single' && !empty($receiptData)) ||
                            ($mode === 'bulk' && !empty($receiptsData) && count($receiptsData) > 0)
                        )
                            <button type="button" onclick="window.printReceipts()" class="btn btn-success rounded-pill px-4 shadow-sm">
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

@script
    <script>
        window.printReceipts = function() {
            const receipts = document.querySelectorAll('.combined-receipt');
            if (!receipts.length) {
                alert('❌ لم يتم العثور على إيصالات للطباعة');
                return;
            }

            const printWin = window.open('', '_blank');
            let printContent = '';
            
            receipts.forEach((receipt) => {
                printContent += '<div class="page-break">' + receipt.outerHTML + '</div>';
            });

            const styles = `
                <style>
                    @media print {
                        @page { 
                            size: "Receipt_18x18"; 
                            margin: 0 !important;         
                        }
                        html, body {
                            margin: 0 !important; 
                            padding: 0 !important; 
                            width: 100% !important; 
                            height: auto !important; 
                            background: white !important; 
                        }
                        .page-break { 
                            width: 18cm !important; 
                            height: 18cm !important; 
                            margin: 0 auto !important; 
                            padding: 0.3cm !important; 
                            page-break-after: always !important; 
                            display: flex !important; 
                            align-items: center !important; 
                            justify-content: center !important; 
                            position: relative !important; 
                            box-sizing: border-box !important; 
                        }
                        .combined-receipt { 
                            width: 17.4cm !important; 
                            height: 17.4cm !important; 
                            margin: 0 !important; 
                            padding: 0 !important; 
                            display: flex !important; 
                            flex-direction: column !important; 
                            position: relative !important; 
                            box-sizing: border-box !important; 
                        }
                    }
                </style>
            `;

            printWin.document.write('<!DOCTYPE html><html dir="rtl"><head><meta charset="UTF-8"><title>طباعة الإيصالات</title>' + styles + '</head><body>' + printContent + '</body></html>');
            printWin.document.close();

            printWin.onload = function() {
                setTimeout(() => {
                    printWin.print();
                    printWin.close();
                }, 300);
            };
        };
    </script>
@endscript
