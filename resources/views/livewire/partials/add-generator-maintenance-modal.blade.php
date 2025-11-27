<div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5)">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title">
                    <i class="bi bi-plus-circle me-2"></i>
                    إضافة مصاريف صيانة جديدة
                </h5>
                <button type="button" class="btn-close" wire:click="closeMaintenanceModal"></button>
            </div>
            <div class="modal-body">
                <form wire:submit="saveMaintenance">
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
                        <label class="form-label fw-bold">المبلغ ($) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" class="form-control" style="text-align: right;" wire:model="amount" placeholder="0.00" min="0.01">
                        @error('amount') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">الوصف (اختياري)</label>
                        <textarea class="form-control" rows="3" wire:model="description" placeholder="أدخل وصفاً للصيانة..."></textarea>
                        @error('description') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" wire:click="closeMaintenanceModal">
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