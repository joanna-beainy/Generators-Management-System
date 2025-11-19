<div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5)">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title">
                    <i class="bi bi-plus-circle me-2"></i>
                    إضافة شراء وقود جديد
                </h5>
                <button type="button" class="btn-close" wire:click="closePurchaseModal"></button>
            </div>
            <div class="modal-body">
                <form wire:submit="savePurchase">
                    <div class="mb-3">
                        <label class="form-label fw-bold">الشركة <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" wire:model="company" placeholder="أدخل اسم الشركة">
                        @error('company') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">اللترات المشتراة <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" style="text-align: right;" wire:model="liters_purchased" placeholder="0" min="1">
                                @error('liters_purchased') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">السعر الإجمالي ($) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" style="text-align: right;" class="form-control" wire:model="total_price" placeholder="0.00" min="0.01">
                                @error('total_price') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">الوصف (اختياري)</label>
                        <textarea class="form-control" rows="3" wire:model="description" placeholder="أدخل وصفاً إضافياً..."></textarea>
                        @error('description') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" wire:click="closePurchaseModal">
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