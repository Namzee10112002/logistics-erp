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
            $table->string('driver_code', 50)->nullable()->unique();
            $table->date('start_date')->nullable();
            $table->string('rank', 100)->nullable();
            $table->date('contract_expiry')->nullable();
            $table->text('note')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->dropColumn(['driver_code', 'start_date', 'rank', 'contract_expiry', 'note']);
        });
    }
};
