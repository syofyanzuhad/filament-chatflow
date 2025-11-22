<?php

namespace Syofyanzuhad\FilamentChatflow\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Syofyanzuhad\FilamentChatflow\Models\ChatflowConversation;

class ChatTranscriptMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public ChatflowConversation $conversation
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Chat Transcript - ' . $this->conversation->chatflow->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'filament-chatflow::emails.chat-transcript',
            with: [
                'conversation' => $this->conversation,
                'messages' => $this->conversation->messages()->with('step')->get(),
                'chatflow' => $this->conversation->chatflow,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
