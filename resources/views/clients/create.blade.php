@extends('layouts.app')
@section('title', 'إضافة مشترك جديد')

@section('content')
<div class="container py-4" dir="rtl">
    <div class="row justify-content-center">
        <div class="col-md-8"> <!-- 👈 narrower column -->
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-person-plus-fill text-success"></i> إضافة مشترك جديد
                    </h5>
                </div>

                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle-fill"></i> حدثت بعض الأخطاء:
                            <ul class="mb-0 mt-2">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('clients.store') }}">
                        @csrf

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">الاسم الأول</label>
                                <input type="text" name="first_name" class="form-control" value="{{ old('first_name') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">اسم الأب</label>
                                <input type="text" name="father_name" class="form-control" value="{{ old('father_name') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">الكنية</label>
                                <input type="text" name="last_name" class="form-control" value="{{ old('last_name') }}">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">رقم الهاتف</label>
                                <input type="text" name="phone_number" class="form-control" value="{{ old('phone_number') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">العنوان</label>
                                <input type="text" name="address" class="form-control" value="{{ old('address') }}">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">المولد</label>
                                <select name="generator_id" class="form-select">
                                    <option value="">اختر المولد</option>
                                    @foreach ($generators as $generator)
                                        <option value="{{ $generator->id }}" {{ old('generator_id') == $generator->id ? 'selected' : '' }}>
                                            {{ $generator->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">فئة العداد</label>
                                <select name="meter_category_id" class="form-select">
                                    <option value="">اختر الفئة</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('meter_category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->category }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">العداد الحالي (اختياري)</label>
                            <input type="number" name="previous_meter" class="form-control" value="{{ old('current_meter') }}">
                        </div>

                        <div class="d-flex justify-content-start mb-3">
                            <a href="{{ route('active.clients.index') }}" class="btn btn-outline-secondary me-2">
                                <i class="bi bi-x-circle"></i> إلغاء
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-circle"></i> إضافة المشترك
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div> <!-- /col -->
    </div> <!-- /row -->
</div>
@endsection
