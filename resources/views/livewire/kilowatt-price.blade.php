<div class="d-flex justify-content-center">
    <div class="card shadow-sm border-0 rounded-4 mb-4 w-100" style="max-width: 750px;" dir="rtl">
        <div class="card-header bg-white text-center fw-bold rounded-top-4 py-3">
            <i class="bi bi-lightning-charge-fill me-2 text-warning"></i>
            <span class="text-dark">سعر الكيلووات</span>
        </div>
        <div class="card-body p-4">
            {{-- Success Message --}}
            @if (session()->has('success_kilowatt'))
                <div class="alert alert-success alert-dismissible fade show text-center rounded-3 shadow-sm">
                    <i class="bi bi-check-circle me-1"></i> {{ session('success_kilowatt') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            {{-- Update Price Form --}}
            <form wire:submit.prevent="updatePrice" class="row g-3 align-items-center justify-content-center">
                <div class="col-md-6">
                    <input type="number" 
                           wire:model.defer="price" 
                           step="0.01" 
                           class="form-control text-center rounded-3 shadow-sm"
                           style="direction: rtl;" 
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
                <div class="text-danger mt-3 text-center">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>
