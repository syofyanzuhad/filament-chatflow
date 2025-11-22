<?php

namespace Syofyanzuhad\FilamentChatflow\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Syofyanzuhad\FilamentChatflow\Models\ChatflowConversation;
use Syofyanzuhad\FilamentChatflow\Services\EmailNotificationService;

class SendChatTranscriptEmail implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public ChatflowConversation $conversation
    ) {}

    public function handle(EmailNotificationService $emailService): void
    {
        $emailService->sendTranscript($this->conversation);
    }

    public function retryUntil(): int
    {
        return now()->addHours(24)->timestamp;
    }
}
