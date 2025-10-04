@extends('layouts.app') {{-- or your preferred layout --}}

@section('title', 'إدخال قراءات العدادات')

@section('content')
    @livewire('meter-readings')
@endsection
