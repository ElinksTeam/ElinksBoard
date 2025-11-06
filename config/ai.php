<?php

return [
    /*
    |--------------------------------------------------------------------------
    | OpenAI Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for OpenAI API integration
    |
    */
    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'organization' => env('OPENAI_ORGANIZATION'),
        'embedding_model' => env('OPENAI_EMBEDDING_MODEL', 'text-embedding-3-small'),
        'chat_model' => env('OPENAI_CHAT_MODEL', 'gpt-4-turbo-preview'),
        'timeout' => env('OPENAI_TIMEOUT', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | AI Knowledge Base Features
    |--------------------------------------------------------------------------
    |
    | Enable or disable AI-powered features for the knowledge base
    |
    */
    'knowledge' => [
        // Feature toggles
        'enabled' => env('AI_KNOWLEDGE_ENABLED', false),
        'semantic_search' => env('AI_SEMANTIC_SEARCH_ENABLED', false),
        'chat' => env('AI_CHAT_ENABLED', false),
        'recommendations' => env('AI_RECOMMENDATIONS_ENABLED', false),
        'auto_tagging' => env('AI_AUTO_TAGGING_ENABLED', false),

        // Rate limits
        'rate_limits' => [
            'chat_per_hour' => env('AI_CHAT_RATE_LIMIT', 10),
            'search_per_minute' => env('AI_SEARCH_RATE_LIMIT', 30),
            'embedding_per_day' => env('AI_EMBEDDING_RATE_LIMIT', 1000),
        ],

        // Chat configuration
        'chat' => [
            'max_tokens' => env('AI_CHAT_MAX_TOKENS', 1000),
            'temperature' => env('AI_CHAT_TEMPERATURE', 0.7),
            'context_articles' => env('AI_CHAT_CONTEXT_ARTICLES', 3),
            'system_prompt' => env('AI_CHAT_SYSTEM_PROMPT', '你是 ElinksBoard 的智能助手。根據提供的知識庫內容回答使用者問題。'),
        ],

        // Embedding configuration
        'embedding' => [
            'batch_size' => env('AI_EMBEDDING_BATCH_SIZE', 100),
            'cache_ttl' => env('AI_EMBEDDING_CACHE_TTL', 86400 * 7), // 7 days
            'similarity_threshold' => env('AI_SIMILARITY_THRESHOLD', 0.7),
        ],

        // Recommendation configuration
        'recommendations' => [
            'max_results' => env('AI_RECOMMENDATIONS_MAX', 5),
            'similar_weight' => 0.4,
            'popular_weight' => 0.3,
            'personalized_weight' => 0.3,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Caching
    |--------------------------------------------------------------------------
    |
    | Cache configuration for AI features
    |
    */
    'cache' => [
        'enabled' => env('AI_CACHE_ENABLED', true),
        'driver' => env('AI_CACHE_DRIVER', 'redis'),
        'prefix' => env('AI_CACHE_PREFIX', 'ai:'),
        'ttl' => [
            'embeddings' => 86400 * 7, // 7 days
            'search_results' => 3600, // 1 hour
            'recommendations' => 1800, // 30 minutes
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    |
    | Logging configuration for AI operations
    |
    */
    'logging' => [
        'enabled' => env('AI_LOGGING_ENABLED', true),
        'channel' => env('AI_LOG_CHANNEL', 'daily'),
        'level' => env('AI_LOG_LEVEL', 'info'),
        'log_queries' => env('AI_LOG_QUERIES', true),
        'log_responses' => env('AI_LOG_RESPONSES', false), // May contain sensitive data
    ],

    /*
    |--------------------------------------------------------------------------
    | Cost Control
    |--------------------------------------------------------------------------
    |
    | Configuration for controlling API costs
    |
    */
    'cost_control' => [
        'enabled' => env('AI_COST_CONTROL_ENABLED', true),
        'daily_budget' => env('AI_DAILY_BUDGET', 10.0), // USD
        'monthly_budget' => env('AI_MONTHLY_BUDGET', 100.0), // USD
        'alert_threshold' => env('AI_COST_ALERT_THRESHOLD', 0.8), // 80%
    ],
];
