<div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5); backdrop-filter: blur(5px);">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-success bg-opacity-10 text-success">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-plus-circle me-2"></i>
                    إضافة شراء وقود جديد
                </h5>
                <button type="button" class="btn-close shadow-none" wire:click="closePurchaseModal"></button>
            </div>
            <div class="modal-body p-4">
                <form wire:submit="savePurchase">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary">الشركة <span class="text-danger">*</span></label>
                        <div class="shadow-sm rounded-pill overflow-hidden border">
                            <input type="text" class="form-control border-0" wire:model="company" placeholder="أدخل اسم الشركة" style="box-shadow: none;">
                        </div>
                        @error('company') <span class="text-danger small ms-2">{{ $message }}</span> @enderror
                    </div>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold text-secondary">اللترات المشتراة <span class="text-danger">*</span></label>
                                <div class="shadow-sm rounded-pill overflow-hidden border">
                                    <input type="number" class="form-control border-0" style="text-align: right; box-shadow: none;" wire:model="liters_purchased" placeholder="0" min="1">
                                </div>
                                @error('liters_purchased') <span class="text-danger small ms-2">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold text-secondary">السعر الإجمالي ($) <span class="text-danger">*</span></label>
                                <div class="shadow-sm rounded-pill overflow-hidden border">
                                    <input type="number" step="0.50" style="text-align: right; box-shadow: none;" class="form-control border-0" wire:model="total_price" placeholder="0.00">
                                </div>
                                @error('total_price') <span class="text-danger small ms-2">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold text-secondary">الوصف (اختياري)</label>
                        <textarea class="form-control shadow-sm rounded-4 border" rows="3" wire:model="description" placeholder="أدخل وصفاً إضافياً..."></textarea>
                        @error('description') <span class="text-danger small ms-2">{{ $message }}</span> @enderror
                    </div>
                    
                    <div class="modal-footer border-0 p-0 justify-content-start">
                        <button type="submit" class="btn btn-success rounded-pill px-5 shadow-sm">
                            <i class="bi bi-check-circle me-1"></i> حفظ
                        </button>
                         <button type="button" class="btn btn-outline-secondary rounded-pill px-4" wire:click="closePurchaseModal">
                            <i class="bi bi-x-circle me-1"></i> إلغاء
                        </button>
                        
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
