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
        Schema::table('dispatch_orders', function (Blueprint $table) {
            $table->foreignId('start_location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->foreignId('end_location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->unsignedTinyInteger('loading_percent')->default(0);
            $table->decimal('current_latitude', 10, 7)->nullable();
            $table->decimal('current_longitude', 10, 7)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dispatch_orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('start_location_id');
            $table->dropConstrainedForeignId('end_location_id');
            $table->dropColumn(['loading_percent', 'current_latitude', 'current_longitude']);
        });
    }
};
