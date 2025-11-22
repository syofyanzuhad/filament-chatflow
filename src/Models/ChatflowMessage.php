<?php

namespace Syofyanzuhad\FilamentChatflow\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatflowMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'step_id',
        'type',
        'content',
        'options',
        'selected_option',
        'metadata',
    ];

    protected $casts = [
        'options' => 'array',
        'metadata' => 'array',
    ];

    public const TYPE_BOT = 'bot';

    public const TYPE_USER = 'user';

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(ChatflowConversation::class, 'conversation_id');
    }

    public function step(): BelongsTo
    {
        return $this->belongsTo(ChatflowStep::class, 'step_id');
    }

    public function isBot(): bool
    {
        return $this->type === self::TYPE_BOT;
    }

    public function isUser(): bool
    {
        return $this->type === self::TYPE_USER;
    }

    public function scopeBot($query)
    {
        return $query->where('type', self::TYPE_BOT);
    }

    public function scopeUser($query)
    {
        return $query->where('type', self::TYPE_USER);
    }

    protected static function newFactory()
    {
        return \Syofyanzuhad\FilamentChatflow\Database\Factories\ChatflowMessageFactory::new();
    }
}
