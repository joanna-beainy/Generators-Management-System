<div class="d-flex justify-content-center">
    <div class="card shadow-sm border-0 rounded-4 w-100" style="max-width: 950px;" dir="rtl">
        <div class="card-header bg-white text-center fw-bold rounded-top-4 py-3">
            <i class="bi bi-tags me-2 text-primary h4"></i>
            <span class="text-dark">فئات الاشتراك</span>
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


            {{-- Toggle Add Form --}}
            <div class="text-start mb-4">
                <button type="button" class="btn btn-outline-success rounded-pill shadow-sm"
                        wire:click="toggleAddForm">
                    <i class="bi bi-plus-circle me-1"></i>
                    {{ $showAddForm ? 'إخفاء النموذج' : 'إضافة فئة جديدة' }}
                </button>
            </div>

            {{-- Add Category Form --}}
            @if ($showAddForm)
                <form wire:submit.prevent="addCategory" class="mb-4">
                    <div class="row g-3">
                        <div class="col-md-5">
                            <input type="text" class="form-control rounded-3 shadow-sm @error('newCategoryName') is-invalid @enderror"
                                   placeholder="اسم الفئة" wire:model.defer="newCategoryName">
                            @error('newCategoryName') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <input type="number" step="0.01"
                                   class="form-control rounded-3 shadow-sm @error('newCategoryPrice') is-invalid @enderror"
                                   placeholder="السعر" wire:model.defer="newCategoryPrice" style="direction: rtl;">
                            @error('newCategoryPrice') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-success w-100 rounded-pill shadow-sm">
                                <i class="bi bi-check2-circle me-1"></i> إضافة
                            </button>
                        </div>
                    </div>
                </form>
            @endif

            {{-- Update Existing Categories --}}
            <form wire:submit.prevent="updatePrices">
                <div class="table-responsive">
                    <table class="table table-striped table-hover text-center align-middle rounded-3">
                        <thead class="table-light">
                            <tr>
                                <th>الفئة</th>
                                <th style="width: 220px;">السعر</th>
                                <th>إجراء</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($categories as $index => $category)
                                <tr>
                                    <td class="fw-semibold">{{ $category['name'] }}</td>
                                    <td>
                                        <input type="number" step="0.01"
                                               class="form-control text-center rounded-3 shadow-sm @error("categories.$index.price") is-invalid @enderror"
                                               wire:model.defer="categories.{{ $index }}.price"
                                        @error("categories.$index.price") 
                                            <div class="invalid-feedback">{{ $message }}</div> 
                                        @enderror
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-outline-danger btn-sm rounded-pill shadow-sm"
                                                x-data="{ categoryId: {{ $category['id'] }} }"
                                                @click="if(confirm('هل أنت متأكد من حذف هذه الفئة؟')) { $wire.call('deleteCategory', categoryId) }"
                                                title="حذف">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-success px-5 py-2 rounded-pill shadow-sm">
                        <i class="bi bi-arrow-repeat me-1"></i> تحديث الأسعار
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
