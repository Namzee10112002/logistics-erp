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
        Schema::table('locations', function (Blueprint $table) {
            if (Schema::hasColumn('locations', 'location_type')) {
                $table->dropColumn('location_type');
            }
            $table->string('type', 50)->after('location_name');
            $table->string('province', 100)->after('address')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->dropColumn(['type', 'province']);
            $table->enum('location_type', ['Cảng', 'ICD', 'Kho bãi', 'Cửa khẩu']);
        });
    }
};
