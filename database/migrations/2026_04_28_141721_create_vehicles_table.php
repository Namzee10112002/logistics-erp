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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('license_plate', 20)->unique();
            $table->enum('vehicle_type', ['Đầu kéo', 'Mooc', 'Xe tải']);
            $table->decimal('payload', 10, 2)->nullable();
            $table->date('registration_expiry')->nullable();
            $table->enum('status', ['Rảnh', 'Đang chạy', 'Bảo trì'])->default('Rảnh');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
