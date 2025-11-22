<?php

use Illuminate\Support\Facades\Route;
use Syofyanzuhad\FilamentChatflow\Http\Controllers\ChatflowApiController;

Route::prefix('api/chatflow')
    ->middleware(['api'])
    ->name('chatflow.api.')
    ->group(function () {
        // Get chatflow configuration
        Route::get('{chatflow}/config', [ChatflowApiController::class, 'getConfig'])
            ->name('config');

        // Start a new conversation
        Route::post('{chatflow}/start', [ChatflowApiController::class, 'startConversation'])
            ->middleware(config('chatflow.rate_limit.enabled', true) ? 'throttle:' . config('chatflow.rate_limit.max_attempts', 60) . ',' . config('chatflow.rate_limit.decay_minutes', 1) : [])
            ->name('start');

        // Send a message in conversation
        Route::post('message', [ChatflowApiController::class, 'sendMessage'])
            ->middleware(config('chatflow.rate_limit.enabled', true) ? 'throttle:' . config('chatflow.rate_limit.max_attempts', 60) . ',' . config('chatflow.rate_limit.decay_minutes', 1) : [])
            ->name('message');

        // End a conversation
        Route::post('end', [ChatflowApiController::class, 'endConversation'])
            ->name('end');

        // Get conversation history
        Route::get('history', [ChatflowApiController::class, 'getHistory'])
            ->name('history');
    });
