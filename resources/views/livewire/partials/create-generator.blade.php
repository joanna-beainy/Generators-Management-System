@if($showAddForm)
<div class="modal fade show d-block" tabindex="-1" role="dialog" style="background: rgba(0,0,0,0.5);">
    <div class="modal-dialog modal-md modal-dialog-centered" role="document">
        <div class="modal-content shadow rounded-4">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-plus-circle me-2"></i>
                    إضافة مولد جديد
                </h5>
                <button type="button" class="btn-close" wire:click="toggleAddForm"></button>
            </div>
            <div class="modal-body">
                <form wire:submit.prevent="addGenerator">
                    <div class="mb-3">
                        <label class="form-label">اسم المولد</label>
                        <input type="text"
                               wire:model.defer="name"
                               class="form-control @error('name') is-invalid @enderror rounded-pill">
                        @error('name')
                            <div class="invalid-feedback text-end">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-success rounded-pill px-4">
                            <i class="bi bi-check2-circle me-1"></i> إضافة
                        </button>
                        <button type="button" class="btn btn-secondary rounded-pill ms-2 px-4" wire:click="toggleAddForm">
                            إلغاء
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endif
