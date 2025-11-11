@extends('layouts.app')

@section('title', 'سجل الدفعات للمشترك')

@section('content')
<div class="container mt-4" dir="rtl">
    @livewire('payment-history', ['clientId' => $clientId])
</div>
@endsection
