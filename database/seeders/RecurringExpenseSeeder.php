<?php

namespace Database\Seeders;

use App\Models\RecurringExpense;
use Illuminate\Database\Seeder;

class RecurringExpenseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $expenses = [
            ['expense_code' => 'FIX-0001', 'name' => 'Chi phí thuê văn phòng', 'category' => 'Văn phòng', 'amount' => 15000000, 'cycle' => 'monthly', 'effective_from' => now()->subYear()->startOfMonth(), 'effective_to' => null, 'status' => 'active', 'note' => 'Thanh toán định kỳ vào đầu tháng.'],
            ['expense_code' => 'FIX-0002', 'name' => 'Chi phí phần mềm điều hành', 'category' => 'Hệ thống', 'amount' => 3000000, 'cycle' => 'monthly', 'effective_from' => now()->subMonths(8)->startOfMonth(), 'effective_to' => null, 'status' => 'active', 'note' => 'Gói vận hành phần mềm logistics.'],
            ['expense_code' => 'FIX-0003', 'name' => 'Chi phí bãi đỗ cố định', 'category' => 'Kho bãi', 'amount' => 12000000, 'cycle' => 'monthly', 'effective_from' => now()->subMonths(10)->startOfMonth(), 'effective_to' => null, 'status' => 'active', 'note' => 'Bao gồm chỗ đậu đầu kéo và mooc.'],
            ['expense_code' => 'FIX-0004', 'name' => 'Bảo hiểm trách nhiệm vận tải', 'category' => 'Bảo hiểm', 'amount' => 36000000, 'cycle' => 'yearly', 'effective_from' => now()->startOfYear(), 'effective_to' => now()->endOfYear(), 'status' => 'active', 'note' => 'Phân bổ cho các chuyến vận tải trong năm.'],
        ];

        foreach ($expenses as $expense) {
            RecurringExpense::updateOrCreate(
                ['expense_code' => $expense['expense_code']],
                $expense
            );
        }
    }
}
