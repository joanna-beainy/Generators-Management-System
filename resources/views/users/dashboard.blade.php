@extends('layouts.app')

@section('title', 'لوحة التحكم')

@section('content')
<div class="row justify-content-center" 
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

    <div class="col-md-10">
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-header bg-light text-dark text-center rounded-top-4 border-bottom">
                <h4 class="mb-0 fw-bold">
                    <i class="bi bi-speedometer2 me-2"></i> لوحة التحكم
                </h4>
            </div>

            <div class="card-body">
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
                     class="alert alert-dismissible fade show text-center rounded-3 shadow-sm mb-4"
                     style="display: none;">
                    <i :class="{
                        'bi-check-circle': alertType === 'success',
                        'bi-exclamation-triangle': alertType === 'danger'
                    }" class="bi me-1"></i>
                    <span x-text="alertMessage"></span>
                    <button type="button" class="btn-close" @click="showAlert = false"></button>
                </div>

                <div class="row row-cols-1 row-cols-md-3 g-3" dir="rtl">
                    @php
                        $buttons = [
                            ['label' => 'إدخال دفعات', 'route' => 'payment.entry', 'icon' => 'bi-wallet2'],
                            ['label' => 'إدخال العدادات', 'route' => 'meter.readings', 'icon' => 'bi-speedometer2'],
                            ['label' => 'عرض لائحة المشتركين و تعديلها', 'route' => 'clients.index', 'icon' => 'bi-people'],
                            ['label' => 'إدخال مشترك', 'route' => 'clients.create', 'icon' => 'bi-person-plus'],
                            ['label' => 'إدخال الأسعار', 'route' => 'manage.prices', 'icon' => 'bi-cash-stack'],
                            ['label' => 'تقرير شهري لقراءة العدادات', 'route' => 'meter-reading.form-report', 'icon' => 'bi-speedometer'],
                            ['label' => 'تقرير شهري للمشتركين', 'route' => 'meter-readings.monthly-report', 'icon' => 'bi-file-earmark-text'],
                            ['label' => 'تقرير تحصيل شهري', 'route' => 'monthly.payment.report', 'icon' => 'bi-coin'],
                            ['label' => ' المولدات و الصيانة', 'route' => 'manage.generators', 'icon' => 'bi-lightning'],                            
                            ['label' => 'إدخال صيانة للمشترك', 'route' => 'maintenance.entry', 'icon' => 'bi-tools'],
                            ['label' => 'تقرير عن المبالغ المستحقة', 'route' => 'outstanding.amounts.report', 'icon' => 'bi-currency-dollar'],
                            ['label' => 'تقرير شراء/استهلاك الوقود ', 'route' => 'fuel.purchase.report', 'icon' => 'bi-fuel-pump-diesel'],                           

                        ];
                    @endphp

                    @foreach ($buttons as $button)
                        <div class="col">
                            <a href="{{ route($button['route']) }}" 
                               class="btn btn-outline-secondary w-100 py-3 rounded-3 d-flex align-items-center justify-content-center gap-2">
                                <i class="{{ $button['icon'] }}"></i>
                                <span>{{ $button['label'] }}</span>
                            </a>
                        </div>
                    @endforeach


                    <!-- عرض مصاريف الصيانة -->
                    <div class="col">
                        <button type="button" 
                                onclick="Livewire.dispatch('openClientSearch', { actionType: 'view-maintenance' })"
                                class="btn btn-outline-secondary w-100 py-3 rounded-3 d-flex align-items-center justify-content-center gap-2">
                            <i class="bi bi-tools"></i>
                            <span>مصاريف الصيانة للمشترك</span>
                        </button>
                    </div>

                    <!-- طباعة جميع الإيصالات -->
                    <div class="col">
                        <button type="button" 
                                onclick="Livewire.dispatch('showBulkReceipts')"
                                class="btn btn-outline-secondary w-100 py-3 rounded-3 d-flex align-items-center justify-content-center gap-2">
                            <i class="bi bi-printer"></i>
                            <span>طباعة جميع الإيصالات</span>
                        </button>
                    </div>

                    <!-- تعديل سعر الصرف -->
                    <div class="col">
                        <button type="button" 
                                onclick="Livewire.dispatch('openExchangeRateModal')"
                                class="btn btn-outline-secondary w-100 py-3 rounded-3 d-flex align-items-center justify-content-center gap-2">
                            <i class="bi bi-currency-exchange"></i>
                            <span>تعديل سعر الصرف</span>
                        </button>
                    </div>

                     <!-- عرض العداد الموافق لكل مشترك -->
                    <div class="col">
                        <button type="button" 
                                onclick="Livewire.dispatch('openClientSearch', { actionType: 'view-meter' })"
                                class="btn btn-outline-secondary w-100 py-3 rounded-3 d-flex align-items-center justify-content-center gap-2">
                            <i class="bi bi-speedometer2"></i>
                            <span>عرض العداد الموافق لكل مشترك</span>
                        </button>
                    </div>

                    <!-- طباعة إيصال مشترك -->
                    <div class="col">
                        <button type="button" 
                                onclick="Livewire.dispatch('openClientSearch', { actionType: 'print-receipt' })"
                                class="btn btn-outline-secondary w-100 py-3 rounded-3 d-flex align-items-center justify-content-center gap-2">
                            <i class="bi bi-receipt-cutoff"></i>
                            <span>طباعة إيصال مشترك</span>
                        </button>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</div>
@endsection