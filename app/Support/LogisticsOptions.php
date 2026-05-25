<?php

namespace App\Support;

class LogisticsOptions
{
    /**
     * @return array<string, string>
     */
    public static function departments(): array
    {
        return [
            'Ban điều hành' => 'Ban điều hành',
            'Kinh doanh' => 'Kinh doanh',
            'Chứng từ' => 'Chứng từ',
            'Điều vận' => 'Điều vận',
            'Kế toán' => 'Kế toán',
            'Hiện trường' => 'Hiện trường',
            'Đội xe' => 'Đội xe',
            'Kho bãi' => 'Kho bãi',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function positions(): array
    {
        return [
            'Giám đốc vận hành' => 'Giám đốc vận hành',
            'Trưởng bộ phận' => 'Trưởng bộ phận',
            'Nhân viên kinh doanh' => 'Nhân viên kinh doanh',
            'Chuyên viên chứng từ' => 'Chuyên viên chứng từ',
            'Điều phối vận tải' => 'Điều phối vận tải',
            'Kế toán công nợ' => 'Kế toán công nợ',
            'Nhân viên hiện trường' => 'Nhân viên hiện trường',
            'Tài xế container' => 'Tài xế container',
            'Giám sát kho' => 'Giám sát kho',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function provincesNearHaiPhong(): array
    {
        return [
            'Hải Phòng' => 'Hải Phòng',
            'Quảng Ninh' => 'Quảng Ninh',
            'Hải Dương' => 'Hải Dương',
            'Hưng Yên' => 'Hưng Yên',
            'Thái Bình' => 'Thái Bình',
            'Nam Định' => 'Nam Định',
            'Bắc Ninh' => 'Bắc Ninh',
            'Hà Nội' => 'Hà Nội',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function vehicleTypes(): array
    {
        return [
            'Đầu kéo container' => 'Đầu kéo container',
            'Mooc 20 feet' => 'Mooc 20 feet',
            'Mooc 40 feet' => 'Mooc 40 feet',
            'Xe tải 5 tấn' => 'Xe tải 5 tấn',
            'Xe tải 10 tấn' => 'Xe tải 10 tấn',
            'Xe tải lạnh' => 'Xe tải lạnh',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function payloads(): array
    {
        return [
            '5' => '5 tấn',
            '8.5' => '8.5 tấn',
            '10' => '10 tấn',
            '15' => '15 tấn',
            '20' => '20 tấn',
            '22' => '22 tấn',
            '28' => '28 tấn',
            '32.5' => '32.5 tấn',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function driverRanks(): array
    {
        return [
            'Tài xế chính' => 'Tài xế chính',
            'Tài xế phụ' => 'Tài xế phụ',
            'Tài xế container' => 'Tài xế container',
            'Tài xế đường dài' => 'Tài xế đường dài',
            'Tổ trưởng đội xe' => 'Tổ trưởng đội xe',
            'Tài xế dự phòng' => 'Tài xế dự phòng',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function serviceUnits(): array
    {
        return [
            'Chuyến' => 'Chuyến',
            'Cont' => 'Cont',
            'Cont 20' => 'Cont 20',
            'Cont 40' => 'Cont 40',
            'KG' => 'KG',
            'Tấn' => 'Tấn',
            'Ngày' => 'Ngày',
            'Lần' => 'Lần',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function customerContactRoles(): array
    {
        return [
            'Kế toán' => 'Kế toán',
            'Nhân viên chứng từ' => 'Nhân viên chứng từ',
            'Phụ trách logistics' => 'Phụ trách logistics',
            'Điều phối kho' => 'Điều phối kho',
            'Nhân viên mua hàng' => 'Nhân viên mua hàng',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function fieldAssignmentTasks(): array
    {
        return [
            'Làm thủ tục giao nhận hàng' => 'Làm thủ tục giao nhận hàng',
            'Kiểm tra cont' => 'Kiểm tra cont',
            'Làm thủ tục chứng từ' => 'Làm thủ tục chứng từ',
            'Giám sát bốc xếp' => 'Giám sát bốc xếp',
            'Xử lý sự cố' => 'Xử lý sự cố',
        ];
    }
}
