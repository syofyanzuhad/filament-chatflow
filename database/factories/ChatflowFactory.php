<?php

namespace Syofyanzuhad\FilamentChatflow\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Syofyanzuhad\FilamentChatflow\Models\Chatflow;

class ChatflowFactory extends Factory
{
    protected $model = Chatflow::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(3),
            'description' => $this->faker->sentence(10),
            'is_active' => $this->faker->boolean(80),
            'welcome_message' => [
                'en' => 'Hello! How can I help you today?',
                'id' => 'Halo! Ada yang bisa saya bantu?',
            ],
            'position' => $this->faker->randomElement(['bottom-right', 'bottom-left', 'top-right', 'top-left']),
            'settings' => [
                'theme_color' => $this->faker->hexColor(),
                'sound_enabled' => true,
                'notification_sound' => 'notification.mp3',
                'message_sound' => 'message.mp3',
                'show_badge' => true,
                'auto_open' => false,
                'email_enabled' => true,
                'email_recipients' => [$this->faker->email()],
            ],
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function bottomRight(): static
    {
        return $this->state(fn (array $attributes) => [
            'position' => 'bottom-right',
        ]);
    }
}
