@extends('layouts.app')

@section('title', 'لوحة التحكم')

@section('content')
<div class="row justify-content-center" 
     x-data="{ showAlert: {{ session('success') ? 'true' : 'false' }} }" 
     x-init="if(showAlert) { setTimeout(() => showAlert = false, 5000) }">

    <div class="col-md-10">
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-header bg-light text-dark text-center rounded-top-4 border-bottom">
                <h4 class="mb-0 fw-bold">
                    <i class="bi bi-speedometer2 me-2"></i> لوحة التحكم
                </h4>
            </div>

            <div class="card-body">
                <!-- ✅ Include Livewire components -->
                @livewire('exchange-rate-modal')
                @livewire('receipt-modal')
                @livewire('search-client-modal')

                <!-- ✅ Success Message -->
                <template x-if="showAlert">
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle me-2"></i>
                        {{ session('success') }}
                        <button type="button" class="btn-close" @click="showAlert = false"></button>
                    </div>
                </template>

                <div class="row row-cols-1 row-cols-md-3 g-3" dir="rtl">
                    @php
                        $buttons = [
                            ['label' => 'إدخال العدادات', 'route' => 'meter.readings', 'icon' => 'bi-speedometer2'],
                            ['label' => 'إدخال الأسعار', 'route' => 'manage.prices', 'icon' => 'bi-cash-stack'],
                            ['label' => 'عرض لائحة المشتركين و تعديلها', 'route' => 'clients.index', 'icon' => 'bi-people'],
                            ['label' => 'تقرير شهري للمشتركين', 'route' => 'users.dashboard', 'icon' => 'bi-file-earmark-text'],
                            ['label' => 'تقرير تحصيل شهري', 'route' => 'users.dashboard', 'icon' => 'bi-coin'],
                            ['label' => 'تقرير شهري لقراءة العدادات', 'route' => 'users.dashboard', 'icon' => 'bi-speedometer'],
                            ['label' => 'إدخال مشترك', 'route' => 'clients.create', 'icon' => 'bi-person-plus'],
                            ['label' => 'إدخال دفعات', 'route' => 'payment.entry', 'icon' => 'bi-wallet2'],
                             ['label' => 'إدخال صيانة', 'route' => 'maintenance.entry', 'icon' => 'bi-tools'],
                            ['label' => 'عرض المولدات', 'route' => 'manage.generators', 'icon' => 'bi-lightning'],
                            ['label' => 'تقرير بأسماء المشتركين', 'route' => 'users.dashboard', 'icon' => 'bi-file-person'],
                            ['label' => 'تقرير عن المبالغ المستخدمة', 'route' => 'users.dashboard', 'icon' => 'bi-currency-dollar'],
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

                    <!-- ✅ تعديل سعر الصرف -->
                    <div class="col">
                        <button type="button" 
                                onclick="Livewire.dispatch('openExchangeRateModal')"
                                class="btn btn-outline-secondary w-100 py-3 rounded-3 d-flex align-items-center justify-content-center gap-2">
                            <i class="bi bi-currency-exchange"></i>
                            <span>تعديل سعر الصرف</span>
                        </button>
                    </div>

                    <!-- ✅ عرض مصاريف الصيانة -->
                    <div class="col">
                        <button type="button" 
                                onclick="Livewire.dispatch('openClientSearch', { actionType: 'view-maintenance' })"
                                class="btn btn-outline-secondary w-100 py-3 rounded-3 d-flex align-items-center justify-content-center gap-2">
                            <i class="bi bi-tools"></i>
                            <span>عرض مصاريف الصيانة</span>
                        </button>
                    </div>

                    <!-- ✅ عرض دفعات مشترك -->
                    <div class="col">
                        <button type="button" 
                                onclick="Livewire.dispatch('openClientSearch', { actionType: 'view-payments' })"
                                class="btn btn-outline-secondary w-100 py-3 rounded-3 d-flex align-items-center justify-content-center gap-2">
                            <i class="bi bi-wallet-fill"></i>
                            <span>عرض دفعات مشترك</span>
                        </button>
                    </div>


                    <!-- ✅ طباعة جميع الإيصالات -->
                    <div class="col">
                        <button type="button" 
                                onclick="Livewire.dispatch('showBulkReceipts')"
                                class="btn btn-outline-secondary w-100 py-3 rounded-3 d-flex align-items-center justify-content-center gap-2">
                            <i class="bi bi-printer"></i>
                            <span>طباعة جميع الإيصالات</span>
                        </button>
                    </div>

                    <!-- ✅ طباعة إيصال مشترك -->
                    <div class="col">
                        <button type="button" 
                                onclick="Livewire.dispatch('openClientSearch', { actionType: 'print-receipt' })"
                                class="btn btn-outline-secondary w-100 py-3 rounded-3 d-flex align-items-center justify-content-center gap-2">
                            <i class="bi bi-receipt"></i>
                            <span>طباعة إيصال مشترك</span>
                        </button>
                    </div>

                    <!-- ✅ عرض العداد الموافق لكل مشترك -->
                    <div class="col">
                        <button type="button" 
                                onclick="Livewire.dispatch('openClientSearch', { actionType: 'view-meter' })"
                                class="btn btn-outline-secondary w-100 py-3 rounded-3 d-flex align-items-center justify-content-center gap-2">
                            <i class="bi bi-speedometer2"></i>
                            <span>عرض العداد الموافق لكل مشترك</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
