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
        Schema::create('exercises', function (Blueprint $table) {
            $table->id('exercise_id');
            $table->string('exercise_name', 100);
            $table->text('description')->nullable();
            $table->enum('difficulty_level', ['beginner', 'medium', 'expert']);
            $table->enum('target_muscle_group', ['core', 'upper_body', 'lower_body']);
            $table->integer('default_duration_seconds')->default(20);
            $table->integer('default_rest_duration_seconds')->default(10);
            $table->text('instructions')->nullable();
            $table->text('safety_tips')->nullable();
            $table->decimal('calories_burned_per_minute', 5, 2)->nullable();
            $table->string('equipment_needed', 255)->nullable();
            $table->string('exercise_category', 50)->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['target_muscle_group', 'difficulty_level'], 'idx_exercises_muscle_difficulty');
            $table->index(['difficulty_level', 'target_muscle_group', 'exercise_category'], 'idx_exercises_ml_features');
            $table->index(['equipment_needed', 'difficulty_level'], 'idx_exercises_equipment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exercises');
    }
};
