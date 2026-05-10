@extends('layouts.app')

@section('title', 'Quản lý Đơn hàng')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <h4 class="fw-bold mb-0">Danh sách Đơn hàng (Jobs)</h4>
    @if(Auth::user()->hasRole(['ADMIN', 'SALES']))
    <a href="{{ route('shipping-jobs.create') }}" class="btn btn-navy px-4 fw-bold">
        <i class="fa fa-plus me-2"></i> TẠO ĐƠN HÀNG MỚI
    </a>
    @endif
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<!-- Filters -->
<div class="card border-0 rounded-4 shadow-sm p-4 mb-4">
    <form action="{{ route('shipping-jobs.index') }}" method="GET" class="row g-3">
        <div class="col-md-7">
            <input type="text" name="search" class="form-control border-light" placeholder="Tìm theo Mã Job, Số Cont, Tên khách hàng..." value="{{ request('search') }}">
        </div>
        <div class="col-md-3">
            <select name="status" class="form-select border-light">
                <option value="">-- Tất cả trạng thái --</option>
                <option value="new" {{ request('status') == 'new' ? 'selected' : '' }}>Mới tạo</option>
                <option value="dispatched" {{ request('status') == 'dispatched' ? 'selected' : '' }}>Đã điều xe</option>
                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Hoàn thành</option>
                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-navy w-100">Lọc</button>
        </div>
    </form>
</div>

<!-- Data Table -->
<div class="card border-0 rounded-4 shadow-sm overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light">
                <tr class="small text-muted text-uppercase">
                    <th class="ps-4">Mã Job / Ngày tạo</th>
                    <th>Khách Hàng</th>
                    <th>Tuyến Đường (Bốc -> Dỡ)</th>
                    <th>Container / Loại hàng</th>
                    <th>Trạng Thái</th>
                    <th class="text-center">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($shippingJobs as $job)
                    <tr>
                        <td class="ps-4">
                            <div class="fw-bold text-navy">{{ $job->job_code }}</div>
                            <div class="small text-muted">{{ $job->created_at->format('d/m/Y H:i') }}</div>
                        </td>
                        <td>
                            <div class="fw-bold text-truncate" style="max-width: 200px;">{{ $job->customer->customer_name }}</div>
                            <div class="small text-muted">{{ $job->customer->customer_code }}</div>
                        </td>
                        <td>
                            <div class="small">
                                <span class="badge bg-light text-dark fw-normal border">{{ $job->pickupLocation->location_name }}</span>
                                <i class="fa fa-arrow-right mx-1 text-muted small"></i>
                                <span class="badge bg-light text-dark fw-normal border">{{ $job->deliveryLocation->location_name }}</span>
                            </div>
                        </td>
                        <td>
                            <div class="fw-bold">{{ $job->container_number ?? 'N/A' }}</div>
                            <div class="small text-muted">{{ $job->cargo_type }} ({{ $job->container_type ?? 'Lẻ' }})</div>
                        </td>
                        <td>
                            @php
                                $statusClass = match($job->status) {
                                    'new' => 'bg-info text-dark',
                                    'processing' => 'bg-primary',
                                    'dispatched' => 'bg-warning text-dark',
                                    'completed' => 'bg-success',
                                    'cancelled' => 'bg-secondary',
                                    default => 'bg-light text-dark'
                                };
                                $statusName = match($job->status) {
                                    'new' => 'Mới tạo',
                                    'processing' => 'Đang xử lý',
                                    'dispatched' => 'Đã điều xe',
                                    'completed' => 'Hoàn thành',
                                    'cancelled' => 'Đã hủy',
                                    default => 'Khác'
                                };
                            @endphp
                            <span class="badge {{ $statusClass }}">{{ $statusName }}</span>
                        </td>
                        <td class="text-center">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-light border-0" type="button" data-bs-toggle="dropdown">
                                    <i class="fa fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                    <li><a class="dropdown-item" href="{{ route('shipping-jobs.show', $job->id) }}"><i class="fa fa-eye me-2 text-info"></i> Chi tiết</a></li>
                                    @if(Auth::user()->hasRole(['ADMIN', 'SALES']))
                                    <li><a class="dropdown-item" href="{{ route('shipping-jobs.edit', $job->id) }}"><i class="fa fa-edit me-2 text-warning"></i> Chỉnh sửa</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item text-danger" href="javascript:void(0)" onclick="handleDelete('{{ $job->id }}', 'Xóa đơn hàng {{ $job->job_code }}?')">
                                            <i class="fa fa-trash me-2"></i> Xóa
                                        </a>
                                    </li>
                                    @endif
                                </ul>
                            </div>
                            <form id="delete-form-{{ $job->id }}" action="{{ route('shipping-jobs.destroy', $job->id) }}" method="POST" style="display: none;">
                                @csrf
                                @method('DELETE')
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">Không tìm thấy đơn hàng nào.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4 border-top">
        {{ $shippingJobs->appends(request()->query())->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection
