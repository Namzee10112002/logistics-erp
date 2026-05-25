<?php

namespace Tests\Feature;

use App\Models\Location;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FieldStaffManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_field_staff_management(): void
    {
        $admin = $this->userWithRole('ADMIN');

        $this->actingAs($admin)
            ->get(route('field-staff.index'))
            ->assertOk()
            ->assertSee('Quản lý Nhân viên hiện trường');
    }

    public function test_dispatch_can_create_field_staff(): void
    {
        $dispatch = $this->userWithRole('DISPATCH');
        $fieldUser = $this->userWithRole('FIELD', ['email' => 'field.staff@example.test']);
        $location = Location::factory()->create([
            'type' => 'warehouse',
        ]);

        $response = $this->actingAs($dispatch)
            ->post(route('field-staff.store'), [
                'user_id' => $fieldUser->id,
                'full_name' => 'Nguyễn Văn Hiện Trường',
                'phone' => '0909009009',
                'date_of_birth' => '1995-05-21',
                'certificates' => 'Chứng chỉ an toàn kho bãi',
                'responsible_location_id' => $location->id,
                'start_date' => '2026-05-18',
                'status' => 'active',
                'note' => 'Phụ trách kho trung tâm.',
            ]);

        $response->assertRedirect(route('field-staff.index'));

        $this->assertDatabaseHas('field_staff', [
            'user_id' => $fieldUser->id,
            'full_name' => 'Nguyễn Văn Hiện Trường',
            'responsible_location_id' => $location->id,
            'status' => 'active',
        ]);
    }

    public function test_non_admin_or_dispatch_cannot_manage_field_staff(): void
    {
        $accountant = $this->userWithRole('ACCOUNTANT');

        $this->actingAs($accountant)
            ->get(route('field-staff.index'))
            ->assertRedirect(route('dashboard'));
    }

    public function test_database_seeder_creates_linked_field_staff_records(): void
    {
        $this->seed();

        $this->assertDatabaseCount('field_staff', 3);
        $this->assertDatabaseMissing('field_staff', [
            'user_id' => null,
        ]);
    }

    public function test_field_staff_must_use_depot_or_warehouse_location(): void
    {
        $admin = $this->userWithRole('ADMIN');
        $port = Location::factory()->create([
            'type' => 'port',
        ]);

        $this->actingAs($admin)
            ->post(route('field-staff.store'), [
                'full_name' => 'Nhân viên cảng',
                'phone' => '0909009009',
                'date_of_birth' => '1995-05-21',
                'responsible_location_id' => $port->id,
                'status' => 'active',
            ])
            ->assertSessionHasErrors('responsible_location_id');

        $this->assertDatabaseCount('field_staff', 0);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function userWithRole(string $roleCode, array $attributes = []): User
    {
        $role = Role::factory()->create([
            'role_code' => $roleCode,
            'role_name' => $roleCode,
        ]);

        return User::factory()->create($attributes + [
            'role_id' => $role->id,
        ]);
    }
}
