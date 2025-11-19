@extends('layouts.app')

@section('title', 'مصاريف الصيانة')

@section('content')
    @livewire('maintenance-list', ['clientId' => $clientId])
@endsection
