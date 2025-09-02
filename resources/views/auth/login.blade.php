@extends('layouts.app')

@section('title', 'تسجيل دخول')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-body">
                <h4 class="mb-4 text-center">تسجيل الدخول</h4>

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="name" class="form-label"><bdi>اسم المستخدم</bdi></label>
                        <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" required autofocus>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label"><bdi>كلمة المرور</bdi></label>
                        <input type="password" name="password" id="password" class="form-control" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">دخول</button>
                </form>

                @if ($errors->any())
                    <div class="mt-3">
                        @foreach ($errors->all() as $error)
                            <div class="alert alert-danger text-center">{{ $error }}</div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

