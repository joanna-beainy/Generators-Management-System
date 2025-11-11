@if($showEditModal)
<div class="modal fade show d-block" tabindex="-1" role="dialog" style="background: rgba(0,0,0,0.5);" wire:ignore.self>
    <div class="modal-dialog modal-lg" role="document" dir="rtl">
        <div class="modal-content shadow rounded-4">

            <div class="modal-header bg-light">
                <h5 class="modal-title">
                    <i class="bi bi-pencil-square text-success me-2"></i>
                    تعديل بيانات المشترك
                </h5>
                <button type="button" class="btn-close" wire:click="closeModal"></button>
            </div>

            <div class="modal-body">
                {{-- Error Alert for Modal (only shows if update fails) --}}
                @error('create')
                    <div class="alert alert-danger alert-dismissible fade show text-center rounded-3 shadow-sm mb-4">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        {{ $message }}
                        <button type="button" class="btn-close" wire:click="$set('alertMessage', null)"></button>
                    </div>
                @enderror

                <form wire:submit.prevent="updateClient">
                    {{-- Personal info --}}
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">الاسم الأول <span class="text-danger">*</span></label>
                            <input type="text" 
                                   wire:model="first_name" 
                                   class="form-control @error('first_name') is-invalid @enderror"
                                   placeholder="الاسم الأول">
                            @error('first_name') 
                                <div class="invalid-feedback">{{ $message }}</div> 
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">اسم الأب</label>
                            <input type="text" 
                                   wire:model="father_name" 
                                   class="form-control @error('father_name') is-invalid @enderror"
                                   placeholder="اسم الأب">
                            @error('father_name') 
                                <div class="invalid-feedback">{{ $message }}</div> 
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">الكنية</label>
                            <input type="text" 
                                   wire:model="last_name" 
                                   class="form-control @error('last_name') is-invalid @enderror"
                                   placeholder="الكنية">
                            @error('last_name') 
                                <div class="invalid-feedback">{{ $message }}</div> 
                            @enderror
                        </div>
                    </div>

                    {{-- Contact info --}}
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">رقم الهاتف</label>
                            <input type="text" 
                                   wire:model="phone_number" 
                                   class="form-control @error('phone_number') is-invalid @enderror"
                                   placeholder="رقم الهاتف">
                            @error('phone_number') 
                                <div class="invalid-feedback">{{ $message }}</div> 
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">العنوان <span class="text-danger">*</span></label>
                            <input type="text" 
                                   wire:model="address" 
                                   class="form-control @error('address') is-invalid @enderror"
                                   placeholder="العنوان">
                            @error('address') 
                                <div class="invalid-feedback">{{ $message }}</div> 
                            @enderror
                        </div>
                    </div>

                    {{-- Generator & Category --}}
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">المولد <span class="text-danger">*</span></label>
                            <select wire:model="generator_id" 
                                    class="form-select @error('generator_id') is-invalid @enderror">
                                <option value="">-- اختر المولد --</option>
                                @foreach (\App\Models\Generator::where('user_id', auth()->id())->get() as $generator)
                                    <option value="{{ $generator->id }}">{{ $generator->name }}</option>
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
                                <option value="">-- اختر الفئة --</option>
                                @foreach (\App\Models\MeterCategory::where('user_id', auth()->id())->get() as $category)
                                    <option value="{{ $category->id }}">{{ $category->category }}</option>
                                @endforeach
                            </select>
                            @error('meter_category_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Meter and offered --}}
                    <div class="row mb-3 align-items-center">
                        <div class="col-md-6">
                            <label class="form-label">العداد الحالي <span class="text-danger">*</span></label>
                            <input type="number" 
                                   wire:model="current_meter" 
                                   min="0" 
                                   class="form-control text-start @error('current_meter') is-invalid @enderror" 
                                   placeholder="العداد الحالي"
                                   required>
                            @error('current_meter') 
                                <div class="invalid-feedback">{{ $message }}</div> 
                            @enderror
                        </div>

                        <div class="col-md-6 mt-4">
                            <div class="form-check">
                                <input class="form-check-input @error('is_offered') is-invalid @enderror" 
                                       type="checkbox" 
                                       wire:model.live="is_offered" 
                                       id="is_offered">
                                <label class="form-check-label fw-bold text-primary" for="is_offered">
                                    تقدمة
                                </label>
                            </div>
                            @error('is_offered')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Buttons --}}
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-secondary" wire:click="closeModal">
                            <i class="bi bi-x-circle me-1"></i> إلغاء
                        </button>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle me-1"></i> تحديث البيانات
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endif