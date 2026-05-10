<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\ShippingJob;
use App\Models\Vehicle;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function operational()
    {
        // 1. Job Status Distribution
        $jobStatuses = ShippingJob::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get();

        // 2. Vehicle & Driver Performance
        $totalVehicles = Vehicle::count();
        $activeVehicles = DB::table('dispatch_orders')
            ->whereIn('dispatch_status', ['dispatched', 'on_way'])
            ->distinct('vehicle_id')
            ->count();

        // Driver Performance (Total jobs completed)
        $topDrivers = DB::table('dispatch_orders')
            ->join('drivers', 'dispatch_orders.driver_id', '=', 'drivers.id')
            ->select('drivers.full_name', DB::raw('count(*) as total_jobs'))
            ->where('dispatch_orders.dispatch_status', 'completed')
            ->groupBy('drivers.id', 'drivers.full_name')
            ->orderBy('total_jobs', 'desc')
            ->limit(5)
            ->get();

        // Vehicle Usage Distribution
        $vehicleStats = DB::table('dispatch_orders')
            ->join('vehicles', 'dispatch_orders.vehicle_id', '=', 'vehicles.id')
            ->select('vehicles.plate_number', DB::raw('count(*) as total_trips'))
            ->groupBy('vehicles.id', 'vehicles.plate_number')
            ->orderBy('total_trips', 'desc')
            ->limit(5)
            ->get();

        return view('reports.operational', compact('jobStatuses', 'totalVehicles', 'activeVehicles', 'topDrivers', 'vehicleStats'));
    }

    public function financial()
    {
        // 1. Monthly Revenue (Last 6 months)
        $monthlyRevenue = DB::table('debit_notes')
            ->select(DB::raw("DATE_FORMAT(issued_at, '%m/%Y') as month"), DB::raw('SUM(grand_total) as total'))
            ->where('issued_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('issued_at', 'asc')
            ->get();

        // 2. Revenue by Customer
        $customerRevenue = DB::table('debit_notes')
            ->join('customers', 'debit_notes.customer_id', '=', 'customers.id')
            ->select('customers.company_name', DB::raw('SUM(grand_total) as total'))
            ->groupBy('customers.id', 'customers.company_name')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get();

        // 3. Profit Margin Calculation
        $totalRevenue = DB::table('debit_notes')->sum('grand_total');
        $totalExpenses = DB::table('expenses')->where('status', 'approved')->sum('amount');
        $profit = $totalRevenue - $totalExpenses;

        // 4. Overdue Debt (Unpaid > 30 days)
        $overdueDebt = DB::table('debit_notes')
            ->join('customers', 'debit_notes.customer_id', '=', 'customers.id')
            ->where('debit_notes.status', 'unpaid')
            ->where('debit_notes.issued_at', '<', now()->subDays(30))
            ->select('debit_notes.*', 'customers.company_name as customer_name')
            ->get();

        return view('reports.financial', compact('monthlyRevenue', 'customerRevenue', 'totalRevenue', 'totalExpenses', 'profit', 'overdueDebt'));
    }
}
