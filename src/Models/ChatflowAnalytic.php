<?php

namespace Syofyanzuhad\FilamentChatflow\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatflowAnalytic extends Model
{
    use HasFactory;

    protected $fillable = [
        'chatflow_id',
        'date',
        'total_conversations',
        'completed_conversations',
        'abandoned_conversations',
        'avg_completion_time_seconds',
        'drop_off_points',
        'popular_paths',
        'hourly_distribution',
    ];

    protected $casts = [
        'date' => 'date',
        'total_conversations' => 'integer',
        'completed_conversations' => 'integer',
        'abandoned_conversations' => 'integer',
        'avg_completion_time_seconds' => 'integer',
        'drop_off_points' => 'array',
        'popular_paths' => 'array',
        'hourly_distribution' => 'array',
    ];

    public function chatflow(): BelongsTo
    {
        return $this->belongsTo(Chatflow::class);
    }

    public function getCompletionRateAttribute(): float
    {
        if ($this->total_conversations === 0) {
            return 0;
        }

        return round(($this->completed_conversations / $this->total_conversations) * 100, 2);
    }

    public function getAbandonmentRateAttribute(): float
    {
        if ($this->total_conversations === 0) {
            return 0;
        }

        return round(($this->abandoned_conversations / $this->total_conversations) * 100, 2);
    }

    public function getAvgCompletionTimeInMinutesAttribute(): float
    {
        return round($this->avg_completion_time_seconds / 60, 2);
    }

    public function scopeForDate($query, $date)
    {
        return $query->whereDate('date', $date);
    }

    public function scopeForDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    protected static function newFactory()
    {
        return \Syofyanzuhad\FilamentChatflow\Database\Factories\ChatflowAnalyticFactory::new();
    }
}
