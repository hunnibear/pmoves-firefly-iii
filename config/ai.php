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
    
    'providers' => [
        'openai' => [
            'api_key' => env('OPENAI_API_KEY'),
            'model' => env('OPENAI_MODEL', 'gpt-3.5-turbo'),
        ],
        'groq' => [
            'api_key' => env('GROQ_API_KEY'),
            'model' => env('GROQ_MODEL', 'mixtral-8x7b-32768'),
        ],
        'ollama' => [
            'base_url' => env('OLLAMA_BASE_URL', 'http://localhost:11434'),
            'model' => env('OLLAMA_MODEL', 'llama3.1'),
        ],
    ],
    
];