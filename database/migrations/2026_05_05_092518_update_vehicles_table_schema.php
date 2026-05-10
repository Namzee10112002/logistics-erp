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
        Schema::table('vehicles', function (Blueprint $table) {
            if (Schema::hasColumn('vehicles', 'license_plate')) {
                $table->renameColumn('license_plate', 'plate_number');
            }
            $table->string('vehicle_type', 100)->change();
            $table->string('status', 50)->default('available')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->renameColumn('plate_number', 'license_plate');
            $table->enum('vehicle_type', ['Đầu kéo', 'Mooc', 'Xe tải'])->change();
            $table->enum('status', ['Rảnh', 'Đang chạy', 'Bảo trì'])->default('Rảnh')->change();
        });
    }
};
