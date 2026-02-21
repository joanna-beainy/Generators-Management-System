<div class="d-flex justify-content-center">

    <div class="card shadow-sm border-0 rounded-4 w-100 overflow-hidden" style="max-width: 950px;" dir="rtl">
        <div class="card-header bg-success bg-opacity-10 text-center py-2 border-0">
            <h5 class="mb-0 fw-bold text-success">
                <i class="bi bi-tags me-2"></i> فئات الاشتراك
            </h5>
        </div>
        <div class="card-body p-4 bg-white">
            @if ($alertMessage)
                <div 
                    x-data="{ show: true }" 
                    x-show="show" 
                    x-init="setTimeout(() => { show = false; $wire.set('alertMessage', null) }, 5000)" 
                    x-transition:leave="transition ease-in duration-300"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0">
                    <div class="alert alert-{{ $alertType }} border-0 text-center rounded-3 shadow-sm mb-4 position-relative">
                        <button type="button" class="btn-close position-absolute top-50 translate-middle-y" style="right: 1rem;" wire:click="$set('alertMessage', null)"></button>
                        <div class="d-flex align-items-center justify-content-center">
                            <i class="bi {{ $alertType === 'success' ? 'bi-check-circle' : 'bi-exclamation-triangle' }} me-2"></i>
                            {{ $alertMessage }}
                        </div>
                    </div>
                </div>
            @endif


            <!-- Toggle Add Form -->
            <div class="text-start mb-4">
                <button type="button" class="btn btn-outline-success rounded-pill shadow-sm px-4 fw-bold"
                        wire:click="toggleAddForm">
                    <i class="bi {{ $showAddForm ? 'bi-dash-lg' : 'bi-plus-lg' }} me-1"></i>
                    {{ $showAddForm ? 'إخفاء النموذج' : 'إضافة فئة جديدة' }}
                </button>
            </div>

            <!-- Add Category Form -->
            @if ($showAddForm)
                <div class="bg-light p-4 rounded-4 mb-4 border-0 shadow-sm" x-transition>
                    <h6 class="fw-bold mb-3 text-dark medium"><i class="bi bi-plus-circle me-1 text-success"></i> تفاصيل الفئة الجديدة</h6>
                    <form wire:submit.prevent="addCategory">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-5">
                                <label class="form-label medium fw-bold">اسم الفئة</label>
                                <input type="text" class="form-control rounded-pill border shadow-sm px-3 @error('newCategoryName') is-invalid @enderror"
                                       placeholder="اسم الفئة" wire:model.defer="newCategoryName" style="box-shadow: none;">
                                @error('newCategoryName') <div class="invalid-feedback ps-2">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label medium fw-bold">السعر ($)</label>
                                <input type="number" step="0.01"
                                       class="form-control rounded-pill border shadow-sm px-3 @error('newCategoryPrice') is-invalid @enderror"
                                       placeholder="0.00" wire:model.defer="newCategoryPrice" style="direction: rtl; box-shadow: none;">
                                @error('newCategoryPrice') <div class="invalid-feedback ps-2">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-success w-100 rounded-pill shadow-sm py-2 fw-bold">
                                    <i class="bi bi-check-lg me-1"></i> إضافة الفئة
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            @endif

            <!-- Update Existing Categories -->
            <form wire:submit.prevent="updatePrices">
                <div class="table-responsive rounded-3 border">
                    <table class="table table-hover text-center align-middle mb-0">
                        <thead class="table-secondary" style="position: sticky; top: 0; z-index: 1;">
                            <tr class="fw-bold text-uppercase">
                                <th>الفئة</th>
                                <th style="width: 220px;">السعر الحالي ($)</th>
                                <th>إجراء</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($categories as $index => $category)
                                <tr wire:key="category-row-{{ $category['id'] }}">
                                    <td class="fw-bold text-dark">{{ $category['name'] }}</td>
                                    <td>
                                        <div class="input-group input-group-sm shadow-sm rounded-pill overflow-hidden border mx-auto" style="max-width: 150px;">
                                            <input type="number" step="0.01"
                                                   class="form-control border-0 text-center @error("categories.$index.price") is-invalid @enderror"
                                                   wire:model.defer="categories.{{ $index }}.price"
                                                   style="box-shadow: none;">
                                        </div>
                                        @error("categories.$index.price") 
                                            <div class="text-danger small mt-1 fw-bold">{{ $message }}</div> 
                                        @enderror
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-link text-danger p-0 text-decoration-none"
                                                wire:click="confirmDelete({{ $category['id'] }})"
                                                title="حذف">
                                            <i class="bi bi-trash3 h5 mb-0"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-success px-5 py-2 rounded-pill shadow-sm fw-bold">
                        <i class="bi bi-check-all me-1 h5 mb-0"></i> حفظ جميع التعديلات
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
