<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('financial_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('financial_movement_group_id')->constrained();
            $table->foreignId('financial_movement_category_id')->constrained();
            $table->foreignId('dealer_id')->nullable()->constrained();
            $table->nullableMorphs('movementable');
            $table->integer('installment_number');
            $table->integer('total_installments');
            $table->string('description')->nullable();
            $table->decimal('value', 10, 2);
            $table->date('due_date')->nullable();
            $table->date('payment_date')->nullable();
            $table->enum('status', ['pending', 'paid', 'cancelled']);
            $table->enum('type', ['income', 'expense']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_movements');
    }
};
