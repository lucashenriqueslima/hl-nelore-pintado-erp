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
        Schema::table('embryos', function (Blueprint $table) {
            // Make collection_date nullable
            $table->date('collection_date')->nullable()->change();

            // Make is_sexed_semen nullable
            $table->boolean('is_sexed_semen')->nullable()->change();

            // Add new was_purchased column as nullable boolean
            $table->boolean('was_purchased')->nullable();

            $table->enum('status', [
                'collected',
                'fertilized',
                'frozen',
                'selled',
            ])->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('embryos', function (Blueprint $table) {
            // Revert collection_date to not nullable
            $table->date('collection_date')->nullable(false)->change();

            // Revert is_sexed_semen to not nullable
            $table->boolean('is_sexed_semen')->nullable(false)->change();

            // Remove was_purchased column
            $table->dropColumn('was_purchased');

            // Revert status to not nullable
            $table->enum('status', [
                'collected',
                'fertilized',
                'frozen',
                'selled',
            ])->nullable(false)->change();
        });
    }
};
