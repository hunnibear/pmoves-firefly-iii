<?php

declare(strict_types=1);

namespace FireflyIII\Console\Commands;

use Illuminate\Console\Command;
use FireflyIII\Services\Internal\AIService;

class TestAIConnectivity extends Command
{
    protected $signature = 'firefly-iii:test-ai-connectivity';
    protected $description = 'Test AI provider connectivity';

    public function handle(AIService $aiService)
    {
        $this->info('Testing AI provider connectivity...');
        $results = $aiService->testConnectivity();
        $this->info(json_encode($results, JSON_PRETTY_PRINT));
    }
}
