<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chatflow_analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chatflow_id')->constrained('chatflows')->cascadeOnDelete();
            $table->date('date'); // Daily aggregation
            $table->integer('total_conversations')->default(0);
            $table->integer('completed_conversations')->default(0);
            $table->integer('abandoned_conversations')->default(0);
            $table->integer('avg_completion_time_seconds')->default(0); // Average time in seconds
            $table->json('drop_off_points')->nullable(); // Step IDs where users commonly drop off
            $table->json('popular_paths')->nullable(); // Most common navigation paths
            $table->json('hourly_distribution')->nullable(); // Conversations per hour
            $table->timestamps();

            $table->unique(['chatflow_id', 'date']);
            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chatflow_analytics');
    }
};
