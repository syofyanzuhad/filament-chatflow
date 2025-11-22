<?php

namespace Syofyanzuhad\FilamentChatflow\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Chatflow extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'is_active',
        'welcome_message',
        'position',
        'settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'welcome_message' => 'array',
        'settings' => 'array',
    ];

    public function steps(): HasMany
    {
        return $this->hasMany(ChatflowStep::class);
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(ChatflowConversation::class);
    }

    public function analytics(): HasMany
    {
        return $this->hasMany(ChatflowAnalytic::class);
    }

    public function rootSteps(): HasMany
    {
        return $this->hasMany(ChatflowStep::class)->whereNull('parent_id')->orderBy('order');
    }

    public function activeConversations(): HasMany
    {
        return $this->conversations()->where('status', 'active');
    }

    public function getCompletionRateAttribute(): float
    {
        $total = $this->conversations()->count();

        if ($total === 0) {
            return 0;
        }

        $completed = $this->conversations()->where('status', 'completed')->count();

        return round(($completed / $total) * 100, 2);
    }

    public function getWelcomeMessageForLocale(string $locale = 'en'): string
    {
        return $this->welcome_message[$locale] ?? $this->welcome_message['en'] ?? '';
    }

    protected static function newFactory()
    {
        return \Syofyanzuhad\FilamentChatflow\Database\Factories\ChatflowFactory::new();
    }
}
