@extends('layouts.app')

@section('content')

    <div class="container mt-4">
        @livewire('payment-entry')
    </div>
@section('scripts')
@livewireScripts
@endsection
