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
        Schema::create('embryos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cattle_id')->nullable()->constrained('cattle');
            $table->foreignId('father_id')->nullable()->constrained('cattle');
            $table->foreignId('mother_id')->nullable()->constrained('cattle');
            $table->foreignId('receiver_id')->nullable()->constrained('cattle');
            $table->string('rgd')->nullable();
            // $table->foreignId('embryo_group_id')->nullable()->constrained('embryo_groups');
            $table->integer('count_group_identification')->nullable();
            $table->date('collection_date');
            $table->date('fertilization_date')->nullable();
            $table->boolean('is_sexed_semen');
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->enum('status', [
                'collected',
                'fertilized',
                'frozen',
                'selled',
            ])->default('collected');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('embryos');
    }
};
