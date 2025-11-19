@extends('layouts.app')

@section('title', 'سجل الدفعات للمشترك')

@section('content')
    @livewire('payment-history', ['clientId' => $clientId])
@endsection
