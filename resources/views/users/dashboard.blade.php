@extends('layouts.app')

@section('title', 'لوحة التحكم')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-header bg-light text-dark text-center rounded-top-4 border-bottom">
                <h4 class="mb-0 fw-bold"><i class="bi bi-speedometer2 me-2"></i> لوحة التحكم</h4>
            </div>

            <div class="card-body">
                <div class="row row-cols-1 row-cols-md-3 g-3" dir="rtl">
                    @php
                        $buttons = [
                            ['label' => 'إدخال العدادات', 'route' => '', 'icon' => 'bi-speedometer2'],
                            ['label' => 'إدخال الأسعار', 'route' => 'manage.prices', 'icon' => 'bi-cash-stack'],
                            ['label' => 'طباعة الإيصالات', 'route' => '', 'icon' => 'bi-printer'],
                            ['label' => 'عرض لائحة المشتركين و تعديلها', 'route' => 'active.clients.index', 'icon' => 'bi-people'],
                            ['label' => 'تقرير شهري للمشتركين', 'route' => '', 'icon' => 'bi-file-earmark-text'],
                            ['label' => 'تقرير تحصيل شهري', 'route' => '', 'icon' => 'bi-coin'],
                            ['label' => 'تقرير شهري لقراءة العدادات', 'route' => '', 'icon' => 'bi-speedometer'],
                            ['label' => 'إدخال مشترك', 'route' => 'clients.create', 'icon' => 'bi-person-plus'],
                            ['label' => 'إدخال دفعات', 'route' => '', 'icon' => 'bi-wallet2'],
                            ['label' => 'عرض المولدات', 'route' => 'manage.generators', 'icon' => 'bi-lightning'],
                            ['label' => 'طباعة إيصال مشترك', 'route' => '', 'icon' => 'bi-receipt'],
                            ['label' => 'عرض العداد الموافق لكل مشترك', 'route' => '', 'icon' => 'bi-speedometer2'],
                            ['label' => 'تقرير بأسماء المشتركين', 'route' => '', 'icon' => 'bi-file-person'],
                            ['label' => 'تقرير عن المبالغ المستخدمة', 'route' => '', 'icon' => 'bi-currency-dollar'],
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
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
