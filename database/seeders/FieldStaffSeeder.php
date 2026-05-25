<?php

namespace Database\Seeders;

use App\Models\FieldStaff;
use App\Models\Location;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class FieldStaffSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $fieldRole = Role::query()->where('role_code', 'FIELD')->first();

        if (! $fieldRole) {
            return;
        }

        $locations = $this->responsibleLocations();

        $users = [
            User::updateOrCreate(
                ['username' => 'field_test'],
                [
                    'full_name' => 'Đặng Hải Nam',
                    'name' => 'Đặng Hải Nam',
                    'email' => 'field@example.test',
                    'password' => Hash::make('password'),
                    'role_id' => $fieldRole->id,
                    'status' => 1,
                    'employee_code' => 'NV-'.now()->format('ym').'-006',
                    'position' => 'Nhân viên hiện trường',
                    'department' => 'Hiện trường',
                    'date_of_birth' => now()->subYears(28)->toDateString(),
                    'joined_at' => now()->subMonths(13)->toDateString(),
                    'theme_color' => '#166534',
                    'is_dark_mode' => false,
                ]
            ),
            User::updateOrCreate(
                ['username' => 'field_kho_test'],
                [
                    'full_name' => 'Mai Gia Huy',
                    'name' => 'Mai Gia Huy',
                    'email' => 'field.kho@example.test',
                    'password' => Hash::make('password'),
                    'role_id' => $fieldRole->id,
                    'status' => 1,
                    'employee_code' => 'NV-'.now()->format('ym').'-008',
                    'position' => 'Giám sát kho',
                    'department' => 'Hiện trường',
                    'date_of_birth' => now()->subYears(31)->toDateString(),
                    'joined_at' => now()->subMonths(9)->toDateString(),
                    'theme_color' => '#166534',
                    'is_dark_mode' => false,
                ]
            ),
            User::updateOrCreate(
                ['username' => 'field_bai_test'],
                [
                    'full_name' => 'Tạ Minh Châu',
                    'name' => 'Tạ Minh Châu',
                    'email' => 'field.bai@example.test',
                    'password' => Hash::make('password'),
                    'role_id' => $fieldRole->id,
                    'status' => 1,
                    'employee_code' => 'NV-'.now()->format('ym').'-009',
                    'position' => 'Điều phối bãi',
                    'department' => 'Hiện trường',
                    'date_of_birth' => now()->subYears(29)->toDateString(),
                    'joined_at' => now()->subMonths(6)->toDateString(),
                    'theme_color' => '#166534',
                    'is_dark_mode' => false,
                ]
            ),
        ];

        $staff = [
            [
                'staff_code' => 'HT-'.now()->format('ym').'-001',
                'user_id' => $users[0]->id,
                'full_name' => $users[0]->name,
                'phone' => '0922001001',
                'date_of_birth' => now()->subYears(28)->toDateString(),
                'certificates' => 'Chứng chỉ an toàn kho bãi; Chứng chỉ nghiệp vụ hải quan cơ bản',
                'responsible_location_id' => $locations[0]->id,
                'start_date' => now()->subMonths(13)->toDateString(),
                'status' => 'active',
                'note' => 'Phụ trách kiểm tra chứng từ và xác nhận hiện trường tại kho.',
            ],
            [
                'staff_code' => 'HT-'.now()->format('ym').'-002',
                'user_id' => $users[1]->id,
                'full_name' => $users[1]->name,
                'phone' => '0922001002',
                'date_of_birth' => now()->subYears(31)->toDateString(),
                'certificates' => 'Chứng chỉ PCCC cơ bản; Chứng chỉ vận hành kho',
                'responsible_location_id' => $locations[1]->id,
                'start_date' => now()->subMonths(9)->toDateString(),
                'status' => 'active',
                'note' => 'Theo dõi nhập xuất và phối hợp tài xế tại khu vực kho.',
            ],
            [
                'staff_code' => 'HT-'.now()->format('ym').'-003',
                'user_id' => $users[2]->id,
                'full_name' => $users[2]->name,
                'phone' => '0922001003',
                'date_of_birth' => now()->subYears(29)->toDateString(),
                'certificates' => 'Chứng chỉ an toàn bãi container',
                'responsible_location_id' => $locations[2]->id,
                'start_date' => now()->subMonths(6)->toDateString(),
                'status' => 'inactive',
                'note' => 'Tạm ngưng phân công trong tháng này.',
            ],
        ];

        foreach ($staff as $staffMember) {
            FieldStaff::updateOrCreate(
                ['staff_code' => $staffMember['staff_code']],
                $staffMember
            );
        }
    }

    /**
     * @return array<int, Location>
     */
    private function responsibleLocations(): array
    {
        $locations = Location::query()
            ->whereIn('type', ['depot', 'warehouse'])
            ->orderBy('id')
            ->get();

        if ($locations->count() >= 3) {
            return $locations->take(3)->values()->all();
        }

        $fallbackLocations = [
            ['location_code' => 'WH-901', 'location_name' => 'Kho Hiện Trường Trung Tâm', 'type' => 'warehouse', 'address' => 'Khu logistics trung tâm', 'province' => 'Hải Phòng', 'status' => 'active'],
            ['location_code' => 'DEPOT-901', 'location_name' => 'Bãi Điều Phối Đông Nam', 'type' => 'depot', 'address' => 'Khu bãi điều phối Đông Nam', 'province' => 'Hải Dương', 'status' => 'active'],
            ['location_code' => 'WH-902', 'location_name' => 'Kho Trung Chuyển Cái Mép', 'type' => 'warehouse', 'address' => 'Khu cảng Cái Mép', 'province' => 'Quảng Ninh', 'status' => 'active'],
        ];

        foreach ($fallbackLocations as $fallbackLocation) {
            Location::firstOrCreate(
                ['location_name' => $fallbackLocation['location_name']],
                $fallbackLocation
            );
        }

        return Location::query()
            ->whereIn('type', ['depot', 'warehouse'])
            ->orderBy('id')
            ->take(3)
            ->get()
            ->all();
    }
}
