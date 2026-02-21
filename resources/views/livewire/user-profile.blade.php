<div class="container mt-2" dir="rtl" style="overflow: auto;">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-0">
                <i class="bi bi-person-fill-gear text-success me-2"></i> إعدادات الحساب
            </h3>
            <p class="text-secondary mb-0 mt-1">
                <i class="bi bi-info-circle me-1"></i> تعديل معلومات المستخدم وكلمة المرور
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('users.dashboard') }}" class="btn btn-outline-danger fw-bold rounded-pill shadow-sm px-4">
                <i class="bi bi-x-circle me-1"></i> إغلاق
            </a>
        </div>
    </div>

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

    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
        <div class="card-body p-4">
            <!-- User Information Form -->
            <form wire:submit.prevent="updateProfile">
                <div class="row g-3 mb-4">
                    <!-- Name Field (Full Width) -->
                    <div class="col-12">
                        <label class="form-label fw-bold">
                            <i class="bi bi-person me-1"></i> الاسم
                        </label>
                        <div class="shadow-sm rounded-pill overflow-hidden border">
                            <input type="text" 
                                   wire:model="name" 
                                   class="form-control border-0 @error('name') is-invalid @enderror" 
                                   placeholder="أدخل اسم المستخدم"
                                   style="text-align: right; box-shadow: none;">
                        </div>
                        @error('name')
                            <div class="text-danger small mt-1 px-2">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Password Section -->
                <div class="border-0 rounded-4 p-4 bg-light shadow-sm mb-4">
                    <h6 class="fw-bold text-dark mb-3">
                        <i class="bi bi-key me-2"></i> تغيير كلمة المرور
                    </h6>
                    
                    <div class="row g-3">
                        <!-- New Password -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark">كلمة المرور الجديدة</label>
                            <div class="shadow-sm rounded-pill overflow-hidden border-0">
                                <input type="password" 
                                       wire:model="password" 
                                       class="form-control border-0 @error('password') is-invalid @enderror" 
                                       placeholder="اتركه فارغاً إذا لم ترغب بالتغيير"
                                       style="text-align: right; box-shadow: none;">
                            </div>
                            @error('password')
                                <div class="text-danger small mt-1 px-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Confirm Password -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark">تأكيد كلمة المرور</label>
                            <div class="shadow-sm rounded-pill overflow-hidden border-0">
                                <input type="password" 
                                       wire:model="password_confirmation" 
                                       class="form-control border-0" 
                                       placeholder="أعد إدخال كلمة المرور الجديدة"
                                       style="text-align: right; box-shadow: none;">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Save Button -->
                <div class="text-start">
                    <button type="submit" class="btn btn-success rounded-pill px-5 shadow-sm">
                        <i class="bi bi-check-circle me-2"></i> حفظ التغيرات
                    </button>
                </div>
            </form>

            <hr class="my-4">

            <!-- Phone Numbers Section -->
            <div class="border-0 rounded-4 p-4 bg-light shadow-sm">
                <h6 class="fw-bold text-dark mb-3">
                    <i class="bi bi-telephone me-2"></i> أرقام الهاتف
                </h6>

                <!-- Add Phone Input -->
                <div class="row g-2 mb-3">
                    <div class="col-md-8">
                        <div class="shadow-sm rounded-pill overflow-hidden border">
                            <input type="text" 
                                   wire:model="newPhone" 
                                   class="form-control border-0 @error('newPhone') is-invalid @enderror" 
                                   placeholder="أدخل رقم هاتف جديد"
                                   style="text-align: right; box-shadow: none;">
                        </div>
                        @error('newPhone')
                            <div class="text-danger small mt-1 px-2">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <button class="btn btn-success rounded-pill w-100 shadow-sm" wire:click="addPhone">
                            <i class="bi bi-plus-circle me-1"></i> إضافة
                        </button>
                    </div>
                </div>

                <!-- Phone Numbers List -->
                @if (count($phoneNumbers) > 0)
                    <div class="list-group shadow-sm rounded-4 overflow-hidden">
                        @foreach ($phoneNumbers as $id => $number)
                            <div class="list-group-item d-flex justify-content-between align-items-center border-0 bg-white">
                                <span class="fw-semibold text-dark">
                                    <i class="bi bi-telephone-fill text-success me-2"></i>{{ $number }}
                                </span>
                                <button class="btn btn-sm btn-outline-danger rounded-pill" 
                                    title="حذف"
                                    wire:click="confirmDeletePhone({{ $id }})">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-telephone-x display-4 text-muted opacity-25 mb-2"></i>
                        <p class="text-muted mb-0">لا توجد أرقام هاتف مسجلة حالياً</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>