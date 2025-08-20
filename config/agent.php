<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Agent Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the Transaction Intelligence Agent system
    | This includes service URLs, queue settings, and agent behavior parameters
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Agent Service URL
    |--------------------------------------------------------------------------
    |
    | The URL where the Python-based Transaction Intelligence Agent service
    | is running. This should point to the FastAPI service.
    |
    */
    'service_url' => env('AGENT_SERVICE_URL', 'http://localhost:8001'),

    /*
    |--------------------------------------------------------------------------
    | Agent Service Timeout
    |--------------------------------------------------------------------------
    |
    | Timeout in seconds for requests to the agent service
    |
    */
    'timeout' => env('AGENT_TIMEOUT', 60),

    /*
    |--------------------------------------------------------------------------
    | Queue Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for agent event processing queue
    |
    */
    'queue' => [
        'name' => env('AGENT_QUEUE_NAME', 'agent_processing'),
        'retries' => env('AGENT_QUEUE_RETRIES', 3),
        'timeout' => env('AGENT_QUEUE_TIMEOUT', 120),
    ],

    /*
    |--------------------------------------------------------------------------
    | Agent Behavior Settings
    |--------------------------------------------------------------------------
    |
    | Control how the agent behaves and what actions it can take
    |
    */
    'behavior' => [
        // Minimum confidence required for automatic actions
        'auto_action_threshold' => env('AGENT_AUTO_ACTION_THRESHOLD', 0.8),
        
        // Confidence threshold for requiring user approval
        'approval_threshold' => env('AGENT_APPROVAL_THRESHOLD', 0.6),
        
        // Enable automatic categorization
        'auto_categorize' => env('AGENT_AUTO_CATEGORIZE', true),
        
        // Enable automatic rule creation
        'auto_create_rules' => env('AGENT_AUTO_CREATE_RULES', false),
        
        // Enable anomaly detection
        'anomaly_detection' => env('AGENT_ANOMALY_DETECTION', true),
        
        // Enable pattern recognition
        'pattern_recognition' => env('AGENT_PATTERN_RECOGNITION', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | AI Service Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for AI services used by the agent
    |
    */
    'ai' => [
        'langextract_url' => env('LANGEXTRACT_URL', 'http://localhost:8000'),
        'ollama_url' => env('OLLAMA_URL', 'http://ollama:11434'),
        'default_model' => env('AGENT_DEFAULT_MODEL', 'gemma2:9b'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    |
    | Security configuration for webhook validation and authentication
    |
    */
    'security' => [
        // Webhook signature validation
        'validate_webhooks' => env('AGENT_VALIDATE_WEBHOOKS', true),
        'webhook_secret' => env('AGENT_WEBHOOK_SECRET'),
        
        // Allowed IP addresses for webhooks (development only)
        'allowed_webhook_ips' => [
            '127.0.0.1',
            '::1',
            'localhost',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Learning and Storage
    |--------------------------------------------------------------------------
    |
    | Configuration for agent learning and data storage
    |
    */
    'learning' => [
        // Enable learning from user corrections
        'enable_learning' => env('AGENT_ENABLE_LEARNING', true),
        
        // Cache settings for agent insights
        'cache_insights' => env('AGENT_CACHE_INSIGHTS', true),
        'cache_duration' => env('AGENT_CACHE_DURATION', 3600), // 1 hour
        
        // Storage for agent data
        'data_storage' => env('AGENT_DATA_STORAGE', 'database'), // database, redis, file
    ],

    /*
    |--------------------------------------------------------------------------
    | Monitoring and Metrics
    |--------------------------------------------------------------------------
    |
    | Configuration for agent monitoring and performance metrics
    |
    */
    'monitoring' => [
        // Enable metrics collection
        'collect_metrics' => env('AGENT_COLLECT_METRICS', true),
        
        // Metrics retention period (days)
        'metrics_retention' => env('AGENT_METRICS_RETENTION', 30),
        
        // Error notification settings
        'notify_on_errors' => env('AGENT_NOTIFY_ON_ERRORS', false),
        'error_threshold' => env('AGENT_ERROR_THRESHOLD', 0.1), // 10% error rate
    ],
];