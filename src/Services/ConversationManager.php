<?php

namespace Syofyanzuhad\FilamentChatflow\Services;

use Illuminate\Support\Str;
use Syofyanzuhad\FilamentChatflow\Events\ConversationEnded;
use Syofyanzuhad\FilamentChatflow\Events\ConversationStarted;
use Syofyanzuhad\FilamentChatflow\Models\Chatflow;
use Syofyanzuhad\FilamentChatflow\Models\ChatflowConversation;

class ConversationManager
{
    public function createConversation(
        Chatflow $chatflow,
        ?int $userId = null,
        array $metadata = [],
        ?string $locale = null
    ): ChatflowConversation {
        $conversation = ChatflowConversation::create([
            'chatflow_id' => $chatflow->id,
            'user_id' => $userId,
            'session_id' => Str::uuid()->toString(),
            'status' => ChatflowConversation::STATUS_ACTIVE,
            'locale' => $locale ?? app()->getLocale(),
            'metadata' => $metadata,
            'started_at' => now(),
            'expires_at' => now()->addHours(config('chatflow.conversation.expire_after_hours', 24)),
        ]);

        event(new ConversationStarted($conversation));

        return $conversation;
    }

    public function findBySessionId(string $sessionId): ?ChatflowConversation
    {
        return ChatflowConversation::where('session_id', $sessionId)
            ->where('status', ChatflowConversation::STATUS_ACTIVE)
            ->first();
    }

    public function markAsCompleted(ChatflowConversation $conversation): void
    {
        $conversation->markAsCompleted();

        event(new ConversationEnded($conversation));
    }

    public function markAsAbandoned(ChatflowConversation $conversation): void
    {
        $conversation->markAsAbandoned();

        event(new ConversationEnded($conversation));
    }

    public function cleanupExpiredConversations(): int
    {
        $expiredConversations = ChatflowConversation::expired()
            ->active()
            ->get();

        foreach ($expiredConversations as $conversation) {
            $this->markAsAbandoned($conversation);
        }

        return $expiredConversations->count();
    }

    public function updateUserInfo(ChatflowConversation $conversation, ?string $email = null, ?string $name = null): void
    {
        $updates = [];

        if ($email) {
            $updates['user_email'] = $email;
        }

        if ($name) {
            $updates['user_name'] = $name;
        }

        if (! empty($updates)) {
            $conversation->update($updates);
        }
    }

    public function deleteOldConversations(int $days = 1): int
    {
        return ChatflowConversation::where('ended_at', '<', now()->subDays($days))
            ->delete();
    }

    public function getActiveConversationCount(Chatflow $chatflow): int
    {
        return $chatflow->activeConversations()->count();
    }

    public function getTotalConversationCount(Chatflow $chatflow): int
    {
        return $chatflow->conversations()->count();
    }

    public function getCompletionRate(Chatflow $chatflow): float
    {
        return $chatflow->completion_rate;
    }
}
