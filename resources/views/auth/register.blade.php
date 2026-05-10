@extends('layouts.auth')

@section('title', 'Đăng ký')

@section('content')
<section class="auth-container">
    <div class="auth-card">
        <div class="auth-image"></div>
        <div class="auth-form">
            <div class="text-center mb-4">
                <h2 class="fw-bold text-navy">Tạo Tài Khoản</h2>
                <p class="text-muted">Cấp quyền nhân sự nội bộ</p>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('register') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label small fw-semibold">Họ và Tên</label>
                        <input type="text" name="full_name" class="form-control bg-light border-0" value="{{ old('full_name') }}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label small fw-semibold">Tên đăng nhập</label>
                        <input type="text" name="username" class="form-control bg-light border-0" value="{{ old('username') }}" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Email công ty</label>
                    <input type="email" name="email" class="form-control bg-light border-0" value="{{ old('email') }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Vai trò (Role)</label>
                    <select name="role_id" class="form-select bg-light border-0" required>
                        <option value="">Chọn vai trò...</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                {{ $role->role_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <label class="form-label small fw-semibold">Mật khẩu</label>
                        <input type="password" name="password" class="form-control bg-light border-0" required>
                    </div>
                    <div class="col-md-6 mb-4">
                        <label class="form-label small fw-semibold">Xác nhận</label>
                        <input type="password" name="password_confirmation" class="form-control bg-light border-0" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-navy w-100 py-2 fw-bold">TẠO TÀI KHOẢN</button>
                <p class="text-center mt-3 text-muted small">
                    Đã có tài khoản? <a href="{{ route('login') }}" class="text-navy fw-bold text-decoration-none">Đăng nhập</a>
                </p>
            </form>
        </div>
    </div>
</section>
@endsection
