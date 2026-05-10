<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'shipping_job_id' => 'required|exists:shipping_jobs,id',
            'doc_category' => 'required|string|max:100',
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120', // Max 5MB
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store('documents/'.$request->shipping_job_id, 'public');

            Document::create([
                'shipping_job_id' => $request->shipping_job_id,
                'doc_category' => $request->doc_category,
                'file_url' => $path,
                'uploaded_by' => Auth::id(),
                'status' => 'active',
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
