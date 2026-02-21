<div class="container d-flex flex-column h-100" style="overflow-y: auto;" dir="rtl">
    <style>
        :root {
            --fluid-v-gap: clamp(0.5rem, 2.5vh, 2rem);
            --fluid-v-padding: clamp(0.75rem, 3vh, 2.5rem);
            --fluid-v-header-margin: clamp(0.5rem, 2vh, 1.5rem);
        }
    </style>

    <!-- Header Section -->
    <div class="flex-shrink-0 d-flex justify-content-between align-items-center" style="margin-top: var(--fluid-v-header-margin); margin-bottom: var(--fluid-v-header-margin);">
        <div>
            <h3 class="fw-bold text-dark mb-0">
                <i class="bi bi-person-plus-fill text-success me-2"></i> إضافة مشترك جديد
            </h3>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('users.dashboard') }}" class="btn btn-outline-danger fw-bold rounded-pill shadow-sm px-4">
                <i class="bi bi-x-circle me-1"></i> إغلاق
            </a>
        </div>
    </div>



    @if ($alertMessage)
        <div class="flex-shrink-0"
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

    <div class="flex-shrink-1 pb-4">
        <div class="row justify-content-center mx-0">
            <div class="col-md-9 px-0">
                <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                    <div class="card-body bg-white" style="padding-top: var(--fluid-v-padding); padding-bottom: var(--fluid-v-padding); padding-left: 1.5rem; padding-right: 1.5rem;">
                        <form wire:submit="store">
                            <!-- Names Section -->
                            <div class="row g-3 g-md-4" style="margin-bottom: var(--fluid-v-gap);">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">الاسم الأول <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           wire:model="first_name" 
                                           class="form-control rounded-pill border shadow-sm px-3 @error('first_name') is-invalid @enderror" 
                                           placeholder="أدخل الاسم الأول"
                                           style="box-shadow: none;">
                                    @error('first_name')
                                        <div class="invalid-feedback ps-2 fw-bold">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">اسم الأب</label>
                                    <input type="text" 
                                           wire:model="father_name" 
                                           class="form-control rounded-pill border shadow-sm px-3 @error('father_name') is-invalid @enderror"
                                           placeholder="أدخل اسم الأب"
                                           style="box-shadow: none;">
                                    @error('father_name')
                                        <div class="invalid-feedback ps-2 fw-bold">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">الشهرة</label>
                                    <input type="text" 
                                           wire:model="last_name" 
                                           class="form-control rounded-pill border shadow-sm px-3 @error('last_name') is-invalid @enderror"
                                           placeholder="أدخل الشهرة"
                                           style="box-shadow: none;">
                                    @error('last_name')
                                        <div class="invalid-feedback ps-2 fw-bold">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Contact Section -->
                            <div class="row g-3 g-md-4" style="margin-bottom: var(--fluid-v-gap);">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">رقم الهاتف</label>
                                    <input type="text" 
                                           wire:model="phone_number" 
                                           class="form-control rounded-pill border shadow-sm px-3 @error('phone_number') is-invalid @enderror"
                                           placeholder="أدخل رقم الهاتف"
                                           style="box-shadow: none;">
                                    @error('phone_number')
                                        <div class="invalid-feedback ps-2 fw-bold">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">العنوان <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           wire:model="address" 
                                           class="form-control rounded-pill border shadow-sm px-3 @error('address') is-invalid @enderror" 
                                           placeholder="أدخل العنوان"
                                           style="box-shadow: none;">
                                    @error('address')
                                        <div class="invalid-feedback ps-2 fw-bold">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Generator + Category Section -->
                            <div class="row g-3 g-md-4" style="margin-bottom: var(--fluid-v-gap);">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">المولد <span class="text-danger">*</span></label>
                                    <select wire:model="generator_id" 
                                            class="form-select rounded-pill border shadow-sm px-3 @error('generator_id') is-invalid @enderror" 
                                            style="box-shadow: none;">
                                        <option value="">اختر المولد</option>
                                        @foreach ($generators as $generator)
                                            <option value="{{ $generator->id }}">
                                                {{ $generator->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('generator_id')
                                        <div class="invalid-feedback ps-2 fw-bold">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">فئة العداد</label>
                                    <select wire:model="meter_category_id" 
                                            class="form-select rounded-pill border shadow-sm px-3 @error('meter_category_id') is-invalid @enderror"
                                            style="box-shadow: none;"
                                            @if($is_offered) disabled @endif>
                                        <option value="">اختر الفئة</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}">
                                                {{ $category->category }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('meter_category_id')
                                        <div class="invalid-feedback ps-2 fw-bold">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Initial Meter Section -->
                            <div style="margin-bottom: var(--fluid-v-gap);">
                                <label class="form-label fw-bold">العداد الحالي (اختياري)</label>
                                <input type="number" 
                                       wire:model="initial_meter" 
                                       class="form-control rounded-pill border shadow-sm px-3 @error('initial_meter') is-invalid @enderror" 
                                       min="0" 
                                       step="1"
                                       placeholder="أدخل قراءة العداد الحالية"
                                       style="box-shadow: none; text-align: right !important;">
                                @error('initial_meter')
                                    <div class="invalid-feedback ps-2 fw-bold">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Offered Checkbox Section -->
                            <div class="bg-primary-subtle rounded-4 border-0" style="margin-bottom: var(--fluid-v-padding); padding-top: var(--fluid-v-gap); padding-bottom: var(--fluid-v-gap); padding-left: 1rem; padding-right: 1rem;">
                                <div class="form-check">
                                    <input class="form-check-input @error('is_offered') is-invalid @enderror" 
                                           type="checkbox" 
                                           wire:model.live="is_offered" 
                                           id="is_offered" 
                                           value="1"
                                           style="width: 1.2rem; height: 1.2rem;">
                                    <label class="form-check-label fw-bold text-primary ms-2 pt-1" for="is_offered">
                                        تقدمة
                                    </label>
                                </div>
                                @error('is_offered')
                                    <div class="text-danger ps-4 fw-bold mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <hr class="text-secondary opacity-25" style="margin-bottom: var(--fluid-v-gap);">

                            <!-- Buttons Section -->
                            <div class="d-flex justify-content-start gap-2">
                                <button type="submit" class="btn btn-success rounded-pill px-5 shadow-sm py-2 fw-bold">
                                    <i class="bi bi-person-plus-fill me-1"></i> إضافة المشترك
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>