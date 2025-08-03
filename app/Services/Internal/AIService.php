<?php

declare(strict_types=1);

namespace FireflyIII\Services\Internal;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * AI Service for integrating local and cloud AI models
 * Supports Ollama (local), OpenAI, and Groq
 */
class AIService
{
    private Client $httpClient;
    private array $config;

    public function __construct()
    {
        $this->httpClient = new Client(['timeout' => 30]);
        $this->config = [
            'ollama_url' => env('OLLAMA_URL', 'http://localhost:11434'),
            'openai_key' => env('OPENAI_API_KEY'),
            'groq_key' => env('GROQ_API_KEY'),
            'default_provider' => env('AI_DEFAULT_PROVIDER', 'ollama'),
        ];
    }

    /**
     * Categorize a transaction using AI
     */
    public function categorizeTransaction(TransactionJournal $journal): ?string
    {
        $description = $journal->description;
        $amount = $journal->transactions->first()?->amount ?? 0;
        $account = $journal->transactions->first()?->account->name ?? '';

        $prompt = "Categorize this financial transaction into one of these categories: " .
                 "Food & Dining, Transportation, Entertainment, Bills & Utilities, Shopping, " .
                 "Healthcare, Income, Business, Travel, Education, Other. " .
                 "Description: '{$description}', Amount: {$amount}, Account: '{$account}'. " .
                 "Reply with only the category name.";

        try {
            $category = $this->callAI($prompt, 'categorization');
            Log::info('AI categorized transaction', [
                'journal_id' => $journal->id,
                'description' => $description,
                'category' => $category
            ]);
            return trim($category);
        } catch (\Exception $e) {
            Log::error('AI categorization failed', [
                'journal_id' => $journal->id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Generate financial insights from transaction data
     */
    public function generateInsights(array $transactions, int $userId): array
    {
        $cacheKey = "ai_insights_user_{$userId}_" . md5(serialize($transactions));
        
        return Cache::remember($cacheKey, 3600, function() use ($transactions) {
            $summary = $this->summarizeTransactions($transactions);
            
            $prompt = "As a financial advisor, analyze this spending data and provide insights: {$summary}. " .
                     "Provide 3-4 actionable insights about spending patterns, potential savings, " .
                     "and financial recommendations. Format as a JSON array with 'insight', 'type' (tip/warning/info), and 'priority' (high/medium/low).";

            try {
                $response = $this->callAI($prompt, 'insights');
                $insights = json_decode($response, true);
                
                if (!$insights || !is_array($insights)) {
                    return $this->getDefaultInsights($transactions);
                }
                
                return $insights;
            } catch (\Exception $e) {
                Log::error('AI insights generation failed', ['error' => $e->getMessage()]);
                return $this->getDefaultInsights($transactions);
            }
        });
    }

    /**
     * Chat with AI about financial data
     */
    public function chat(string $message, array $context = []): string
    {
        $systemPrompt = "You are a helpful financial advisor assistant for Firefly III. " .
                       "You help users understand their spending, budgeting, and financial planning. " .
                       "Be concise but helpful. Reference their actual data when relevant.";

        $contextString = empty($context) ? '' : "User's financial context: " . json_encode($context);
        
        $fullPrompt = "{$systemPrompt}\n{$contextString}\nUser question: {$message}";

        try {
            return $this->callAI($fullPrompt, 'chat');
        } catch (\Exception $e) {
            Log::error('AI chat failed', ['error' => $e->getMessage()]);
            return "I'm sorry, I'm having trouble connecting to the AI service right now. Please try again later.";
        }
    }

    /**
     * Detect spending anomalies
     */
    public function detectAnomalies(array $transactions): array
    {
        $anomalies = [];
        
        // Group by category and calculate statistics
        $categoryStats = [];
        foreach ($transactions as $transaction) {
            $category = $transaction['category'] ?? 'Other';
            $amount = abs($transaction['amount']);
            
            if (!isset($categoryStats[$category])) {
                $categoryStats[$category] = [];
            }
            $categoryStats[$category][] = $amount;
        }

        // Find outliers
        foreach ($categoryStats as $category => $amounts) {
            if (count($amounts) < 3) continue;
            
            $mean = array_sum($amounts) / count($amounts);
            $stdDev = $this->calculateStdDev($amounts, $mean);
            
            foreach ($amounts as $amount) {
                if ($amount > $mean + (2 * $stdDev)) {
                    $anomalies[] = [
                        'category' => $category,
                        'amount' => $amount,
                        'deviation' => round(($amount - $mean) / $stdDev, 2),
                        'type' => 'high_spending'
                    ];
                }
            }
        }

        return array_slice($anomalies, 0, 5); // Return top 5 anomalies
    }

    /**
     * Call AI service with fallback providers
     */
    private function callAI(string $prompt, string $type = 'general'): string
    {
        $providers = ['ollama', 'groq', 'openai'];
        $errors = [];

        foreach ($providers as $provider) {
            try {
                switch ($provider) {
                    case 'ollama':
                        return $this->callOllama($prompt);
                    case 'groq':
                        return $this->callGroq($prompt);
                    case 'openai':
                        return $this->callOpenAI($prompt);
                }
            } catch (\Exception $e) {
                $errors[$provider] = $e->getMessage();
                Log::warning("AI provider {$provider} failed", ['error' => $e->getMessage()]);
                continue;
            }
        }

        throw new \Exception('All AI providers failed: ' . json_encode($errors));
    }

    /**
     * Call Ollama local AI
     */
    private function callOllama(string $prompt): string
    {
        $response = $this->httpClient->post($this->config['ollama_url'] . '/api/generate', [
            'json' => [
                'model' => 'llama3.2:1b',
                'prompt' => $prompt,
                'stream' => false,
                'options' => [
                    'temperature' => 0.7,
                    'top_p' => 0.9
                ]
            ],
            'headers' => [
                'Content-Type' => 'application/json'
            ]
        ]);

        $data = json_decode($response->getBody()->getContents(), true);
        return $data['response'] ?? 'No response from AI';
    }

    /**
     * Call Groq cloud AI
     */
    private function callGroq(string $prompt): string
    {
        if (empty($this->config['groq_key'])) {
            throw new \Exception('Groq API key not configured');
        }

        $response = $this->httpClient->post('https://api.groq.com/openai/v1/chat/completions', [
            'json' => [
                'model' => 'llama3-8b-8192',
                'messages' => [
                    ['role' => 'user', 'content' => $prompt]
                ],
                'max_tokens' => 500,
                'temperature' => 0.7
            ],
            'headers' => [
                'Authorization' => 'Bearer ' . $this->config['groq_key'],
                'Content-Type' => 'application/json'
            ]
        ]);

        $data = json_decode($response->getBody()->getContents(), true);
        return $data['choices'][0]['message']['content'] ?? 'No response from AI';
    }

    /**
     * Call OpenAI API
     */
    private function callOpenAI(string $prompt): string
    {
        if (empty($this->config['openai_key'])) {
            throw new \Exception('OpenAI API key not configured');
        }

        $response = $this->httpClient->post('https://api.openai.com/v1/chat/completions', [
            'json' => [
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    ['role' => 'user', 'content' => $prompt]
                ],
                'max_tokens' => 500,
                'temperature' => 0.7
            ],
            'headers' => [
                'Authorization' => 'Bearer ' . $this->config['openai_key'],
                'Content-Type' => 'application/json'
            ]
        ]);

        $data = json_decode($response->getBody()->getContents(), true);
        return $data['choices'][0]['message']['content'] ?? 'No response from AI';
    }

    /**
     * Summarize transactions for AI analysis
     */
    private function summarizeTransactions(array $transactions): string
    {
        $totalSpent = 0;
        $categories = [];
        $count = count($transactions);

        foreach ($transactions as $transaction) {
            $amount = abs($transaction['amount']);
            $totalSpent += $amount;
            
            $category = $transaction['category'] ?? 'Other';
            $categories[$category] = ($categories[$category] ?? 0) + $amount;
        }

        arsort($categories);
        $topCategories = array_slice($categories, 0, 5, true);

        return "Total transactions: {$count}, Total spent: $" . number_format($totalSpent, 2) . 
               ", Top categories: " . json_encode($topCategories);
    }

    /**
     * Get default insights when AI fails
     */
    private function getDefaultInsights(array $transactions): array
    {
        return [
            [
                'insight' => 'Your financial data is being analyzed. Check back later for personalized insights.',
                'type' => 'info',
                'priority' => 'low'
            ]
        ];
    }

    /**
     * Calculate standard deviation
     */
    private function calculateStdDev(array $values, float $mean): float
    {
        $variance = array_sum(array_map(function($x) use ($mean) {
            return pow($x - $mean, 2);
        }, $values)) / count($values);
        
        return sqrt($variance);
    }

    /**
     * Test AI connectivity
     */
    public function testConnectivity(): array
    {
        $results = [
            'ollama' => false,
            'groq' => false,
            'openai' => false
        ];

        // Test Ollama
        try {
            $this->httpClient->get($this->config['ollama_url'] . '/api/tags', ['timeout' => 5]);
            $results['ollama'] = true;
        } catch (\Exception $e) {
            // Ollama not available
        }

        // Test Groq
        if (!empty($this->config['groq_key'])) {
            try {
                $this->callGroq('Test connection');
                $results['groq'] = true;
            } catch (\Exception $e) {
                // Groq not available
            }
        }

        // Test OpenAI
        if (!empty($this->config['openai_key'])) {
            try {
                $this->callOpenAI('Test connection');
                $results['openai'] = true;
            } catch (\Exception $e) {
                // OpenAI not available
            }
        }

        return $results;
    }
}
