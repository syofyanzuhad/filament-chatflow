<?php

namespace Syofyanzuhad\FilamentChatflow\Listeners;

use Syofyanzuhad\FilamentChatflow\Events\MessageSent;

class LogMessage
{
    public function handle(MessageSent $event): void
    {
        if (config('chatflow.logging.log_messages', false)) {
            logger()->info('Chatflow message sent', [
                'message_id' => $event->message->id,
                'conversation_id' => $event->message->conversation_id,
                'type' => $event->message->type,
                'content_length' => strlen($event->message->content),
            ]);
        }
    }
}
