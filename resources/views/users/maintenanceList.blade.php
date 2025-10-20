@extends('layouts.app')

@section('title', 'مصاريف الصيانة')

@section('content')
<div class="container mt-4">
    @livewire('maintenance-list', ['clientId' => $clientId])
</div>
@endsection
