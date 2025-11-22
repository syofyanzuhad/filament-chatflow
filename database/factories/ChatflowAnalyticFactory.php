<?php

namespace Syofyanzuhad\FilamentChatflow\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Syofyanzuhad\FilamentChatflow\Models\Chatflow;
use Syofyanzuhad\FilamentChatflow\Models\ChatflowAnalytic;

class ChatflowAnalyticFactory extends Factory
{
    protected $model = ChatflowAnalytic::class;

    public function definition(): array
    {
        $totalConversations = $this->faker->numberBetween(10, 200);
        $completedConversations = $this->faker->numberBetween(0, $totalConversations);
        $abandonedConversations = $totalConversations - $completedConversations;

        return [
            'chatflow_id' => Chatflow::factory(),
            'date' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'total_conversations' => $totalConversations,
            'completed_conversations' => $completedConversations,
            'abandoned_conversations' => $abandonedConversations,
            'avg_completion_time_seconds' => $this->faker->numberBetween(60, 600),
            'drop_off_points' => [
                'step_3' => $this->faker->numberBetween(1, 10),
                'step_5' => $this->faker->numberBetween(1, 10),
                'step_7' => $this->faker->numberBetween(1, 10),
            ],
            'popular_paths' => [
                'start -> step2 -> step3 -> end' => $this->faker->numberBetween(10, 50),
                'start -> step2 -> step4 -> end' => $this->faker->numberBetween(5, 30),
            ],
            'hourly_distribution' => array_fill(0, 24, $this->faker->numberBetween(0, 20)),
        ];
    }

    public function forToday(): static
    {
        return $this->state(fn (array $attributes) => [
            'date' => now()->toDateString(),
        ]);
    }

    public function forDate(string $date): static
    {
        return $this->state(fn (array $attributes) => [
            'date' => $date,
        ]);
    }
}
