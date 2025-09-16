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
        Schema::create('exercise_muscle_groups', function (Blueprint $table) {
            $table->id('exercise_muscle_id');
            $table->foreignId('exercise_id')->constrained('exercises', 'exercise_id')->onDelete('cascade');
            $table->foreignId('muscle_group_id')->constrained('muscle_groups', 'muscle_group_id')->onDelete('cascade');
            $table->boolean('primary_target')->default(true);
            $table->decimal('activation_percentage', 5, 2)->nullable()->comment('0.00 to 100.00');

            // Unique constraint and index
            $table->unique(['exercise_id', 'muscle_group_id']);
            $table->index(['exercise_id', 'primary_target'], 'idx_exercise_muscle_groups_lookup');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exercise_muscle_groups');
    }
};
