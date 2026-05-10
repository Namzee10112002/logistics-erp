@extends('layouts.app')

@section('title', 'Báo cáo Tài chính - NT Logistics')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h4 class="fw-bold text-navy">Báo cáo Tài chính</h4>
            <p class="text-muted small">Phân tích doanh thu, chi phí và lợi nhuận hệ thống.</p>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 rounded-4 shadow-sm bg-primary text-white h-100">
                <div class="card-body p-4 text-center">
                    <h6 class="opacity-75 mb-2">Tổng Doanh thu</h6>
                    <h2 class="fw-bold mb-0">{{ number_format($totalRevenue) }}đ</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 rounded-4 shadow-sm bg-danger text-white h-100">
                <div class="card-body p-4 text-center">
                    <h6 class="opacity-75 mb-2">Tổng Chi phí (Đã duyệt)</h6>
                    <h2 class="fw-bold mb-0">{{ number_format($totalExpenses) }}đ</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 rounded-4 shadow-sm bg-success text-white h-100">
                <div class="card-body p-4 text-center">
                    <h6 class="opacity-75 mb-2">Lợi nhuận gộp</h6>
                    <h2 class="fw-bold mb-0">{{ number_format($profit) }}đ</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Chart: Monthly Revenue -->
        <div class="col-lg-8">
            <div class="card border-0 rounded-4 shadow-sm h-100">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-navy mb-4">Biến động Doanh thu (6 tháng gần nhất)</h6>
                    <div style="height: 300px;">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart: Customer Share -->
        <div class="col-lg-4">
            <div class="card border-0 rounded-4 shadow-sm h-100">
                <div class="card-body p-4 text-center">
                    <h6 class="fw-bold text-navy mb-4">Cơ cấu Khách hàng (Top 5)</h6>
                    <div style="height: 300px;">
                        <canvas id="customerChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table: Overdue Debt -->
        <div class="col-12">
            <div class="card border-0 rounded-4 shadow-sm">
                <div class="card-header bg-white border-0 p-4 pb-0">
                    <h6 class="fw-bold text-danger mb-0">Cảnh báo Nợ quá hạn (> 30 ngày)</h6>
                </div>
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Số Báo nợ</th>
                                    <th>Khách hàng</th>
                                    <th>Ngày lập</th>
                                    <th class="text-end">Số tiền nợ</th>
                                    <th class="text-center">Số ngày quá hạn</th>
                                    <th class="text-center">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($overdueDebt as $debt)
                                    <tr>
                                        <td class="fw-bold text-navy">{{ $debt->note_number }}</td>
                                        <td>{{ $debt->customer_name }}</td>
                                        <td>{{ \Carbon\Carbon::parse($debt->issued_at)->format('d/m/Y') }}</td>
                                        <td class="text-end fw-bold text-danger">{{ number_format($debt->grand_total) }}đ</td>
                                        <td class="text-center">
                                            <span class="badge bg-danger">
                                                {{ \Carbon\Carbon::parse($debt->issued_at)->diffInDays(now()) }} ngày
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('debit-notes.show', $debt->id) }}" class="btn btn-sm btn-outline-navy">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted small">Không có nợ quá hạn.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Revenue Chart
    const revCtx = document.getElementById('revenueChart').getContext('2d');
    new Chart(revCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($monthlyRevenue->pluck('month')) !!},
            datasets: [{
                label: 'Doanh thu (VNĐ)',
                data: {!! json_encode($monthlyRevenue->pluck('total')) !!},
                borderColor: '#1e3a8a',
                backgroundColor: 'rgba(30, 58, 138, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    // Customer Chart
    const cusCtx = document.getElementById('customerChart').getContext('2d');
    new Chart(cusCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($customerRevenue->pluck('company_name')) !!},
            datasets: [{
                data: {!! json_encode($customerRevenue->pluck('total')) !!},
                backgroundColor: ['#1e3a8a', '#3b82f6', '#60a5fa', '#93c5fd', '#dbeafe']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } }
        }
    });
</script>
@endsection
