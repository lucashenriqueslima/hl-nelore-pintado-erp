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
        Schema::create('embryo_status_histories', function (Blueprint $table) {
            $table->id();
            //cascade on delete
            $table->foreignId('embryo_id')->constrained('embryos')->cascadeOnDelete();
            $table->enum('status', [
                'collected',
                'fertilized',
                'frozen',
                'selled',
            ]);
            //exited_at
            $table->dateTime('exited_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('embryo_status_histories');
    }
};
