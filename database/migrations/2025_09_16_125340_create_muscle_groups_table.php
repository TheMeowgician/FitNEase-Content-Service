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
        Schema::create('muscle_groups', function (Blueprint $table) {
            $table->id('muscle_group_id');
            $table->enum('group_name', ['core', 'upper_body', 'lower_body'])->unique();
            $table->text('description')->nullable();
            $table->text('primary_muscles')->nullable();
            $table->text('secondary_muscles')->nullable();
            $table->text('exercise_benefits')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('muscle_groups');
    }
};
