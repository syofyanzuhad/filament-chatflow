<?php

namespace Syofyanzuhad\FilamentChatflow\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Syofyanzuhad\FilamentChatflow\Models\Chatflow;
use Syofyanzuhad\FilamentChatflow\Services\ChatflowAnalytics;

class UpdateDailyAnalytics implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public Chatflow $chatflow,
        public string $date
    ) {}

    public function handle(ChatflowAnalytics $analyticsService): void
    {
        $analyticsService->generateDailyAnalytics($this->chatflow, $this->date);
    }

    public function retryUntil(): int
    {
        return now()->addHours(12)->timestamp;
    }
}
