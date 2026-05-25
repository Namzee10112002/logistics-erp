@extends('layouts.app')

@section('title', 'Chi tiết Báo nợ ' . $debitNote->note_number)

@section('content')
@php
    $shippingJob = $debitNote->shippingJob;
    $approvedExpenses = $shippingJob->expenses->where('status', 'approved');
    $totalPaid = $debitNote->payments->sum('amount_paid');
    $remainingAmount = max($debitNote->grand_total - $totalPaid, 0);
@endphp

<div class="container-fluid py-4 no-print">
    <div class="mb-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <a href="{{ route('shipping-jobs.show', $shippingJob) }}" class="text-navy text-decoration-none small fw-bold">
                <i class="fa fa-arrow-left me-1"></i> Quay lại đơn hàng
            </a>
            <h4 class="fw-bold text-navy mt-2 mb-1">Giấy Báo Nợ: {{ $debitNote->note_number }}</h4>
            @php
                $statusBadge = match($debitNote->status) {
                    'paid' => ['bg-success', 'Đã tất toán'],
                    'partial' => ['bg-warning text-dark', 'Đã thanh toán một phần'],
                    default => ['bg-danger', 'Chưa thu tiền']
                };
            @endphp
            <span class="badge {{ $statusBadge[0] }}">{{ $statusBadge[1] }}</span>
        </div>
        <button onclick="window.print()" class="btn btn-navy fw-bold">
            <i class="fa fa-print me-2"></i> IN BÁO NỢ / XUẤT PDF
        </button>
    </div>
</div>

<div class="card border-0 rounded-4 shadow-sm invoice-container p-5 mx-auto mb-5" style="max-width: 900px; background: white;">
    <div class="d-flex justify-content-between mb-5 border-bottom pb-4">
        <div>
            <h2 class="fw-bold text-navy mb-1"><i class="fa fa-truck-fast me-2"></i> NT LOGISTICS</h2>
            <p class="mb-0 small">Địa chỉ: 123 Đường Logistic, Quận 7, TP. HCM</p>
            <p class="mb-0 small">Hotline: 0909 123 456 | Email: billing@ntlogistics.vn</p>
        </div>
        <div class="text-end">
            <h3 class="fw-bold text-navy">GIẤY BÁO NỢ</h3>
            <p class="mb-0">Số: <strong>{{ $debitNote->note_number }}</strong></p>
            <p class="mb-0">Ngày lập: {{ $debitNote->issued_at->format('d/m/Y') }}</p>
        </div>
    </div>

    <div class="row mb-5">
        <div class="col-md-6">
            <h6 class="text-muted text-uppercase small fw-bold">Khách hàng:</h6>
            <h5 class="fw-bold text-navy mb-1">{{ $debitNote->customer->company_name ?? $debitNote->customer->customer_name }}</h5>
            <p class="mb-0 small">Mã số thuế: {{ $debitNote->customer->tax_code ?? '---' }}</p>
            <p class="mb-0 small">Địa chỉ: {{ $debitNote->customer->address ?? '---' }}</p>
        </div>
        <div class="col-md-6 text-md-end mt-4 mt-md-0">
            <h6 class="text-muted text-uppercase small fw-bold">Thông tin đơn hàng:</h6>
            <p class="mb-0">Mã job: <strong>{{ $shippingJob->job_code }}</strong></p>
            <p class="mb-0">Container: {{ $shippingJob->container_number ?? '---' }}</p>
            <p class="mb-0">{{ $shippingJob->pickupLocation->location_name }} → {{ $shippingJob->deliveryLocation->location_name }}</p>
        </div>
    </div>

    <table class="table table-bordered mb-4">
        <thead class="bg-light">
            <tr>
                <th class="text-center" style="width: 50px;">STT</th>
                <th>Nội dung</th>
                <th class="text-end">Thành tiền (VNĐ)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center">1</td>
                <td>
                    <div class="fw-bold text-navy">Phí dịch vụ vận chuyển</div>
                    <div class="small text-muted">{{ $shippingJob->cargo_type }} - {{ $shippingJob->container_type ?? 'Hàng lẻ' }}</div>
                </td>
                <td class="text-end fw-bold">{{ number_format($debitNote->total_service_fee) }}</td>
            </tr>
            @foreach($approvedExpenses as $expense)
                <tr>
                    <td class="text-center">{{ $loop->iteration + 1 }}</td>
                    <td>
                        <div class="fw-bold">{{ $expense->expense_type }}</div>
                        <div class="small text-muted">{{ $expense->note ?? 'Chi hộ phát sinh' }}</div>
                    </td>
                    <td class="text-end fw-bold">{{ number_format($expense->amount) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2" class="text-end fw-bold text-uppercase">Tổng phí dịch vụ:</td>
                <td class="text-end fw-bold">{{ number_format($debitNote->total_service_fee) }}</td>
            </tr>
            <tr>
                <td colspan="2" class="text-end fw-bold text-uppercase">Tổng chi hộ:</td>
                <td class="text-end fw-bold">{{ number_format($debitNote->total_expense_paid) }}</td>
            </tr>
            <tr class="table-navy">
                <td colspan="2" class="text-end fw-bold text-uppercase fs-5">Tổng cộng thanh toán:</td>
                <td class="text-end fw-bold fs-4 text-danger">{{ number_format($debitNote->grand_total) }}</td>
            </tr>
            <tr>
                <td colspan="2" class="text-end fw-bold text-uppercase">Đã thanh toán:</td>
                <td class="text-end fw-bold text-success">{{ number_format($totalPaid) }}</td>
            </tr>
            <tr>
                <td colspan="2" class="text-end fw-bold text-uppercase">Còn phải thu:</td>
                <td class="text-end fw-bold text-navy">{{ number_format($remainingAmount) }}</td>
            </tr>
        </tfoot>
    </table>

    @if($debitNote->payments->isNotEmpty())
        <div class="mb-5">
            <h6 class="fw-bold text-navy mb-3">Lịch sử thanh toán</h6>
            <table class="table table-sm align-middle">
                <thead class="bg-light">
                    <tr class="small text-muted">
                        <th>Ngày thu</th>
                        <th>Phương thức</th>
                        <th>Chứng từ</th>
                        <th class="text-end">Số tiền</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($debitNote->payments as $payment)
                        <tr>
                            <td>{{ $payment->payment_date->format('d/m/Y') }}</td>
                            <td>{{ $payment->payment_method }}</td>
                            <td>{{ $payment->reference_no ?? '---' }}</td>
                            <td class="text-end fw-bold">{{ number_format($payment->amount_paid) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <div class="row mt-5 pt-4">
        <div class="col-6 text-center">
            <h6 class="fw-bold text-navy mb-5 pb-3">ĐẠI DIỆN KHÁCH HÀNG</h6>
            <p class="text-muted small">(Ký và ghi rõ họ tên)</p>
        </div>
        <div class="col-6 text-center">
            <h6 class="fw-bold text-navy mb-5 pb-3">NGƯỜI LẬP BIỂU</h6>
            <p class="text-navy fw-bold mb-0">Kế toán trưởng</p>
            <p class="text-muted small">(Ký và đóng dấu)</p>
        </div>
    </div>
</div>

<style>
    @media print {
        .sidebar, .top-navbar, .no-print, .btn, footer { display: none !important; }
        .main-wrapper { margin-left: 0 !important; padding: 0 !important; }
        .invoice-container { border: none !important; box-shadow: none !important; width: 100% !important; max-width: none !important; margin-bottom: 0 !important; }
        body { background: white !important; }
    }
    .table-navy { background-color: #f8f9fa; }
</style>
@endsection
