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
        Schema::create('exercise_instructions', function (Blueprint $table) {
            $table->id('instruction_id');
            $table->foreignId('exercise_id')->constrained('exercises', 'exercise_id')->onDelete('cascade');
            $table->enum('instruction_type', ['setup', 'execution', 'breathing', 'modification', 'common_mistakes']);
            $table->text('instruction_text');
            $table->integer('step_order')->nullable();
            $table->boolean('is_critical')->default(false);
            $table->timestamps();

            // Index
            $table->index(['exercise_id', 'instruction_type', 'step_order'], 'idx_exercise_instructions_lookup');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exercise_instructions');
    }
};
