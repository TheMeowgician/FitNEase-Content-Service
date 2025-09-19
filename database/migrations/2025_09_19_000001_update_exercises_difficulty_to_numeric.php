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
        Schema::table('exercises', function (Blueprint $table) {
            // Change difficulty_level from enum to integer
            $table->integer('difficulty_level')->comment('1=beginner, 2=beginner+, 3=intermediate, 4=advanced, 5=expert')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exercises', function (Blueprint $table) {
            // Revert back to enum
            $table->enum('difficulty_level', ['beginner', 'medium', 'expert'])->change();
        });
    }
};