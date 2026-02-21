@extends('layouts.app')

@section('title', 'إدارة الأسعار')

@section('content')
<div class="container d-flex flex-column h-100" style="overflow-y: auto;" dir="rtl">
    <style>
        :root {
            --fluid-v-gap: clamp(0.5rem, 2vh, 1.5rem);
            --fluid-v-header-margin: clamp(0.5rem, 2vh, 1.5rem);
        }
    </style>

    <!-- Header Section -->
    <div class="flex-shrink-0 d-flex justify-content-between align-items-center" style="margin-top: var(--fluid-v-header-margin); margin-bottom: var(--fluid-v-header-margin);">
        <div>
            <h3 class="fw-bold text-dark mb-0">
                <i class="bi bi-cash-stack text-success me-2"></i> إدارة الأسعار
            </h3>
            <p class="text-secondary mb-0 mt-1 small">تعديل أسعار الكيلووات وفئات الاشتراك</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('users.dashboard') }}" class="btn btn-outline-danger fw-bold rounded-pill shadow-sm px-4">
                <i class="bi bi-x-circle me-1"></i> إغلاق
            </a>
        </div>
    </div>

    <!-- Page Body -->
    <div class="flex-shrink-1 pb-4">
        <div style="margin-bottom: var(--fluid-v-gap);">
            @livewire('kilowatt-price-manager')
        </div>

        <div>
            @livewire('meter-category-prices')
        </div>
    </div>
</div>
@endsection
