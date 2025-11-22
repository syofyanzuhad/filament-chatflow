<?php

namespace Syofyanzuhad\FilamentChatflow\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Syofyanzuhad\FilamentChatflow\Services\ConversationManager;

class CleanupExpiredConversations implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function handle(ConversationManager $conversationManager): void
    {
        $cleanedCount = $conversationManager->cleanupExpiredConversations();

        logger()->info('Cleaned up expired chatflow conversations', [
            'count' => $cleanedCount,
        ]);

        // Also delete old conversations based on config
        if (config('chatflow.conversation.auto_cleanup', true)) {
            $daysToKeep = config('chatflow.conversation.expire_after_hours', 24) / 24;
            $deletedCount = $conversationManager->deleteOldConversations($daysToKeep);

            logger()->info('Deleted old chatflow conversations', [
                'count' => $deletedCount,
                'days_to_keep' => $daysToKeep,
            ]);
        }
    }
}
