<?php

namespace Syofyanzuhad\FilamentChatflow\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Syofyanzuhad\FilamentChatflow\Models\Chatflow;
use Syofyanzuhad\FilamentChatflow\Models\ChatflowStep;

class ChatflowStepFactory extends Factory
{
    protected $model = ChatflowStep::class;

    public function definition(): array
    {
        return [
            'chatflow_id' => Chatflow::factory(),
            'parent_id' => null,
            'type' => $this->faker->randomElement([
                ChatflowStep::TYPE_MESSAGE,
                ChatflowStep::TYPE_QUESTION,
                ChatflowStep::TYPE_CONDITION,
                ChatflowStep::TYPE_END,
            ]),
            'content' => [
                'en' => $this->faker->sentence(),
                'id' => $this->faker->sentence(),
            ],
            'options' => null,
            'next_step_id' => null,
            'position_x' => $this->faker->numberBetween(0, 1000),
            'position_y' => $this->faker->numberBetween(0, 1000),
            'order' => $this->faker->numberBetween(0, 100),
            'conditions' => null,
            'metadata' => [
                'color' => $this->faker->hexColor(),
                'icon' => $this->faker->randomElement(['message', 'question', 'branch', 'flag']),
            ],
        ];
    }

    public function message(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => ChatflowStep::TYPE_MESSAGE,
            'content' => [
                'en' => 'This is a message step.',
                'id' => 'Ini adalah langkah pesan.',
            ],
        ]);
    }

    public function question(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => ChatflowStep::TYPE_QUESTION,
            'content' => [
                'en' => 'Please choose an option:',
                'id' => 'Silakan pilih opsi:',
            ],
            'options' => [
                [
                    'value' => 'option1',
                    'label' => [
                        'en' => 'Option 1',
                        'id' => 'Opsi 1',
                    ],
                    'next_step_id' => null,
                ],
                [
                    'value' => 'option2',
                    'label' => [
                        'en' => 'Option 2',
                        'id' => 'Opsi 2',
                    ],
                    'next_step_id' => null,
                ],
            ],
        ]);
    }

    public function end(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => ChatflowStep::TYPE_END,
            'content' => [
                'en' => 'Thank you for chatting with us!',
                'id' => 'Terima kasih telah berbincang dengan kami!',
            ],
        ]);
    }
}
