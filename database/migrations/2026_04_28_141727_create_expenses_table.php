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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipping_job_id')->constrained('shipping_jobs');
            $table->foreignId('dispatch_order_id')->nullable()->constrained('dispatch_orders');
            $table->string('expense_type', 100);
            $table->decimal('amount', 15, 2);
            $table->string('note')->nullable();
            $table->foreignId('document_id')->nullable()->constrained('documents');
            $table->foreignId('reported_by')->constrained('users');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
