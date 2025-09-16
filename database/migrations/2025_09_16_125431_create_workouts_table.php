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
        Schema::create('workouts', function (Blueprint $table) {
            $table->id('workout_id');
            $table->string('workout_name', 100);
            $table->text('description')->nullable();
            $table->integer('total_duration_minutes')->nullable();
            $table->enum('difficulty_level', ['beginner', 'medium', 'expert']);
            $table->set('target_muscle_groups', ['core', 'upper_body', 'lower_body'])->nullable();
            $table->enum('workout_type', ['individual', 'group', 'both'])->default('both');
            $table->integer('created_by')->nullable()->comment('FK to Users.user_id');
            $table->boolean('is_public')->default(true);
            $table->boolean('is_system_generated')->default(false);
            $table->integer('total_exercises')->default(0);
            $table->decimal('estimated_calories_burned', 6, 2)->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['difficulty_level', 'target_muscle_groups'], 'idx_workouts_difficulty_muscle');
            $table->index(['is_public', 'is_system_generated'], 'idx_workouts_public_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workouts');
    }
};
