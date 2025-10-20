@extends('layouts.app')

@section('title', 'إدارة الأسعار')

@section('content')
<div class="container py-5" dir="rtl">

    <!-- Page Card Wrapper -->
    <div class="card shadow-sm rounded-4 border-0">
        <!-- Page Header -->
        <div class="card-header bg-success text-white rounded-top-4">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-cash-stack me-2"></i>
                    إدارة الأسعار 
                </h5>
               
                <a href="{{ route('users.dashboard') }}" class="btn btn-light btn-sm">
                    <i class="bi bi-house me-1"></i>
                    إغلاق
                </a>
            </div>
        </div>

        <!-- Page Body -->
        <div class="card-body p-4">
            <div class="mb-4">
                @livewire('kilowatt-price')
            </div>

            <div class="mb-4">
                @livewire('meter-category-prices')
            </div>
        </div>
    </div>
</div>
@endsection
