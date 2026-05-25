@extends('layouts.app')

@section('title', 'Cài đặt')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h4 class="fw-bold text-navy">Cài đặt</h4>
            <p class="text-muted small">Quản lý hồ sơ cá nhân, bảo mật và tùy chỉnh hiển thị.</p>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4 text-center">
                    <div class="rounded-circle bg-navy text-white d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 96px; height: 96px; font-size: 2rem;">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <h5 class="fw-bold text-navy mb-1">{{ $user->name }}</h5>
                    <div class="text-muted small mb-3">{{ $user->role->role_name ?? 'Nhân viên' }}</div>
                    <div class="small text-muted">Mã nhân sự</div>
                    <div class="fw-bold">{{ $user->employee_code ?? '---' }}</div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <ul class="nav nav-tabs mb-4" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#profileTab" type="button">Hồ sơ cá nhân</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#securityTab" type="button">Bảo mật & hệ thống</button>
                        </li>
                    </ul>

                    <form action="{{ route('profile.update') }}" method="POST">
                        @csrf
                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="profileTab">
                                <div class="row g-3">
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
                                    <div class="col-md-4">
                                        <label class="form-label small text-muted">Màu chủ đạo</label>
                                        <input type="color" name="theme_color" class="form-control form-control-color" value="{{ old('theme_color', $user->theme_color ?? '#1a237e') }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small text-muted">Múi giờ</label>
                                        <select name="timezone" class="form-select">
                                            @foreach(['Asia/Ho_Chi_Minh' => 'Việt Nam', 'Asia/Bangkok' => 'Bangkok', 'UTC' => 'UTC'] as $value => $label)
                                                <option value="{{ $value }}" {{ old('timezone', $user->timezone ?? 'Asia/Ho_Chi_Minh') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small text-muted">Định dạng ngày</label>
                                        <select name="date_format" class="form-select">
                                            @foreach(['d/m/Y' => '21/05/2026', 'Y-m-d' => '2026-05-21', 'd-m-Y' => '21-05-2026'] as $value => $label)
                                                <option value="{{ $value }}" {{ old('date_format', $user->date_format ?? 'd/m/Y') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <input type="hidden" name="is_dark_mode" value="0">
                                        <div class="form-check form-switch mt-2">
                                            <input class="form-check-input" type="checkbox" name="is_dark_mode" id="darkModeSwitch" value="1" {{ old('is_dark_mode', $user->is_dark_mode) ? 'checked' : '' }}>
                                            <label class="form-check-label fw-bold text-navy" for="darkModeSwitch">Chế độ tối</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <input type="hidden" name="two_factor_enabled" value="0">
                                        <div class="form-check form-switch mt-2">
                                            <input class="form-check-input" type="checkbox" name="two_factor_enabled" id="twoFactorSwitch" value="1" {{ old('two_factor_enabled', $user->two_factor_enabled) ? 'checked' : '' }}>
                                            <label class="form-check-label fw-bold text-navy" for="twoFactorSwitch">Xác thực 2 yếu tố</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="securityTab">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <div class="alert alert-info small mb-0">Mật khẩu mới tối thiểu 8 ký tự và phải có chữ hoa, chữ thường, chữ số, ký tự đặc biệt.</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small text-muted">Mật khẩu mới</label>
                                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">
                                        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small text-muted">Xác nhận mật khẩu mới</label>
                                        <input type="password" name="password_confirmation" class="form-control">
                                    </div>
                                    <div class="col-12">
                                        <div class="table-responsive">
                                            <table class="table table-sm align-middle mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Thiết bị</th>
                                                        <th>IP</th>
                                                        <th>Lần hoạt động</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td>Phiên hiện tại</td>
                                                        <td>{{ request()->ip() }}</td>
                                                        <td>{{ now()->format('d/m/Y') }}</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-navy px-5 py-2 fw-bold">LƯU THAY ĐỔI</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
