<?php

namespace Syofyanzuhad\FilamentChatflow\Services;

use Syofyanzuhad\FilamentChatflow\Models\Chatflow;
use Syofyanzuhad\FilamentChatflow\Models\ChatflowAnalytic;
use Syofyanzuhad\FilamentChatflow\Models\ChatflowConversation;

class ChatflowAnalytics
{
    public function generateDailyAnalytics(Chatflow $chatflow, ?string $date = null): ChatflowAnalytic
    {
        $date = $date ?? now()->toDateString();

        $conversations = ChatflowConversation::where('chatflow_id', $chatflow->id)
            ->whereDate('started_at', $date)
            ->get();

        $totalConversations = $conversations->count();
        $completedConversations = $conversations->where('status', ChatflowConversation::STATUS_COMPLETED)->count();
        $abandonedConversations = $conversations->where('status', ChatflowConversation::STATUS_ABANDONED)->count();

        $avgCompletionTime = $conversations
            ->where('status', ChatflowConversation::STATUS_COMPLETED)
            ->avg('duration') ?? 0;

        $dropOffPoints = $this->calculateDropOffPoints($conversations);
        $popularPaths = $this->calculatePopularPaths($conversations);
        $hourlyDistribution = $this->calculateHourlyDistribution($conversations);

        return ChatflowAnalytic::updateOrCreate(
            [
                'chatflow_id' => $chatflow->id,
                'date' => $date,
            ],
            [
                'total_conversations' => $totalConversations,
                'completed_conversations' => $completedConversations,
                'abandoned_conversations' => $abandonedConversations,
                'avg_completion_time_seconds' => (int) $avgCompletionTime,
                'drop_off_points' => $dropOffPoints,
                'popular_paths' => $popularPaths,
                'hourly_distribution' => $hourlyDistribution,
            ]
        );
    }

    public function getAnalyticsForDateRange(Chatflow $chatflow, string $startDate, string $endDate): array
    {
        $analytics = ChatflowAnalytic::where('chatflow_id', $chatflow->id)
            ->forDateRange($startDate, $endDate)
            ->orderBy('date')
            ->get();

        return [
            'total_conversations' => $analytics->sum('total_conversations'),
            'completed_conversations' => $analytics->sum('completed_conversations'),
            'abandoned_conversations' => $analytics->sum('abandoned_conversations'),
            'avg_completion_rate' => $analytics->avg('completion_rate'),
            'avg_completion_time_minutes' => $analytics->avg('avg_completion_time_in_minutes'),
            'daily_breakdown' => $analytics->map(function ($analytic) {
                return [
                    'date' => $analytic->date->toDateString(),
                    'total' => $analytic->total_conversations,
                    'completed' => $analytic->completed_conversations,
                    'completion_rate' => $analytic->completion_rate,
                ];
            })->toArray(),
        ];
    }

    public function getTopDropOffSteps(Chatflow $chatflow, int $limit = 5): array
    {
        $analytics = ChatflowAnalytic::where('chatflow_id', $chatflow->id)
            ->whereNotNull('drop_off_points')
            ->get();

        $allDropOffs = [];

        foreach ($analytics as $analytic) {
            if ($analytic->drop_off_points) {
                foreach ($analytic->drop_off_points as $stepId => $count) {
                    if (! isset($allDropOffs[$stepId])) {
                        $allDropOffs[$stepId] = 0;
                    }
                    $allDropOffs[$stepId] += $count;
                }
            }
        }

        arsort($allDropOffs);

        return array_slice($allDropOffs, 0, $limit, true);
    }

    protected function calculateDropOffPoints($conversations): array
    {
        $dropOffs = [];

        foreach ($conversations->where('status', ChatflowConversation::STATUS_ABANDONED) as $conversation) {
            if ($conversation->current_step_id) {
                $stepKey = 'step_' . $conversation->current_step_id;
                if (! isset($dropOffs[$stepKey])) {
                    $dropOffs[$stepKey] = 0;
                }
                $dropOffs[$stepKey]++;
            }
        }

        return $dropOffs;
    }

    protected function calculatePopularPaths($conversations): array
    {
        $paths = [];

        foreach ($conversations as $conversation) {
            $messages = $conversation->messages()->with('step')->orderBy('created_at')->get();
            $path = $messages->pluck('step.id')->filter()->map(function ($stepId) {
                return 'step_' . $stepId;
            })->implode(' -> ');

            if ($path) {
                if (! isset($paths[$path])) {
                    $paths[$path] = 0;
                }
                $paths[$path]++;
            }
        }

        arsort($paths);

        return array_slice($paths, 0, 10, true);
    }

    protected function calculateHourlyDistribution($conversations): array
    {
        $distribution = array_fill(0, 24, 0);

        foreach ($conversations as $conversation) {
            $hour = $conversation->started_at->hour;
            $distribution[$hour]++;
        }

        return $distribution;
    }

    public function getOverallStats(Chatflow $chatflow): array
    {
        return [
            'total_conversations' => $chatflow->conversations()->count(),
            'active_conversations' => $chatflow->activeConversations()->count(),
            'completed_conversations' => $chatflow->conversations()->completed()->count(),
            'abandoned_conversations' => $chatflow->conversations()->abandoned()->count(),
            'completion_rate' => $chatflow->completion_rate,
            'avg_duration_minutes' => $chatflow->conversations()
                ->completed()
                ->get()
                ->avg('duration_in_minutes') ?? 0,
        ];
    }

    public function getTodayStats(Chatflow $chatflow): array
    {
        $todayAnalytic = ChatflowAnalytic::where('chatflow_id', $chatflow->id)
            ->forDate(now()->toDateString())
            ->first();

        if (! $todayAnalytic) {
            return [
                'total_conversations' => 0,
                'completed_conversations' => 0,
                'abandoned_conversations' => 0,
                'completion_rate' => 0,
                'avg_completion_time_minutes' => 0,
            ];
        }

        return [
            'total_conversations' => $todayAnalytic->total_conversations,
            'completed_conversations' => $todayAnalytic->completed_conversations,
            'abandoned_conversations' => $todayAnalytic->abandoned_conversations,
            'completion_rate' => $todayAnalytic->completion_rate,
            'avg_completion_time_minutes' => $todayAnalytic->avg_completion_time_in_minutes,
        ];
    }
}
