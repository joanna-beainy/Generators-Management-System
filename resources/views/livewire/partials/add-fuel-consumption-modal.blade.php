<div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5)">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title">
                    <i class="bi bi-plus-circle me-2"></i>
                    إضافة استهلاك وقود جديد
                </h5>
                <button type="button" class="btn-close" wire:click="closeConsumptionModal"></button>
            </div>
            <div class="modal-body">
                <form wire:submit="saveConsumption">

                    <!-- Available Fuel Info -->
                    <div class="alert alert-info">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-info-circle me-2"></i>
                            <div>
                                <small class="fw-bold">الوقود المتاح: {{ number_format(\App\Models\FuelPurchase::where('user_id', auth()->id())->sum('remaining_liters')) }} لتر</small>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">المولد <span class="text-danger">*</span></label>
                        <select class="form-select" wire:model="generator_id">
                            <option value="">اختر المولد</option>
                            @foreach($generators as $generator)
                                <option value="{{ $generator->id }}">{{ $generator->name }}</option>
                            @endforeach
                        </select>
                        @error('generator_id') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">اللترات المستهلكة <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" style="text-align: right;" wire:model="liters_consumed" placeholder="0" min="1">
                        @error('liters_consumed') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">ملاحظات (اختياري)</label>
                        <textarea class="form-control" rows="3" wire:model="notes" placeholder="أدخل ملاحظات إضافية..."></textarea>
                        @error('notes') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" wire:click="closeConsumptionModal">
                            <i class="bi bi-x-circle me-1"></i> إلغاء
                        </button>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle me-1"></i> حفظ
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>