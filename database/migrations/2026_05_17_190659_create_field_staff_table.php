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
        Schema::create('field_staff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->string('staff_code', 50)->unique();
            $table->string('full_name', 100);
            $table->string('phone', 20)->nullable();
            $table->text('certificates')->nullable();
            $table->foreignId('responsible_location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->date('start_date')->nullable();
            $table->string('status', 50)->default('active');
            $table->text('note')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('field_staff');
    }
};
