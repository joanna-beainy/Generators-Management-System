@extends('layouts.app')

@section('title', 'تسجيل دخول')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-header bg-white text-center border-0 rounded-top-4">
                <h4 class="mb-0 fw-bold text-dark">تسجيل الدخول</h4>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="name" class="form-label fw-semibold"><bdi>اسم المستخدم</bdi></label>
                        <input type="text" 
                               name="name" 
                               id="name" 
                               class="form-control form-control-lg" 
                               value="{{ old('name') }}" 
                               required 
                               autofocus
                               placeholder="أدخل اسم المستخدم">
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label fw-semibold"><bdi>كلمة المرور</bdi></label>
                        <input type="password" 
                               name="password" 
                               id="password" 
                               class="form-control form-control-lg" 
                               required
                               placeholder="••••••••">
                    </div>

                    <button type="submit" class="btn btn-dark btn-lg w-100 rounded-3">
                        دخول
                    </button>
                </form>

                @if ($errors->any())
                    <div class="mt-4">
                        @foreach ($errors->all() as $error)
                            <div class="alert alert-danger text-center small py-2 mb-2">{{ $error }}</div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
