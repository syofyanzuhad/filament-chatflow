<?php

namespace Syofyanzuhad\FilamentChatflow\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatflowStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'chatflow_id',
        'parent_id',
        'type',
        'content',
        'options',
        'next_step_id',
        'position_x',
        'position_y',
        'order',
        'conditions',
        'metadata',
    ];

    protected $casts = [
        'content' => 'array',
        'options' => 'array',
        'conditions' => 'array',
        'metadata' => 'array',
        'position_x' => 'integer',
        'position_y' => 'integer',
        'order' => 'integer',
    ];

    public const TYPE_MESSAGE = 'message';

    public const TYPE_QUESTION = 'question';

    public const TYPE_CONDITION = 'condition';

    public const TYPE_END = 'end';

    public function chatflow(): BelongsTo
    {
        return $this->belongsTo(Chatflow::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ChatflowStep::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(ChatflowStep::class, 'parent_id');
    }

    public function nextStep(): BelongsTo
    {
        return $this->belongsTo(ChatflowStep::class, 'next_step_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatflowMessage::class, 'step_id');
    }

    public function getContentForLocale(string $locale = 'en'): string
    {
        return $this->content[$locale] ?? $this->content['en'] ?? '';
    }

    public function getOptionsForLocale(string $locale = 'en'): array
    {
        if (! $this->options) {
            return [];
        }

        return collect($this->options)->map(function ($option) use ($locale) {
            if (is_array($option) && isset($option['label'])) {
                return [
                    'value' => $option['value'] ?? '',
                    'label' => is_array($option['label'])
                        ? ($option['label'][$locale] ?? $option['label']['en'] ?? '')
                        : $option['label'],
                    'next_step_id' => $option['next_step_id'] ?? null,
                ];
            }

            return $option;
        })->toArray();
    }

    public function isType(string $type): bool
    {
        return $this->type === $type;
    }

    protected static function newFactory()
    {
        return \Syofyanzuhad\FilamentChatflow\Database\Factories\ChatflowStepFactory::new();
    }
}
