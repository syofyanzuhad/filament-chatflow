<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chatflow_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chatflow_id')->constrained('chatflows')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('chatflow_steps')->nullOnDelete();
            $table->string('type'); // message, question, condition, end
            $table->json('content'); // Multi-language support for step content
            $table->json('options')->nullable(); // For question type: array of choices/buttons
            $table->foreignId('next_step_id')->nullable()->constrained('chatflow_steps')->nullOnDelete();
            $table->integer('position_x')->default(0); // For visual builder
            $table->integer('position_y')->default(0); // For visual builder
            $table->integer('order')->default(0); // For sequential ordering
            $table->json('conditions')->nullable(); // For condition type: rules for branching
            $table->json('metadata')->nullable(); // Additional data like colors, icons, etc.
            $table->timestamps();

            $table->index(['chatflow_id', 'type']);
            $table->index('parent_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chatflow_steps');
    }
};
