<?php

namespace Syofyanzuhad\FilamentChatflow\Listeners;

use Syofyanzuhad\FilamentChatflow\Events\ConversationStarted;

class LogConversationStart
{
    public function handle(ConversationStarted $event): void
    {
        logger()->info('Chatflow conversation started', [
            'conversation_id' => $event->conversation->id,
            'chatflow_id' => $event->conversation->chatflow_id,
            'user_id' => $event->conversation->user_id,
            'session_id' => $event->conversation->session_id,
        ]);
    }
}
