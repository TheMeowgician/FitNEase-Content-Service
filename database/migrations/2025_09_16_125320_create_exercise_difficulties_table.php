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
        Schema::create('exercise_difficulties', function (Blueprint $table) {
            $table->id('difficulty_id');
            $table->enum('difficulty_name', ['beginner', 'medium', 'expert'])->unique();
            $table->text('description')->nullable();
            $table->integer('min_experience_months')->default(0);
            $table->string('recommended_fitness_level', 100)->nullable();
            $table->decimal('intensity_scale', 3, 2)->nullable()->comment('1.00 to 10.00');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exercise_difficulties');
    }
};
