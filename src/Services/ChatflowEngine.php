<?php

namespace Syofyanzuhad\FilamentChatflow\Services;

use Syofyanzuhad\FilamentChatflow\Models\Chatflow;
use Syofyanzuhad\FilamentChatflow\Models\ChatflowConversation;
use Syofyanzuhad\FilamentChatflow\Models\ChatflowMessage;
use Syofyanzuhad\FilamentChatflow\Models\ChatflowStep;

class ChatflowEngine
{
    public function __construct(
        protected ConversationManager $conversationManager
    ) {}

    public function startConversation(Chatflow $chatflow, ?int $userId = null, array $metadata = []): ChatflowConversation
    {
        return $this->conversationManager->createConversation($chatflow, $userId, $metadata);
    }

    public function processUserInput(ChatflowConversation $conversation, string $input, ?string $selectedOption = null): array
    {
        // Record user message
        $userMessage = $this->recordUserMessage($conversation, $input, $selectedOption);

        // Get next step based on current state
        $nextStep = $this->determineNextStep($conversation, $selectedOption);

        if (! $nextStep) {
            return $this->handleNoNextStep($conversation);
        }

        // Update conversation current step
        $conversation->update(['current_step_id' => $nextStep->id]);

        // Generate bot response
        $botMessage = $this->generateBotResponse($conversation, $nextStep);

        // Check if this is an end step
        if ($nextStep->isType(ChatflowStep::TYPE_END)) {
            $this->conversationManager->markAsCompleted($conversation);
        }

        return [
            'conversation_id' => $conversation->id,
            'step' => $nextStep,
            'message' => $botMessage,
            'is_completed' => $nextStep->isType(ChatflowStep::TYPE_END),
        ];
    }

    public function getWelcomeMessage(Chatflow $chatflow, string $locale = 'en'): array
    {
        $firstStep = $chatflow->rootSteps()->first();

        $welcomeMessage = ChatflowMessage::create([
            'conversation_id' => null,
            'step_id' => null,
            'type' => ChatflowMessage::TYPE_BOT,
            'content' => $chatflow->getWelcomeMessageForLocale($locale),
        ]);

        $response = [
            'welcome_message' => $welcomeMessage->content,
            'messages' => [$welcomeMessage],
        ];

        if ($firstStep) {
            $response['first_step'] = $firstStep;
            $response['content'] = $firstStep->getContentForLocale($locale);
            $response['options'] = $firstStep->getOptionsForLocale($locale);
        }

        return $response;
    }

    protected function recordUserMessage(ChatflowConversation $conversation, string $content, ?string $selectedOption = null): ChatflowMessage
    {
        return ChatflowMessage::create([
            'conversation_id' => $conversation->id,
            'step_id' => $conversation->current_step_id,
            'type' => ChatflowMessage::TYPE_USER,
            'content' => $content,
            'selected_option' => $selectedOption,
        ]);
    }

    protected function determineNextStep(ChatflowConversation $conversation, ?string $selectedOption = null): ?ChatflowStep
    {
        $currentStep = $conversation->currentStep;

        if (! $currentStep) {
            // If no current step, get the first root step
            return $conversation->chatflow->rootSteps()->first();
        }

        // For question type, find next step based on selected option
        if ($currentStep->isType(ChatflowStep::TYPE_QUESTION) && $selectedOption) {
            $options = $currentStep->options ?? [];

            foreach ($options as $option) {
                if ($option['value'] === $selectedOption && isset($option['next_step_id'])) {
                    return ChatflowStep::find($option['next_step_id']);
                }
            }
        }

        // For message or condition type, use next_step_id
        if ($currentStep->next_step_id) {
            return ChatflowStep::find($currentStep->next_step_id);
        }

        // Check for child steps
        return $currentStep->children()->first();
    }

    protected function generateBotResponse(ChatflowConversation $conversation, ChatflowStep $step): ChatflowMessage
    {
        $locale = $conversation->locale ?? 'en';

        $message = ChatflowMessage::create([
            'conversation_id' => $conversation->id,
            'step_id' => $step->id,
            'type' => ChatflowMessage::TYPE_BOT,
            'content' => $step->getContentForLocale($locale),
            'options' => $step->isType(ChatflowStep::TYPE_QUESTION) ? $step->getOptionsForLocale($locale) : null,
        ]);

        return $message;
    }

    protected function handleNoNextStep(ChatflowConversation $conversation): array
    {
        $this->conversationManager->markAsCompleted($conversation);

        $endMessage = ChatflowMessage::create([
            'conversation_id' => $conversation->id,
            'step_id' => null,
            'type' => ChatflowMessage::TYPE_BOT,
            'content' => 'Thank you for chatting with us!',
        ]);

        return [
            'conversation_id' => $conversation->id,
            'step' => null,
            'message' => $endMessage,
            'is_completed' => true,
        ];
    }

    public function getConversationHistory(ChatflowConversation $conversation): array
    {
        return $conversation->messages()
            ->with('step')
            ->orderBy('created_at')
            ->get()
            ->map(function ($message) {
                return [
                    'id' => $message->id,
                    'type' => $message->type,
                    'content' => $message->content,
                    'options' => $message->options,
                    'selected_option' => $message->selected_option,
                    'created_at' => $message->created_at->toIso8601String(),
                ];
            })
            ->toArray();
    }

    public function endConversation(ChatflowConversation $conversation, bool $abandoned = false): void
    {
        if ($abandoned) {
            $this->conversationManager->markAsAbandoned($conversation);
        } else {
            $this->conversationManager->markAsCompleted($conversation);
        }
    }
}
