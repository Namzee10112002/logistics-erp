@extends('layouts.app')

@section('title', 'Chi tiết Lệnh điều xe ' . $dispatchOrder->order_number)

@section('content')
<div class="mb-4 d-flex justify-content-between align-items-center">
    <div>
        <a href="{{ route('dispatch-orders.index') }}" class="text-navy text-decoration-none small fw-bold">
            <i class="fa fa-arrow-left me-1"></i> Quay lại danh sách
        </a>
        <h4 class="fw-bold mt-2">Lệnh Điều Xe: {{ $dispatchOrder->order_number }}</h4>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-navy fw-bold px-4">
            <i class="fa fa-print me-2"></i> IN PHIẾU ĐIỀU XE
        </button>
    </div>
</div>

<div class="row g-4">
    <!-- Main Info -->
    <div class="col-lg-8">
        <div class="card border-0 rounded-4 shadow-sm mb-4">
            <div class="card-header bg-white border-0 p-4 pb-0">
                <h5 class="fw-bold text-navy mb-0">Thông tin hành trình</h5>
            </div>
            <div class="card-body p-4">
                <div class="row g-4">
                    <div class="col-md-12">
                        <div class="p-3 bg-light rounded-3 d-flex justify-content-between align-items-center border">
                            <div class="text-center flex-grow-1">
                                <label class="small text-muted text-uppercase d-block mb-1">Điểm Đi</label>
                                <span class="fw-bold fs-6 text-navy">{{ $dispatchOrder->shippingJob->pickupLocation->location_name }}</span>
                                <div class="small text-muted">{{ $dispatchOrder->shippingJob->pickupLocation->address }}</div>
                            </div>
                            <div class="px-4">
                                <i class="fa fa-truck text-navy fs-3"></i>
                            </div>
                            <div class="text-center flex-grow-1">
                                <label class="small text-muted text-uppercase d-block mb-1">Điểm Đến</label>
                                <span class="fw-bold fs-6 text-navy">{{ $dispatchOrder->shippingJob->deliveryLocation->location_name }}</span>
                                <div class="small text-muted">{{ $dispatchOrder->shippingJob->deliveryLocation->address }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="small text-muted text-uppercase fw-bold">Tài Xế Thực Hiện</label>
                        <div class="d-flex align-items-center mt-2">
                            <div class="bg-navy text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px;">
                                <i class="fa fa-user"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-navy fs-5">{{ $dispatchOrder->driver->full_name }}</div>
                                <div class="small text-muted">GPLX: {{ $dispatchOrder->driver->license_number }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="small text-muted text-uppercase fw-bold">Phương Tiện</label>
                        <div class="d-flex align-items-center mt-2">
                            <div class="bg-navy text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px;">
                                <i class="fa fa-truck-moving"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-navy fs-5">{{ $dispatchOrder->vehicle->plate_number }}</div>
                                <div class="small text-muted">{{ $dispatchOrder->vehicle->vehicle_type }} - {{ $dispatchOrder->vehicle->payload }} Tấn</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 mt-4">
                        <label class="small text-muted text-uppercase fw-bold">Ghi chú từ điều vận</label>
                        <div class="p-3 bg-light rounded-3 italic text-muted border-start border-4 border-navy mt-2">
                            {{ $dispatchOrder->note ?? 'Không có ghi chú.' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tracking Timeline -->
        <div class="card border-0 rounded-4 shadow-sm">
            <div class="card-header bg-white border-0 p-4 pb-0">
                <h5 class="fw-bold text-navy mb-0">Nhật ký hành trình</h5>
            </div>
            <div class="card-body p-4">
                <div class="timeline-custom">
                    @forelse($dispatchOrder->trackingLogs()->with('updater')->orderBy('created_at', 'desc')->get() as $log)
                        <div class="timeline-item d-flex gap-3 mb-4">
                            <div class="timeline-marker text-primary pt-1">
                                <i class="fa fa-circle-dot"></i>
                            </div>
                            <div class="timeline-content">
                                <div class="fw-bold text-navy">
                                    @php
                                        $statusText = match($log->status_update) {
                                            'dispatched' => 'Đã điều xe (Lập lệnh)',
                                            'on_way' => 'Bắt đầu khởi hành',
                                            'completed' => 'Hoàn thành chuyến xe',
                                            default => $log->status_update
                                        };
                                    @endphp
                                    {{ $statusText }}
                                </div>
                                <div class="small text-muted">
                                    {{ $log->created_at->format('d/m/Y H:i:s') }} 
                                    <span class="mx-1">•</span> 
                                    Cập nhật bởi: {{ $log->updater->name }}
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted small">
                            <i class="fa fa-info-circle me-1"></i> Chưa có ghi nhận hành trình thực tế.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <style>
            .timeline-custom { position: relative; padding-left: 5px; }
            .timeline-custom::before {
                content: '';
                position: absolute;
                left: 10px;
                top: 0;
                bottom: 0;
                width: 2px;
                background: #e9ecef;
            }
            .timeline-marker { position: relative; z-index: 1; background: #fff; }
        </style>
    </div>

    <!-- Sidebar Info -->
    <div class="col-lg-4">
        <div class="card border-0 rounded-4 shadow-sm mb-4">
            <div class="card-body p-4">
                <h6 class="fw-bold text-navy mb-3">Liên kết đơn hàng</h6>
                <div class="p-3 rounded-3 border bg-light mb-3">
                    <label class="small text-muted d-block">Mã Job:</label>
                    <a href="{{ route('shipping-jobs.show', $dispatchOrder->shipping_job_id) }}" class="fw-bold text-navy text-decoration-none fs-5">
                        {{ $dispatchOrder->shippingJob->job_code }} <i class="fa fa-external-link small ms-1"></i>
                    </a>
                </div>
                <ul class="list-unstyled mb-0 small">
                    <li class="mb-2 d-flex justify-content-between">
                        <span class="text-muted">Khách hàng:</span>
                        <span class="fw-bold">{{ $dispatchOrder->shippingJob->customer->customer_name }}</span>
                    </li>
                    <li class="mb-2 d-flex justify-content-between">
                        <span class="text-muted">Loại hàng:</span>
                        <span class="fw-bold">{{ $dispatchOrder->shippingJob->cargo_type }}</span>
                    </li>
                    <li class="d-flex justify-content-between">
                        <span class="text-muted">Container:</span>
                        <span class="fw-bold">{{ $dispatchOrder->shippingJob->container_number ?? 'Lẻ' }}</span>
                    </li>
                </ul>
            </div>
        </div>

        <div class="card border-0 rounded-4 shadow-sm">
            <div class="card-body p-4">
                <h6 class="fw-bold text-navy mb-3">Thông tin lệnh</h6>
                <div class="mb-3">
                    <label class="small text-muted d-block">Trạng thái hiện tại:</label>
                    @php
                        $statusClass = match($dispatchOrder->dispatch_status) {
                            'dispatched' => 'bg-info text-dark',
                            'on_way' => 'bg-warning text-dark',
                            'completed' => 'bg-success',
                            default => 'bg-light text-dark'
                        };
                        $statusName = match($dispatchOrder->dispatch_status) {
                            'dispatched' => 'Đã điều xe',
                            'on_way' => 'Đang đi',
                            'completed' => 'Hoàn thành',
                            default => 'Khác'
                        };
                    @endphp
                    <span class="badge {{ $statusClass }} fs-6">{{ $statusName }}</span>
                </div>

                <!-- Update Status Buttons for Drivers -->
                <div class="mt-4 pt-3 border-top">
                    @if($dispatchOrder->dispatch_status === 'dispatched')
                        <form action="{{ route('dispatch-orders.update-status', $dispatchOrder) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="on_way">
                            <button type="submit" class="btn btn-warning w-100 fw-bold py-3 rounded-3 shadow-sm">
                                <i class="fa fa-play me-2"></i> BẮT ĐẦU CHUYẾN XE
                            </button>
                        </form>
                    @elseif($dispatchOrder->dispatch_status === 'on_way')
                        <form action="{{ route('dispatch-orders.update-status', $dispatchOrder) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="completed">
                            <button type="submit" class="btn btn-success w-100 fw-bold py-3 rounded-3 shadow-sm">
                                <i class="fa fa-check-circle me-2"></i> HOÀN THÀNH CHUYẾN
                            </button>
                        </form>
                    @else
                        <div class="text-center py-2 text-success fw-bold">
                            <i class="fa fa-circle-check"></i> Chuyến xe đã hoàn tất
                        </div>
                    @endif
                </div>

                <div class="mt-4">
                    <label class="small text-muted d-block">Thời gian bắt đầu:</label>
                    <span class="fw-bold">{{ $dispatchOrder->start_time ? \Carbon\Carbon::parse($dispatchOrder->start_time)->format('d/m/Y H:i') : '---' }}</span>
                </div>
                <div class="mb-3">
                    <label class="small text-muted d-block">Thời gian kết thúc:</label>
                    <span class="fw-bold">{{ $dispatchOrder->end_time ? \Carbon\Carbon::parse($dispatchOrder->end_time)->format('d/m/Y H:i') : '---' }}</span>
                </div>
                <div class="mb-3">
                    <label class="small text-muted d-block">Thời gian lập lệnh:</label>
                    <span class="fw-bold">{{ $dispatchOrder->created_at->format('d/m/Y H:i') }}</span>
                </div>
                <div>
                    <label class="small text-muted d-block">Người lập:</label>
                    <span class="fw-bold">{{ $dispatchOrder->creator->name }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
