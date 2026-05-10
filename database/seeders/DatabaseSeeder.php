<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Driver;
use App\Models\Location;
use App\Models\Role;
use App\Models\ServicePrice;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Roles
        $this->call([
            RoleSeeder::class,
        ]);

        // 2. Create Users for each role
        $roles = Role::all();
        foreach ($roles as $role) {
            User::factory()->create([
                'username' => strtolower($role->role_code).'_test',
                'role_id' => $role->id,
            ]);
        }

        // 3. Create dummy master data
        Customer::factory(10)->create();
        Vehicle::factory(5)->create();
        Driver::factory(5)->create();
        Location::factory(5)->create();

        $services = [
            ['service_name' => 'Vận chuyển 20DC', 'unit' => 'Chuyến', 'unit_price' => 2500000],
            ['service_name' => 'Vận chuyển 40HC', 'unit' => 'Chuyến', 'unit_price' => 3500000],
            ['service_name' => 'Lưu ca bãi', 'unit' => 'Ngày', 'unit_price' => 500000],
        ];

        foreach ($services as $service) {
            ServicePrice::create($service);
        }
    }
}
