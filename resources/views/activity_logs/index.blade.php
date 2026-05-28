@extends('layouts.app')

@section('title', 'Nhật Ký Hoạt Động - NT Logistics')

@section('content')

{{-- start hover blue navy
<style>
    #activity-log-table tbody tr:hover td {
        background-color: rgb(0, 0, 128) !important;
        color: #fff !important;
    }
    #activity-log-table tbody tr:hover .text-navy,
    #activity-log-table tbody tr:hover .text-muted,
    #activity-log-table tbody tr:hover .fw-semibold,
    #activity-log-table tbody tr:hover .fw-bold,
    #activity-log-table tbody tr:hover .small,
    #activity-log-table tbody tr:hover .font-monospace {
        color: rgba(255,255,255,0.85) !important;
    }
    #activity-log-table tbody tr:hover .badge {
        background-color: rgba(255,255,255,0.2) !important;
        color: #fff !important;
        border-color: rgba(255,255,255,0.4) !important;
    }
    #activity-log-table tbody tr:hover .bg-navy {
        background-color: rgba(255,255,255,0.2) !important;
    }
</style>
end hover blue navy --}}
<div class="container-fluid py-4">

    {{-- Tiêu đề --}}
    <div class="row mb-4 align-items-center">
        <div class="col">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('settings.index') }}#tab-phanquyen" class="btn btn-outline-secondary rounded-circle" style="width: 40px; height: 40px; display: inline-flex; align-items: center; justify-content: center;">
                    <i class="fa fa-arrow-left"></i>
                </a>
                <div>
                    <h4 class="fw-bold text-navy mb-0">Nhật Ký Hoạt Động</h4>
                    <p class="text-muted small mb-0">Lưu vết các thao tác cấu hình hệ thống và quản trị viên</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Bảng dữ liệu --}}
    <div class="card border-0 rounded-4 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
{{-- start table default --}}
                <table class="table table-hover align-middle mb-0">
{{-- end table default --}}
{{-- start table hover style
                <table class="table table-hover align-middle mb-0" id="activity-log-table">
end table hover style --}}
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4 border-0">Thời gian</th>
                            <th class="border-0">Tài khoản thao tác</th>
                            <th class="border-0">Mã hành động</th>
                            <th class="border-0">Chi tiết</th>
                            <th class="pe-4 border-0">IP / Thiết bị</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td class="ps-4 text-nowrap">
                                    <div class="fw-bold text-navy">{{ $log->created_at->format('d/m/Y') }}</div>
                                    <div class="small text-muted">{{ $log->created_at->format('H:i:s') }}</div>
                                </td>
                                <td>
                                    @if($log->user)
                                        <div class="d-flex align-items-center gap-2">
{{-- start avatar default --}}
                                            <div class="bg-navy bg-opacity-10 text-navy rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                                {{ strtoupper(substr($log->user->name, 0, 1)) }}
                                            </div>
{{-- end avatar default --}}
{{-- start avatar icon
                                            <div class="bg-navy bg-opacity-10 text-navy rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 0.9rem;">
                                                <i class="fa fa-user"></i>
                                            </div>
end avatar icon --}}
                                            <div>
                                                <div class="fw-semibold">{{ $log->user->name }}</div>
                                                <div class="small text-muted">{{ $log->user->email }}</div>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-muted fst-italic">Hệ thống / Không xác định</span>
                                    @endif
                                </td>
                                <td>
{{-- start action badge default --}}
                                    <span class="badge bg-light text-navy border">{{ $log->action }}</span>
{{-- end action badge default --}}
{{-- start action badge styled
                                    @php
                                        $actionMap = [
                                            'update_settings'        => ['icon' => 'fa-sliders',        'bg' => 'bg-primary',   'label' => 'Cập nhật cài đặt'],
                                            'backup_database'        => ['icon' => 'fa-database',       'bg' => 'bg-success',   'label' => 'Sao lưu CSDL'],
                                            'backup_database_failed' => ['icon' => 'fa-exclamation-triangle', 'bg' => 'bg-danger', 'label' => 'Sao lưu thất bại'],
                                            'restore_database'       => ['icon' => 'fa-rotate-left',    'bg' => 'bg-warning',   'label' => 'Khôi phục CSDL'],
                                            'restore_database_failed'=> ['icon' => 'fa-times-circle',   'bg' => 'bg-danger',    'label' => 'Khôi phục thất bại'],
                                            'upload_asset'           => ['icon' => 'fa-upload',         'bg' => 'bg-info',      'label' => 'Tải lên tài sản'],
                                        ];
                                        $meta = $actionMap[$log->action] ?? ['icon' => 'fa-circle-dot', 'bg' => 'bg-secondary', 'label' => $log->action];
                                    @endphp
                                    <span class="badge {{ $meta['bg'] }} d-inline-flex align-items-center gap-1 px-2 py-1" style="font-size:.78rem;">
                                        <i class="fa {{ $meta['icon'] }}"></i>
                                        {{ $meta['label'] }}
                                    </span>
end action badge styled --}}
                                </td>
                                <td>
                                    <div class="text-wrap" style="max-width: 300px;">
                                        {{ $log->description }}
                                    </div>
                                </td>
                                <td class="pe-4">
                                    <div class="small text-muted font-monospace">{{ $log->ip_address ?? 'N/A' }}</div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="text-muted mb-2"><i class="fa fa-folder-open fs-1 text-light"></i></div>
                                    <p class="mb-0">Chưa có nhật ký hoạt động nào.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            {{-- Phân trang --}}
            @if($logs->hasPages())
                <div class="card-footer bg-white border-top p-3">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
    </div>

</div>
@endsection
