@extends('layouts.app')

@section('title', 'لوحة التحكم')

@section('content')
<div class="h-100 d-flex flex-column bg-body-tertiary rounded-4 p-1 p-md-2 overflow-hidden" dir="rtl" 
     x-data="{
        showAlert: false,
        alertMessage: '',
        alertType: 'success',
        init() {
            // Listen for alert events from Livewire components
            Livewire.on('showAlert', (data) => {
                this.showAlert = true;
                this.alertMessage = data.message;
                this.alertType = data.type;
                
                // Auto hide after 5 seconds
                setTimeout(() => {
                    this.showAlert = false;
                }, 5000);
            });
        }
     }">

    <!-- Fixed Header Section -->
    <div class="row justify-content-center py-2 mx-0 flex-shrink-0">
        <div class="col-md-10 px-0">
            <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                <div class="card-body bg-white border border-success-subtle text-dark text-center rounded-4 py-2 py-md-3">
                    <h4 class="mb-0 fw-bold">
                        <i class="bi bi-speedometer2 me-2"></i> لوحة التحكم
                    </h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Scrollable Content Section -->
    <div class="flex-grow-1 overflow-auto" style="min-height: 0;">
        <div class="row justify-content-center mx-0 pb-2">
            <div class="col-md-10 px-0">
                <div class="card shadow-lg border-0 rounded-4">
                    <div class="card-body p-3 p-md-4">
                        <!-- Include Livewire components -->
                        @livewire('exchange-rate-modal')
                        @livewire('receipt-modal')
                        @livewire('search-client-modal')

                        <!-- Global Alert for Dashboard -->
                        <div x-show="showAlert" 
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0 transform translate-y-2"
                             x-transition:enter-end="opacity-100 transform translate-y-0"
                             x-transition:leave="transition ease-in duration-300"
                             x-transition:leave-start="opacity-100 transform translate-y-0"
                             x-transition:leave-end="opacity-0 transform translate-y-2"
                             :class="{
                                 'alert-success': alertType === 'success',
                                 'alert-danger': alertType === 'danger'
                             }"
                             class="alert border-0 text-center rounded-3 shadow-sm mb-3 position-relative"
                             style="display: none;">
                            <button type="button" class="btn-close position-absolute top-50 translate-middle-y" style="right: 1rem;" @click="showAlert = false"></button>
                            <div class="d-flex align-items-center justify-content-center">
                                <i :class="{
                                    'bi-check-circle': alertType === 'success',
                                    'bi-exclamation-triangle': alertType === 'danger'
                                }" class="bi me-2"></i>
                                <span x-text="alertMessage"></span>
                            </div>
                        </div>

                        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-2">
                            @php
                                $buttons = [
                                    ['label' => 'إدخال دفعات', 'route' => 'payment.entry', 'icon' => 'bi-wallet2'],
                                    ['label' => 'إدخال العدادات', 'route' => 'meter.readings', 'icon' => 'bi-speedometer2', 'confirm' => true],
                                    ['label' => 'عرض لائحة المشتركين وتعديلها', 'route' => 'clients.index', 'icon' => 'bi-people'],
                                    ['label' => 'إدخال مشترك', 'route' => 'clients.create', 'icon' => 'bi-person-plus'],
                                    ['label' => 'إدخال الأسعار', 'route' => 'manage.prices', 'icon' => 'bi-cash-stack'],
                                    ['label' => 'تقرير شهري لقراءة العدادات', 'route' => 'meter-reading.form-report', 'icon' => 'bi-speedometer'],
                                    ['label' => 'تقرير شهري للمشتركين', 'route' => 'meter-readings.monthly-report', 'icon' => 'bi-file-earmark-text'],
                                    ['label' => 'تقرير تحصيل شهري', 'route' => 'monthly.payment.report', 'icon' => 'bi-coin'],
                                    ['label' => ' المولدات والصيانة', 'route' => 'manage.generators', 'icon' => 'bi-lightning'],                            
                                    ['label' => 'إدخال صيانة للمشترك', 'route' => 'maintenance.entry', 'icon' => 'bi-tools'],
                                    ['label' => 'تقرير عن المبالغ المستحقة', 'route' => 'outstanding.amounts.report', 'icon' => 'bi-currency-dollar'],
                                    ['label' => 'تقرير شراء/استهلاك الوقود ', 'route' => 'fuel.purchase.report', 'icon' => 'bi-fuel-pump-diesel'],
                                    ['label' => 'مصاريف الصيانة للمشترك', 'type' => 'view-maintenance', 'icon' => 'bi-tools'],
                                    ['label' => 'طباعة جميع الإيصالات', 'type' => 'showBulkReceipts', 'icon' => 'bi-printer'],
                                    ['label' => 'تعديل سعر الصرف', 'type' => 'openExchangeRateModal', 'icon' => 'bi-currency-exchange'],
                                    ['label' => 'عرض العداد الموافق لكل مشترك', 'type' => 'view-meter', 'icon' => 'bi-speedometer2'],
                                    ['label' => 'طباعة إيصال مشترك', 'type' => 'print-receipt', 'icon' => 'bi-receipt-cutoff'],
                                ];
                            @endphp

                            @foreach ($buttons as $button)
                                <div class="col">
                                    @if(isset($button['route']))
                                        @if(isset($button['confirm']) && $button['confirm'] && (time() - session('auth.password_confirmed_at', 0)) > 10800) {{-- 3 hours timeout --}}
                                            <button type="button" 
                                                    onclick="Livewire.dispatch('confirmPassword', { data: '{{ route($button['route']) }}' })"
                                                    class="btn btn-light border border-secondary-subtle w-100 h-100 py-2 px-3 rounded-4 shadow-sm d-flex align-items-center justify-content-between gap-2 text-end fw-semibold">
                                                <span class="text-dark">{{ $button['label'] }}</span>
                                                <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-success-subtle text-success flex-shrink-0" style="width: 2.1rem; height: 2.1rem;">
                                                    <i class="{{ $button['icon'] }}"></i>
                                                </span>
                                            </button>
                                        @else
                                            <a href="{{ route($button['route']) }}" 
                                               class="btn btn-light border border-secondary-subtle w-100 h-100 py-2 px-3 rounded-4 shadow-sm d-flex align-items-center justify-content-between gap-2 text-end fw-semibold">
                                                <span class="text-dark">{{ $button['label'] }}</span>
                                                <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-success-subtle text-success flex-shrink-0" style="width: 2.1rem; height: 2.1rem;">
                                                    <i class="{{ $button['icon'] }}"></i>
                                                </span>
                                            </a>
                                        @endif
                                    @elseif(isset($button['type']))
                                        @php
                                            $dispatch = match($button['type']) {
                                                'print-receipt' => "Livewire.dispatch('openClientSearch', { actionType: 'print-receipt' })",
                                                'openExchangeRateModal' => "Livewire.dispatch('openExchangeRateModal')",
                                                'view-meter' => "Livewire.dispatch('openClientSearch', { actionType: 'view-meter' })",
                                                'view-maintenance' => "Livewire.dispatch('openClientSearch', { actionType: 'view-maintenance' })",
                                                'showBulkReceipts' => "Livewire.dispatch('showBulkReceipts')",
                                                default => ""
                                            };
                                        @endphp
                                        <button type="button" 
                                                onclick="{{ $dispatch }}"
                                                class="btn btn-light border border-secondary-subtle w-100 h-100 py-2 px-3 rounded-4 shadow-sm d-flex align-items-center justify-content-between gap-2 text-end fw-semibold">
                                            <span class="text-dark">{{ $button['label'] }}</span>
                                            <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-success-subtle text-success flex-shrink-0" style="width: 2.1rem; height: 2.1rem;">
                                                <i class="{{ $button['icon'] }}"></i>
                                            </span>
                                        </button>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
