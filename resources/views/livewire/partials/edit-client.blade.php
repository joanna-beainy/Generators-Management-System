@if($showEditModal)
<div class="modal fade show d-block" tabindex="-1" role="dialog" style="background: rgba(0,0,0,0.5);">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content shadow rounded-4">
            <div class="modal-header">
                <h5 class="modal-title">تعديل بيانات المشترك</h5>
                <button type="button" class="btn-close" wire:click="$set('showEditModal', false)"></button>
            </div>
            <div class="modal-body">
                <form wire:submit.prevent="updateClient">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label>الاسم الأول</label>
                            <input type="text" wire:model.defer="first_name" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label>اسم الأب</label>
                            <input type="text" wire:model.defer="father_name" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label>الكنية</label>
                            <input type="text" wire:model.defer="last_name" class="form-control">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label>رقم الهاتف</label>
                            <input type="text" wire:model.defer="phone_number" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label>العنوان</label>
                            <input type="text" wire:model.defer="address" class="form-control">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label>المولد</label>
                            <select wire:model.defer="generator_id" class="form-select">
                                @foreach (\App\Models\Generator::where('user_id', auth()->id())->get() as $generator)
                                    <option value="{{ $generator->id }}">{{ $generator->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label>فئة العداد</label>
                            <select wire:model.defer="meter_category_id" class="form-select">
                                @foreach (\App\Models\MeterCategory::where('user_id', auth()->id())->get() as $category)
                                    <option value="{{ $category->id }}">{{ $category->category }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">تحديث البيانات</button>
                        <button type="button" class="btn btn-secondary ms-2" wire:click="$set('showEditModal', false)">إلغاء</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endif
