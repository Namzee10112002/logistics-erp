@extends('layouts.app')

@section('title', 'Chi tiết Báo nợ ' . $debitNote->debit_note_number)

@section('content')
<div class="container-fluid py-4 no-print">
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h4 class="fw-bold text-navy">Giấy Báo Nợ: {{ $debitNote->debit_note_number }}</h4>
            <span class="badge {{ $debitNote->status === 'paid' ? 'bg-success' : 'bg-warning' }}">
                {{ $debitNote->status === 'paid' ? 'Đã thanh toán' : 'Chưa thanh toán' }}
            </span>
        </div>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-navy fw-bold">
                <i class="fa fa-print me-2"></i> IN BÁO NỢ / XUẤT PDF
            </button>
        </div>
    </div>
</div>

<!-- Invoice Style Content for Printing -->
<div class="card border-0 rounded-4 shadow-sm invoice-container p-5 mx-auto mb-5" style="max-width: 900px; background: white;">
    <div class="d-flex justify-content-between mb-5 border-bottom pb-4">
        <div>
            <h2 class="fw-bold text-navy mb-1"><i class="fa fa-truck-fast me-2"></i> NT LOGISTICS</h2>
            <p class="mb-0 small">Địa chỉ: 123 Đường Logistic, Quận 7, TP. HCM</p>
            <p class="mb-0 small">Hotline: 0909 123 456 | Email: billing@ntlogistics.vn</p>
        </div>
        <div class="text-end">
            <h3 class="fw-bold text-navy">GIẤY BÁO NỢ</h3>
            <p class="mb-0">Số: <strong>{{ $debitNote->debit_note_number }}</strong></p>
            <p class="mb-0">Ngày lập: {{ $debitNote->issued_at->format('d/m/Y') }}</p>
        </div>
    </div>

    <div class="row mb-5">
        <div class="col-6">
            <h6 class="text-muted text-uppercase small fw-bold">Khách hàng:</h6>
            <h5 class="fw-bold text-navy mb-1">{{ $debitNote->customer->company_name }}</h5>
            <p class="mb-0 small">Mã số thuế: {{ $debitNote->customer->tax_code ?? '---' }}</p>
            <p class="mb-0 small">Địa chỉ: {{ $debitNote->customer->address ?? '---' }}</p>
        </div>
        <div class="col-6 text-end">
            <h6 class="text-muted text-uppercase small fw-bold">Thông tin thanh toán:</h6>
            <p class="mb-1 fw-bold text-navy">Ngân hàng TMCP Ngoại Thương (VCB)</p>
            <p class="mb-0">STK: 0071000123456</p>
            <p class="mb-0">Chủ TK: CÔNG TY TNHH VẬN TẢI NT LOGISTICS</p>
        </div>
    </div>

    <table class="table table-bordered mb-4">
        <thead class="bg-light">
            <tr>
                <th class="text-center" style="width: 50px;">STT</th>
                <th>Nội dung dịch vụ / Mã lô hàng</th>
                <th class="text-end">Thành tiền (VNĐ)</th>
            </tr>
        </thead>
        <tbody>
            @php $total = 0; @endphp
            @foreach($debitNote->shippingJobs as $index => $job)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>
                        <div class="fw-bold text-navy">{{ $job->job_code }}</div>
                        <div class="small text-muted">{{ $job->cargo_type }} - Tuyến: {{ $job->pickupLocation->location_name }} to {{ $job->deliveryLocation->location_name }}</div>
                    </td>
                    <td class="text-end fw-bold">{{ number_format($job->pivot->amount) }}</td>
                </tr>
                @php $total += $job->pivot->amount; @endphp
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2" class="text-end fw-bold text-uppercase">Tổng cộng tiền dịch vụ:</td>
                <td class="text-end fw-bold fs-5 text-navy">{{ number_format($total) }}</td>
            </tr>
            @if($debitNote->tax_amount > 0)
            <tr>
                <td colspan="2" class="text-end fw-bold text-uppercase">VAT ({{ $debitNote->tax_percent }}%):</td>
                <td class="text-end fw-bold">{{ number_format($debitNote->tax_amount) }}</td>
            </tr>
            @endif
            <tr class="table-navy">
                <td colspan="2" class="text-end fw-bold text-uppercase fs-5">TỔNG CỘNG THANH TOÁN:</td>
                <td class="text-end fw-bold fs-4 text-danger">{{ number_format($debitNote->grand_total) }}</td>
            </tr>
        </tfoot>
    </table>

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
