@extends('layouts.app')

@section('title', 'لوحة التحكم')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card shadow-sm">
            <div class="card-header bg-secondary-subtle text-dark text-center border-bottom">
                <h5 class="mb-0">لوحة التحكم</h5>
            </div>


            <div class="card-body">
                <div class="row row-cols-1 row-cols-md-2 g-3" dir="rtl">
                    @php
                        $buttons = [
                            ['label' => 'إدخال العدادات', 'route' => 'user.dashboard'],
                            ['label' => 'إدخال الأسعار', 'route' => 'user.dashboard'],
                            ['label' => 'طباعة الإيصالات', 'route' => 'user.dashboard'],
                            ['label' => 'عرض لائحة المشتركين و تعديلها', 'route' => 'user.dashboard'],
                            ['label' => 'تقرير شهري للمشتركين', 'route' => 'user.dashboard'],
                            ['label' => 'تقرير تحصيل شهري', 'route' => 'user.dashboard'],
                            ['label' => 'تقرير شهري لقراءة العدادات', 'route' => 'user.dashboard'],
                            ['label' => 'إدخال مشترك', 'route' => 'user.dashboard'],
                            ['label' => 'إدخال دفعات', 'route' => 'user.dashboard'],
                            ['label' => 'إدخال مواد', 'route' => 'user.dashboard'],
                            ['label' => 'طباعة إيصال مشترك', 'route' => 'user.dashboard'],
                            ['label' => 'عرض العداد الموافق لكل مشترك', 'route' => 'user.dashboard'],
                            ['label' => 'تقرير بأسماء المشتركين', 'route' => 'user.dashboard'],
                            ['label' => 'تقرير عن المبالغ المستخدمة', 'route' => 'user.dashboard'],
                        ];
                    @endphp

                    @foreach ($buttons as $button)
                        <div class="col">
                            <a href="{{ route($button['route']) }}" class="btn btn-outline-secondary w-100 py-3">
                                {{ $button['label'] }}
                            </a>

                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
