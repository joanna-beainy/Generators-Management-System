<div class="d-flex justify-content-center">
    <div class="card shadow-sm border-0 rounded-4 w-100 overflow-hidden" style="max-width: 950px;" dir="rtl">
        <div class="card-header bg-success bg-opacity-10 text-center py-2 border-0">
            <h5 class="mb-0 fw-bold text-success">
                <i class="bi bi-lightning-fill me-2"></i> سعر الكيلووات
            </h5>
        </div>
        <div class="card-body p-4 bg-white">
            <!-- Alpine.js Auto-Disappearing Alert -->
            @if ($alertMessage)
                <div 
                    x-data="{ show: true }" 
                    x-show="show" 
                    x-init="setTimeout(() => { show = false; $wire.set('alertMessage', null) }, 5000)" 
                    x-transition:leave="transition ease-in duration-300"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="alert alert-{{ $alertType }} border-0 text-center rounded-3 shadow-sm mb-4">
                    <i class="bi {{ $alertType === 'success' ? 'bi-check-circle' : 'bi-exclamation-triangle' }} me-1"></i>
                    {{ $alertMessage }}
                    <button type="button" class="btn-close" wire:click="$set('alertMessage', null)"></button>
                </div>
            @endif


            <!-- Update Price Form -->
            <form wire:submit.prevent="updatePrice" class="row g-3 align-items-center justify-content-center">
                <div class="col-md-6">
                    <div class="input-group shadow-sm rounded-pill overflow-hidden border">
                        <input type="number" 
                               wire:model.defer="price" 
                               step="0.01" 
                               class="form-control border-0 text-center py-2 @error('price') is-invalid @enderror"
                               placeholder="أدخل السعر الجديد" 
                               required
                               style="box-shadow: none;">
                        <span class="input-group-text bg-white border-0 text-secondary fw-bold">$ / k.w</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-success w-100 rounded-pill shadow-sm py-2 px-4 fw-bold">
                        <i class="bi bi-arrow-repeat me-1"></i> تحديث السعر
                    </button>
                </div>
            </form>

            @error('price')
                <div class="text-danger small text-center mt-2 fw-bold"><i class="bi bi-exclamation-circle me-1"></i> {{ $message }}</div>
            @enderror
        </div>
    </div>
</div>
