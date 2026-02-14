@extends('layouts.app')

@section('title', 'إدارة الأسعار')

@section('content')
<div class="container mt-2 mb-3" dir="rtl">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-0">
                <i class="bi bi-cash-stack text-success me-2"></i> إدارة الأسعار
            </h3>
            <p class="text-secondary mb-0 mt-1">تعديل أسعار الكيلووات وفئات الاشتراك</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('users.dashboard') }}" class="btn btn-outline-secondary rounded-pill shadow-sm px-4">
                <i class="bi bi-x-circle me-1"></i> إغلاق
            </a>
        </div>
    </div>

        <!-- Page Body -->
        <div class="mb-4">
            @livewire('kilowatt-price-manager')
        </div>

        <div>
            @livewire('meter-category-prices')
        </div>
</div>
@endsection
