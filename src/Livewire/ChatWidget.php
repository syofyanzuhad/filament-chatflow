<?php

namespace Syofyanzuhad\FilamentChatflow\Livewire;

use Illuminate\Support\Facades\Http;
use Livewire\Component;
use Syofyanzuhad\FilamentChatflow\Models\Chatflow;

class ChatWidget extends Component
{
    public Chatflow $chatflow;

    public bool $isOpen = false;

    public bool $isMinimized = false;

    public bool $hasNewMessage = false;

    public ?string $sessionId = null;

    public ?int $conversationId = null;

    public array $messages = [];

    public string $userMessage = '';

    public bool $isLoading = false;

    public ?array $currentOptions = null;

    public bool $isCompleted = false;

    protected $listeners = ['chatWidgetToggle' => 'toggle'];

    public function mount(Chatflow $chatflow): void
    {
        $this->chatflow = $chatflow;
        $this->loadFromSession();
    }

    public function loadFromSession(): void
    {
        $sessionId = session()->get('chatflow_session_id');
        $conversationId = session()->get('chatflow_conversation_id');

        if ($sessionId && $conversationId) {
            $this->sessionId = $sessionId;
            $this->conversationId = $conversationId;
            $this->loadConversationHistory();
        }
    }

    public function toggle(): void
    {
        $this->isOpen = ! $this->isOpen;
        $this->isMinimized = false;

        if ($this->isOpen && ! $this->sessionId) {
            $this->startConversation();
        }

        $this->hasNewMessage = false;
    }

    public function minimize(): void
    {
        $this->isMinimized = true;
    }

    public function maximize(): void
    {
        $this->isMinimized = false;
        $this->hasNewMessage = false;
    }

    public function startConversation(): void
    {
        $this->isLoading = true;

        try {
            $response = Http::post(route('chatflow.api.start', ['chatflow' => $this->chatflow->id]), [
                'locale' => app()->getLocale(),
                'metadata' => [
                    'url' => url()->current(),
                ],
            ]);

            if ($response->successful()) {
                $data = $response->json('data');
                $this->sessionId = $data['session_id'];
                $this->conversationId = $data['conversation_id'];

                session()->put('chatflow_session_id', $this->sessionId);
                session()->put('chatflow_conversation_id', $this->conversationId);

                $this->messages[] = [
                    'type' => 'bot',
                    'content' => $data['welcome_message'],
                    'timestamp' => now()->toIso8601String(),
                ];

                if ($data['content']) {
                    $this->messages[] = [
                        'type' => 'bot',
                        'content' => $data['content'],
                        'options' => $data['options'] ?? null,
                        'timestamp' => now()->toIso8601String(),
                    ];

                    $this->currentOptions = $data['options'] ?? null;
                }
            }
        } catch (\Exception $e) {
            $this->addErrorMessage();
        } finally {
            $this->isLoading = false;
        }
    }

    public function sendMessage(?string $selectedOption = null): void
    {
        if (! $this->sessionId || $this->isCompleted) {
            return;
        }

        $message = trim($this->userMessage);

        if (empty($message) && ! $selectedOption) {
            return;
        }

        $this->isLoading = true;

        // Add user message to UI
        $this->messages[] = [
            'type' => 'user',
            'content' => $selectedOption ? $this->getOptionLabel($selectedOption) : $message,
            'timestamp' => now()->toIso8601String(),
        ];

        $this->userMessage = '';
        $this->currentOptions = null;

        try {
            $response = Http::post(route('chatflow.api.message'), [
                'session_id' => $this->sessionId,
                'message' => $selectedOption ?: $message,
                'selected_option' => $selectedOption,
            ]);

            if ($response->successful()) {
                $data = $response->json('data');

                $this->messages[] = [
                    'type' => 'bot',
                    'content' => $data['message']['content'],
                    'options' => $data['message']['options'] ?? null,
                    'timestamp' => $data['message']['created_at'],
                ];

                $this->currentOptions = $data['message']['options'] ?? null;
                $this->isCompleted = $data['is_completed'] ?? false;

                if (! $this->isOpen) {
                    $this->hasNewMessage = true;
                    $this->dispatch('playNotificationSound');
                }
            } else {
                $this->addErrorMessage();
            }
        } catch (\Exception $e) {
            $this->addErrorMessage();
        } finally {
            $this->isLoading = false;
            $this->dispatch('scrollToBottom');
        }
    }

    public function selectOption(string $value): void
    {
        $this->sendMessage($value);
    }

    public function endConversation(): void
    {
        if (! $this->sessionId) {
            return;
        }

        try {
            Http::post(route('chatflow.api.end'), [
                'session_id' => $this->sessionId,
                'abandoned' => false,
            ]);

            $this->resetConversation();
        } catch (\Exception $e) {
            $this->addErrorMessage();
        }
    }

    public function restartConversation(): void
    {
        $this->resetConversation();
        $this->startConversation();
    }

    protected function resetConversation(): void
    {
        $this->sessionId = null;
        $this->conversationId = null;
        $this->messages = [];
        $this->userMessage = '';
        $this->currentOptions = null;
        $this->isCompleted = false;

        session()->forget(['chatflow_session_id', 'chatflow_conversation_id']);
    }

    protected function loadConversationHistory(): void
    {
        try {
            $response = Http::get(route('chatflow.api.history'), [
                'session_id' => $this->sessionId,
            ]);

            if ($response->successful()) {
                $data = $response->json('data');
                $this->messages = $data['messages'];
            }
        } catch (\Exception $e) {
            // Silent fail, start fresh
        }
    }

    protected function addErrorMessage(): void
    {
        $this->messages[] = [
            'type' => 'bot',
            'content' => __('chatflow::chatflow.messages.error'),
            'timestamp' => now()->toIso8601String(),
        ];
    }

    protected function getOptionLabel(string $value): string
    {
        if (! $this->currentOptions) {
            return $value;
        }

        foreach ($this->currentOptions as $option) {
            if ($option['value'] === $value) {
                return is_array($option['label']) ? $option['label'][app()->getLocale()] ?? $option['label']['en'] : $option['label'];
            }
        }

        return $value;
    }

    public function render()
    {
        return view('filament-chatflow::livewire.chat-widget');
    }
}
