<?php

namespace Syofyanzuhad\FilamentChatflow\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Syofyanzuhad\FilamentChatflow\Models\Chatflow;
use Syofyanzuhad\FilamentChatflow\Models\ChatflowConversation;

class ChatflowStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalConversations = ChatflowConversation::count();
        $activeConversations = ChatflowConversation::where('status', 'active')->count();
        $completedToday = ChatflowConversation::where('status', 'completed')
            ->whereDate('ended_at', today())
            ->count();

        $avgCompletionRate = Chatflow::all()->avg('completion_rate') ?? 0;

        return [
            Stat::make('Total Conversations', $totalConversations)
                ->description('All time conversations')
                ->descriptionIcon('heroicon-o-chat-bubble-left-right')
                ->color('primary'),

            Stat::make('Active Conversations', $activeConversations)
                ->description('Currently active')
                ->descriptionIcon('heroicon-o-arrow-trending-up')
                ->color('warning'),

            Stat::make('Completed Today', $completedToday)
                ->description('Conversations completed today')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success'),

            Stat::make('Avg Completion Rate', round($avgCompletionRate, 1) . '%')
                ->description('Across all chatflows')
                ->descriptionIcon('heroicon-o-chart-bar')
                ->color($avgCompletionRate >= 70 ? 'success' : ($avgCompletionRate >= 50 ? 'warning' : 'danger')),
        ];
    }
}
