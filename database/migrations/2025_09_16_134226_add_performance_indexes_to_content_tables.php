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
        // Additional indexes for exercises table (some are already added in the main migration)
        Schema::table('exercises', function (Blueprint $table) {
            // These indexes are already added in the main migration, but adding here for completeness
            // $table->index(['target_muscle_group', 'difficulty_level'], 'idx_exercises_muscle_difficulty');
            // $table->index(['difficulty_level', 'target_muscle_group', 'exercise_category'], 'idx_exercises_ml_features');
            // $table->index(['equipment_needed', 'difficulty_level'], 'idx_exercises_equipment');
        });

        // Additional indexes for workouts table (some are already added in the main migration)
        Schema::table('workouts', function (Blueprint $table) {
            // These indexes are already added in the main migration
            // $table->index(['difficulty_level', 'target_muscle_groups'], 'idx_workouts_difficulty_muscle');
            // $table->index(['is_public', 'is_system_generated'], 'idx_workouts_public_active');
        });

        // Additional indexes for videos table (already added in main migration)
        Schema::table('videos', function (Blueprint $table) {
            // $table->index(['exercise_id', 'is_active'], 'idx_videos_exercise');
        });

        // Additional indexes for files table (already added in main migration)
        Schema::table('files', function (Blueprint $table) {
            // $table->index(['entity_type', 'entity_id', 'is_active'], 'idx_files_entity');
        });

        // Additional indexes for exercise_muscle_groups table (already added in main migration)
        Schema::table('exercise_muscle_groups', function (Blueprint $table) {
            // $table->index(['exercise_id', 'primary_target'], 'idx_exercise_muscle_groups_lookup');
        });

        // Additional indexes for exercise_instructions table (already added in main migration)
        Schema::table('exercise_instructions', function (Blueprint $table) {
            // $table->index(['exercise_id', 'instruction_type', 'step_order'], 'idx_exercise_instructions_lookup');
        });

        // Add some additional performance indexes not covered in main migrations
        Schema::table('exercises', function (Blueprint $table) {
            $table->index(['created_at'], 'idx_exercises_created_at');
            $table->index(['exercise_name'], 'idx_exercises_name');
        });

        Schema::table('workouts', function (Blueprint $table) {
            $table->index(['created_by'], 'idx_workouts_created_by');
            $table->index(['created_at'], 'idx_workouts_created_at');
            $table->index(['workout_name'], 'idx_workouts_name');
        });

        Schema::table('videos', function (Blueprint $table) {
            $table->index(['video_type'], 'idx_videos_type');
            $table->index(['created_at'], 'idx_videos_created_at');
        });

        Schema::table('files', function (Blueprint $table) {
            $table->index(['uploaded_by'], 'idx_files_uploaded_by');
            $table->index(['file_type'], 'idx_files_type');
            $table->index(['uploaded_at'], 'idx_files_uploaded_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exercises', function (Blueprint $table) {
            $table->dropIndex('idx_exercises_created_at');
            $table->dropIndex('idx_exercises_name');
        });

        Schema::table('workouts', function (Blueprint $table) {
            $table->dropIndex('idx_workouts_created_by');
            $table->dropIndex('idx_workouts_created_at');
            $table->dropIndex('idx_workouts_name');
        });

        Schema::table('videos', function (Blueprint $table) {
            $table->dropIndex('idx_videos_type');
            $table->dropIndex('idx_videos_created_at');
        });

        Schema::table('files', function (Blueprint $table) {
            $table->dropIndex('idx_files_uploaded_by');
            $table->dropIndex('idx_files_type');
            $table->dropIndex('idx_files_uploaded_at');
        });
    }
};
