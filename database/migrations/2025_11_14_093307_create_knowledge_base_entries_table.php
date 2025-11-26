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
        Schema::create('knowledge_base_entries', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // 'receipt_source' or 'note_template'
            $table->string('title'); // Name/title of the entry
            $table->text('description')->nullable(); // General description
            $table->json('data'); // Flexible JSON storage for type-specific fields
            $table->string('category')->nullable(); // Category/tag for organization
            $table->boolean('is_active')->default(true); // Enable/disable entries
            $table->integer('sort_order')->default(0); // Manual sorting
            $table->timestamps();

            $table->index('type');
            $table->index('category');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('knowledge_base_entries');
    }
};
