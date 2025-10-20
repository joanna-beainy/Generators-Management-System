<div>
    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1"
             style="background-color: rgba(0,0,0,0.5);" wire:click.self="$set('showModal', false)">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content rounded-4 shadow">
                    <div class="modal-header bg-light">
                        <h5 class="modal-title">تحديث سعر الصرف</h5>
                        <button type="button" class="btn-close" wire:click="$set('showModal', false)"></button>
                    </div>
                    <div class="modal-body" dir="rtl">
                        <div class="mb-3">
                            <label for="exchangeRate" class="form-label">سعر الصرف (ل.ل لكل $)</label>
                            <input type="number" 
                                   id="exchangeRate" 
                                   wire:model="exchangeRate"
                                   class="form-control text-end" 
                                   min="1"
                                   step="1"
                                   placeholder="89500">
                            @error('exchangeRate')
                                <span class="text-danger">{{ $message }}</span> 
                            @enderror
                            
                            <!-- Show current value in LBP -->
                            <div class="mt-2 p-2 bg-light rounded text-center">
                                <small class="text-muted">القيمة الحالية: </small>
                                <strong class="text-success">1$ = {{ number_format($exchangeRate, 0) }} ل.ل</strong>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="$set('showModal', false)">إلغاء</button>
                        <button type="button" class="btn btn-primary" wire:click="updateRate">تحديث</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>