@extends('layouts.app')

@section('title', 'Chỉnh sửa Nhân viên - NT Logistics')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <a href="{{ route('users.index') }}" class="text-navy text-decoration-none small fw-bold">
                <i class="fa fa-arrow-left me-1"></i> Quay lại danh sách
            </a>
            <h4 class="fw-bold mt-2 text-navy">Chỉnh sửa Tài khoản: {{ $user->name }}</h4>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card border-0 rounded-4 shadow-sm">
                <div class="card-body p-4">
                    <form action="{{ route('users.update', $user) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Họ tên nhân viên</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" data-validate="person-name" data-label="Họ tên nhân viên" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Email đăng nhập</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Mã nhân sự</label>
                                <input type="text" class="form-control bg-light fw-bold text-navy" value="{{ $user->employee_code ?? '---' }}" disabled>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Chức vụ</label>
                                <select name="position" class="form-select" required>
                                    @foreach($positions as $value => $label)
                                        <option value="{{ $value }}" {{ old('position', $user->position) === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Bộ phận / phòng ban</label>
                                <select name="department" class="form-select" required>
                                    @foreach($departments as $value => $label)
                                        <option value="{{ $value }}" {{ old('department', $user->department) === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Ngày sinh</label>
                                <input type="text" name="date_of_birth" class="form-control" value="{{ \App\Support\VietnameseDate::display(old('date_of_birth', $user->date_of_birth)) }}" placeholder="Ngày/Tháng/Năm" data-date-input data-label="Ngày sinh" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Ngày tham gia</label>
                                <input type="text" name="joined_at" class="form-control" value="{{ \App\Support\VietnameseDate::display(old('joined_at', $user->joined_at ?? now()->toDateString())) }}" placeholder="Ngày/Tháng/Năm" data-date-input data-label="Ngày tham gia" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Vai trò hệ thống</label>
                            <select name="role_id" class="form-select @error('role_id') is-invalid @enderror" required>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}" {{ old('role_id', $user->role_id) == $role->id ? 'selected' : '' }}>
                                        {{ $role->role_name }} ({{ $role->role_code }})
                                    </option>
                                @endforeach
                            </select>
                            @error('role_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="p-3 bg-light rounded-3 mb-4 border-start border-4 border-warning">
                            <h6 class="fw-bold text-navy mb-1 small">Đổi mật khẩu (Tùy chọn)</h6>
                            <p class="small text-muted mb-3">Chỉ nhập nếu bạn muốn thay đổi mật khẩu cho nhân viên này.</p>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Mật khẩu mới</label>
                                    <input type="password" name="password" class="form-control form-control-sm @error('password') is-invalid @enderror">
                                    <div class="form-text">Có chữ hoa, chữ thường, chữ số và ký tự đặc biệt.</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Xác nhận mật khẩu</label>
                                    <input type="password" name="password_confirmation" class="form-control form-control-sm">
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-navy w-100 fw-bold py-2">
                            <i class="fa fa-save me-2"></i> CẬP NHẬT THÔNG TIN
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
