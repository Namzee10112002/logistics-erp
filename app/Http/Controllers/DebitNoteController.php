<?php

namespace App\Http\Controllers;

use App\Models\DebitNote;
use App\Models\ServicePrice;
use App\Models\ShippingJob;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DebitNoteController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'shipping_job_id' => 'required|exists:shipping_jobs,id',
        ]);

        $job = ShippingJob::with(['expenses'])->findOrFail($request->shipping_job_id);

        // Simple price matching logic: match service_name with container_type
        // e.g. "Vận chuyển 20DC"
        $serviceName = 'Vận chuyển '.$job->container_type;
        $priceRecord = ServicePrice::where('service_name', 'like', "%$serviceName%")->first();

        $serviceFee = $priceRecord ? $priceRecord->unit_price : 0;
        $totalExpenses = $job->expenses->where('status', 'approved')->sum('amount');

        $debitNote = DebitNote::updateOrCreate(
            ['shipping_job_id' => $job->id, 'status' => 'unpaid'],
            [
                'customer_id' => $job->customer_id,
                'total_service_fee' => $serviceFee,
                'total_expense_paid' => $totalExpenses,
                'grand_total' => $serviceFee + $totalExpenses,
                'issued_at' => now(),
            ]
        );

        if (! $debitNote->wasRecentlyCreated) {
            return back()->with('success', 'Đã cập nhật lại Giấy báo nợ với dữ liệu chi phí mới nhất!');
        }

        if (! $debitNote->note_number) {
            $debitNote->update(['note_number' => 'DN-'.strtoupper(Str::random(8))]);
        }

        return back()->with('success', 'Đã lập Giấy báo nợ thành công!');
    }

    public function show(DebitNote $debitNote)
    {
        $debitNote->load(['shippingJob', 'customer', 'shippingJob.expenses']);

        return view('debit_notes.show', compact('debitNote'));
    }
}
