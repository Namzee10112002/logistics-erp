<?php

namespace Database\Factories;

use App\Models\Document;
use App\Models\ShippingJob;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Document>
 */
class DocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'shipping_job_id' => ShippingJob::factory(),
            'doc_category' => fake()->randomElement(['Tờ khai hải quan', 'Phiếu nâng hạ', 'Hóa đơn đầu vào', 'Biên bản giao nhận']),
            'document_flow' => fake()->randomElement(['input', 'output']),
            'tax_stage' => fake()->randomElement(['before_tax', 'after_tax']),
            'file_url' => 'documents/seed/'.fake()->uuid().'.pdf',
            'uploaded_by' => User::factory(),
            'status' => fake()->randomElement(['active', 'inactive', 'pending']),
            'note' => fake()->optional()->sentence(),
        ];
    }
}
