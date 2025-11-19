@extends('layouts.app')

@section('title', 'إدارة الأسعار')

@section('content')
<div class="container mt-2 mb-3" dir="rtl">
    <div class="card shadow-sm">
        <div class="card-header bg-light text-dark">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-cash-stack me-2 text-success"></i>
                    إدارة الأسعار 
                </h5>
               
                <a href="{{ route('users.dashboard') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-house me-1"></i>
                        إغلاق
                </a>
            </div>
        </div>

        <!-- Page Body -->
        <div class="card-body p-4">
            <div class="mb-4">
                @livewire('kilowatt-price-manager')
            </div>

            <div >
                @livewire('meter-category-prices')
            </div>
        </div>
    </div>
</div>
@endsection
