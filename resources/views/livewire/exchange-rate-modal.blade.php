<div>
    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1"
             style="background-color: rgba(0,0,0,0.4); backdrop-filter: blur(4px);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
                    <div class="modal-header bg-success bg-opacity-10 border-bottom border-success border-opacity-25 pb-3">
                        <h5 class="modal-title fw-bold text-success">
                            <i class="bi bi-currency-exchange me-2"></i>
                            تحديث سعر الصرف
                        </h5>
                        <button type="button" class="btn-close shadow-none" wire:click="closeModal"></button>
                    </div>
                    
                    <div class="modal-body p-4" dir="rtl">
                        {{-- Error Alert (only shows if update fails) --}}
                        @if ($alertMessage)
                            <div 
                                x-data="{ show: true }" 
                                x-show="show" 
                                x-init="setTimeout(() => { show = false; $wire.set('alertMessage', null) }, 5000)" 
                                x-transition:leave="transition ease-in duration-300"
                                x-transition:leave-start="opacity-100"
                                x-transition:leave-end="opacity-0">
                                <div class="alert alert-{{ $alertType }} border-0 text-center rounded-3 shadow-sm mb-4 position-relative">
                                    <button type="button" class="btn-close position-absolute top-50 translate-middle-y" style="right: 1rem;" wire:click="$set('alertMessage', null)"></button>
                                    <div class="d-flex align-items-center justify-content-center">
                                        <i class="bi {{ $alertType === 'success' ? 'bi-check-circle' : 'bi-exclamation-triangle' }} me-2"></i>
                                        {{ $alertMessage }}
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="mb-3">
                            <label for="exchangeRate" class="form-label fw-bold">
                                <i class="bi bi-coin me-1"></i> سعر الصرف (ل.ل لكل $)
                            </label>
                            <div class="input-group shadow-sm rounded-pill overflow-hidden border">
                                <span class="input-group-text bg-light border-0 ps-4 text-muted">LBP</span>
                                <input type="number" 
                                       id="exchangeRate" 
                                       wire:model="exchangeRate"
                                       class="form-control border-0 text-center fw-bold fs-5 text-success @error('exchangeRate') is-invalid @enderror" 
                                       min="1"
                                       step="1"
                                       placeholder="89500"
                                       style="box-shadow: none;">
                            </div>
                            @error('exchangeRate')
                                <div class="invalid-feedback">{{ $message }}</div> 
                            @enderror
                            
                            <!-- Show current value in LBP -->
                            <div class="mt-4 p-3 bg-light bg-opacity-50 border rounded-4 text-center">
                                <small class="text-muted medium d-block mb-1">القيمة الحالية للدولار</small>
                                <strong class="text-success fs-4 font-monospace">1$ = {{ number_format((float)$exchangeRate, 0) }} ل.ل</strong>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-0 p-4 bg-light bg-opacity-50 justify-content-between">
                        <button type="button" class="btn btn-outline-danger rounded-pill px-4" wire:click="closeModal">
                            <i class="bi bi-x me-1"></i> اغلاق
                        </button>
                        <button type="button" class="btn btn-success rounded-pill px-5 shadow-sm" wire:click="updateRate">
                            <i class="bi bi-arrow-repeat me-2"></i> تحديث السعر
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
