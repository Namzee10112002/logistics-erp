<?php

namespace App\Http\Controllers;

use App\Models\DebitNote;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'debit_note_id' => 'required|exists:debit_notes,id',
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|string',
            'reference_no' => 'nullable|string',
            'note' => 'nullable|string',
        ]);

        $validated['received_by'] = Auth::id();
        $validated['payment_date'] = now();

        Payment::create($validated);

        // Update Debit Note status
        $dn = DebitNote::findOrFail($request->debit_note_id);
        $totalPaid = Payment::where('debit_note_id', $dn->id)->sum('amount');

        if ($totalPaid >= $dn->grand_total) {
            $dn->update(['status' => 'paid']);
        } elseif ($totalPaid > 0) {
            $dn->update(['status' => 'partial']);
        }

        return back()->with('success', 'Ghi nhận thanh toán thành công!');
    }
}
