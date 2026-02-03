<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds demo_gif_url column for exercise demonstration GIFs
     */
    public function up(): void
    {
        Schema::table('exercises', function (Blueprint $table) {
            $table->string('demo_gif_url', 500)->nullable()->after('exercise_category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exercises', function (Blueprint $table) {
            $table->dropColumn('demo_gif_url');
        });
    }
};
