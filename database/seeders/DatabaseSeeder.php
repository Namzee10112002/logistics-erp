<?php

namespace Database\Seeders;

use App\Models\CashAdvance;
use App\Models\Customer;
use App\Models\DebitNote;
use App\Models\DispatchOrder;
use App\Models\Document;
use App\Models\Driver;
use App\Models\Expense;
use App\Models\Location;
use App\Models\Payment;
use App\Models\Role;
use App\Models\ServicePrice;
use App\Models\Setting;
use App\Models\ShippingJob;
use App\Models\TrackingLog;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            RecurringExpenseSeeder::class,
        ]);

        $roles = Role::query()->get()->keyBy('role_code');
        $users = $this->seedUsers($roles);
        $customers = $this->seedCustomers();
        $locations = $this->seedLocations();
        $vehicles = $this->seedVehicles();
        $drivers = $this->seedDrivers($users);
        $servicePrices = $this->seedServicePrices();

        $this->seedSettings();
        $this->call(FieldStaffSeeder::class);

        $shippingJobs = $this->seedShippingJobs($customers, $locations, $users);
        $dispatchOrders = $this->seedDispatchOrders($shippingJobs, $vehicles, $drivers, $users);
        $documents = $this->seedDocuments($shippingJobs, $users);

        $this->seedTrackingLogs($dispatchOrders, $users);
        $this->seedExpenses($shippingJobs, $dispatchOrders, $documents, $users);
        $this->seedCashAdvances($shippingJobs, $dispatchOrders, $users);

        $debitNotes = $this->seedDebitNotes($shippingJobs, $servicePrices);

        $this->seedPayments($debitNotes, $users);
    }

    /**
     * @param  Collection<string, Role>  $roles
     * @return array<string, User>
     */
    private function seedUsers(Collection $roles): array
    {
        $employeePrefix = 'NV-'.now()->format('ym').'-';
        $users = [
            'ADMIN' => [
                'username' => 'admin_test',
                'full_name' => 'Nguyễn Minh Quân',
                'name' => 'Nguyễn Minh Quân',
                'email' => 'admin@example.test',
                'position' => 'Giám đốc vận hành',
                'department' => 'Ban điều hành',
                'theme_color' => '#1a237e',
                'is_dark_mode' => false,
            ],
            'SALES' => [
                'username' => 'sales_test',
                'full_name' => 'Trần Hoài An',
                'name' => 'Trần Hoài An',
                'email' => 'sales@example.test',
                'position' => 'Nhân viên kinh doanh',
                'department' => 'Kinh doanh',
                'theme_color' => '#0f766e',
                'is_dark_mode' => false,
            ],
            'DOCUMENT' => [
                'username' => 'document_test',
                'full_name' => 'Lê Thu Hà',
                'name' => 'Lê Thu Hà',
                'email' => 'document@example.test',
                'position' => 'Chuyên viên chứng từ',
                'department' => 'Chứng từ',
                'theme_color' => '#7c2d12',
                'is_dark_mode' => false,
            ],
            'DISPATCH' => [
                'username' => 'dispatch_test',
                'full_name' => 'Phạm Quốc Bảo',
                'name' => 'Phạm Quốc Bảo',
                'email' => 'dispatch@example.test',
                'position' => 'Điều phối vận tải',
                'department' => 'Điều vận',
                'theme_color' => '#0369a1',
                'is_dark_mode' => true,
            ],
            'ACCOUNTANT' => [
                'username' => 'accountant_test',
                'full_name' => 'Võ Khánh Linh',
                'name' => 'Võ Khánh Linh',
                'email' => 'accountant@example.test',
                'position' => 'Kế toán công nợ',
                'department' => 'Kế toán',
                'theme_color' => '#854d0e',
                'is_dark_mode' => false,
            ],
            'FIELD' => [
                'username' => 'field_test',
                'full_name' => 'Đặng Hải Nam',
                'name' => 'Đặng Hải Nam',
                'email' => 'field@example.test',
                'position' => 'Nhân viên hiện trường',
                'department' => 'Hiện trường',
                'theme_color' => '#166534',
                'is_dark_mode' => false,
            ],
            'DRIVER' => [
                'username' => 'driver_test',
                'full_name' => 'Bùi Văn Hùng',
                'name' => 'Bùi Văn Hùng',
                'email' => 'driver@example.test',
                'position' => 'Tài xế container',
                'department' => 'Đội xe',
                'theme_color' => '#334155',
                'is_dark_mode' => true,
            ],
        ];

        $seededUsers = [];
        $sequence = 1;

        foreach ($users as $roleCode => $data) {
            $seededUsers[$roleCode] = User::query()->create($data + [
                'password' => Hash::make('password'),
                'role_id' => $roles[$roleCode]->id,
                'status' => 1,
                'employee_code' => $employeePrefix.str_pad((string) $sequence, 3, '0', STR_PAD_LEFT),
                'joined_at' => now()->subMonths(18 - $sequence)->toDateString(),
            ]);

            $sequence++;
        }

        return $seededUsers;
    }

    /**
     * @return array<int, Customer>
     */
    private function seedCustomers(): array
    {
        $customerPrefix = 'KH-'.now()->format('ym').'-';
        $customers = [
            ['customer_name' => 'Công ty TNHH An Phát Logistics', 'tax_code' => '0312456789', 'address' => '12 Nguyễn Văn Linh, Quận 7, TP. Hồ Chí Minh', 'contact_person' => 'Nguyễn Thanh Tâm', 'phone' => '0901001001', 'email' => 'tam@anphat.example'],
            ['customer_name' => 'Công ty Cổ phần Thép Nam Việt', 'tax_code' => '0309876543', 'address' => 'KCN Sóng Thần 2, Dĩ An, Bình Dương', 'contact_person' => 'Hoàng Minh Đức', 'phone' => '0902002002', 'email' => 'duc@namviet.example'],
            ['customer_name' => 'Công ty TNHH May Mặc Sao Mai', 'tax_code' => '0311122233', 'address' => '45 Quốc lộ 1A, Thủ Đức, TP. Hồ Chí Minh', 'contact_person' => 'Lê Thị Mai', 'phone' => '0903003003', 'email' => 'mai@saomai.example'],
            ['customer_name' => 'Công ty Cổ phần Nông Sản Mekong', 'tax_code' => '1802233445', 'address' => '88 Nguyễn Trãi, Ninh Kiều, Cần Thơ', 'contact_person' => 'Phan Quốc Toàn', 'phone' => '0904004004', 'email' => 'toan@mekong.example'],
            ['customer_name' => 'Công ty TNHH Điện Máy Á Châu', 'tax_code' => '0315566778', 'address' => '19 Trường Sơn, Tân Bình, TP. Hồ Chí Minh', 'contact_person' => 'Đỗ Bảo Ngọc', 'phone' => '0905005005', 'email' => 'ngoc@achau.example'],
            ['customer_name' => 'Công ty TNHH Hóa Chất Minh Khang', 'tax_code' => '0319988776', 'address' => 'KCN Tân Tạo, Bình Tân, TP. Hồ Chí Minh', 'contact_person' => 'Vũ Hải Long', 'phone' => '0906006006', 'email' => 'long@minhkhang.example'],
        ];

        return collect($customers)
            ->values()
            ->map(fn (array $customer, int $index): Customer => Customer::query()->create($customer + [
                'customer_code' => $customerPrefix.str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT),
                'company_name' => $customer['customer_name'],
            ]))
            ->all();
    }

    /**
     * @return array<int, Location>
     */
    private function seedLocations(): array
    {
        $locations = [
            ['location_name' => 'Cảng Cát Lái', 'type' => 'port', 'address' => '1295B Nguyễn Thị Định, Thủ Đức', 'province' => 'TP. Hồ Chí Minh'],
            ['location_name' => 'ICD Sóng Thần', 'type' => 'depot', 'address' => 'Đường số 10, KCN Sóng Thần 1, Dĩ An', 'province' => 'Bình Dương'],
            ['location_name' => 'Kho An Phát Quận 7', 'type' => 'warehouse', 'address' => '12 Nguyễn Văn Linh, Quận 7', 'province' => 'TP. Hồ Chí Minh'],
            ['location_name' => 'Nhà máy Thép Nam Việt', 'type' => 'factory', 'address' => 'KCN Sóng Thần 2, Dĩ An', 'province' => 'Bình Dương'],
            ['location_name' => 'Kho Sao Mai Thủ Đức', 'type' => 'warehouse', 'address' => '45 Quốc lộ 1A, Thủ Đức', 'province' => 'TP. Hồ Chí Minh'],
            ['location_name' => 'Cảng Cái Mép', 'type' => 'port', 'address' => 'Phú Mỹ, Tân Thành', 'province' => 'Bà Rịa - Vũng Tàu'],
            ['location_name' => 'Điểm giao Mekong Cần Thơ', 'type' => 'other', 'address' => '88 Nguyễn Trãi, Ninh Kiều', 'province' => 'Cần Thơ'],
        ];

        return collect($locations)
            ->map(fn (array $location): Location => Location::query()->create($location))
            ->all();
    }

    /**
     * @return array<int, Vehicle>
     */
    private function seedVehicles(): array
    {
        $vehicles = [
            ['plate_number' => '51C-123.45', 'vehicle_type' => 'Đầu kéo container', 'payload' => 32.5, 'registration_expiry' => now()->addMonths(14)->toDateString(), 'status' => 'busy', 'note' => 'Đang ưu tiên tuyến cảng Cát Lái.'],
            ['plate_number' => '51R-456.78', 'vehicle_type' => 'Mooc 40 feet', 'payload' => 28.0, 'registration_expiry' => now()->addMonths(20)->toDateString(), 'status' => 'available', 'note' => 'Phù hợp container 40HC.'],
            ['plate_number' => '61C-222.33', 'vehicle_type' => 'Xe tải 10 tấn', 'payload' => 10.0, 'registration_expiry' => now()->addMonths(9)->toDateString(), 'status' => 'available', 'note' => 'Xe thùng kín, chạy nội tỉnh.'],
            ['plate_number' => '72H-888.99', 'vehicle_type' => 'Đầu kéo container', 'payload' => 31.0, 'registration_expiry' => now()->addMonths(6)->toDateString(), 'status' => 'busy', 'note' => 'Đang chạy tuyến Cái Mép.'],
            ['plate_number' => '51D-909.10', 'vehicle_type' => 'Xe tải lạnh', 'payload' => 8.5, 'registration_expiry' => now()->addMonths(18)->toDateString(), 'status' => 'maintenance', 'note' => 'Bảo dưỡng hệ thống lạnh định kỳ.'],
            ['plate_number' => '50LD-345.67', 'vehicle_type' => 'Mooc 20 feet', 'payload' => 22.0, 'registration_expiry' => now()->addMonths(24)->toDateString(), 'status' => 'available', 'note' => 'Sẵn sàng ghép chuyến ngắn.'],
        ];

        return collect($vehicles)
            ->map(fn (array $vehicle): Vehicle => Vehicle::query()->create($vehicle))
            ->all();
    }

    /**
     * @param  array<string, User>  $users
     * @return array<int, Driver>
     */
    private function seedDrivers(array $users): array
    {
        $driverPrefix = 'TX-'.now()->format('ym').'-';
        $drivers = [
            ['user_id' => $users['DRIVER']->id, 'full_name' => 'Bùi Văn Hùng', 'phone' => '0911001001', 'license_number' => 'GPLX-B2-000001', 'status' => 'active', 'start_date' => now()->subYears(4)->toDateString(), 'rank' => 'Tài xế chính', 'contract_expiry' => now()->addYear()->toDateString(), 'note' => 'Chuyên tuyến cảng - kho nội địa.'],
            ['user_id' => null, 'full_name' => 'Ngô Thành Phúc', 'phone' => '0911001002', 'license_number' => 'GPLX-C-000002', 'status' => 'active', 'start_date' => now()->subYears(3)->toDateString(), 'rank' => 'Tài xế container', 'contract_expiry' => now()->addMonths(10)->toDateString(), 'note' => 'Ưu tiên tuyến Bình Dương và Đồng Nai.'],
            ['user_id' => null, 'full_name' => 'Trịnh Anh Tuấn', 'phone' => '0911001003', 'license_number' => 'GPLX-FC-000003', 'status' => 'active', 'start_date' => now()->subYears(2)->toDateString(), 'rank' => 'Tài xế đường dài', 'contract_expiry' => now()->addMonths(18)->toDateString(), 'note' => 'Có kinh nghiệm chạy hàng lạnh.'],
            ['user_id' => null, 'full_name' => 'Đỗ Minh Khoa', 'phone' => '0911001004', 'license_number' => 'GPLX-C-000004', 'status' => 'inactive', 'start_date' => now()->subYear()->toDateString(), 'rank' => 'Tài xế dự phòng', 'contract_expiry' => now()->addMonths(4)->toDateString(), 'note' => 'Tạm nghỉ theo lịch cá nhân.'],
            ['user_id' => null, 'full_name' => 'Lâm Quốc Việt', 'phone' => '0911001005', 'license_number' => 'GPLX-FC-000005', 'status' => 'active', 'start_date' => now()->subMonths(15)->toDateString(), 'rank' => 'Tài xế container', 'contract_expiry' => now()->addMonths(12)->toDateString(), 'note' => 'Thông thạo tuyến Cái Mép - TP.HCM.'],
        ];

        return collect($drivers)
            ->values()
            ->map(fn (array $driver, int $index): Driver => Driver::query()->create($driver + [
                'driver_code' => $driverPrefix.str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT),
            ]))
            ->all();
    }

    /**
     * @return array<string, ServicePrice>
     */
    private function seedServicePrices(): array
    {
        $services = [
            ['package_code' => 'GOI-0001', 'service_name' => 'Vận chuyển 20DC', 'unit' => 'Chuyến', 'unit_price' => 2500000, 'is_tax_included' => false],
            ['package_code' => 'GOI-0002', 'service_name' => 'Vận chuyển 40HC', 'unit' => 'Chuyến', 'unit_price' => 3500000, 'is_tax_included' => false],
            ['package_code' => 'GOI-0003', 'service_name' => 'Nâng hạ container', 'unit' => 'Cont', 'unit_price' => 450000, 'is_tax_included' => true],
            ['package_code' => 'GOI-0004', 'service_name' => 'Lưu ca bãi', 'unit' => 'Ngày', 'unit_price' => 500000, 'is_tax_included' => false],
            ['package_code' => 'GOI-0005', 'service_name' => 'Phí cầu đường TP.HCM - Bình Dương', 'unit' => 'Chuyến', 'unit_price' => 850000, 'is_tax_included' => true],
            ['package_code' => 'GOI-0006', 'service_name' => 'Vận chuyển hàng lẻ', 'unit' => 'Chuyến', 'unit_price' => 1800000, 'is_tax_included' => false],
        ];

        return collect($services)
            ->mapWithKeys(fn (array $service): array => [
                $service['package_code'] => ServicePrice::query()->create($service),
            ])
            ->all();
    }

    private function seedSettings(): void
    {
        $settings = [
            ['key' => 'company.name', 'value' => 'Example Logistics', 'group' => 'company', 'description' => 'Tên doanh nghiệp hiển thị trên chứng từ.'],
            ['key' => 'company.tax_code', 'value' => '0312345678', 'group' => 'company', 'description' => 'Mã số thuế doanh nghiệp.'],
            ['key' => 'company.address', 'value' => 'TP. Hồ Chí Minh', 'group' => 'company', 'description' => 'Địa chỉ doanh nghiệp.'],
            ['key' => 'operation.default_currency', 'value' => 'VND', 'group' => 'operation', 'description' => 'Đơn vị tiền tệ mặc định.'],
        ];

        foreach ($settings as $setting) {
            Setting::query()->create($setting);
        }
    }

    /**
     * @param  array<int, Customer>  $customers
     * @param  array<int, Location>  $locations
     * @param  array<string, User>  $users
     * @return array<int, ShippingJob>
     */
    private function seedShippingJobs(array $customers, array $locations, array $users): array
    {
        $jobPrefix = 'JOB-'.now()->format('Ymd').'-';
        $jobs = [
            ['customer_id' => $customers[0]->id, 'customs_declaration_no' => 'TK-20260517-001', 'container_number' => 'MSKU1234567', 'pickup_location_id' => $locations[0]->id, 'delivery_location_id' => $locations[2]->id, 'cargo_type' => 'Hàng điện tử', 'container_type' => '20DC', 'expected_date' => now()->subDays(2), 'status' => 'completed', 'created_by' => $users['SALES']->id],
            ['customer_id' => $customers[1]->id, 'customs_declaration_no' => 'TK-20260517-002', 'container_number' => 'TGHU7654321', 'pickup_location_id' => $locations[1]->id, 'delivery_location_id' => $locations[3]->id, 'cargo_type' => 'Thép cuộn', 'container_type' => '40HC', 'expected_date' => now()->addDay(), 'status' => 'dispatched', 'created_by' => $users['SALES']->id],
            ['customer_id' => $customers[2]->id, 'customs_declaration_no' => 'TK-20260517-003', 'container_number' => 'CMAU2468135', 'pickup_location_id' => $locations[0]->id, 'delivery_location_id' => $locations[4]->id, 'cargo_type' => 'Nguyên liệu may mặc', 'container_type' => '20DC', 'expected_date' => now()->addDays(2), 'status' => 'dispatched', 'created_by' => $users['DOCUMENT']->id],
            ['customer_id' => $customers[3]->id, 'customs_declaration_no' => 'TK-20260517-004', 'container_number' => 'OOLU1357924', 'pickup_location_id' => $locations[6]->id, 'delivery_location_id' => $locations[0]->id, 'cargo_type' => 'Nông sản đóng bao', 'container_type' => '40DC', 'expected_date' => now()->addDays(4), 'status' => 'processing', 'created_by' => $users['FIELD']->id],
            ['customer_id' => $customers[4]->id, 'customs_declaration_no' => 'TK-20260517-005', 'container_number' => 'HLCU9876543', 'pickup_location_id' => $locations[5]->id, 'delivery_location_id' => $locations[2]->id, 'cargo_type' => 'Máy lạnh dân dụng', 'container_type' => 'LCL', 'expected_date' => now()->addDays(5), 'status' => 'new', 'created_by' => $users['SALES']->id],
            ['customer_id' => $customers[5]->id, 'customs_declaration_no' => 'TK-20260517-006', 'container_number' => 'ONEU5647382', 'pickup_location_id' => $locations[0]->id, 'delivery_location_id' => $locations[5]->id, 'cargo_type' => 'Hóa chất công nghiệp', 'container_type' => '20DC', 'expected_date' => now()->subDay(), 'status' => 'cancelled', 'created_by' => $users['ADMIN']->id],
        ];

        return collect($jobs)
            ->values()
            ->map(fn (array $job, int $index): ShippingJob => ShippingJob::query()->create($job + [
                'job_code' => $jobPrefix.str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT),
            ]))
            ->all();
    }

    /**
     * @param  array<int, ShippingJob>  $shippingJobs
     * @param  array<int, Vehicle>  $vehicles
     * @param  array<int, Driver>  $drivers
     * @param  array<string, User>  $users
     * @return array<int, DispatchOrder>
     */
    private function seedDispatchOrders(array $shippingJobs, array $vehicles, array $drivers, array $users): array
    {
        $orderPrefix = 'DO-'.now()->format('ymd').'-';
        $orders = [
            ['shipping_job_id' => $shippingJobs[0]->id, 'vehicle_id' => $vehicles[0]->id, 'driver_id' => $drivers[0]->id, 'dispatch_status' => 'completed', 'note' => 'Đã hoàn tất giao hàng và nhận biên bản.', 'start_location_id' => $shippingJobs[0]->pickup_location_id, 'end_location_id' => $shippingJobs[0]->delivery_location_id, 'loading_percent' => 100, 'current_latitude' => 10.7281000, 'current_longitude' => 106.7214000, 'start_time' => now()->subDays(2)->setTime(8, 30), 'end_time' => now()->subDays(2)->setTime(15, 45), 'fuel_quota' => 52.5, 'toll_quota' => 650000, 'created_by' => $users['DISPATCH']->id],
            ['shipping_job_id' => $shippingJobs[1]->id, 'vehicle_id' => $vehicles[3]->id, 'driver_id' => $drivers[1]->id, 'dispatch_status' => 'on_way', 'note' => 'Xe đã rời ICD, đang về nhà máy.', 'start_location_id' => $shippingJobs[1]->pickup_location_id, 'end_location_id' => $shippingJobs[1]->delivery_location_id, 'loading_percent' => 65, 'current_latitude' => 10.9092000, 'current_longitude' => 106.7429000, 'start_time' => now()->subHours(4), 'end_time' => null, 'fuel_quota' => 46.0, 'toll_quota' => 520000, 'created_by' => $users['DISPATCH']->id],
            ['shipping_job_id' => $shippingJobs[2]->id, 'vehicle_id' => $vehicles[1]->id, 'driver_id' => $drivers[2]->id, 'dispatch_status' => 'dispatched', 'note' => 'Đã phân xe, chờ xác nhận lấy cont.', 'start_location_id' => $shippingJobs[2]->pickup_location_id, 'end_location_id' => $shippingJobs[2]->delivery_location_id, 'loading_percent' => 15, 'current_latitude' => 10.7702000, 'current_longitude' => 106.7850000, 'start_time' => now()->subHour(), 'end_time' => null, 'fuel_quota' => 38.0, 'toll_quota' => 430000, 'created_by' => $users['DISPATCH']->id],
        ];

        return collect($orders)
            ->values()
            ->map(fn (array $order, int $index): DispatchOrder => DispatchOrder::query()->create($order + [
                'order_number' => $orderPrefix.str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT),
            ]))
            ->all();
    }

    /**
     * @param  array<int, ShippingJob>  $shippingJobs
     * @param  array<string, User>  $users
     * @return array<int, Document>
     */
    private function seedDocuments(array $shippingJobs, array $users): array
    {
        $documents = [
            ['shipping_job_id' => $shippingJobs[0]->id, 'doc_category' => 'Tờ khai hải quan', 'document_flow' => 'input', 'tax_stage' => 'before_tax', 'file_url' => 'documents/seed/job-001/to-khai.pdf', 'uploaded_by' => $users['DOCUMENT']->id, 'status' => 'active', 'note' => 'Bản scan tờ khai trước thuế.'],
            ['shipping_job_id' => $shippingJobs[0]->id, 'doc_category' => 'Biên bản giao nhận', 'document_flow' => 'output', 'tax_stage' => 'after_tax', 'file_url' => 'documents/seed/job-001/bien-ban-giao-nhan.pdf', 'uploaded_by' => $users['FIELD']->id, 'status' => 'active', 'note' => 'Đã có chữ ký khách hàng.'],
            ['shipping_job_id' => $shippingJobs[1]->id, 'doc_category' => 'Phiếu nâng hạ', 'document_flow' => 'input', 'tax_stage' => 'before_tax', 'file_url' => 'documents/seed/job-002/phieu-nang-ha.pdf', 'uploaded_by' => $users['DOCUMENT']->id, 'status' => 'pending', 'note' => 'Chờ bổ sung bản gốc.'],
            ['shipping_job_id' => $shippingJobs[2]->id, 'doc_category' => 'Hóa đơn đầu vào', 'document_flow' => 'input', 'tax_stage' => 'after_tax', 'file_url' => 'documents/seed/job-003/hoa-don-dau-vao.pdf', 'uploaded_by' => $users['ACCOUNTANT']->id, 'status' => 'active', 'note' => 'Phục vụ đối chiếu chi phí.'],
            ['shipping_job_id' => $shippingJobs[3]->id, 'doc_category' => 'Booking note', 'document_flow' => 'output', 'tax_stage' => 'before_tax', 'file_url' => 'documents/seed/job-004/booking-note.pdf', 'uploaded_by' => $users['SALES']->id, 'status' => 'active', 'note' => 'Booking xuất cảng.'],
        ];

        return collect($documents)
            ->map(fn (array $document): Document => Document::query()->create($document))
            ->all();
    }

    /**
     * @param  array<int, DispatchOrder>  $dispatchOrders
     * @param  array<string, User>  $users
     */
    private function seedTrackingLogs(array $dispatchOrders, array $users): void
    {
        $logs = [
            ['dispatch_order_id' => $dispatchOrders[0]->id, 'status_update' => 'dispatched', 'updated_by' => $users['DISPATCH']->id, 'created_at' => now()->subDays(2)->setTime(8, 15), 'updated_at' => now()->subDays(2)->setTime(8, 15)],
            ['dispatch_order_id' => $dispatchOrders[0]->id, 'status_update' => 'on_way', 'updated_by' => $users['DRIVER']->id, 'created_at' => now()->subDays(2)->setTime(8, 45), 'updated_at' => now()->subDays(2)->setTime(8, 45)],
            ['dispatch_order_id' => $dispatchOrders[0]->id, 'status_update' => 'completed', 'updated_by' => $users['DRIVER']->id, 'created_at' => now()->subDays(2)->setTime(15, 45), 'updated_at' => now()->subDays(2)->setTime(15, 45)],
            ['dispatch_order_id' => $dispatchOrders[1]->id, 'status_update' => 'dispatched', 'updated_by' => $users['DISPATCH']->id, 'created_at' => now()->subHours(5), 'updated_at' => now()->subHours(5)],
            ['dispatch_order_id' => $dispatchOrders[1]->id, 'status_update' => 'on_way', 'updated_by' => $users['DRIVER']->id, 'created_at' => now()->subHours(4), 'updated_at' => now()->subHours(4)],
            ['dispatch_order_id' => $dispatchOrders[2]->id, 'status_update' => 'dispatched', 'updated_by' => $users['DISPATCH']->id, 'created_at' => now()->subHour(), 'updated_at' => now()->subHour()],
        ];

        foreach ($logs as $log) {
            TrackingLog::query()->create($log);
        }
    }

    /**
     * @param  array<int, ShippingJob>  $shippingJobs
     * @param  array<int, DispatchOrder>  $dispatchOrders
     * @param  array<int, Document>  $documents
     * @param  array<string, User>  $users
     */
    private function seedExpenses(array $shippingJobs, array $dispatchOrders, array $documents, array $users): void
    {
        $expenses = [
            ['shipping_job_id' => $shippingJobs[0]->id, 'dispatch_order_id' => $dispatchOrders[0]->id, 'expense_type' => 'Phí nâng hạ', 'amount' => 450000, 'note' => 'Nâng hạ cont tại Cát Lái.', 'document_id' => $documents[0]->id, 'reported_by' => $users['FIELD']->id, 'status' => 'approved'],
            ['shipping_job_id' => $shippingJobs[0]->id, 'dispatch_order_id' => $dispatchOrders[0]->id, 'expense_type' => 'Phí cầu đường', 'amount' => 650000, 'note' => 'Theo quota tuyến nội thành.', 'document_id' => $documents[1]->id, 'reported_by' => $users['DRIVER']->id, 'status' => 'approved'],
            ['shipping_job_id' => $shippingJobs[1]->id, 'dispatch_order_id' => $dispatchOrders[1]->id, 'expense_type' => 'Lưu bãi', 'amount' => 500000, 'note' => 'Phát sinh một ngày lưu bãi.', 'document_id' => $documents[2]->id, 'reported_by' => $users['FIELD']->id, 'status' => 'pending'],
            ['shipping_job_id' => $shippingJobs[2]->id, 'dispatch_order_id' => $dispatchOrders[2]->id, 'expense_type' => 'Phí vệ sinh container', 'amount' => 300000, 'note' => 'Đã được kế toán duyệt.', 'document_id' => $documents[3]->id, 'reported_by' => $users['ACCOUNTANT']->id, 'status' => 'approved'],
            ['shipping_job_id' => $shippingJobs[3]->id, 'dispatch_order_id' => null, 'expense_type' => 'Phí booking', 'amount' => 250000, 'note' => 'Chi phí dự kiến, chưa điều xe.', 'document_id' => $documents[4]->id, 'reported_by' => $users['SALES']->id, 'status' => 'rejected'],
        ];

        foreach ($expenses as $expense) {
            Expense::query()->create($expense);
        }
    }

    /**
     * @param  array<int, ShippingJob>  $shippingJobs
     * @param  array<int, DispatchOrder>  $dispatchOrders
     * @param  array<string, User>  $users
     */
    private function seedCashAdvances(array $shippingJobs, array $dispatchOrders, array $users): void
    {
        $advances = [
            ['shipping_job_id' => $shippingJobs[0]->id, 'dispatch_order_id' => $dispatchOrders[0]->id, 'requested_by' => $users['DRIVER']->id, 'approved_by' => $users['ACCOUNTANT']->id, 'amount' => 1200000, 'reason' => 'Tạm ứng dầu và phí cầu đường.', 'status' => 'settled'],
            ['shipping_job_id' => $shippingJobs[1]->id, 'dispatch_order_id' => $dispatchOrders[1]->id, 'requested_by' => $users['DISPATCH']->id, 'approved_by' => $users['ACCOUNTANT']->id, 'amount' => 1000000, 'reason' => 'Tạm ứng chi phí phát sinh tuyến Bình Dương.', 'status' => 'approved'],
            ['shipping_job_id' => $shippingJobs[2]->id, 'dispatch_order_id' => $dispatchOrders[2]->id, 'requested_by' => $users['FIELD']->id, 'approved_by' => null, 'amount' => 800000, 'reason' => 'Chờ duyệt tạm ứng lấy cont.', 'status' => 'pending'],
        ];

        foreach ($advances as $advance) {
            CashAdvance::query()->create($advance);
        }
    }

    /**
     * @param  array<int, ShippingJob>  $shippingJobs
     * @param  array<string, ServicePrice>  $servicePrices
     * @return array<int, DebitNote>
     */
    private function seedDebitNotes(array $shippingJobs, array $servicePrices): array
    {
        $notePrefix = 'DN-'.now()->format('ymd').'-';
        $jobsForDebitNotes = [
            ['job' => $shippingJobs[0], 'service_price' => $servicePrices['GOI-0001'], 'status' => 'paid'],
            ['job' => $shippingJobs[1], 'service_price' => $servicePrices['GOI-0002'], 'status' => 'partial'],
            ['job' => $shippingJobs[2], 'service_price' => $servicePrices['GOI-0001'], 'status' => 'unpaid'],
        ];

        return collect($jobsForDebitNotes)
            ->values()
            ->map(function (array $data, int $index) use ($notePrefix): DebitNote {
                /** @var ShippingJob $job */
                $job = $data['job'];
                /** @var ServicePrice $servicePrice */
                $servicePrice = $data['service_price'];
                $approvedExpenses = $job->expenses()->where('status', 'approved')->sum('amount');

                return DebitNote::query()->create([
                    'note_number' => $notePrefix.str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT),
                    'shipping_job_id' => $job->id,
                    'customer_id' => $job->customer_id,
                    'total_service_fee' => $servicePrice->unit_price,
                    'total_expense_paid' => $approvedExpenses,
                    'grand_total' => $servicePrice->unit_price + $approvedExpenses,
                    'issued_at' => now()->subDays(2 - $index)->toDateString(),
                    'status' => $data['status'],
                ]);
            })
            ->all();
    }

    /**
     * @param  array<int, DebitNote>  $debitNotes
     * @param  array<string, User>  $users
     */
    private function seedPayments(array $debitNotes, array $users): void
    {
        $payments = [
            ['debit_note_id' => $debitNotes[0]->id, 'amount_paid' => $debitNotes[0]->grand_total, 'payment_method' => 'Chuyển khoản', 'payment_date' => now()->subDay(), 'received_by' => $users['ACCOUNTANT']->id, 'reference_no' => 'VCB-20260517-0001', 'note' => 'Khách hàng thanh toán đủ công nợ.'],
            ['debit_note_id' => $debitNotes[1]->id, 'amount_paid' => 1500000, 'payment_method' => 'Tiền mặt', 'payment_date' => now(), 'received_by' => $users['ACCOUNTANT']->id, 'reference_no' => 'PT-20260517-0002', 'note' => 'Thanh toán một phần, còn chờ đối soát.'],
        ];

        foreach ($payments as $payment) {
            Payment::query()->create($payment);
        }
    }
}
