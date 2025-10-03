@extends('layouts.app')
@section('title', 'إضافة مشترك جديد')

@section('content')
<div class="container py-4" dir="rtl">
    <div class="card shadow-sm">
        <div class="card-header bg-light">
            <h5 class="mb-0">إضافة مشترك جديد</h5>
        </div>

        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
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
                        <label>الاسم الأول</label>
                        <input type="text" name="first_name" class="form-control" value="{{ old('first_name') }}">
                    </div>
                    <div class="col-md-4">
                        <label>اسم الأب</label>
                        <input type="text" name="father_name" class="form-control" value="{{ old('father_name') }}">
                    </div>
                    <div class="col-md-4">
                        <label>الكنية</label>
                        <input type="text" name="last_name" class="form-control" value="{{ old('last_name') }}">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label>رقم الهاتف</label>
                        <input type="text" name="phone_number" class="form-control" value="{{ old('phone_number') }}">
                    </div>
                    <div class="col-md-6">
                        <label>العنوان</label>
                        <input type="text" name="address" class="form-control" value="{{ old('address') }}">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label>المولد</label>
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
                        <label>فئة العداد</label>
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
                    <label>العداد الحالي (اختياري)</label>
                    <input type="number" name="current_meter" class="form-control" value="{{ old('current_meter') }}">
                </div>

                <div class="d-flex justify-content-start mb-3 ">
                    <a href="{{ route('active.clients.index') }}" class="btn btn-outline-secondary">إلغاء</a>
                    <button type="submit" class="btn btn-primary">إضافة المشترك</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
