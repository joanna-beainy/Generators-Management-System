@extends('layouts.app')

@section('title', 'لوحة التحكم')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4>إدارة أصحاب المولدات</h4>
    <a href="{{ route('users.create') }}" class="btn btn-primary">إضافة صاحب مولد</a>
</div>

@if(session('success'))
    <div class="alert alert-success text-center">{{ session('success') }}</div>
@endif

<table class="table table-bordered table-hover text-center">
    <thead class="table-light">
        <tr>
            <th>الاسم</th>
            <th>أرقام الهاتف</th>
            <th>الإجراءات</th>
        </tr>
    </thead>
    <tbody>
        @forelse($users as $user)
            <tr>
                <td>{{ $user->name }}</td>
                <td>
                    @foreach($user->phoneNumbers as $phone)
                        <div>{{ $phone->phone_number }}</div>
                    @endforeach
                </td>
                <td>
                    <form action="{{ route('users.destroy', $user) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذا المستخدم؟')">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-danger">حذف</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="3">لا يوجد أصحاب مولدات حتى الآن.</td>
            </tr>
        @endforelse
    </tbody>
</table>
@endsection
