<?php

// config for Syofyanzuhad/FilamentChatflow
return [
    /*
    |--------------------------------------------------------------------------
    | Models
    |--------------------------------------------------------------------------
    |
    | The model classes used by the chatflow package. You can extend these
    | models to add custom functionality if needed.
    |
    */
    'models' => [
        'chatflow' => \Syofyanzuhad\FilamentChatflow\Models\Chatflow::class,
        'chatflow_step' => \Syofyanzuhad\FilamentChatflow\Models\ChatflowStep::class,
        'chatflow_conversation' => \Syofyanzuhad\FilamentChatflow\Models\ChatflowConversation::class,
        'chatflow_message' => \Syofyanzuhad\FilamentChatflow\Models\ChatflowMessage::class,
        'chatflow_analytic' => \Syofyanzuhad\FilamentChatflow\Models\ChatflowAnalytic::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Table Names
    |--------------------------------------------------------------------------
    |
    | The database table names used by the chatflow package. You can customize
    | these if you need different table names.
    |
    */
    'table_names' => [
        'chatflows' => 'chatflows',
        'chatflow_steps' => 'chatflow_steps',
        'chatflow_conversations' => 'chatflow_conversations',
        'chatflow_messages' => 'chatflow_messages',
        'chatflow_analytics' => 'chatflow_analytics',
    ],

    /*
    |--------------------------------------------------------------------------
    | Widget Settings
    |--------------------------------------------------------------------------
    |
    | Default settings for the chat widget that appears on your frontend.
    |
    */
    'widget' => [
        'position' => env('CHATFLOW_POSITION', 'bottom-right'), // bottom-left, top-right, top-left
        'theme_color' => env('CHATFLOW_THEME_COLOR', '#3b82f6'),
        'sound_enabled' => env('CHATFLOW_SOUND_ENABLED', true),
        'notification_sound' => 'notification.mp3',
        'message_sound' => 'message.mp3',
        'show_badge' => true,
        'auto_open' => false,
        'z_index' => 9999,
    ],

    /*
    |--------------------------------------------------------------------------
    | Conversation Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for chat conversations including expiration and cleanup.
    |
    */
    'conversation' => [
        'expire_after_hours' => env('CHATFLOW_EXPIRE_HOURS', 24),
        'auto_cleanup' => env('CHATFLOW_AUTO_CLEANUP', true),
        'cleanup_schedule' => 'daily', // hourly, daily, weekly
        'store_metadata' => true, // Store user agent, IP, etc.
    ],

    /*
    |--------------------------------------------------------------------------
    | Email Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for sending chat transcripts via email.
    |
    */
    'email' => [
        'enabled' => env('CHATFLOW_EMAIL_ENABLED', true),
        'send_to_user' => env('CHATFLOW_EMAIL_TO_USER', true),
        'send_to_admin' => env('CHATFLOW_EMAIL_TO_ADMIN', false),
        'admin_email' => env('CHATFLOW_ADMIN_EMAIL', null),
        'queue' => env('CHATFLOW_EMAIL_QUEUE', 'default'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Analytics Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for analytics data collection and retention.
    |
    */
    'analytics' => [
        'enabled' => env('CHATFLOW_ANALYTICS_ENABLED', true),
        'retention_days' => env('CHATFLOW_ANALYTICS_RETENTION', 90),
        'track_user_agent' => true,
        'track_ip_address' => true,
        'track_referrer' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging Settings
    |--------------------------------------------------------------------------
    |
    | Control what gets logged from the chatflow package.
    |
    */
    'logging' => [
        'log_conversations' => env('CHATFLOW_LOG_CONVERSATIONS', true),
        'log_messages' => env('CHATFLOW_LOG_MESSAGES', false),
        'log_errors' => env('CHATFLOW_LOG_ERRORS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Multi-Language Support
    |--------------------------------------------------------------------------
    |
    | Supported locales for the chatflow package.
    |
    */
    'locales' => [
        'en',
        'id',
    ],

    'default_locale' => env('CHATFLOW_DEFAULT_LOCALE', 'en'),

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Rate limiting for API endpoints to prevent abuse.
    |
    */
    'rate_limit' => [
        'enabled' => env('CHATFLOW_RATE_LIMIT_ENABLED', true),
        'max_attempts' => env('CHATFLOW_RATE_LIMIT_ATTEMPTS', 60),
        'decay_minutes' => env('CHATFLOW_RATE_LIMIT_DECAY', 1),
    ],

    /*
    |--------------------------------------------------------------------------
    | Feature Flags
    |--------------------------------------------------------------------------
    |
    | Enable or disable specific features.
    |
    */
    'features' => [
        'visual_builder' => env('CHATFLOW_VISUAL_BUILDER', false), // Future feature
        'ai_integration' => env('CHATFLOW_AI_INTEGRATION', false), // Future feature
        'file_uploads' => env('CHATFLOW_FILE_UPLOADS', false), // Future feature
        'voice_messages' => env('CHATFLOW_VOICE_MESSAGES', false), // Future feature
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    |
    | Cache configuration for chatflow data.
    |
    */
    'cache' => [
        'enabled' => env('CHATFLOW_CACHE_ENABLED', true),
        'ttl' => env('CHATFLOW_CACHE_TTL', 3600), // seconds
        'prefix' => 'chatflow',
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    |
    | Security configuration for the chatflow package.
    |
    */
    'security' => [
        'sanitize_input' => true,
        'max_message_length' => 1000,
        'allowed_html_tags' => [], // Empty means no HTML allowed
        'csrf_protection' => true,
    ],
];
