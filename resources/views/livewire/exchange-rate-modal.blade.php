<div>
    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1"
             style="background-color: rgba(0,0,0,0.5);" wire:click.self="closeModal">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content rounded-4 shadow">
                    <div class="modal-header bg-light">
                        <h5 class="modal-title">
                            <i class="bi bi-currency-exchange text-success me-2"></i>
                            تحديث سعر الصرف
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <div class="modal-body" dir="rtl">
                        {{-- Error Alert (only shows if update fails) --}}
                        @if ($alertMessage)
                            <div 
                                x-data="{ show: true }" 
                                x-show="show" 
                                x-init="setTimeout(() => { show = false; $wire.set('alertMessage', null) }, 5000)" 
                                x-transition:leave="transition ease-in duration-300"
                                x-transition:leave-start="opacity-100"
                                x-transition:leave-end="opacity-0"
                                class="alert alert-{{ $alertType }} alert-dismissible fade show text-center rounded-3 shadow-sm mb-4">
                                <i class="bi {{ $alertType === 'success' ? 'bi-check-circle' : 'bi-exclamation-triangle' }} me-1"></i>
                                {{ $alertMessage }}
                                <button type="button" class="btn-close" wire:click="$set('alertMessage', null)"></button>
                            </div>
                        @endif

                        <div class="mb-3">
                            <label for="exchangeRate" class="form-label">سعر الصرف (ل.ل لكل $)</label>
                            <input type="number" 
                                   id="exchangeRate" 
                                   wire:model="exchangeRate"
                                   class="form-control text-end @error('exchangeRate') is-invalid @enderror" 
                                   min="1"
                                   step="1"
                                   placeholder="89500">
                            @error('exchangeRate')
                                <div class="invalid-feedback">{{ $message }}</div> 
                            @enderror
                            
                            <!-- Show current value in LBP -->
                            <div class="mt-2 p-2 bg-light rounded text-center">
                                <small class="text-muted">القيمة الحالية: </small>
                                <strong class="text-success">1$ = {{ number_format($exchangeRate, 0) }} ل.ل</strong>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModal">إلغاء</button>
                        <button type="button" class="btn btn-success" wire:click="updateRate">تحديث</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>