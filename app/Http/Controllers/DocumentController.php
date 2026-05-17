<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $documents = Document::with(['shippingJob.customer', 'uploader'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('doc_category', 'like', "%{$search}%")
                        ->orWhereHas('shippingJob', function ($jobQuery) use ($search) {
                            $jobQuery->where('job_code', 'like', "%{$search}%")
                                ->orWhere('container_number', 'like', "%{$search}%");
                        })
                        ->orWhereHas('shippingJob.customer', function ($customerQuery) use ($search) {
                            $customerQuery->where('customer_name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->filled('document_flow'), fn ($query) => $query->where('document_flow', $request->document_flow))
            ->when($request->filled('tax_stage'), fn ($query) => $query->where('tax_stage', $request->tax_stage))
            ->latest()
            ->paginate(10);

        return view('documents.index', compact('documents'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'shipping_job_id' => 'required|exists:shipping_jobs,id',
            'doc_category' => 'required|string|max:100',
            'document_flow' => ['required', Rule::in(['input', 'output'])],
            'tax_stage' => ['required', Rule::in(['before_tax', 'after_tax'])],
            'note' => 'nullable|string|max:255',
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120', // Max 5MB
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store('documents/'.$request->shipping_job_id, 'public');

            Document::create([
                'shipping_job_id' => $request->shipping_job_id,
                'doc_category' => $request->doc_category,
                'document_flow' => $request->document_flow,
                'tax_stage' => $request->tax_stage,
                'file_url' => $path,
                'uploaded_by' => Auth::id(),
                'status' => 'active',
                'note' => $request->note,
            ]);

            return back()->with('success', 'Tải lên chứng từ thành công!');
        }

        return back()->with('error', 'Không tìm thấy file tải lên.');
    }

    public function destroy($id)
    {
        $document = Document::findOrFail($id);
        Storage::disk('public')->delete($document->file_url);
        $document->delete();

        return back()->with('success', 'Đã xóa chứng từ.');
    }
}
