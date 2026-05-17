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
        Schema::table('service_prices', function (Blueprint $table) {
            $table->string('package_code', 50)->nullable()->unique();
            $table->boolean('is_tax_included')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_prices', function (Blueprint $table) {
            $table->dropColumn(['package_code', 'is_tax_included']);
        });
    }
};
