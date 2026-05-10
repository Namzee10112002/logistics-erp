<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\ShippingJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->groupBy('group');

        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        foreach ($request->settings as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        return back()->with('success', 'Đã cập nhật cấu hình hệ thống thành công!');
    }

    public function backup()
    {
        $jobs = ShippingJob::with(['customer', 'creator'])->get();

        $filename = 'backup_shipping_jobs_'.date('Ymd_His').'.csv';
        $handle = fopen('php://output', 'w');
        fwrite($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM

        fputcsv($handle, ['ID', 'Mã Job', 'Khách hàng', 'Loại hàng', 'Trạng thái', 'Ngày tạo', 'Người tạo']);

        foreach ($jobs as $job) {
            fputcsv($handle, [
                $job->id,
                $job->job_code,
                $job->customer->company_name,
                $job->container_type,
                $job->status,
                $job->created_at,
                $job->creator->name,
            ]);
        }

        $callback = function () use ($jobs) {
            $file = fopen('php://output', 'w');
            fwrite($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, ['ID', 'Mã Job', 'Khách hàng', 'Loại hàng', 'Trạng thái', 'Ngày tạo', 'Người tạo']);
            foreach ($jobs as $job) {
                fputcsv($file, [
                    $job->id,
                    $job->job_code,
                    $job->customer->company_name,
                    $job->container_type,
                    $job->status,
                    $job->created_at,
                    $job->creator->name,
                ]);
            }
            fclose($file);
        };

        return Response::streamDownload($callback, $filename, [
            'Content-type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=$filename",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ]);
    }
}
