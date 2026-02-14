@if($showAddForm)
<div class="modal fade show d-block" tabindex="-1" role="dialog" style="background: rgba(0,0,0,0.5); backdrop-filter: blur(4px);">
    <div class="modal-dialog modal-md modal-dialog-centered" role="document" dir="rtl">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-success bg-opacity-10 border-0 py-3">
                <h5 class="modal-title fw-bold text-success">
                    <i class="bi bi-plus-circle me-2"></i> إضافة مولد جديد
                </h5>
                <button type="button" class="btn-close shadow-none" wire:click="toggleAddForm"></button>
            </div>
            <div class="modal-body p-4 bg-white">
                <form wire:submit.prevent="addGenerator">
                    <div class="mb-5">
                        <label class="form-label small fw-bold text-secondary">اسم المولد</label>
                        <input type="text"
                               wire:model.defer="name"
                               class="form-control rounded-pill border shadow-sm px-3 @error('name') is-invalid @enderror"
                               placeholder="أدخل اسم المولد هنا"
                               style="box-shadow: none; text-align: right;">
                        @error('name')
                            <div class="invalid-feedback ps-2 fw-bold small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success rounded-pill px-5 py-2 shadow-sm fw-bold">
                            <i class="bi bi-check-lg me-1"></i> إضافة المولد
                        </button>
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" wire:click="toggleAddForm">
                            إلغاء
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endif
