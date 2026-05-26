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
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
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
                                <label class="form-label fw-bold">Bộ phận / Phòng ban</label>
                                <select id="department" name="department" class="form-select @error('department') is-invalid @enderror" required>
                                    <option value="">-- Chọn bộ phận --</option>
                                    @foreach($departments as $value => $label)
                                        <option value="{{ $value }}" {{ old('department', $user->department) === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('department') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Chức vụ</label>
                                <select id="position" name="position" class="form-select @error('position') is-invalid @enderror" required>
                                    <option value="">-- Chọn bộ phận trước --</option>
                                </select>
                                @error('position') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Ngày sinh</label>
                                <input type="text" onfocus="(this.type='date')" onblur="(this.value == '' ? this.type='text' : this.type='date')" placeholder="VD: 25/05/1990" name="date_of_birth" class="form-control" value="{{ old('date_of_birth', $user->date_of_birth?->format('Y-m-d')) }}" min="1950-01-01" max="{{ now()->subYears(18)->toDateString() }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Ngày tham gia</label>
                                <input type="text" onfocus="(this.type='date')" onblur="(this.value == '' ? this.type='text' : this.type='date')" placeholder="VD: {{ now()->format('d/m/Y') }}" name="joined_at" class="form-control" value="{{ old('joined_at', $user->joined_at?->format('Y-m-d') ?? now()->toDateString()) }}" min="{{ now()->subYears(10)->toDateString() }}" max="{{ now()->toDateString() }}" required>
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

@push('scripts')
<script>
    const DEPT_POSITION_MAP = @json(\App\Support\LogisticsOptions::departmentPositionMap());
    const OLD_POSITION = '{{ old('position', $user->position) }}';
    const OLD_DEPARTMENT = '{{ old('department', $user->department) }}';

    document.addEventListener('DOMContentLoaded', function () {
        const deptSelect = document.getElementById('department');
        const posSelect = document.getElementById('position');
        const emailInput = document.querySelector('input[name="email"]');
        const submitBtn = document.querySelector('button[type="submit"]');

        /** --- Cascading dropdown --- */
        function populatePositions(department, selectedPosition) {
            posSelect.innerHTML = '';
            const positions = DEPT_POSITION_MAP[department] || [];
            if (positions.length === 0) {
                posSelect.innerHTML = '<option value="">-- Chọn bộ phận trước --</option>';
                posSelect.disabled = true;
                return;
            }
            posSelect.disabled = false;
            posSelect.innerHTML = '<option value="">-- Chọn chức vụ --</option>';
            positions.forEach(function (pos) {
                const opt = document.createElement('option');
                opt.value = pos;
                opt.textContent = pos;
                if (pos === selectedPosition) { opt.selected = true; }
                posSelect.appendChild(opt);
            });
        }

        // Init on page load: restore existing user values (or old() after validation failure)
        if (OLD_DEPARTMENT) {
            populatePositions(OLD_DEPARTMENT, OLD_POSITION);
        } else {
            posSelect.disabled = true;
        }

        deptSelect.addEventListener('change', function () {
            populatePositions(this.value, '');
        });

        /** --- Email live validation --- */
        if (emailInput) {
            emailInput.addEventListener('input', function () {
                const pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                let errorDiv = document.getElementById('js-email-error');
                if (!errorDiv) {
                    errorDiv = document.createElement('div');
                    errorDiv.id = 'js-email-error';
                    errorDiv.className = 'invalid-feedback';
                    errorDiv.innerText = 'Email không đúng định dạng (Ví dụ: user@example.com)';
                    this.parentNode.appendChild(errorDiv);
                }
                const isValid = this.value.length === 0 || pattern.test(this.value);
                this.classList.toggle('is-invalid', !isValid);
                errorDiv.style.display = isValid ? 'none' : 'block';
                if (submitBtn) { submitBtn.disabled = !isValid; }
            });

            // Trigger on load in case editing mode dispatchEvent
            emailInput.dispatchEvent(new Event('input'));
        }
    });
</script>
@endpush
