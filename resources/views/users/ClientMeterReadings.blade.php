@extends('layouts.app')
@section('title', 'قراءات عداد المشترك')

@section('content')
    @livewire('client-meter-readings', ['clientId' => $clientId])
@endsection
