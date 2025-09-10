@extends('layouts.app')

@section('title', 'إدارة الأسعار')

@section('content')
<div class="container py-5" dir="rtl">
    <h2 class="mb-5 text-center fw-bold text-dark">
        <i class="bi bi-cash-stack me-2 text-success"></i> إدارة الأسعار
    </h2>

    <div class="mb-4">
        @livewire('kilowatt-price')
    </div>

    <div class="mb-4">
        @livewire('meter-category-prices')
    </div>

    <div class="text-center mt-5">
        <a href="{{ route('users.dashboard') }}" class="btn btn-outline-secondary rounded-pill px-5 py-2">
            <i class="bi bi-x-circle me-1"></i> إغلاق
        </a>
    </div>
</div>
@endsection
