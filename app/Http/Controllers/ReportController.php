<?php

namespace App\Http\Controllers;

use App\Models\DispatchOrder;
use App\Models\Driver;
use App\Models\RecurringExpense;
use App\Models\ShippingJob;
use App\Models\Vehicle;
use App\Services\ExportService;
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
            return app(ExportService::class)->download((string) $request->string('export'), 'Báo cáo vận hành', $periodLabel, [
                'Tài xế', 'Số chuyến hoàn thành', 'Số ngày chạy', 'Tổng giờ chạy',
            ], $driverProductivity->map(fn ($driver): array => [
                $driver->full_name,
                $driver->total_trips,
                $driver->total_days,
                $driver->total_hours,
            ])->all());
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
            ->get();

        if ($request->filled('export')) {
            return app(ExportService::class)->download((string) $request->string('export'), 'Báo cáo tài chính', $periodLabel, [
                'Chỉ tiêu', 'Giá trị',
            ], [
                ['Tổng doanh thu', $totalRevenue],
                ['Chi phí phát sinh đã duyệt', $totalExpenses],
                ['Chi phí cố định hàng tháng', $recurringMonthlyTotal],
                ['Lợi nhuận dự kiến', $profit],
            ]);
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
