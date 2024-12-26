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
        Schema::create('cattle', function (Blueprint $table) {
            $table->id();
            $table->string('rgd')->unique();
            $table->string('name');
            $table->enum('gender', ['male', 'female']);
            $table->date('aquisition_date')->nullable();
            $table->date('birth_date')->nullable();
            $table->date('death_date')->nullable();
            $table->enum('type', ['po', 'recipient'])->nullable();
            $table->decimal('value', 10, 2)->nullable();
            $table->decimal('commision_percentage', 5, 2)->nullable();
            $table->integer('number_installments')->nullable();
            $table->date('first_installment_date')->nullable();
            $table->string('aquisition_contract_path')->nullable();
            $table->string('sell_contract_path')->nullable();
            $table->string('profile_photo_path')->nullable();
            $table->json('attachments')->nullable();
            $table->foreignId('farm_id')->constrained();
            $table->foreignId('father_id')->nullable()->constrained('cattle');
            $table->foreignId('mother_id')->nullable()->constrained('cattle');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cattle');
    }
};
