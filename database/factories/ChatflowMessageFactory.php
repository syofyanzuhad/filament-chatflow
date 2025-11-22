<?php

namespace Syofyanzuhad\FilamentChatflow\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Syofyanzuhad\FilamentChatflow\Models\ChatflowConversation;
use Syofyanzuhad\FilamentChatflow\Models\ChatflowMessage;

class ChatflowMessageFactory extends Factory
{
    protected $model = ChatflowMessage::class;

    public function definition(): array
    {
        return [
            'conversation_id' => ChatflowConversation::factory(),
            'step_id' => null,
            'type' => $this->faker->randomElement([ChatflowMessage::TYPE_BOT, ChatflowMessage::TYPE_USER]),
            'content' => $this->faker->sentence(),
            'options' => null,
            'selected_option' => null,
            'metadata' => [],
        ];
    }

    public function bot(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => ChatflowMessage::TYPE_BOT,
            'content' => $this->faker->sentence(),
        ]);
    }

    public function user(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => ChatflowMessage::TYPE_USER,
            'content' => $this->faker->sentence(),
        ]);
    }

    public function withOptions(): static
    {
        return $this->state(fn (array $attributes) => [
            'options' => [
                ['value' => 'option1', 'label' => 'Option 1'],
                ['value' => 'option2', 'label' => 'Option 2'],
            ],
        ]);
    }
}
