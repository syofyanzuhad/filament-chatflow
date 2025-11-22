<?php

namespace Syofyanzuhad\FilamentChatflow\Services;

use Illuminate\Support\Facades\Mail;
use Syofyanzuhad\FilamentChatflow\Mail\ChatTranscriptMail;
use Syofyanzuhad\FilamentChatflow\Models\ChatflowConversation;

class EmailNotificationService
{
    public function sendTranscript(ChatflowConversation $conversation): bool
    {
        if (! $this->shouldSendEmail($conversation)) {
            return false;
        }

        $recipients = $this->getRecipients($conversation);

        if (empty($recipients)) {
            return false;
        }

        try {
            Mail::to($recipients)->send(new ChatTranscriptMail($conversation));

            return true;
        } catch (\Exception $e) {
            logger()->error('Failed to send chat transcript email', [
                'conversation_id' => $conversation->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    protected function shouldSendEmail(ChatflowConversation $conversation): bool
    {
        // Check if email is enabled globally
        if (! config('chatflow.email.enabled', true)) {
            return false;
        }

        // Check if email is enabled for this chatflow
        $settings = $conversation->chatflow->settings ?? [];

        if (isset($settings['email_enabled']) && ! $settings['email_enabled']) {
            return false;
        }

        // Only send for completed conversations
        if (! $conversation->isCompleted()) {
            return false;
        }

        return true;
    }

    protected function getRecipients(ChatflowConversation $conversation): array
    {
        $recipients = [];

        // Add user email if available and enabled
        if (config('chatflow.email.send_to_user', true) && $conversation->user_email) {
            $recipients[] = $conversation->user_email;
        }

        // Add admin emails if enabled
        if (config('chatflow.email.send_to_admin', false)) {
            $adminEmails = $this->getAdminEmails($conversation);
            $recipients = array_merge($recipients, $adminEmails);
        }

        // Add custom recipients from chatflow settings
        $settings = $conversation->chatflow->settings ?? [];
        if (isset($settings['email_recipients']) && is_array($settings['email_recipients'])) {
            $recipients = array_merge($recipients, $settings['email_recipients']);
        }

        return array_unique(array_filter($recipients));
    }

    protected function getAdminEmails(ChatflowConversation $conversation): array
    {
        $adminEmail = config('chatflow.email.admin_email');

        if ($adminEmail) {
            return is_array($adminEmail) ? $adminEmail : [$adminEmail];
        }

        return [];
    }
}
