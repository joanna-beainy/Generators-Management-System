<div class="container py-4 d-flex justify-content-center" dir="rtl">
    <div class="card shadow-sm" style="width: 550px; max-width: 100%;">
        <div class="card-header bg-light text-dark">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">
                        <i class="bi bi-person-fill-gear text-success me-2"></i>تعديل معلومات المستخدم
                    </h5>
                </div>
                <a href="{{ route('users.dashboard') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-house me-1"></i>
                    إغلاق
                </a>
            </div>
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

            <!-- Update Name & Password -->
            <form wire:submit.prevent="updateProfile" class="mb-4">
                <div class="mb-3">
                    <label class="form-label fw-bold">الاسم</label>
                    <input type="text" wire:model.defer="name" class="form-control @error('name') is-invalid @enderror">
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">كلمة المرور الجديدة</label>
                    <input type="password" wire:model.defer="password" class="form-control @error('password') is-invalid @enderror">
                    @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">تأكيد كلمة المرور</label>
                    <input type="password" wire:model.defer="password_confirmation" class="form-control">
                </div>

                <button type="submit" class="btn btn-success px-4">تحديث المعلومات</button>
            </form>

            <hr>

            <!-- Phone Numbers Section -->
            <h6 class="fw-bold mb-3"><i class="bi bi-telephone me-2"></i>أرقام الهاتف</h6>

            <div class="mb-3 d-flex gap-2">
                <input type="text" wire:model.defer="newPhone" class="form-control w-50 @error('newPhone') is-invalid @enderror" placeholder="أدخل رقم جديد">
                <button class="btn btn-primary" wire:click="addPhone">إضافة</button>
                @error('newPhone') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>

            @if ($phoneNumbers)
                <ul class="list-group">
                    @foreach ($phoneNumbers as $id => $number)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>{{ $number }}</span>
                            <button class="btn btn-sm btn-outline-danger" 
                                wire:click="deletePhone({{ $id }})"
                                wire:confirm="هل أنت متأكد من حذف هذا الرقم ؟">
                                <i class="bi bi-trash"></i>
                            </button>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="text-muted">لا توجد أرقام حالياً.</p>
            @endif
        </div>
    </div>
</div>
