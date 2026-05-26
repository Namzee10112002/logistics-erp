<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Setting;
use App\Models\ShippingJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    /**
     * Danh sách key tham số hệ thống mặc định ở Phân khu 2.
     *
     * @var array<string, array{description: string, default: string}>
     */
    private static array $systemParamDefaults = [
        'system.usd_rate' => ['description' => 'Tỷ giá quy đổi (USD/VND)', 'default' => '25450'],
        'system.vat_percent' => ['description' => 'Thuế GTGT mặc định (%)', 'default' => '10'],
        'system.fuel_limit' => ['description' => 'Hạn mức tiền dầu (VND)', 'default' => '5000000'],
        'system.toll_limit' => ['description' => 'Hạn mức phí cầu đường (VND)', 'default' => '2000000'],
        'system.overage_alert' => ['description' => 'Bật cảnh báo vượt định mức', 'default' => '0'],
    ];

    public function index()
    {
        $settings = Setting::all()->groupBy('group');

        // Đảm bảo các key tham số hệ thống luôn tồn tại
        foreach (self::$systemParamDefaults as $key => $meta) {
            Setting::firstOrCreate(
                ['key' => $key],
                ['value' => $meta['default'], 'group' => 'system', 'description' => $meta['description']]
            );
        }

        // Làm mới sau khi seed mặc định
        $settings = Setting::all()->groupBy('group');

        $systemParams = Setting::where('group', 'system')->get()->keyBy('key');
        $user = auth()->user();

        return view('settings.index', compact('settings', 'systemParams', 'user'));
    }

    public function update(Request $request)
    {
        // Cập nhật tham số cấu hình công ty / chung
        if ($request->has('settings')) {
            foreach ($request->settings as $key => $value) {
                Setting::updateOrCreate(['key' => $key], ['value' => $value]);
            }
        }

        // Cập nhật tham số hệ thống (Phân khu 2)
        if ($request->has('system_params')) {
            foreach ($request->system_params as $key => $value) {
                Setting::updateOrCreate(
                    ['key' => $key],
                    ['value' => $value, 'group' => 'system']
                );
            }
        }

        // Checkbox "Bật cảnh báo vượt định mức" (unchecked gửi null)
        Setting::updateOrCreate(
            ['key' => 'system.overage_alert'],
            ['value' => $request->boolean('system_params.system.overage_alert') ? '1' : '0', 'group' => 'system']
        );

        ActivityLog::log('update_settings', 'Cập nhật cấu hình và tham số hệ thống');

        return back()->with('success', 'Đã cập nhật cấu hình hệ thống thành công!');
    }

    public function backup()
    {
        $dbHost = config('database.connections.mysql.host', '127.0.0.1');
        $dbPort = config('database.connections.mysql.port', '3306');
        $dbName = config('database.connections.mysql.database');
        $dbUser = config('database.connections.mysql.username');
        $dbPass = config('database.connections.mysql.password');

        // Tìm mysqldump.exe (XAMPP hoặc PATH)
        $mysqldumpBin = 'mysqldump';
        $xamppMysqldump = 'C:\\xampp\\mysql\\bin\\mysqldump.exe';
        if (file_exists($xamppMysqldump)) {
            $mysqldumpBin = "\"{$xamppMysqldump}\"";
        }

        $passArg = $dbPass ? '-p'.escapeshellarg($dbPass) : '';
        
        $filename = 'backup_logistics_'.date('Ymd_His').'.sql';
        
        // Tạo thư mục tạm nếu chưa có
        if (!Storage::exists('backup_tmp')) {
            Storage::makeDirectory('backup_tmp');
        }
        
        $fullPath = Storage::path('backup_tmp/'.$filename);

        $command = "{$mysqldumpBin} -h {$dbHost} -P {$dbPort} -u ".escapeshellarg($dbUser)." {$passArg} ".escapeshellarg($dbName).' > '.escapeshellarg($fullPath).' 2>&1';

        $output = [];
        $exitCode = 0;
        exec($command, $output, $exitCode);

        if ($exitCode !== 0) {
            ActivityLog::log('backup_database_failed', 'Thất bại khi xuất cơ sở dữ liệu: '.implode(' ', $output));
            return back()->with('error', 'Sao lưu thất bại: '.implode(' ', $output));
        }

        ActivityLog::log('backup_database', 'Xuất bản sao lưu cơ sở dữ liệu hệ thống (.sql)');

        return Response::download($fullPath, $filename, [
            'Content-Type' => 'application/sql',
        ])->deleteFileAfterSend(true);
    }

    public function restore(Request $request)
    {
        $request->validate([
            'restore_file' => 'required|file|mimes:sql,txt|max:51200',
            'admin_password' => 'required|string',
        ], [
            'restore_file.required' => 'Vui lòng chọn file .sql để khôi phục.',
            'restore_file.mimes' => 'File khôi phục phải có định dạng .sql hoặc .txt.',
            'restore_file.max' => 'File khôi phục không được vượt quá 50MB.',
            'admin_password.required' => 'Vui lòng nhập mật khẩu xác nhận.',
        ]);

        // Kiểm tra mật khẩu admin
        if (! Hash::check($request->admin_password, auth()->user()->password)) {
            return back()->with('error', 'Mật khẩu xác nhận không chính xác. Chặn quyền khôi phục!');
        }

        $file = $request->file('restore_file');
        $sqlPath = $file->storeAs('restore_tmp', 'restore_'.now()->format('YmdHis').'.sql');
        $fullPath = Storage::path($sqlPath);

        $dbHost = config('database.connections.mysql.host', '127.0.0.1');
        $dbPort = config('database.connections.mysql.port', '3306');
        $dbName = config('database.connections.mysql.database');
        $dbUser = config('database.connections.mysql.username');
        $dbPass = config('database.connections.mysql.password');

        // Tìm mysql.exe (XAMPP hoặc PATH)
        $mysqlBin = 'mysql';
        $xamppMysql = 'C:\\xampp\\mysql\\bin\\mysql.exe';
        if (file_exists($xamppMysql)) {
            $mysqlBin = "\"{$xamppMysql}\"";
        }

        $passArg = $dbPass ? '-p'.escapeshellarg($dbPass) : '';
        $command = "{$mysqlBin} -h {$dbHost} -P {$dbPort} -u ".escapeshellarg($dbUser)." {$passArg} ".escapeshellarg($dbName).' < '.escapeshellarg($fullPath).' 2>&1';

        $output = [];
        $exitCode = 0;
        exec($command, $output, $exitCode);

        // Dọn file tạm
        Storage::delete($sqlPath);

        if ($exitCode !== 0) {
            ActivityLog::log('restore_database_failed', 'Thất bại khi khôi phục cơ sở dữ liệu: '.implode(' ', $output));

            return back()->with('error', 'Khôi phục thất bại: '.implode(' ', $output));
        }

        ActivityLog::log('restore_database', 'Khôi phục cơ sở dữ liệu thành công từ file: '.$file->getClientOriginalName());

        return back()->with('success', 'Khôi phục cơ sở dữ liệu thành công!');
    }

    public function uploadAsset(Request $request)
    {
        $request->validate([
            'stamp' => 'nullable|file|mimes:png,jpg,jpeg|max:2048',
            'logo' => 'nullable|file|mimes:png,jpg,jpeg|max:2048',
        ], [
            'stamp.mimes' => 'Con dấu phải là file ảnh PNG hoặc JPG.',
            'logo.mimes' => 'Logo phải là file ảnh PNG hoặc JPG.',
            'stamp.max' => 'Con dấu không được vượt quá 2MB.',
            'logo.max' => 'Logo không được vượt quá 2MB.',
        ]);

        if (! $request->hasFile('stamp') && ! $request->hasFile('logo')) {
            return back()->with('error', 'Vui lòng chọn ít nhất một tệp để tải lên.');
        }

        if ($request->hasFile('stamp')) {
            $request->file('stamp')->storeAs('public/assets', 'company-stamp.png');
        }

        if ($request->hasFile('logo')) {
            $request->file('logo')->storeAs('public/assets', 'company-logo.png');
        }

        ActivityLog::log('upload_asset', 'Tải lên mẫu in ấn mới (Con dấu/Logo)');

        return back()->with('success', 'Tải lên tệp thành công! Các mẫu in sẽ sử dụng tệp mới ngay lập tức.');
    }
}
