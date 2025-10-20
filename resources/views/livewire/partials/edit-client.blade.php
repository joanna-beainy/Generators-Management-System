@if($showEditModal)
<div class="modal fade show d-block" tabindex="-1" role="dialog" style="background: rgba(0,0,0,0.5);" wire:ignore.self>
    <div class="modal-dialog modal-lg" role="document" dir="rtl">
        <div class="modal-content shadow rounded-4">

            <div class="modal-header bg-light">
                <h5 class="modal-title">تعديل بيانات المشترك</h5>
                <button type="button" class="btn-close" wire:click="closeModal"></button>
            </div>

            <div class="modal-body">
                <form wire:submit.prevent="updateClient">

                    {{-- Personal info --}}
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label>الاسم الأول</label>
                            <input type="text" wire:model.defer="first_name" class="form-control">
                            @error('first_name') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="col-md-4">
                            <label>اسم الأب</label>
                            <input type="text" wire:model.defer="father_name" class="form-control">
                            @error('father_name') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="col-md-4">
                            <label>الكنية</label>
                            <input type="text" wire:model.defer="last_name" class="form-control">
                            @error('last_name') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Contact info --}}
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label>رقم الهاتف</label>
                            <input type="text" wire:model.defer="phone_number" class="form-control">
                            @error('phone_number') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="col-md-6">
                            <label>العنوان</label>
                            <input type="text" wire:model.defer="address" class="form-control">
                            @error('address') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Generator & Category --}}
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label>المولد</label>
                            <select wire:model.defer="generator_id" class="form-select">
                                <option value="">-- اختر المولد --</option>
                                @foreach (\App\Models\Generator::where('user_id', auth()->id())->get() as $generator)
                                    <option value="{{ $generator->id }}">{{ $generator->name }}</option>
                                @endforeach
                            </select>
                            @error('generator_id') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="col-md-6">
                            <label>فئة العداد</label>
                            <select wire:model.defer="meter_category_id" class="form-select" @if($is_offered) disabled @endif>
                                <option value="">-- اختر الفئة --</option>
                                @foreach (\App\Models\MeterCategory::where('user_id', auth()->id())->get() as $category)
                                    <option value="{{ $category->id }}">{{ $category->category }}</option>
                                @endforeach
                            </select>
                            @error('meter_category_id') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Meter and offered --}}
                    <div class="row mb-3 align-items-center">
                        <div class="col-md-6">
                            <label>العداد الحالي</label>
                            <input type="number" wire:model.defer="current_meter" min="0" class="form-control" required>
                            @error('current_meter') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="col-md-6 mt-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" wire:model.live="is_offered" id="is_offered">
                                <label class="form-check-label" for="is_offered">
                                    تقدمة
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- Buttons --}}
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">تحديث البيانات</button>
                        <button type="button" class="btn btn-secondary ms-2" wire:click="closeModal">إلغاء</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endif