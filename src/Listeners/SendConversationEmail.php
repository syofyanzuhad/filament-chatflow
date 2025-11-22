<?php

namespace Syofyanzuhad\FilamentChatflow\Listeners;

use Syofyanzuhad\FilamentChatflow\Events\ConversationEnded;
use Syofyanzuhad\FilamentChatflow\Jobs\SendChatTranscriptEmail;

class SendConversationEmail
{
    public function handle(ConversationEnded $event): void
    {
        // Only send email for completed conversations
        if (! $event->conversation->isCompleted()) {
            return;
        }

        // Dispatch job to send email asynchronously
        SendChatTranscriptEmail::dispatch($event->conversation);
    }
}
