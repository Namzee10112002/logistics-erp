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
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Bộ phận / Phòng ban</label>
                                <select id="department" name="department" class="form-select @error('department') is-invalid @enderror" required>
                                    <option value="">-- Chọn bộ phận --</option>
                                    @foreach($departments as $value => $label)
                                        <option value="{{ $value }}" {{ old('department') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('department') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Chức vụ</label>
                                <select id="position" name="position" class="form-select @error('position') is-invalid @enderror" required>
                                    <option value="">-- Chọn bộ phận trước --</option>
                                </select>
                                @error('position') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Ngày sinh</label>
                                <input type="text" onfocus="(this.type='date')" onblur="(this.value == '' ? this.type='text' : this.type='date')" placeholder="VD: 25/05/1990" name="date_of_birth" class="form-control" value="{{ old('date_of_birth') }}" min="1950-01-01" max="{{ now()->subYears(18)->toDateString() }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Ngày tham gia</label>
                                <input type="text" onfocus="(this.type='date')" onblur="(this.value == '' ? this.type='text' : this.type='date')" placeholder="VD: {{ now()->format('d/m/Y') }}" name="joined_at" class="form-control" value="{{ old('joined_at', now()->toDateString()) }}" min="{{ now()->subYears(10)->toDateString() }}" max="{{ now()->toDateString() }}" required>
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
                                <div class="input-group">
                                    <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                                    <button class="btn btn-outline-secondary toggle-password" type="button" tabindex="-1">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                </div>
                                <div id="password-requirements" class="mt-2 small d-none">
                                    <div class="text-muted mb-1 fw-semibold">Yêu cầu mật khẩu:</div>
                                    <ul class="list-unstyled mb-0" style="font-size: 0.8rem;">
                                        <li id="req_length" class="text-danger"><i class="fa fa-times-circle me-1"></i> Tối thiểu 8 ký tự</li>
                                        <li id="req_lower" class="text-danger"><i class="fa fa-times-circle me-1"></i> Có chữ thường (a-z)</li>
                                        <li id="req_upper" class="text-danger"><i class="fa fa-times-circle me-1"></i> Có chữ hoa (A-Z)</li>
                                        <li id="req_number" class="text-danger"><i class="fa fa-times-circle me-1"></i> Có số (0-9)</li>
                                        <li id="req_special" class="text-danger"><i class="fa fa-times-circle me-1"></i> Có ký tự đặc biệt</li>
                                    </ul>
                                </div>
                                @error('password') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Xác nhận mật khẩu</label>
                                <div class="input-group">
                                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required>
                                    <button class="btn btn-outline-secondary toggle-password" type="button" tabindex="-1">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                </div>
                                <div id="password-match-msg" class="form-text mt-2" style="display: none;"></div>
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

@push('scripts')
<script>
    const DEPT_POSITION_MAP = @json(\App\Support\LogisticsOptions::departmentPositionMap());
    const OLD_POSITION = '{{ old('position') }}';
    const OLD_DEPARTMENT = '{{ old('department') }}';

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

        // Init on page load (restore old() values after validation failure)
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
                checkFormValidity();
            });
        }

        /** --- Password Real-time Validation & Toggle --- */
        const pwdInput = document.getElementById('password');
        const pwdConfInput = document.getElementById('password_confirmation');
        const reqsBox = document.getElementById('password-requirements');
        
        const reqs = {
            length: { el: document.getElementById('req_length'), regex: /.{8,}/ },
            lower: { el: document.getElementById('req_lower'), regex: /[a-z]/ },
            upper: { el: document.getElementById('req_upper'), regex: /[A-Z]/ },
            number: { el: document.getElementById('req_number'), regex: /[0-9]/ },
            special: { el: document.getElementById('req_special'), regex: /[^A-Za-z0-9]/ }
        };

        // Toggle password visibility
        document.querySelectorAll('.toggle-password').forEach(btn => {
            btn.addEventListener('click', function() {
                const input = this.previousElementSibling;
                const icon = this.querySelector('i');
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.replace('fa-eye', 'fa-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.replace('fa-eye-slash', 'fa-eye');
                }
            });
        });

        function updateRequirement(reqKey, val) {
            const item = reqs[reqKey];
            if (item.regex.test(val)) {
                item.el.classList.add('d-none');
            } else {
                item.el.classList.remove('d-none');
            }
        }

        if (pwdInput) {
            pwdInput.addEventListener('input', function() {
                const val = this.value;
                if (val.length === 0) {
                    reqsBox.classList.add('d-none');
                    return;
                }
                
                reqsBox.classList.remove('d-none');
                
                let allMet = true;
                Object.keys(reqs).forEach(key => {
                    updateRequirement(key, val);
                    if (!reqs[key].regex.test(val)) allMet = false;
                });

                if (allMet) {
                    reqsBox.classList.add('d-none');
                }

                // Re-trigger confirmation check if there's already some value
                if (pwdConfInput.value.length > 0) {
                    pwdConfInput.dispatchEvent(new Event('input'));
                }
            });
        }

        if (pwdConfInput) {
            pwdConfInput.addEventListener('input', function() {
                const val = this.value;
                const pwdVal = pwdInput.value;
                
                if (val.length === 0) {
                    pwdMatchMsg.style.display = 'none';
                    pwdConfInput.classList.remove('is-invalid', 'is-valid');
                    isMatchValid = false;
                    return;
                }

                pwdMatchMsg.style.display = 'block';
                if (val === pwdVal) {
                    pwdMatchMsg.className = 'form-text text-success fw-bold';
                    pwdMatchMsg.innerHTML = '<i class="fa fa-check-circle me-1"></i> Mật khẩu khớp';
                    pwdConfInput.classList.remove('is-invalid');
                    pwdConfInput.classList.add('is-valid');
                    isMatchValid = true;
                } else {
                    pwdMatchMsg.className = 'form-text text-danger';
                    pwdMatchMsg.innerHTML = '<i class="fa fa-times-circle me-1"></i> Mật khẩu không khớp';
                    pwdConfInput.classList.remove('is-valid');
                    pwdConfInput.classList.add('is-invalid');
                    isMatchValid = false;
                }
                checkFormValidity();
            });
        }

    });
</script>
@endpush
