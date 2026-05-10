<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            ['role_code' => 'ADMIN', 'role_name' => 'Giám đốc / Quản trị viên', 'description' => 'Quản lý chung, cấu hình hệ thống, phân quyền, xem báo cáo tổng quan.'],
            ['role_code' => 'SALES', 'role_name' => 'Kinh doanh', 'description' => 'Tạo và quản lý thông tin đối tác, khách hàng, khởi tạo đơn hàng.'],
            ['role_code' => 'DOCUMENT', 'role_name' => 'Chứng từ', 'description' => 'Xử lý, phân loại, tải lên và kiểm duyệt hồ sơ, tài liệu.'],
            ['role_code' => 'DISPATCH', 'role_name' => 'Điều vận', 'description' => 'Lập kế hoạch vận tải, phân công xe, tài xế và theo dõi hành trình.'],
            ['role_code' => 'ACCOUNTANT', 'role_name' => 'Kế toán', 'description' => 'Duyệt chi phí, lập hóa đơn, thu chi tạm ứng và quản lý công nợ.'],
            ['role_code' => 'FIELD', 'role_name' => 'Nhân viên Hiện trường', 'description' => 'Xử lý hiện trường, cập nhật trạng thái đơn hàng, tải chứng từ.'],
            ['role_code' => 'DRIVER', 'role_name' => 'Tài xế', 'description' => 'Nhận lệnh, lái xe và cập nhật trạng thái chuyến đi.'],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(['role_code' => $role['role_code']], $role);
        }
    }
}
