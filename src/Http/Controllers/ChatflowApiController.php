<?php

namespace Syofyanzuhad\FilamentChatflow\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Syofyanzuhad\FilamentChatflow\Models\Chatflow;
use Syofyanzuhad\FilamentChatflow\Models\ChatflowConversation;
use Syofyanzuhad\FilamentChatflow\Services\ChatflowEngine;
use Syofyanzuhad\FilamentChatflow\Services\ConversationManager;

class ChatflowApiController extends Controller
{
    public function __construct(
        protected ChatflowEngine $engine,
        protected ConversationManager $conversationManager
    ) {}

    public function getConfig(int $chatflowId): JsonResponse
    {
        $chatflow = Chatflow::where('is_active', true)
            ->findOrFail($chatflowId);

        $locale = request()->header('Accept-Language', config('chatflow.default_locale', 'en'));

        $welcomeData = $this->engine->getWelcomeMessage($chatflow, $locale);

        return response()->json([
            'success' => true,
            'data' => [
                'chatflow' => [
                    'id' => $chatflow->id,
                    'name' => $chatflow->name,
                    'position' => $chatflow->position,
                    'settings' => $chatflow->settings,
                ],
                'welcome_message' => $welcomeData['welcome_message'],
                'first_step' => $welcomeData['first_step'] ?? null,
                'content' => $welcomeData['content'] ?? null,
                'options' => $welcomeData['options'] ?? null,
            ],
        ]);
    }

    public function startConversation(Request $request, int $chatflowId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'nullable|integer|exists:users,id',
            'locale' => 'nullable|string|in:' . implode(',', config('chatflow.locales', ['en', 'id'])),
            'metadata' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $chatflow = Chatflow::where('is_active', true)
            ->findOrFail($chatflowId);

        $metadata = array_merge($request->input('metadata', []), [
            'user_agent' => $request->userAgent(),
            'ip_address' => $request->ip(),
            'referrer' => $request->header('referer'),
        ]);

        $conversation = $this->engine->startConversation(
            $chatflow,
            $request->input('user_id'),
            $metadata
        );

        // Get first step
        $locale = $request->input('locale', config('chatflow.default_locale', 'en'));
        $conversation->update(['locale' => $locale]);

        $welcomeData = $this->engine->getWelcomeMessage($chatflow, $locale);

        return response()->json([
            'success' => true,
            'data' => [
                'conversation_id' => $conversation->id,
                'session_id' => $conversation->session_id,
                'welcome_message' => $welcomeData['welcome_message'],
                'first_step' => $welcomeData['first_step'] ?? null,
                'content' => $welcomeData['content'] ?? null,
                'options' => $welcomeData['options'] ?? null,
            ],
        ], 201);
    }

    public function sendMessage(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'session_id' => 'required|string|exists:chatflow_conversations,session_id',
            'message' => 'required|string|max:' . config('chatflow.security.max_message_length', 1000),
            'selected_option' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $conversation = $this->conversationManager->findBySessionId($request->input('session_id'));

        if (! $conversation) {
            return response()->json([
                'success' => false,
                'message' => 'Conversation not found or expired',
            ], 404);
        }

        if (! $conversation->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Conversation has ended',
            ], 400);
        }

        $message = $this->sanitizeInput($request->input('message'));
        $selectedOption = $request->input('selected_option');

        $response = $this->engine->processUserInput($conversation, $message, $selectedOption);

        return response()->json([
            'success' => true,
            'data' => [
                'conversation_id' => $response['conversation_id'],
                'step' => $response['step'],
                'message' => [
                    'id' => $response['message']->id,
                    'type' => $response['message']->type,
                    'content' => $response['message']->content,
                    'options' => $response['message']->options,
                    'created_at' => $response['message']->created_at->toIso8601String(),
                ],
                'is_completed' => $response['is_completed'],
            ],
        ]);
    }

    public function endConversation(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'session_id' => 'required|string|exists:chatflow_conversations,session_id',
            'abandoned' => 'nullable|boolean',
            'user_email' => 'nullable|email',
            'user_name' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $conversation = $this->conversationManager->findBySessionId($request->input('session_id'));

        if (! $conversation) {
            return response()->json([
                'success' => false,
                'message' => 'Conversation not found',
            ], 404);
        }

        // Update user info if provided
        if ($request->has('user_email') || $request->has('user_name')) {
            $this->conversationManager->updateUserInfo(
                $conversation,
                $request->input('user_email'),
                $request->input('user_name')
            );
        }

        $abandoned = $request->input('abandoned', false);
        $this->engine->endConversation($conversation, $abandoned);

        return response()->json([
            'success' => true,
            'message' => 'Conversation ended successfully',
            'data' => [
                'conversation_id' => $conversation->id,
                'status' => $conversation->status,
                'duration' => $conversation->duration_in_minutes,
            ],
        ]);
    }

    public function getHistory(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'session_id' => 'required|string|exists:chatflow_conversations,session_id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $conversation = ChatflowConversation::where('session_id', $request->input('session_id'))
            ->firstOrFail();

        $history = $this->engine->getConversationHistory($conversation);

        return response()->json([
            'success' => true,
            'data' => [
                'conversation' => [
                    'id' => $conversation->id,
                    'status' => $conversation->status,
                    'started_at' => $conversation->started_at->toIso8601String(),
                    'ended_at' => $conversation->ended_at?->toIso8601String(),
                ],
                'messages' => $history,
            ],
        ]);
    }

    protected function sanitizeInput(string $input): string
    {
        if (! config('chatflow.security.sanitize_input', true)) {
            return $input;
        }

        $allowedTags = config('chatflow.security.allowed_html_tags', []);

        if (empty($allowedTags)) {
            return strip_tags($input);
        }

        return strip_tags($input, implode('', array_map(fn ($tag) => "<{$tag}>", $allowedTags)));
    }
}
