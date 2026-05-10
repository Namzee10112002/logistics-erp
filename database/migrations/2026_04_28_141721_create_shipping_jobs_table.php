<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('shipping_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('job_code', 50)->unique();
            $table->foreignId('customer_id')->constrained('customers');
            $table->string('customs_declaration_no', 100)->nullable();
            $table->string('container_number', 50)->nullable();
            $table->foreignId('pickup_location_id')->constrained('locations');
            $table->foreignId('delivery_location_id')->constrained('locations');
            $table->string('cargo_type', 100)->nullable();
            $table->string('container_type', 50)->nullable();
            $table->dateTime('expected_date')->nullable();
            $table->enum('status', ['new', 'processing', 'dispatched', 'completed', 'cancelled'])->default('new');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_jobs');
    }
};
