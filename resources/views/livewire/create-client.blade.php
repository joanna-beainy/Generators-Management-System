<div class="container py-4" dir="rtl">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-person-plus-fill text-success"></i> إضافة مشترك جديد
                    </h5>
                </div>

                <div class="card-body">
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

                    <form wire:submit="store">
                        {{-- Names Section --}}
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">الاسم الأول <span class="text-danger">*</span></label>
                                <input type="text" 
                                       wire:model="first_name" 
                                       class="form-control @error('first_name') is-invalid @enderror" 
                                       placeholder="أدخل الاسم الأول">
                                @error('first_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">اسم الأب</label>
                                <input type="text" 
                                       wire:model="father_name" 
                                       class="form-control @error('father_name') is-invalid @enderror"
                                       placeholder="أدخل اسم الأب">
                                @error('father_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">الكنية</label>
                                <input type="text" 
                                       wire:model="last_name" 
                                       class="form-control @error('last_name') is-invalid @enderror"
                                       placeholder="أدخل الكنية">
                                @error('last_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Contact Section --}}
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">رقم الهاتف</label>
                                <input type="text" 
                                       wire:model="phone_number" 
                                       class="form-control @error('phone_number') is-invalid @enderror"
                                       placeholder="أدخل رقم الهاتف">
                                @error('phone_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">العنوان <span class="text-danger">*</span></label>
                                <input type="text" 
                                       wire:model="address" 
                                       class="form-control @error('address') is-invalid @enderror" 
                                       placeholder="أدخل العنوان">
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Generator + Category Section --}}
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">المولد <span class="text-danger">*</span></label>
                                <select wire:model="generator_id" 
                                        class="form-select @error('generator_id') is-invalid @enderror" >
                                    <option value="">اختر المولد</option>
                                    @foreach ($generators as $generator)
                                        <option value="{{ $generator->id }}">
                                            {{ $generator->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('generator_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">فئة العداد</label>
                                <select wire:model="meter_category_id" 
                                        class="form-select @error('meter_category_id') is-invalid @enderror"
                                        @if($is_offered) disabled @endif>
                                    <option value="">اختر الفئة</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}">
                                            {{ $category->category }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('meter_category_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Initial Meter Section --}}
                        <div class="mb-3">
                            <label class="form-label">العداد الحالي (اختياري)</label>
                            <input type="number" 
                                   wire:model="initial_meter" 
                                   class="form-control text-start @error('initial_meter') is-invalid @enderror" 
                                   min="0" 
                                   step="1"
                                   placeholder="أدخل قراءة العداد الحالية">
                            @error('initial_meter')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Offered Checkbox Section --}}
                        <div class="form-check mb-4">
                            <input class="form-check-input @error('is_offered') is-invalid @enderror" 
                                   type="checkbox" 
                                   wire:model.live="is_offered" 
                                   id="is_offered" 
                                   value="1">
                            <label class="form-check-label fw-bold text-primary" for="is_offered">
                                تقدمة
                            </label>
                            @error('is_offered')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Buttons Section --}}
                        <div class="d-flex justify-content-start mb-3">
                            <a href="{{ route('users.dashboard') }}" class="btn btn-outline-secondary me-2">
                                <i class="bi bi-x-circle"></i> إلغاء
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-circle"></i> إضافة المشترك
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>