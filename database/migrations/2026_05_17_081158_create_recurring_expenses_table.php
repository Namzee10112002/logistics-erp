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
        Schema::create('recurring_expenses', function (Blueprint $table) {
            $table->id();
            $table->string('expense_code', 50)->unique();
            $table->string('name');
            $table->string('category', 100)->nullable();
            $table->decimal('amount', 15, 2);
            $table->string('cycle')->default('monthly');
            $table->date('effective_from')->nullable();
            $table->date('effective_to')->nullable();
            $table->string('status', 20)->default('active');
            $table->text('note')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recurring_expenses');
    }
};
