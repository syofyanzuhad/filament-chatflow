<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chatflow_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chatflow_id')->constrained('chatflows')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('session_id')->unique(); // For anonymous users
            $table->string('status')->default('active'); // active, completed, abandoned
            $table->string('locale')->default('en'); // Language used in conversation
            $table->string('user_email')->nullable(); // For sending transcript
            $table->string('user_name')->nullable(); // Optional user identification
            $table->foreignId('current_step_id')->nullable()->constrained('chatflow_steps')->nullOnDelete();
            $table->json('metadata')->nullable(); // Browser info, IP, custom data
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('ended_at')->nullable();
            $table->timestamp('expires_at')->nullable(); // Auto-cleanup after 24 hours
            $table->timestamps();

            $table->index(['chatflow_id', 'status']);
            $table->index(['session_id', 'status']);
            $table->index('expires_at');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chatflow_conversations');
    }
};
