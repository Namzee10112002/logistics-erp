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
        Schema::table('drivers', function (Blueprint $table) {
            if (Schema::hasColumn('drivers', 'license_class')) {
                $table->renameColumn('license_class', 'license_number');
            }
            $table->string('license_number', 50)->change();
            $table->string('status', 50)->default('active')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->renameColumn('license_number', 'license_class');
            $table->enum('status', ['Rảnh', 'Đang chạy', 'Nghỉ'])->default('Rảnh')->change();
        });
    }
};
