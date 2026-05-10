<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\DebitNote;
use App\Models\Expense;
use App\Models\ShippingJob;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_jobs' => ShippingJob::count(),
            'active_jobs' => ShippingJob::whereIn('status', ['new', 'processing', 'dispatched'])->count(),
            'total_revenue' => DebitNote::sum('grand_total'),
            'total_expenses' => Expense::where('status', 'approved')->sum('amount'),
            'total_customers' => Customer::count(),
        ];

        $stats['profit'] = $stats['total_revenue'] - $stats['total_expenses'];

        // Data for charts (last 6 months)
        $months = [];
        $revenueData = [];
        $expenseData = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $months[] = $month->format('M');

            $revenueData[] = DebitNote::whereYear('issued_at', $month->year)
                ->whereMonth('issued_at', $month->month)
                ->sum('grand_total');

            $expenseData[] = Expense::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->where('status', 'approved')
                ->sum('amount');
        }

        // Recent Jobs
        $recentJobs = ShippingJob::with(['customer'])->latest()->limit(5)->get();

        return view('dashboard', compact('stats', 'months', 'revenueData', 'expenseData', 'recentJobs'));
    }
}
