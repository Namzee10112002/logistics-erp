<?php

namespace App\Http\Controllers;

use App\Models\CashAdvance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CashAdvanceController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'shipping_job_id' => 'required|exists:shipping_jobs,id',
            'dispatch_order_id' => 'nullable|exists:dispatch_orders,id',
            'amount' => 'required|numeric|min:0',
            'reason' => 'required|string|max:500',
        ]);

        $validated['requested_by'] = Auth::id();
        $validated['status'] = 'pending';

        CashAdvance::create($validated);

        return back()->with('success', 'Đã gửi yêu cầu tạm ứng. Vui lòng chờ kế toán phê duyệt.');
    }

    public function approve(CashAdvance $cashAdvance)
    {
        $cashAdvance->update([
            'approved_by' => Auth::id(),
            'status' => 'approved',
        ]);

        return back()->with('success', 'Đã phê duyệt chi tạm ứng.');
    }

    public function reject(CashAdvance $cashAdvance)
    {
        $cashAdvance->update([
            'status' => 'rejected',
        ]);

        return back()->with('success', 'Đã từ chối yêu cầu tạm ứng.');
    }
}
