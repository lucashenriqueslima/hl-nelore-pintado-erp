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
        Schema::create('financial_movement_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->enum('type', ['income', 'expense']);
            $table->timestamps();
        });

        DB::table('financial_movement_categories')->insert([
            ['name' => 'Compra de Gado', 'type' => 'expense'],
            ['name' => 'Venda de Gado', 'type' => 'income'],
            ['name' => 'Compra de Embrião', 'type' => 'expense'],
            ['name' => 'Venda de Embrião', 'type' => 'income'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_movement_categories');
    }
};
