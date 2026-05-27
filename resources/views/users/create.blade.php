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
        <div class="col-lg-8">
            <div class="card border-0 rounded-4 shadow-sm">
                <div class="card-body p-4">
                    <form action="{{ route('users.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-bold">Họ tên nhân viên <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" data-validate="person-name" data-label="Họ tên nhân viên" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Email đăng nhập <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" data-validate data-label="Email đăng nhập" required>
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Chức vụ <span class="text-danger">*</span></label>
                                <select name="position" class="form-select" required>
                                    <option value="">Chọn chức vụ</option>
                                    @foreach($positions as $value => $label)
                                        <option value="{{ $value }}" {{ old('position') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Bộ phận / phòng ban <span class="text-danger">*</span></label>
                                <select name="department" class="form-select" required>
                                    <option value="">Chọn bộ phận</option>
                                    @foreach($departments as $value => $label)
                                        <option value="{{ $value }}" {{ old('department') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Ngày sinh <span class="text-danger">*</span></label>
                                <input type="text" name="date_of_birth" class="form-control" value="{{ \App\Support\VietnameseDate::display(old('date_of_birth')) }}" placeholder="Ngày/Tháng/Năm" data-date-input data-label="Ngày sinh" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Ngày tham gia <span class="text-danger">*</span></label>
                                <input type="text" name="joined_at" class="form-control" value="{{ \App\Support\VietnameseDate::display(old('joined_at', now()->toDateString())) }}" placeholder="Ngày/Tháng/Năm" data-date-input data-label="Ngày tham gia" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Vai trò hệ thống <span class="text-danger">*</span></label>
                            <select name="role_id" id="role_id" class="form-select @error('role_id') is-invalid @enderror" data-role-select required>
                                <option value="">Chọn vai trò...</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}" data-role-code="{{ $role->role_code }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                        {{ $role->role_name }} ({{ $role->role_code }})
                                    </option>
                                @endforeach
                            </select>
                            @error('role_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="border rounded-3 p-3 mb-3 d-none" data-profile-section="DRIVER">
                            <div class="fw-bold text-navy mb-3">Thông tin profile tài xế</div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Số điện thoại <span class="text-danger">*</span></label>
                                    <input type="text" name="driver_phone" class="form-control @error('driver_phone') is-invalid @enderror" value="{{ old('driver_phone') }}" maxlength="10" inputmode="numeric" data-validate="phone-vn" data-label="Số điện thoại tài xế" data-profile-required>
                                    @error('driver_phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Số bằng lái <span class="text-danger">*</span></label>
                                    <input type="text" name="driver_license_number" class="form-control @error('driver_license_number') is-invalid @enderror" value="{{ old('driver_license_number') }}" data-validate data-label="Số bằng lái" data-profile-required>
                                    @error('driver_license_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Ngày bắt đầu</label>
                                    <input type="text" name="driver_start_date" class="form-control @error('driver_start_date') is-invalid @enderror" value="{{ \App\Support\VietnameseDate::display(old('driver_start_date', old('joined_at', now()->toDateString()))) }}" placeholder="Ngày/Tháng/Năm" data-date-input data-label="Ngày bắt đầu tài xế">
                                    @error('driver_start_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Cấp bậc</label>
                                    <select name="driver_rank" class="form-select @error('driver_rank') is-invalid @enderror">
                                        <option value="">Chọn cấp bậc</option>
                                        @foreach(\App\Support\LogisticsOptions::driverRanks() as $value => $label)
                                            <option value="{{ $value }}" {{ old('driver_rank') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('driver_rank') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Hạn hợp đồng</label>
                                    <input type="text" name="driver_contract_expiry" class="form-control @error('driver_contract_expiry') is-invalid @enderror" value="{{ \App\Support\VietnameseDate::display(old('driver_contract_expiry')) }}" placeholder="Ngày/Tháng/Năm" data-date-input data-label="Hạn hợp đồng tài xế">
                                    @error('driver_contract_expiry') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Ghi chú</label>
                                    <textarea name="driver_note" class="form-control" rows="2">{{ old('driver_note') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="border rounded-3 p-3 mb-3 d-none" data-profile-section="FIELD">
                            <div class="fw-bold text-navy mb-3">Thông tin profile nhân viên hiện trường</div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Số điện thoại <span class="text-danger">*</span></label>
                                    <input type="text" name="field_phone" class="form-control @error('field_phone') is-invalid @enderror" value="{{ old('field_phone') }}" maxlength="10" inputmode="numeric" data-validate="phone-vn" data-label="Số điện thoại nhân viên hiện trường" data-profile-required>
                                    @error('field_phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Ngày bắt đầu</label>
                                    <input type="text" name="field_start_date" class="form-control @error('field_start_date') is-invalid @enderror" value="{{ \App\Support\VietnameseDate::display(old('field_start_date', old('joined_at', now()->toDateString()))) }}" placeholder="Ngày/Tháng/Năm" data-date-input data-label="Ngày bắt đầu nhân viên hiện trường">
                                    @error('field_start_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Khu vực phụ trách <span class="text-danger">*</span></label>
                                    <select name="field_responsible_location_ids[]" class="form-select @error('field_responsible_location_ids') is-invalid @enderror" multiple data-profile-required>
                                        @foreach($responsibleLocations as $location)
                                            <option value="{{ $location->id }}" {{ in_array($location->id, old('field_responsible_location_ids', [])) ? 'selected' : '' }}>
                                                {{ $location->location_name }} - {{ $location->province }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('field_responsible_location_ids') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    @error('field_responsible_location_ids.*') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Chứng chỉ</label>
                                    <textarea name="field_certificates" class="form-control" rows="2">{{ old('field_certificates') }}</textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Ghi chú</label>
                                    <textarea name="field_note" class="form-control" rows="2">{{ old('field_note') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Mật khẩu <span class="text-danger">*</span></label>
                                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" data-validate required>
                                <div class="form-text">Tối thiểu 8 ký tự, có chữ hoa, chữ thường, chữ số và ký tự đặc biệt.</div>
                                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Xác nhận mật khẩu <span class="text-danger">*</span></label>
                                <input type="password" name="password_confirmation" class="form-control" data-validate required>
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

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const roleSelect = document.querySelector('[data-role-select]');
        const profileSections = document.querySelectorAll('[data-profile-section]');

        const syncProfileSections = () => {
            const roleCode = roleSelect?.selectedOptions?.[0]?.dataset?.roleCode || '';

            profileSections.forEach((section) => {
                const isActive = section.dataset.profileSection === roleCode;
                section.classList.toggle('d-none', !isActive);
                section.querySelectorAll('[data-profile-required]').forEach((field) => {
                    if (isActive) {
                        field.setAttribute('required', 'required');
                    } else {
                        field.removeAttribute('required');
                    }
                });
            });
        };

        roleSelect?.addEventListener('change', syncProfileSections);
        syncProfileSections();
    });
</script>
@endpush
@endsection
