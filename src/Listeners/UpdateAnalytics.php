<?php

namespace Syofyanzuhad\FilamentChatflow\Listeners;

use Syofyanzuhad\FilamentChatflow\Events\ConversationEnded;
use Syofyanzuhad\FilamentChatflow\Jobs\UpdateDailyAnalytics;

class UpdateAnalytics
{
    public function handle(ConversationEnded $event): void
    {
        // Dispatch job to update analytics asynchronously
        UpdateDailyAnalytics::dispatch($event->conversation->chatflow, now()->toDateString());
    }
}
