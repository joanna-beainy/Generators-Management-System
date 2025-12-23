@extends('layouts.app')

@section('content')
<div class="row justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="row justify-content-center">
        <div class="col-md-4">

            <div class="card shadow rounded-2">
                <div class="card-header bg-light text-dark text-center">
                    <h5>تأكيد كلمة السر</h5>
                </div>

                <div class="card-body">

                    <form method="POST" action="{{ route('password.confirm') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">كلمة السر</label>
                            <input type="password" name="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   autofocus>

                            @error('password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-success w-100">
                            تأكيد
                        </button>
                    </form>

                </div>

            </div>

        </div>
    </div>

</div>
@endsection
