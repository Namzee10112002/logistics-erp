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
        Schema::create('debit_notes', function (Blueprint $table) {
            $table->id();
            $table->string('note_number', 50)->unique();
            $table->foreignId('shipping_job_id')->constrained('shipping_jobs');
            $table->foreignId('customer_id')->constrained('customers');
            $table->decimal('total_service_fee', 15, 2)->default(0);
            $table->decimal('total_expense_paid', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);
            $table->date('issued_at');
            $table->enum('status', ['unpaid', 'partial', 'paid'])->default('unpaid');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('debit_notes');
    }
};
