@extends('layouts.app')
@section('title', 'إضافة مشترك جديد')

@section('content')
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
                    {{-- Success Message --}}
                    @if (session('success'))
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
                        </div>
                    @endif

                    {{-- Error Messages --}}
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

                        {{-- Names Section --}}
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">الاسم الأول <span class="text-danger">*</span></label>
                                <input type="text" name="first_name" class="form-control @error('first_name') is-invalid @enderror" 
                                       value="{{ old('first_name') }}" required>
                                @error('first_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">اسم الأب</label>
                                <input type="text" name="father_name" class="form-control @error('father_name') is-invalid @enderror" 
                                       value="{{ old('father_name') }}">
                                @error('father_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">الكنية</label>
                                <input type="text" name="last_name" class="form-control @error('last_name') is-invalid @enderror" 
                                       value="{{ old('last_name') }}">
                                @error('last_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Contact Section --}}
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">رقم الهاتف</label>
                                <input type="text" name="phone_number" class="form-control @error('phone_number') is-invalid @enderror" 
                                       value="{{ old('phone_number') }}">
                                @error('phone_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">العنوان <span class="text-danger">*</span></label>
                                <input type="text" name="address" class="form-control @error('address') is-invalid @enderror" 
                                       value="{{ old('address') }}" required>
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Generator + Category Section --}}
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">المولد <span class="text-danger">*</span></label>
                                <select name="generator_id" class="form-select @error('generator_id') is-invalid @enderror" required>
                                    <option value="">اختر المولد</option>
                                    @foreach ($generators as $generator)
                                        <option value="{{ $generator->id }}" {{ old('generator_id') == $generator->id ? 'selected' : '' }}>
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
                                <select name="meter_category_id" id="meter_category_id" 
                                        class="form-select @error('meter_category_id') is-invalid @enderror">
                                    <option value="">اختر الفئة</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('meter_category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->category }} - {{ number_format($category->price, 2) }}$
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
                            <input type="number" name="initial_meter" class="form-control text-start @error('initial_meter') is-invalid @enderror" 
                                   value="{{ old('initial_meter') }}" min="0" step="1">
                            @error('initial_meter')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Offered Checkbox Section --}}
                        <div class="form-check mb-4">
                            <input class="form-check-input @error('is_offered') is-invalid @enderror" 
                                   type="checkbox" name="is_offered" id="is_offered" value="1"
                                   {{ old('is_offered') ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold text-primary" for="is_offered">تقدمة</label>
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

{{-- Enhanced JavaScript for better UX --}}
<script>
    function toggleMeterCategory() {
        const isOffered = document.getElementById('is_offered').checked;
        const meterCategorySelect = document.getElementById('meter_category_id');
        meterCategorySelect.disabled = isOffered;
        
        if (isOffered) {
            meterCategorySelect.value = '';
            meterCategorySelect.classList.add('bg-gray', 'text-muted');
        } else {
            meterCategorySelect.classList.remove('bg-gray', 'text-muted');
        }
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        // Set initial state
        toggleMeterCategory();
        
        // Update on checkbox change
        document.getElementById('is_offered').addEventListener('change', toggleMeterCategory);
        
        // Clear "offered" status if user manually selects a meter category
        document.getElementById('meter_category_id').addEventListener('change', function() {
            const isOffered = document.getElementById('is_offered').checked;
            if (isOffered && this.value) {
                document.getElementById('is_offered').checked = false;
                toggleMeterCategory();
            }
        });

        // Real-time validation feedback
        const form = document.querySelector('form');
        const inputs = form.querySelectorAll('input, select');
        
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                if (this.checkValidity()) {
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                } else {
                    this.classList.remove('is-valid');
                }
            });
            
            input.addEventListener('input', function() {
                if (this.checkValidity()) {
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                }
            });
        });
    });
</script>

<style>
    .is-valid {
        border-color: #198754 !important;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%23198754' d='M2.3 6.73.6 4.53c-.4-1.04.46-1.4 1.1-.8l1.1 1.4 3.4-3.8c.6-.63 1.6-.27 1.2.7l-4 4.6c-.43.5-.8.4-1.1.1z'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right calc(0.375em + 0.1875rem) center;
        background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
    }
    
    .bg-light:disabled {
        background-color: #f8f9fa !important;
        opacity: 0.7;
    }
</style>
@endsection