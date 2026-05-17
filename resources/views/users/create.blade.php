@extends('layouts.app')

@section('title', 'Thêm Nhân viên - NT Logistics')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <a href="{{ route('users.index') }}" class="text-navy text-decoration-none small fw-bold">
                <i class="fa fa-arrow-left me-1"></i> Quay lại danh sách
            </a>
            <h4 class="fw-bold mt-2 text-navy">Thêm Nhân viên Mới</h4>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card border-0 rounded-4 shadow-sm">
                <div class="card-body p-4">
                    <form action="{{ route('users.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-bold">Họ tên nhân viên</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Email đăng nhập</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Mã nhân sự</label>
                                <input type="text" class="form-control bg-light" value="Tự sinh khi lưu" disabled>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Chức vụ</label>
                                <input type="text" name="position" class="form-control" value="{{ old('position') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Bộ phận / phòng ban</label>
                                <input type="text" name="department" class="form-control" value="{{ old('department') }}">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-bold">Ngày tham gia</label>
                                <input type="date" name="joined_at" class="form-control" value="{{ old('joined_at') }}">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Vai trò hệ thống</label>
                            <select name="role_id" class="form-select @error('role_id') is-invalid @enderror" required>
                                <option value="">Chọn vai trò...</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                        {{ $role->role_name }} ({{ $role->role_code }})
                                    </option>
                                @endforeach
                            </select>
                            @error('role_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Mật khẩu</label>
                                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Xác nhận mật khẩu</label>
                                <input type="password" name="password_confirmation" class="form-control" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-navy w-100 fw-bold py-2">
                            <i class="fa fa-save me-2"></i> KHỞI TẠO TÀI KHOẢN
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
