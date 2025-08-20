<?php

declare(strict_types=1);

return [
    
    /*
    |--------------------------------------------------------------------------
    | AI Auto-Categorization Settings
    |--------------------------------------------------------------------------
    */
    
    'auto_categorize_enabled' => env('AI_AUTO_CATEGORIZE_ENABLED', false),
    
    /*
    |--------------------------------------------------------------------------
    | AI Service Configuration
    |--------------------------------------------------------------------------
    */
    
    'default_provider' => env('AI_DEFAULT_PROVIDER', 'ollama'),
    'default_model' => env('AI_DEFAULT_MODEL', 'gemma3:12b'),
    'fallback_model' => env('AI_FALLBACK_MODEL', 'gemma3:270m'),
    'temperature' => (float) env('AI_TEMPERATURE', 0.3),
    'max_tokens' => (int) env('AI_MAX_TOKENS', 2048),
    'timeout' => (int) env('AI_TIMEOUT', 60),
    
    'providers' => [
        'openai' => [
            'api_key' => env('OPENAI_API_KEY'),
            'model' => env('OPENAI_MODEL', 'gpt-3.5-turbo'),
            'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
        ],
        'groq' => [
            'api_key' => env('GROQ_API_KEY'),
            'model' => env('GROQ_MODEL', 'mixtral-8x7b-32768'),
            'base_url' => env('GROQ_BASE_URL', 'https://api.groq.com/openai/v1'),
        ],
        'anthropic' => [
            'api_key' => env('ANTHROPIC_API_KEY'),
            'model' => env('ANTHROPIC_MODEL', 'claude-3-haiku-20240307'),
            'base_url' => env('ANTHROPIC_BASE_URL', 'https://api.anthropic.com'),
        ],
        'google' => [
            'api_key' => env('GEMINI_API_KEY'),
            'model' => env('GEMINI_MODEL', 'gemini-1.5-flash'),
            'base_url' => env('GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com'),
        ],
        'ollama' => [
            'base_url' => env('OLLAMA_BASE_URL', 'http://localhost:11434'),
            'timeout' => env('OLLAMA_TIMEOUT', 120),
            'models' => [
                'gemma3:12b' => [
                    'name' => 'gemma3:12b',
                    'context_length' => env('AI_GEMMA3_12B_CONTEXT_LENGTH', 131072),
                    'max_tokens' => env('AI_MAX_TOKENS', 4096),
                    'temperature' => env('AI_TEMPERATURE', 0.7),
                    'top_p' => 0.95,
                    'top_k' => 64,
                    'stop' => ['<end_of_turn>'],
                    'capabilities' => ['completion', 'vision'],
                    'parameters' => '12.2B',
                    'quantization' => 'Q4_K_M'
                ],
                'gemma3:270m' => [
                    'name' => 'gemma3:270m',
                    'context_length' => env('AI_GEMMA3_270M_CONTEXT_LENGTH', 32768),
                    'max_tokens' => env('AI_MAX_TOKENS', 4096),
                    'temperature' => env('AI_TEMPERATURE', 0.7),
                    'top_p' => 0.95,
                    'top_k' => 64,
                    'stop' => ['<end_of_turn>'],
                    'capabilities' => ['completion'],
                    'parameters' => '268.10M',
                    'quantization' => 'Q8_0'
                ],
                'mistral-small3.2:24b' => [
                    'name' => 'mistral-small3.2:24b',
                    'context_length' => env('AI_MISTRAL_SMALL_24B_CONTEXT_LENGTH', 131072),
                    'max_tokens' => env('AI_MAX_TOKENS', 4096),
                    'temperature' => 0.15,
                    'capabilities' => ['completion', 'vision', 'tools'],
                    'parameters' => '24.0B',
                    'quantization' => 'Q4_K_M',
                    'system_prompt' => 'You are Mistral Small 3.2, a Large Language Model (LLM) created by Mistral AI.'
                ],
                'gpt-oss:20b' => [
                    'name' => 'gpt-oss:20b',
                    'context_length' => env('AI_GPT_OSS_20B_CONTEXT_LENGTH', 8192),
                    'max_tokens' => env('AI_MAX_TOKENS', 4096),
                    'temperature' => env('AI_TEMPERATURE', 0.7),
                    'capabilities' => ['completion'],
                    'parameters' => '20B'
                ]
            ]
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | LangExtract Configuration
    |--------------------------------------------------------------------------
    */

    'langextract' => [
        'provider' => env('LANGEXTRACT_PROVIDER', 'ollama'),
        'model' => env('LANGEXTRACT_MODEL', 'gemma3:270m'),
        'base_url' => env('LANGEXTRACT_BASE_URL', 'http://localhost:11434'),
        'api_key' => env('LANGEXTRACT_API_KEY'),
        'context_length' => (int) env('LANGEXTRACT_CONTEXT_LENGTH', 32768),
        'extraction_passes' => (int) env('LANGEXTRACT_EXTRACTION_PASSES', 2),
        'max_char_buffer' => (int) env('LANGEXTRACT_MAX_CHAR_BUFFER', 4000),
        'max_workers' => (int) env('LANGEXTRACT_MAX_WORKERS', 2),
        'timeout' => (int) env('LANGEXTRACT_TIMEOUT', 120),
        'temperature' => (float) env('LANGEXTRACT_TEMPERATURE', 0.15),
        'top_p' => (float) env('LANGEXTRACT_TOP_P', 0.95),
        'top_k' => (int) env('LANGEXTRACT_TOP_K', 64),
        'fence_output' => (bool) env('LANGEXTRACT_FENCE_OUTPUT', false),
        'use_schema_constraints' => (bool) env('LANGEXTRACT_USE_SCHEMA_CONSTRAINTS', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Document Processing Configuration
    |--------------------------------------------------------------------------
    */

    'document_processing' => [
        'receipt' => [
            'max_file_size' => (int) env('AI_RECEIPT_MAX_SIZE', 10240), // 10MB
            'supported_types' => ['jpg', 'jpeg', 'png', 'pdf', 'txt'],
            'fallback_enabled' => (bool) env('AI_RECEIPT_FALLBACK_ENABLED', true),
        ],
        'bank_statement' => [
            'max_file_size' => (int) env('AI_STATEMENT_MAX_SIZE', 51200), // 50MB
            'supported_types' => ['pdf', 'csv', 'txt'],
            'fallback_enabled' => (bool) env('AI_STATEMENT_FALLBACK_ENABLED', true),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Couples AI Configuration
    |--------------------------------------------------------------------------
    */

    'couples' => [
        'categorization' => [
            'provider' => env('AI_COUPLES_CAT_PROVIDER', 'ollama'),
            'model' => env('AI_COUPLES_CAT_MODEL', 'gemma3:270m'),
            'confidence_threshold' => (float) env('AI_COUPLES_CAT_THRESHOLD', 0.7),
        ],
        'assignment' => [
            'provider' => env('AI_COUPLES_ASSIGN_PROVIDER', 'ollama'),
            'model' => env('AI_COUPLES_ASSIGN_MODEL', 'gemma3:270m'),
            'confidence_threshold' => (float) env('AI_COUPLES_ASSIGN_THRESHOLD', 0.6),
        ],
        'shared_categories' => [
            'default' => 'Groceries, Utilities, Rent, Insurance, Healthcare, Home Maintenance',
            'configurable' => (bool) env('AI_COUPLES_CUSTOM_CATEGORIES', true),
        ],
    ],
];