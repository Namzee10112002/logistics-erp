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
            $table->string('location_code', 50)->nullable()->unique()->after('id');
            $table->string('status', 50)->default('active')->after('province');
            $table->text('note')->nullable()->after('status');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->date('date_of_birth')->nullable()->after('department');
            $table->string('timezone', 64)->default('Asia/Ho_Chi_Minh')->after('is_dark_mode');
            $table->string('date_format', 20)->default('d/m/Y')->after('timezone');
            $table->boolean('two_factor_enabled')->default(false)->after('date_format');
        });

        Schema::table('drivers', function (Blueprint $table) {
            $table->date('date_of_birth')->nullable()->after('phone');
        });

        Schema::table('field_staff', function (Blueprint $table) {
            $table->date('date_of_birth')->nullable()->after('phone');
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->string('document_code', 50)->nullable()->unique()->after('id');
        });

        Schema::table('dispatch_orders', function (Blueprint $table) {
            $table->foreignId('trailer_id')->nullable()->after('vehicle_id')->constrained('vehicles')->nullOnDelete();
            $table->date('planned_departure_date')->nullable()->after('end_location_id');
            $table->date('planned_return_date')->nullable()->after('planned_departure_date');
            $table->decimal('fuel_price_quota', 15, 2)->nullable()->after('fuel_quota');
            $table->decimal('actual_fuel_liters', 10, 2)->nullable()->after('fuel_price_quota');
            $table->string('approval_status', 50)->default('pending')->after('dispatch_status');
            $table->foreignId('approved_by')->nullable()->after('approval_status')->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->foreignId('rejected_by')->nullable()->after('approved_at')->constrained('users')->nullOnDelete();
            $table->timestamp('rejected_at')->nullable()->after('rejected_by');
            $table->text('rejection_reason')->nullable()->after('rejected_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dispatch_orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('trailer_id');
            $table->dropConstrainedForeignId('approved_by');
            $table->dropConstrainedForeignId('rejected_by');
            $table->dropColumn([
                'planned_departure_date',
                'planned_return_date',
                'fuel_price_quota',
                'actual_fuel_liters',
                'approval_status',
                'approved_at',
                'rejected_at',
                'rejection_reason',
            ]);
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn('document_code');
        });

        Schema::table('field_staff', function (Blueprint $table) {
            $table->dropColumn('date_of_birth');
        });

        Schema::table('drivers', function (Blueprint $table) {
            $table->dropColumn('date_of_birth');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['date_of_birth', 'timezone', 'date_format', 'two_factor_enabled']);
        });

        Schema::table('locations', function (Blueprint $table) {
            $table->dropColumn(['location_code', 'status', 'note']);
        });
    }
};
