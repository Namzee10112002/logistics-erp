@extends('layouts.app')

@section('title', 'Cài đặt - NT Logistics')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h4 class="fw-bold text-navy">Cài đặt</h4>
            <p class="text-muted small">Quản lý thông tin công ty, tham số vận hành và bảo mật dữ liệu.</p>
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
                        <button type="submit" class="btn btn-light fw-bold px-4">LƯU CÀI ĐẶT</button>
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
