@extends('layouts.app')

@section('title', 'Thông tin công ty - NT Logistics')

@section('content')
@php
    $companySettings = $settings->get('company', collect())->keyBy('key');
    $companyName = $companySettings->get('company.name')?->value ?? 'Công Ty TNHH TMDV GNVT NGUYÊN TÂM';
    $companyAddress = $companySettings->get('company.address')?->value ?? 'Hải Phòng';
    $companyPhone = $companySettings->get('company.phone')?->value ?? '0900000000';
    $companyEmail = $companySettings->get('company.email')?->value ?? 'contact@nguyentam-logistics.example';
    $companyContact = $companySettings->get('company.contact_person')?->value ?? 'Bộ phận điều vận';
@endphp

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h4 class="fw-bold text-navy">Thông tin công ty</h4>
            <p class="text-muted small">Quản lý hồ sơ doanh nghiệp, địa chỉ, người liên hệ và thông tin xuất báo cáo.</p>
        </div>
    </div>

    <div class="card border-0 rounded-4 shadow-sm overflow-hidden mb-4">
        <div class="row g-0">
            <div class="col-lg-8 p-4 text-white" style="background: #1a237e;">
                <div class="d-flex flex-wrap gap-2 mb-4">
                    <span class="badge rounded-pill bg-info text-dark px-3 py-2">Hồ sơ doanh nghiệp</span>
                    <span class="badge rounded-pill bg-success px-3 py-2">Thông tin xuất báo cáo</span>
                </div>
                <h3 class="fw-bold mb-3">{{ $companyName }}</h3>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 h-100" style="background: rgba(255,255,255,0.10);">
                            <div class="small opacity-75 mb-1">Địa chỉ</div>
                            <div class="fw-semibold">{{ $companyAddress }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 h-100" style="background: rgba(255,255,255,0.10);">
                            <div class="small opacity-75 mb-1">Người liên hệ</div>
                            <div class="fw-semibold">{{ $companyContact }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 h-100" style="background: rgba(255,255,255,0.10);">
                            <div class="small opacity-75 mb-1">Số điện thoại</div>
                            <div class="fw-semibold">{{ $companyPhone }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 h-100" style="background: rgba(255,255,255,0.10);">
                            <div class="small opacity-75 mb-1">Email</div>
                            <div class="fw-semibold">{{ $companyEmail }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 p-4 d-flex flex-column justify-content-center" style="background: #e0f7fa;">
                <div class="d-flex align-items-center gap-4">
                    <div class="bg-white rounded-4 shadow-sm p-3 flex-shrink-0">
                        <img src="{{ asset('img/company-logo.jpg') }}" alt="Logo công ty" style="width: 92px; height: 92px; object-fit: contain;">
                    </div>
                    <div>
                        <div class="small text-muted text-uppercase fw-bold mb-2">Logo đang dùng</div>
                        <div class="fw-bold text-navy">Tách riêng khỏi tên công ty trong mẫu xuất file</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Settings Form -->
        <div class="col-lg-8">
            <form action="{{ route('settings.update') }}" method="POST">
                @csrf
                @method('POST')
                
                @foreach($settings as $group => $items)
                    <div class="card border-0 rounded-4 shadow-sm mb-4">
                        <div class="card-header bg-white border-0 p-4 pb-0">
                            <h6 class="fw-bold text-navy text-uppercase mb-0">
                                {{ $group == 'general' ? 'Thông tin chung' : ($group == 'finance' ? 'Tham số Tài chính' : $group) }}
                            </h6>
                        </div>
                        <div class="card-body p-4">
                            @foreach($items as $setting)
                                <div class="mb-4">
                                    <label class="form-label fw-bold small text-muted d-block">{{ $setting->description }}</label>
                                    <input type="text" name="settings[{{ $setting->key }}]" class="form-control" value="{{ $setting->value }}">
                                    <span class="small opacity-50" style="font-size: 0.7rem;">Mã key: {{ $setting->key }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach

                <div class="card border-0 rounded-4 shadow-sm bg-navy text-white mb-4">
                    <div class="card-body p-4 d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="fw-bold mb-1">Lưu thay đổi</h6>
                            <p class="small mb-0 opacity-75">Tất cả thay đổi sẽ được áp dụng ngay lập tức trên toàn hệ thống.</p>
                        </div>
                        <button type="submit" class="btn btn-light fw-bold px-4">LƯU THÔNG TIN</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Sidebar Actions: Backup -->
        <div class="col-lg-4">
            <div class="card border-0 rounded-4 shadow-sm mb-4">
                <div class="card-body p-4 text-center">
                    <div class="p-3 bg-primary bg-opacity-10 text-primary rounded-circle d-inline-block mb-3">
                        <i class="fa fa-database fs-3"></i>
                    </div>
                    <h6 class="fw-bold text-navy">Sao lưu Dữ liệu</h6>
                    <p class="small text-muted mb-4">Tải xuống toàn bộ danh sách đơn hàng và dữ liệu vận hành hiện tại dưới dạng tệp CSV để lưu trữ dự phòng.</p>
                    <a href="{{ route('settings.backup') }}" class="btn btn-navy w-100 fw-bold py-2">
                        <i class="fa fa-download me-2"></i> TẢI BẢN SAO LƯU (.CSV)
                    </a>
                </div>
            </div>

            <div class="card border-0 rounded-4 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-navy mb-3">Bản đồ công ty</h6>
                    <div class="ratio ratio-4x3 rounded-3 overflow-hidden border">
                        <iframe src="https://maps.google.com/maps?q=Hai%20Phong%20Vietnam&t=&z=11&ie=UTF8&iwloc=&output=embed" loading="lazy"></iframe>
                    </div>
                </div>
            </div>

            <div class="card border-0 rounded-4 shadow-sm border-start border-warning border-4">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-warning mb-2">
                        <i class="fa fa-exclamation-triangle me-1"></i> Lưu ý Bảo mật
                    </h6>
                    <p class="small text-muted mb-0">
                        Chỉ tài khoản cấp **Giám đốc (GĐ)** mới có quyền thay đổi các thông số tài chính và thực hiện sao lưu. Mọi hành động đều được lưu vết trong nhật ký hệ thống.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
