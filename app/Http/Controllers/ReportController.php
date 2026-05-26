<?php

namespace App\Http\Controllers;

use App\Models\DispatchOrder;
use App\Models\Driver;
use App\Models\RecurringExpense;
use App\Models\ShippingJob;
use App\Models\Vehicle;
use App\Services\ExportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class ReportController extends Controller
{
    public function operational(Request $request)
    {
        [$dateFrom, $dateTo, $periodLabel] = $this->resolveReportRange($request);

        // 1. Job Status Distribution
        $jobStatuses = ShippingJob::select('status', DB::raw('count(*) as total'))
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->groupBy('status')
            ->get();

        // 2. Vehicle & Driver Performance
        $totalVehicles = Vehicle::count();
        $activeVehicles = DB::table('dispatch_orders')
            ->whereIn('dispatch_status', ['dispatched', 'on_way'])
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->distinct('vehicle_id')
            ->count();

        // Driver Performance (Total jobs completed)
        $topDrivers = DB::table('dispatch_orders')
            ->join('drivers', 'dispatch_orders.driver_id', '=', 'drivers.id')
            ->select('drivers.full_name', DB::raw('count(*) as total_jobs'))
            ->where('dispatch_orders.dispatch_status', 'completed')
            ->whereBetween('dispatch_orders.created_at', [$dateFrom, $dateTo])
            ->groupBy('drivers.id', 'drivers.full_name')
            ->orderBy('total_jobs', 'desc')
            ->limit(5)
            ->get();

        // Vehicle Usage Distribution
        $vehicleStats = DB::table('dispatch_orders')
            ->join('vehicles', 'dispatch_orders.vehicle_id', '=', 'vehicles.id')
            ->select('vehicles.plate_number', DB::raw('count(*) as total_trips'))
            ->whereBetween('dispatch_orders.created_at', [$dateFrom, $dateTo])
            ->groupBy('vehicles.id', 'vehicles.plate_number')
            ->orderBy('total_trips', 'desc')
            ->limit(5)
            ->get();

        $driverProductivity = DispatchOrder::with('driver')
            ->where('dispatch_status', 'completed')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->get()
            ->groupBy('driver_id')
            ->map(function ($orders) {
                $hours = $orders->sum(function (DispatchOrder $order): float {
                    if (! $order->start_time || ! $order->end_time) {
                        return 0;
                    }

                    return round($order->start_time->diffInMinutes($order->end_time) / 60, 2);
                });

                return (object) [
                    'full_name' => $orders->first()->driver?->full_name ?? '---',
                    'total_trips' => $orders->count(),
                    'total_days' => $orders->pluck('start_time')->filter()->map->format('Y-m-d')->unique()->count(),
                    'total_hours' => $hours,
                ];
            })
            ->values();

        if ($request->filled('export')) {
            $sheets = [
                [
                    'name' => 'Năng suất tài xế',
                    'headers' => ['Tài xế', 'Số chuyến hoàn thành', 'Số ngày chạy', 'Tổng giờ chạy'],
                    'rows' => $driverProductivity->map(fn ($driver): array => [
                        $driver->full_name,
                        $driver->total_trips,
                        $driver->total_days,
                        $driver->total_hours,
                    ])->all(),
                ],
            ];

            return app(ExportService::class)->download((string) $request->string('export'), 'Báo cáo vận hành', $periodLabel, $sheets);
        }

        return view('reports.operational', compact('jobStatuses', 'totalVehicles', 'activeVehicles', 'topDrivers', 'vehicleStats', 'driverProductivity', 'periodLabel'));
    }

    public function financial(Request $request)
    {
        [$dateFrom, $dateTo, $periodLabel] = $this->resolveReportRange($request);

        // 1. Monthly Revenue (Last 6 months)
        $monthlyRevenue = DB::table('debit_notes')
            ->select(DB::raw("DATE_FORMAT(issued_at, '%m/%Y') as month"), DB::raw('SUM(grand_total) as total'))
            ->whereBetween('issued_at', [$dateFrom, $dateTo])
            ->groupBy('month')
            ->orderBy('issued_at', 'asc')
            ->get();

        // 2. Revenue by Customer
        $customerRevenue = DB::table('debit_notes')
            ->join('customers', 'debit_notes.customer_id', '=', 'customers.id')
            ->select('customers.company_name', DB::raw('SUM(grand_total) as total'))
            ->whereBetween('debit_notes.issued_at', [$dateFrom, $dateTo])
            ->groupBy('customers.id', 'customers.company_name')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get();

        // 3. Profit Margin Calculation
        $totalRevenue = DB::table('debit_notes')->whereBetween('issued_at', [$dateFrom, $dateTo])->sum('grand_total');
        $totalExpenses = DB::table('expenses')->where('status', 'approved')->whereBetween('created_at', [$dateFrom, $dateTo])->sum('amount');
        $recurringExpenses = RecurringExpense::latest()->paginate(10);
        $recurringMonthlyTotal = RecurringExpense::where('status', 'active')->sum('amount');
        $profit = $totalRevenue - $totalExpenses - $recurringMonthlyTotal;

        // 4. Overdue Debt (Unpaid > 30 days)
        $overdueDebt = DB::table('debit_notes')
            ->join('customers', 'debit_notes.customer_id', '=', 'customers.id')
            ->where('debit_notes.status', 'unpaid')
            ->where('debit_notes.issued_at', '<', now()->subDays(30))
            ->select('debit_notes.*', 'customers.company_name as customer_name')
            ->limit(25)
            ->get();

        if ($request->filled('export')) {
            $revenueDetails = DB::table('debit_notes as dn')
                ->leftJoin('customers as c', 'dn.customer_id', '=', 'c.id')
                ->leftJoin('shipping_jobs as sj', 'dn.shipping_job_id', '=', 'sj.id')
                ->select(
                    'dn.note_number',
                    'sj.job_code',
                    'c.company_name as customer_name',
                    'dn.issued_at',
                    'dn.total_service_fee',
                    'dn.total_expense_paid',
                    'dn.grand_total',
                    'dn.status'
                )
                ->whereBetween('dn.issued_at', [$dateFrom, $dateTo])
                ->orderBy('dn.issued_at', 'desc')
                ->get()
                ->map(fn ($r): array => [
                    $r->note_number,
                    $r->job_code ?? '---',
                    $r->customer_name ?? '---',
                    Carbon::parse($r->issued_at)->format('d/m/Y'),
                    $r->total_service_fee,
                    $r->total_expense_paid,
                    $r->grand_total,
                    match ($r->status) {
                        'draft' => 'Nháp',
                        'unpaid' => 'Chưa thanh toán',
                        'partial' => 'Thanh toán một phần',
                        'paid' => 'Đã thanh toán',
                        default => $r->status
                    },
                ])->all();

            $expenseDetails = DB::table('expenses as e')
                ->leftJoin('dispatch_orders as do', 'e.dispatch_order_id', '=', 'do.id')
                ->leftJoin('drivers as d', 'do.driver_id', '=', 'd.id')
                ->select(
                    'e.id as expense_code',
                    'e.expense_type as type',
                    'e.amount',
                    'e.created_at',
                    'do.order_number as dispatch_code',
                    'd.full_name as driver_name'
                )
                ->where('e.status', 'approved')
                ->whereBetween('e.created_at', [$dateFrom, $dateTo])
                ->orderBy('e.created_at', 'desc')
                ->get()
                ->map(fn ($e): array => [
                    'CP'.str_pad($e->expense_code, 5, '0', STR_PAD_LEFT),
                    $e->type,
                    Carbon::parse($e->created_at)->format('d/m/Y'),
                    $e->dispatch_code ?? '---',
                    $e->driver_name ?? '---',
                    $e->amount,
                ])->all();

            $recurringDetails = RecurringExpense::where('status', 'active')
                ->get()
                ->map(fn ($re): array => [
                    $re->expense_code,
                    $re->name,
                    $re->category ?? '---',
                    match ($re->cycle) {
                        'monthly' => 'Hàng tháng',
                        'quarterly' => 'Hàng quý',
                        'yearly' => 'Hàng năm',
                        default => $re->cycle
                    },
                    $re->amount,
                ])->all();

            $sheets = [
                [
                    'name' => 'Tổng hợp',
                    'headers' => ['Chỉ tiêu', 'Giá trị'],
                    'rows' => [
                        ['Tổng doanh thu', $totalRevenue],
                        ['Chi phí phát sinh đã duyệt', $totalExpenses],
                        ['Chi phí cố định hàng tháng', $recurringMonthlyTotal],
                        ['Lợi nhuận dự kiến', $profit],
                    ],
                ],
                [
                    'name' => 'Doanh thu',
                    'headers' => ['Mã hóa đơn', 'Mã đơn hàng', 'Tên khách hàng', 'Ngày phát hành', 'Tiền cước', 'Phụ phí (Hộ chi)', 'Tổng thanh toán', 'Trạng thái'],
                    'rows' => $revenueDetails,
                    'footer' => ['Tổng cộng', '', '', '', '', '', $totalRevenue, ''],
                ],
                [
                    'name' => 'Chi phí phát sinh',
                    'headers' => ['Mã CP', 'Loại chi phí', 'Ngày tạo', 'Mã lệnh điều động', 'Tài xế', 'Số tiền'],
                    'rows' => $expenseDetails,
                    'footer' => ['Tổng cộng', '', '', '', '', $totalExpenses],
                ],
                [
                    'name' => 'Chi phí cố định',
                    'headers' => ['Mã', 'Tên chi phí', 'Nhóm', 'Chu kỳ', 'Số tiền'],
                    'rows' => $recurringDetails,
                    'footer' => ['Tổng cộng', '', '', '', $recurringMonthlyTotal],
                ],
            ];

            return app(ExportService::class)->download((string) $request->string('export'), 'Báo cáo tài chính', $periodLabel, $sheets);
        }

        return view('reports.financial', compact('monthlyRevenue', 'customerRevenue', 'totalRevenue', 'totalExpenses', 'profit', 'overdueDebt', 'recurringExpenses', 'recurringMonthlyTotal', 'periodLabel'));
    }

    private function resolveReportRange(Request $request): array
    {
        $year = (int) $request->query('year', now()->year);
        $period = $request->query('period', 'last_6_months');

        if ($period === 'year') {
            return [now()->setYear($year)->startOfYear(), now()->setYear($year)->endOfYear(), "Năm {$year}"];
        }

        if ($period === 'quarter') {
            $quarter = max(1, min(4, (int) $request->query('quarter', now()->quarter)));
            $startMonth = (($quarter - 1) * 3) + 1;
            $dateFrom = now()->setDate($year, $startMonth, 1)->startOfDay();

            return [$dateFrom, $dateFrom->copy()->addMonths(2)->endOfMonth(), "Quý {$quarter}/{$year}"];
        }

        return [now()->subMonths(5)->startOfMonth(), now()->endOfMonth(), '6 tháng gần nhất'];
    }

    private function streamCsv(string $filename, array $rows)
    {
        return Response::streamDownload(function () use ($rows) {
            $file = fopen('php://output', 'w');
            fwrite($file, chr(0xEF).chr(0xBB).chr(0xBF));

            foreach ($rows as $row) {
                fputcsv($file, $row);
            }

            fclose($file);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
