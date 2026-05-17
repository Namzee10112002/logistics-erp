<?php

namespace App\Http\Controllers;

use App\Models\DebitNote;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PaymentController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'debit_note_id' => 'required|exists:debit_notes,id',
            'amount' => 'required|numeric|min:1',
            'payment_method' => ['required', 'string', Rule::in(['Chuyển khoản', 'Tiền mặt', 'Cấn trừ nợ'])],
            'reference_no' => 'nullable|string',
            'note' => 'nullable|string',
        ]);

        Payment::create([
            'debit_note_id' => $validated['debit_note_id'],
            'amount_paid' => $validated['amount'],
            'payment_method' => $validated['payment_method'],
            'payment_date' => now(),
            'received_by' => Auth::id(),
            'reference_no' => $validated['reference_no'] ?? null,
            'note' => $validated['note'] ?? null,
        ]);

        // Update Debit Note status
        $dn = DebitNote::findOrFail($request->debit_note_id);
        $totalPaid = Payment::where('debit_note_id', $dn->id)->sum('amount_paid');

        if ($totalPaid >= $dn->grand_total) {
            $dn->update(['status' => 'paid']);
        } elseif ($totalPaid > 0) {
            $dn->update(['status' => 'partial']);
        }

        return back()->with('success', 'Ghi nhận thanh toán thành công!');
    }
}
