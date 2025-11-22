<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chatflow_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('chatflow_conversations')->cascadeOnDelete();
            $table->foreignId('step_id')->nullable()->constrained('chatflow_steps')->nullOnDelete();
            $table->string('type'); // bot, user
            $table->text('content'); // Message content
            $table->json('options')->nullable(); // Quick reply buttons shown with this message
            $table->string('selected_option')->nullable(); // User's selected choice
            $table->json('metadata')->nullable(); // Additional data
            $table->timestamps();

            $table->index(['conversation_id', 'created_at']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chatflow_messages');
    }
};
