<div x-cloak x-show="editModalOpen" style="display: none;">
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5); backdrop-filter: blur(4px);">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document" dir="rtl">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">

                <div class="modal-header bg-success bg-opacity-10 border-0 py-3">
                    <h5 class="modal-title fw-bold text-success">
                        <i class="bi bi-pencil-square me-2"></i> تعديل بيانات المشترك
                    </h5>
                    <button type="button" class="btn-close shadow-none" @click="editModalOpen = false"></button>
                </div>

                <div class="modal-body p-4 bg-white">

                    <!-- Loading state -->
                    <div x-show="modalLoading" class="text-center py-5">
                        <div class="spinner-border text-success" role="status" aria-hidden="true" style="width: 3rem; height: 3rem;"></div>
                        <div class="mt-3 text-secondary fw-bold">جاري تحميل البيانات...</div>
                    </div>

                    <!-- Form -->
                    <div x-show="!modalLoading" x-transition>
                        @error('create')
                            <div class="alert alert-danger border-0 text-center rounded-3 shadow-sm mb-4">
                                <i class="bi bi-exclamation-triangle me-2"></i> {{ $message }}
                                <button type="button" class="btn-close" wire:click="$set('alertMessage', null)"></button>
                            </div>
                        @enderror

                        <form wire:submit.prevent="updateClient">
                            {{-- Personal info --}}
                            <div class="row g-3 mb-4">
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-secondary">الاسم الأول <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           wire:model="first_name" 
                                           class="form-control rounded-pill border shadow-sm px-3 @error('first_name') is-invalid @enderror"
                                           placeholder="الاسم الأول" style="box-shadow: none;">
                                    @error('first_name') 
                                        <div class="invalid-feedback ps-2 fw-bold small">{{ $message }}</div> 
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-secondary">اسم الأب</label>
                                    <input type="text" 
                                           wire:model="father_name" 
                                           class="form-control rounded-pill border shadow-sm px-3 @error('father_name') is-invalid @enderror"
                                           placeholder="اسم الأب" style="box-shadow: none;">
                                    @error('father_name') 
                                        <div class="invalid-feedback ps-2 fw-bold small">{{ $message }}</div> 
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-secondary">الكنية</label>
                                    <input type="text" 
                                           wire:model="last_name" 
                                           class="form-control rounded-pill border shadow-sm px-3 @error('last_name') is-invalid @enderror"
                                           placeholder="الكنية" style="box-shadow: none;">
                                    @error('last_name') 
                                        <div class="invalid-feedback ps-2 fw-bold small">{{ $message }}</div> 
                                    @enderror
                                </div>
                            </div>

                            <!-- Contact info -->
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-secondary">رقم الهاتف</label>
                                    <input type="text" 
                                           wire:model="phone_number" 
                                           class="form-control rounded-pill border shadow-sm px-3 @error('phone_number') is-invalid @enderror"
                                           placeholder="رقم الهاتف" style="box-shadow: none;">
                                    @error('phone_number') 
                                        <div class="invalid-feedback ps-2 fw-bold small">{{ $message }}</div> 
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-secondary">العنوان <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           wire:model="address" 
                                           class="form-control rounded-pill border shadow-sm px-3 @error('address') is-invalid @enderror"
                                           placeholder="العنوان" style="box-shadow: none;">
                                    @error('address') 
                                        <div class="invalid-feedback ps-2 fw-bold small">{{ $message }}</div> 
                                    @enderror
                                </div>
                            </div>

                            <!-- Generator & Category -->
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-secondary">المولد <span class="text-danger">*</span></label>
                                    <select wire:model="generator_id" 
                                            class="form-select rounded-pill border shadow-sm px-3 @error('generator_id') is-invalid @enderror"
                                            style="box-shadow: none;">
                                        <option value="">-- اختر المولد --</option>
                                       @foreach ($generators as $generator)
                                            <option value="{{ $generator->id }}">{{ $generator->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('generator_id') 
                                        <div class="invalid-feedback ps-2 fw-bold small">{{ $message }}</div> 
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-secondary">فئة العداد</label>
                                    <select wire:model="meter_category_id" 
                                            class="form-select rounded-pill border shadow-sm px-3 @error('meter_category_id') is-invalid @enderror" 
                                            style="box-shadow: none;"
                                            @if($is_offered) disabled @endif>
                                        <option value="">-- اختر الفئة --</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->category }}</option>
                                        @endforeach
                                    </select>
                                    @error('meter_category_id')
                                        <div class="invalid-feedback ps-2 fw-bold small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Meter and offered -->
                            <div class="row g-3 mb-5 align-items-center">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-secondary">العداد الحالي <span class="text-danger">*</span></label>
                                    <input type="number" 
                                           wire:model="current_meter" 
                                           min="0" 
                                           class="form-control text-end rounded-pill border shadow-sm px-3 @error('current_meter') is-invalid @enderror" 
                                           placeholder="0"
                                           required
                                           style="box-shadow: none; text-align: right !important;">
                                    @error('current_meter') 
                                        <div class="invalid-feedback ps-2 fw-bold small">{{ $message }}</div> 
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <div class="bg-primary-subtle p-3 rounded-4 border-0 mt-2">
                                        <div class="form-check">
                                            <input class="form-check-input @error('is_offered') is-invalid @enderror" 
                                                   type="checkbox" 
                                                   wire:model.live="is_offered" 
                                                   id="edit_is_offered"
                                                   style="width: 1.2rem; height: 1.2rem;">
                                            <label class="form-check-label fw-bold text-primary ms-2 pt-1" for="edit_is_offered">
                                                تقدمة
                                            </label>
                                        </div>
                                    </div>
                                    @error('is_offered')
                                        <div class="text-danger ps-4 fw-bold small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <hr class="mb-4 text-secondary opacity-25">

                            <!-- Buttons -->
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-success rounded-pill px-5 py-2 shadow-sm fw-bold">
                                    <i class="bi bi-check-lg me-1"></i> تحديث البيانات
                                </button>
                                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" @click="editModalOpen = false">
                                    إلغاء
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>