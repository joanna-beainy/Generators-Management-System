<div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5); backdrop-filter: blur(4px);">
    <div class="modal-dialog modal-dialog-centered" dir="rtl">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-success bg-opacity-10 border-0 py-3">
                <h5 class="modal-title fw-bold text-success">
                    <i class="bi bi-plus-circle me-2"></i> إضافة مصاريف صيانة جديدة
                </h5>
                <button type="button" class="btn-close shadow-none" wire:click="closeMaintenanceModal"></button>
            </div>
            <div class="modal-body p-4 bg-white">
                <form wire:submit="saveMaintenance">
                    <div class="mb-4">
                        <label class="form-label fw-bold">المولد <span class="text-danger">*</span></label>
                        <div class="shadow-sm rounded-pill overflow-hidden border">
                            <select class="form-select border-0 px-3" wire:model="generator_id" style="box-shadow: none;">
                                <option value="">اختر المولد</option>
                                @foreach($generators as $generator)
                                    <option value="{{ $generator->id }}">{{ $generator->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @error('generator_id') <span class="text-danger ps-2 fw-bold small mt-1 d-block">{{ $message }}</span> @enderror
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold">المبلغ ($) <span class="text-danger">*</span></label>
                        <input type="number" step="0.50" class="form-control rounded-pill border shadow-sm px-3" 
                               style="text-align: right !important; box-shadow: none;" wire:model="amount" placeholder="0.00">
                        @error('amount') <span class="text-danger ps-2 fw-bold small mt-1 d-block">{{ $message }}</span> @enderror
                    </div>
                    
                    <div class="mb-5">
                        <label class="form-label fw-bold">الوصف (اختياري)</label>
                        <textarea class="form-control rounded-4 border shadow-sm px-3" rows="3" 
                                  wire:model="description" placeholder="أدخل وصفاً للصيانة..." style="box-shadow: none;"></textarea>
                        @error('description') <span class="text-danger ps-2 fw-bold small mt-1 d-block">{{ $message }}</span> @enderror
                    </div>
                    
                    <hr class="mb-4 text-secondary opacity-25">

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success rounded-pill px-5 py-2 shadow-sm fw-bold">
                            <i class="bi bi-check-lg me-1"></i> حفظ 
                        </button>
                        <button type="button" class="btn btn-outline-danger rounded-pill px-4" wire:click="closeMaintenanceModal">
                            إلغاء
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
