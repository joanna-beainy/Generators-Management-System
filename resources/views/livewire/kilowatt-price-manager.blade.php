<div class="d-flex justify-content-center">
    <div class="card shadow-sm border-0 rounded-4 mb-4 w-100" style="max-width: 750px;" dir="rtl">
        <div class="card-header bg-white text-center fw-bold rounded-top-4 py-3">
            <i class="bi bi-lightning-fill me-2 text-warning h4"></i>
            <span class="text-dark">سعر الكيلووات</span>
        </div>
        <div class="card-body p-4">
            {{-- Alpine.js Auto-Disappearing Alert --}}
            @if ($alertMessage)
                <div 
                    x-data="{ show: true }" 
                    x-show="show" 
                    x-init="setTimeout(() => { show = false; $wire.set('alertMessage', null) }, 5000)" 
                    x-transition:leave="transition ease-in duration-300"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="alert alert-{{ $alertType }} alert-dismissible fade show text-center rounded-3 shadow-sm mb-4">
                    <i class="bi {{ $alertType === 'success' ? 'bi-check-circle' : 'bi-exclamation-triangle' }} me-1"></i>
                    {{ $alertMessage }}
                    <button type="button" class="btn-close" wire:click="$set('alertMessage', null)"></button>
                </div>
            @endif


            {{-- Update Price Form --}}
            <form wire:submit.prevent="updatePrice" class="row g-3 align-items-center justify-content-center">
                <div class="col-md-6">
                    <input type="number" 
                           wire:model.defer="price" 
                           step="0.01" 
                           class="form-control text-center rounded-3 shadow-sm @error('price') is-invalid @enderror"
                           placeholder="أدخل السعر الجديد" 
                           required>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-success w-100 rounded-pill shadow-sm">
                        <i class="bi bi-check2-circle me-1"></i> تحديث
                    </button>
                </div>
            </form>

            @error('price')
                <div class="invalid-feedback d-block text-center mt-2">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>
