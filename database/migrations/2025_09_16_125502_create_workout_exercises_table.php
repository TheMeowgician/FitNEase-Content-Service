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
        Schema::create('workout_exercises', function (Blueprint $table) {
            $table->id('workout_exercise_id');
            $table->foreignId('workout_id')->constrained('workouts', 'workout_id')->onDelete('cascade');
            $table->foreignId('exercise_id')->constrained('exercises', 'exercise_id')->onDelete('cascade');
            $table->integer('order_sequence');
            $table->integer('custom_duration_seconds')->nullable();
            $table->integer('custom_rest_duration_seconds')->nullable();
            $table->integer('sets_count')->default(1);

            // Unique constraint
            $table->unique(['workout_id', 'order_sequence']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workout_exercises');
    }
};
