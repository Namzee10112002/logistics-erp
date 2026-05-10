@extends('layouts.app')

@section('title', 'Hồ sơ của tôi')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-navy text-white p-4">
                    <h5 class="mb-0 fw-bold"><i class="fa fa-user-circle me-2"></i> Cài đặt tài khoản</h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('profile.update') }}" method="POST">
                        @csrf
                        
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-navy">Họ và tên</label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold text-navy">Email</label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <hr class="my-4">

                            <div class="col-12">
                                <h6 class="fw-bold text-navy mb-3">Tùy chỉnh giao diện</h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label small text-muted">Màu sắc chủ đạo (Theme Color)</label>
                                        <div class="d-flex align-items-center gap-3">
                                            <input type="color" name="theme_color" class="form-control form-control-color" value="{{ $user->theme_color ?? '#1a237e' }}" title="Chọn màu theme">
                                            <span class="small text-muted">{{ $user->theme_color ?? '#1a237e' }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6 d-flex align-items-center">
                                        <div class="form-check form-switch mt-4">
                                            <input class="form-check-input" type="checkbox" name="is_dark_mode" id="darkModeSwitch" {{ $user->is_dark_mode ? 'checked' : '' }}>
                                            <label class="form-check-label fw-bold text-navy" for="darkModeSwitch">Chế độ tối (Dark Mode)</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="col-12">
                                <h6 class="fw-bold text-navy mb-3">Đổi mật khẩu (Để trống nếu không đổi)</h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label small text-muted">Mật khẩu mới</label>
                                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">
                                        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small text-muted">Xác nhận mật khẩu mới</label>
                                        <input type="password" name="password_confirmation" class="form-control">
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 mt-5">
                                <button type="submit" class="btn btn-navy px-5 py-2 fw-bold">LƯU THAY ĐỔI</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
