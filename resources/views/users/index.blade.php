@extends('layouts.app')

@section('title', 'Quản lý Nhân sự - NT Logistics')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="fw-bold text-navy">Danh sách Nhân viên</h4>
                <p class="text-muted small">Quản lý tài khoản và phân quyền truy cập hệ thống.</p>
            </div>
            <a href="{{ route('users.create') }}" class="btn btn-navy fw-bold px-4">
                <i class="fa fa-user-plus me-2"></i> THÊM NHÂN VIÊN
            </a>
            <x-export-buttons />
        </div>
    </div>

    <div class="card border-0 rounded-4 shadow-sm p-4 mb-4">
        <form action="{{ route('users.index') }}" method="GET" class="row g-3">
            <div class="col-md-10">
                <input type="text" name="search" class="form-control border-light" placeholder="Tìm theo mã nhân sự, họ tên, email, chức vụ, bộ phận..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-navy w-100">Lọc</button>
            </div>
            <div class="col-md-2"><input type="text" name="employee_code" class="form-control border-light" placeholder="Mã" value="{{ request('employee_code') }}"></div>
            <div class="col-md-2"><input type="text" name="name" class="form-control border-light" placeholder="Họ tên" value="{{ request('name') }}"></div>
            <div class="col-md-2"><input type="text" name="email" class="form-control border-light" placeholder="Email" value="{{ request('email') }}"></div>
            <div class="col-md-2"><input type="text" name="position" class="form-control border-light" placeholder="Chức vụ" value="{{ request('position') }}"></div>
            <div class="col-md-2"><input type="text" name="department" class="form-control border-light" placeholder="Bộ phận" value="{{ request('department') }}"></div>
            <div class="col-md-2">
                <select name="role_id" class="form-select border-light">
                    <option value="">Vai trò</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->id }}" {{ (string) request('role_id') === (string) $role->id ? 'selected' : '' }}>{{ $role->role_name }}</option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>

    <div class="card border-0 rounded-4 shadow-sm">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Mã / Họ tên</th>
                            <th>Email</th>
                            <th>Vai trò</th>
                            <th>Chức vụ</th>
                            <th>Bộ phận</th>
                            <th>Ngày sinh</th>
                            <th>Ngày tham gia</th>
                            <th class="text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-navy text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="fw-bold">{{ $user->employee_code ?? '---' }}</div>
                                            <div class="small text-muted">{{ $user->name }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    <span class="badge bg-primary bg-opacity-10 text-primary px-3">
                                        {{ $user->role->role_name }}
                                    </span>
                                </td>
                                <td>{{ $user->position ?? '---' }}</td>
                                <td>{{ $user->department ?? '---' }}</td>
                                <td>{{ $user->date_of_birth?->format('d/m/Y') ?? '---' }}</td>
                                <td>{{ $user->joined_at?->format('d/m/Y') ?? $user->created_at->format('d/m/Y') }}</td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-outline-navy">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        <form action="{{ route('users.destroy', $user) }}" method="POST" onsubmit="return confirm('Xác nhận xóa tài khoản này?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $users->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
