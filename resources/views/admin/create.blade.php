@extends('layouts.app')
@section('title', 'إضافة صاحب مولد')
@section('content')
<div class="container mt-4" dir="rtl">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header text-center bg-light">
                    <h5>إضافة صاحب مولد جديد</h5>
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success text-center">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('users.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label">اسم المستخدم</label>
                            <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">كلمة المرور</label>
                            <input type="password" name="password" id="password" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">أرقام الهاتف</label>
                            <div id="phone-fields">
                                <div class="input-group mb-2">
                                    <input type="text" name="phone_numbers[]" class="form-control" placeholder="أدخل رقم الهاتف" required>
                                    <button type="button" class="btn btn-outline-secondary remove-phone">✖</button>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="add-phone">إضافة رقم آخر</button>
                        </div>

                        <button type="submit" class="btn btn-success w-100">إنشاء الحساب</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>


@endsection
@section('scripts')

{{-- JavaScript to handle dynamic phone fields --}}
<script>
    document.getElementById('add-phone').addEventListener('click', function () {
        const container = document.getElementById('phone-fields');
        const field = document.createElement('div');
        field.classList.add('input-group', 'mb-2');
        field.innerHTML = `
            <input type="text" name="phone_numbers[]" class="form-control" placeholder="أدخل رقم الهاتف" required>
            <button type="button" class="btn btn-outline-secondary remove-phone">✖</button>
        `;
        container.appendChild(field);
    });

    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-phone')) {
            e.target.parentElement.remove();
        }
    });
</script>
@endSection
