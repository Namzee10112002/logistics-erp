<?php

namespace Tests\Feature;

use App\Models\DispatchOrder;
use App\Models\FieldAssignment;
use App\Models\FieldStaff;
use App\Models\Location;
use App\Models\Role;
use App\Models\ShippingJob;
use App\Models\User;
use App\Support\LogisticsOptions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CustomerFeedbackWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_dark_mode_can_be_enabled_and_disabled(): void
    {
        $user = $this->userWithRole('ADMIN', [
            'email' => 'admin@example.test',
            'is_dark_mode' => false,
        ]);

        $this->actingAs($user)
            ->post(route('profile.update'), [
                'name' => 'Admin User',
                'email' => 'admin@example.test',
                'theme_color' => '#1a237e',
                'is_dark_mode' => '1',
                'timezone' => 'Asia/Ho_Chi_Minh',
                'date_format' => 'd/m/Y',
                'two_factor_enabled' => '0',
            ])
            ->assertSessionHasNoErrors();

        $this->assertTrue($user->fresh()->is_dark_mode);

        $this->actingAs($user->fresh())
            ->post(route('profile.update'), [
                'name' => 'Admin User',
                'email' => 'admin@example.test',
                'theme_color' => '#1a237e',
                'is_dark_mode' => '0',
                'timezone' => 'Asia/Ho_Chi_Minh',
                'date_format' => 'd/m/Y',
                'two_factor_enabled' => '0',
            ])
            ->assertSessionHasNoErrors();

        $this->assertFalse($user->fresh()->is_dark_mode);
    }

    public function test_customer_tax_code_phone_and_contact_role_are_validated(): void
    {
        $sales = $this->userWithRole('SALES');

        $this->actingAs($sales)
            ->post(route('customers.store'), [
                'customer_name' => 'Khách hàng kiểm thử',
                'company_name' => 'Công ty kiểm thử',
                'tax_code' => 'ABC123',
                'address' => 'Hải Phòng',
                'phone' => '123456789',
                'email' => 'customer@example.test',
                'contact_person' => 'Giám đốc',
            ])
            ->assertSessionHasErrors(['tax_code', 'phone', 'contact_person']);
    }

    public function test_accountant_approval_creates_internal_dispatch_document_and_updates_job(): void
    {
        $accountant = $this->userWithRole('ACCOUNTANT');
        $job = ShippingJob::factory()->create(['status' => 'new']);
        $dispatchOrder = DispatchOrder::factory()->create([
            'shipping_job_id' => $job->id,
            'approval_status' => 'pending',
            'approved_by' => null,
            'approved_at' => null,
        ]);

        $this->actingAs($accountant)
            ->post(route('dispatch-orders.approve', $dispatchOrder))
            ->assertRedirect();

        $this->assertDatabaseHas('dispatch_orders', [
            'id' => $dispatchOrder->id,
            'approval_status' => 'approved',
            'approved_by' => $accountant->id,
        ]);

        $this->assertDatabaseHas('shipping_jobs', [
            'id' => $job->id,
            'status' => 'dispatched',
        ]);

        $this->assertDatabaseHas('documents', [
            'shipping_job_id' => $job->id,
            'doc_category' => 'Lệnh điều xe',
            'file_url' => 'internal://dispatch-order/'.$dispatchOrder->id,
        ]);
    }

    public function test_field_staff_can_upload_documents_only_for_active_assignment(): void
    {
        Storage::fake('public');

        $fieldUser = $this->userWithRole('FIELD');
        $location = Location::factory()->create();
        $fieldStaff = FieldStaff::factory()->create([
            'user_id' => $fieldUser->id,
            'responsible_location_id' => $location->id,
            'status' => 'active',
        ]);
        $job = ShippingJob::factory()->create();

        $this->actingAs($fieldUser)
            ->post(route('documents.store'), $this->documentPayload($job))
            ->assertSessionHas('error');

        $this->assertDatabaseCount('documents', 0);

        FieldAssignment::factory()->create([
            'shipping_job_id' => $job->id,
            'field_staff_id' => $fieldStaff->id,
            'location_id' => $location->id,
            'created_by' => $this->userWithRole('DISPATCH')->id,
            'tasks' => [array_key_first(LogisticsOptions::fieldAssignmentTasks())],
            'status' => 'assigned',
        ]);

        $this->actingAs($fieldUser)
            ->post(route('documents.store'), $this->documentPayload($job, 'document-2.pdf'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('documents', [
            'shipping_job_id' => $job->id,
            'doc_category' => 'Biên bản hiện trường',
            'uploaded_by' => $fieldUser->id,
        ]);
    }

    public function test_list_exports_use_distinct_file_templates(): void
    {
        $sales = $this->userWithRole('SALES');

        foreach (['xlsx', 'docx', 'pdf'] as $format) {
            $response = $this->actingAs($sales)
                ->get(route('customers.index', ['export' => $format]));

            $response->assertOk();

            $content = $response->streamedContent();

            $this->assertNotEmpty($content);

            if ($format === 'xlsx') {
                $this->assertStringContainsString('PK', substr($content, 0, 2));
                $this->assertStringContainsString('xl/worksheets/sheet1.xml', $content);
            }

            if ($format === 'docx') {
                $this->assertStringContainsString('PK', substr($content, 0, 2));
                $this->assertStringContainsString('word/document.xml', $content);
            }

            if ($format === 'pdf') {
                $this->assertStringStartsWith('%PDF', $content);
                $this->assertStringContainsString('Nhan vien xuat', $content);
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function documentPayload(ShippingJob $job, string $filename = 'document.pdf'): array
    {
        return [
            'shipping_job_id' => $job->id,
            'doc_category' => 'Biên bản hiện trường',
            'document_flow' => 'input',
            'tax_stage' => 'before_tax',
            'file' => UploadedFile::fake()->create($filename, 12, 'application/pdf'),
        ];
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
