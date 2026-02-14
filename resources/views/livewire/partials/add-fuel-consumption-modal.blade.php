<div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5); backdrop-filter: blur(5px);">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-success bg-opacity-10 text-success">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-plus-circle me-2"></i>
                    إضافة استهلاك وقود جديد
                </h5>
                <button type="button" class="btn-close shadow-none" wire:click="closeConsumptionModal"></button>
            </div>
            <div class="modal-body p-4">
                <form wire:submit="saveConsumption">

                    <!-- Available Fuel Info -->
                    <div class="alert alert-success bg-opacity-10 border-success border-opacity-25 rounded-3 mb-4">
                        <div class="d-flex align-items-center text-success">
                            <i class="bi bi-info-circle-fill me-2 fs-5"></i>
                            <div>
                                <small class="fw-bold">الوقود المتاح حالياً:</small>
                                <span class="fw-bold font-monospace mx-1">{{ number_format(\App\Models\FuelPurchase::where('user_id', auth()->id())->sum('remaining_liters')) }}</span>
                                <small>لتر</small>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary">المولد <span class="text-danger">*</span></label>
                        <div class="shadow-sm rounded-pill overflow-hidden border">
                            <select class="form-select border-0" wire:model="generator_id" style="box-shadow: none;">
                                <option value="">اختر المولد</option>
                                @foreach($generators as $generator)
                                    <option value="{{ $generator->id }}">{{ $generator->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @error('generator_id') <span class="text-danger small ms-2">{{ $message }}</span> @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary">اللترات المستهلكة <span class="text-danger">*</span></label>
                        <div class="shadow-sm rounded-pill overflow-hidden border">
                            <input type="number" class="form-control border-0" style="text-align: right; box-shadow: none;" wire:model="liters_consumed" placeholder="0" min="1">
                        </div>
                        @error('liters_consumed') <span class="text-danger small ms-2">{{ $message }}</span> @enderror
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold text-secondary">ملاحظات (اختياري)</label>
                        <textarea class="form-control shadow-sm rounded-4 border" rows="3" wire:model="notes" placeholder="أدخل ملاحظات إضافية..."></textarea>
                        @error('notes') <span class="text-danger small ms-2">{{ $message }}</span> @enderror
                    </div>
                    
                    <div class="modal-footer border-0 p-0 justify-content-start">
                        <button type="submit" class="btn btn-success rounded-pill px-5 shadow-sm">
                            <i class="bi bi-check-circle me-1"></i> حفظ
                        </button>
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" wire:click="closeConsumptionModal">
                            <i class="bi bi-x-circle me-1"></i> إلغاء
                        </button>
                        
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
