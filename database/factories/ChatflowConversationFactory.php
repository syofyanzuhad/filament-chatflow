<?php

namespace Syofyanzuhad\FilamentChatflow\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Syofyanzuhad\FilamentChatflow\Models\Chatflow;
use Syofyanzuhad\FilamentChatflow\Models\ChatflowConversation;

class ChatflowConversationFactory extends Factory
{
    protected $model = ChatflowConversation::class;

    public function definition(): array
    {
        $startedAt = $this->faker->dateTimeBetween('-7 days', 'now');

        return [
            'chatflow_id' => Chatflow::factory(),
            'user_id' => null,
            'session_id' => Str::uuid()->toString(),
            'status' => $this->faker->randomElement([
                ChatflowConversation::STATUS_ACTIVE,
                ChatflowConversation::STATUS_COMPLETED,
                ChatflowConversation::STATUS_ABANDONED,
            ]),
            'locale' => $this->faker->randomElement(['en', 'id']),
            'user_email' => $this->faker->email(),
            'user_name' => $this->faker->name(),
            'current_step_id' => null,
            'metadata' => [
                'user_agent' => $this->faker->userAgent(),
                'ip_address' => $this->faker->ipv4(),
                'referrer' => $this->faker->url(),
            ],
            'started_at' => $startedAt,
            'ended_at' => $this->faker->boolean(70) ? $this->faker->dateTimeBetween($startedAt, 'now') : null,
            'expires_at' => now()->addDay(),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ChatflowConversation::STATUS_ACTIVE,
            'ended_at' => null,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ChatflowConversation::STATUS_COMPLETED,
            'ended_at' => now(),
        ]);
    }

    public function abandoned(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ChatflowConversation::STATUS_ABANDONED,
            'ended_at' => now(),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => now()->subDay(),
        ]);
    }
}
