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
        Schema::create('videos', function (Blueprint $table) {
            $table->id('video_id');
            $table->foreignId('exercise_id')->nullable()->constrained('exercises', 'exercise_id')->onDelete('cascade');
            $table->string('video_title', 255);
            $table->string('video_url', 500);
            $table->text('video_description')->nullable();
            $table->integer('duration_seconds')->nullable();
            $table->enum('video_type', ['instruction', 'form_guide', 'demonstration', 'tips'])->nullable();
            $table->string('thumbnail_url', 500)->nullable();
            $table->enum('video_quality', ['720p', '1080p', '480p'])->default('720p');
            $table->decimal('file_size_mb', 8, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Indexes
            $table->index(['exercise_id', 'is_active'], 'idx_videos_exercise');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('videos');
    }
};
