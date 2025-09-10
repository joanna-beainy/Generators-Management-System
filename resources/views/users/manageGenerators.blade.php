@extends('layouts.app')

@section('title', 'إدارة المولدات')

@section('content')
<div class="container py-5" dir="rtl">
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body">
            @livewire('generators')
        </div>
    </div>
</div>
@endsection
