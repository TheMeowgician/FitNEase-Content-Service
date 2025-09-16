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
        Schema::create('files', function (Blueprint $table) {
            $table->id('file_id');
            $table->string('file_name', 255);
            $table->string('original_file_name', 255);
            $table->string('file_path', 500);
            $table->string('file_type', 50);
            $table->bigInteger('file_size_bytes');
            $table->string('mime_type', 100)->nullable();
            $table->integer('uploaded_by')->comment('FK to Users.user_id');
            $table->string('entity_type', 50)->nullable();
            $table->integer('entity_id')->nullable();
            $table->boolean('is_public')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamp('uploaded_at')->useCurrent();

            // Indexes
            $table->index(['entity_type', 'entity_id', 'is_active'], 'idx_files_entity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
