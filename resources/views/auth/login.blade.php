@extends('layouts.auth')

@section('title', 'Đăng nhập')

@section('content')
<section class="auth-container">
    <div class="auth-card">
        <div class="auth-image"></div>
        <div class="auth-form">
            <div class="text-center mb-5">
                <h2 class="fw-bold text-navy">Đăng Nhập</h2>
                <p class="text-muted">Hệ thống ERP Nguyên Tâm Logistics</p>
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

            <form action="{{ route('login') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="form-label fw-semibold">Tên đăng nhập</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0"><i class="fa fa-user"></i></span>
                        <input type="text" name="username" class="form-control bg-light border-0 py-2" placeholder="Nhập username" value="{{ old('username') }}" required autofocus>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-semibold">Mật khẩu</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0"><i class="fa fa-lock"></i></span>
                        <input type="password" name="password" class="form-control bg-light border-0 py-2" placeholder="********" required>
                    </div>
                </div>
                <div class="d-flex justify-content-between mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember">
                        <label class="form-check-label text-muted" for="remember">Ghi nhớ</label>
                    </div>
                    <a href="#" class="text-navy text-decoration-none small fw-semibold">Quên mật khẩu?</a>
                </div>
                <button type="submit" class="btn btn-navy w-100 py-2 fw-bold">ĐĂNG NHẬP</button>
                <p class="text-center mt-4 text-muted small">
                    Chưa có tài khoản? <a href="{{ route('register') }}" class="text-navy fw-bold text-decoration-none">Đăng ký ngay</a>
                </p>
            </form>
        </div>
    </div>
</section>
@endsection
