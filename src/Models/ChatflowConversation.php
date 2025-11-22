<?php

namespace Syofyanzuhad\FilamentChatflow\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatflowConversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'chatflow_id',
        'user_id',
        'session_id',
        'status',
        'locale',
        'user_email',
        'user_name',
        'current_step_id',
        'metadata',
        'started_at',
        'ended_at',
        'expires_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public const STATUS_ACTIVE = 'active';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_ABANDONED = 'abandoned';

    public function chatflow(): BelongsTo
    {
        return $this->belongsTo(Chatflow::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model', 'App\\Models\\User'));
    }

    public function currentStep(): BelongsTo
    {
        return $this->belongsTo(ChatflowStep::class, 'current_step_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatflowMessage::class, 'conversation_id')->orderBy('created_at');
    }

    public function getDurationAttribute(): ?int
    {
        if (! $this->ended_at) {
            return null;
        }

        return $this->started_at->diffInSeconds($this->ended_at);
    }

    public function getDurationInMinutesAttribute(): ?float
    {
        $duration = $this->duration;

        return $duration ? round($duration / 60, 2) : null;
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isAbandoned(): bool
    {
        return $this->status === self::STATUS_ABANDONED;
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function markAsCompleted(): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'ended_at' => now(),
        ]);
    }

    public function markAsAbandoned(): void
    {
        $this->update([
            'status' => self::STATUS_ABANDONED,
            'ended_at' => now(),
        ]);
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeAbandoned($query)
    {
        return $query->where('status', self::STATUS_ABANDONED);
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }

    protected static function newFactory()
    {
        return \Syofyanzuhad\FilamentChatflow\Database\Factories\ChatflowConversationFactory::new();
    }
}
