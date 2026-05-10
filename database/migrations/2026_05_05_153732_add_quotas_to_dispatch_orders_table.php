<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dispatch_orders', function (Blueprint $table) {
            $table->decimal('fuel_quota', 10, 2)->nullable()->comment('Định mức nhiên liệu (Lít)');
            $table->decimal('toll_quota', 15, 2)->nullable()->comment('Định mức phí cầu đường (VNĐ)');
        });
    }

    public function down(): void
    {
        Schema::table('dispatch_orders', function (Blueprint $table) {
            $table->dropColumn(['fuel_quota', 'toll_quota']);
        });
    }
};
