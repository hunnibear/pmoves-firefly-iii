<?php

declare(strict_types=1);

namespace FireflyIII\Services;

use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class CouplesAIService
{
    private string $pythonPath;
    private string $tempPath;
    
    public function __construct()
    {
        $this->pythonPath = 'C:/Users/russe/Documents/GitHub/pmoves-firefly-iii/.venv/Scripts/python.exe';
        $this->tempPath = storage_path('app/temp');
        
        if (!file_exists($this->tempPath)) {
            mkdir($this->tempPath, 0755, true);
        }
    }

    /**
     * Categorize transaction for couples with AI
     */
    public function categorizeForCouples(array $transactionData, array $couplesProfile = []): array
    {
        try {
            $prompt = $this->buildCategorizationPrompt($transactionData, $couplesProfile);
            
            // Create Python script for AI categorization
            $pythonScript = $this->createCategorizationScript($prompt);
            $scriptPath = $this->tempPath . '/categorize_' . uniqid() . '.py';
            file_put_contents($scriptPath, $pythonScript);

            // Execute AI categorization
            $process = new Process([
                $this->pythonPath,
                $scriptPath
            ]);
            
            $process->setTimeout(30); // 30 second timeout
            $process->run();

            // Clean up
            unlink($scriptPath);

            if (!$process->isSuccessful()) {
                Log::error('AI categorization failed', [
                    'error' => $process->getErrorOutput()
                ]);
                
                return $this->fallbackCategorization($transactionData);
            }

            $result = json_decode($process->getOutput(), true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Failed to parse AI categorization output', [
                    'output' => $process->getOutput()
                ]);
                
                return $this->fallbackCategorization($transactionData);
            }

            return $this->normalizeCategorizationResult($result);
            
        } catch (\Exception $e) {
            Log::error('AI categorization error', [
                'message' => $e->getMessage()
            ]);
            
            return $this->fallbackCategorization($transactionData);
        }
    }

    /**
     * Suggest expense assignment (partner1, partner2, or shared)
     */
    public function suggestPartnerAssignment(array $transactionData, array $couplesProfile = []): array
    {
        try {
            $prompt = $this->buildAssignmentPrompt($transactionData, $couplesProfile);
            
            $pythonScript = $this->createAssignmentScript($prompt);
            $scriptPath = $this->tempPath . '/assign_' . uniqid() . '.py';
            file_put_contents($scriptPath, $pythonScript);

            $process = new Process([
                $this->pythonPath,
                $scriptPath
            ]);
            
            $process->setTimeout(30);
            $process->run();

            unlink($scriptPath);

            if (!$process->isSuccessful()) {
                return $this->fallbackAssignment($transactionData);
            }

            $result = json_decode($process->getOutput(), true);
            return $this->normalizeAssignmentResult($result);
            
        } catch (\Exception $e) {
            Log::error('Partner assignment error', [
                'message' => $e->getMessage()
            ]);
            
            return $this->fallbackAssignment($transactionData);
        }
    }

    /**
     * Build categorization prompt for AI
     */
    private function buildCategorizationPrompt(array $transactionData, array $couplesProfile): string
    {
        $merchant = $transactionData['merchant'] ?? $transactionData['description'] ?? 'Unknown';
        $amount = $transactionData['amount'] ?? 0;
        $items = isset($transactionData['items']) ? implode(', ', $transactionData['items']) : '';
        
        $partner1 = $couplesProfile['partner1_name'] ?? 'Partner 1';
        $partner2 = $couplesProfile['partner2_name'] ?? 'Partner 2';
        $sharedCategories = $couplesProfile['shared_categories'] ?? 'Groceries, Utilities, Rent, Insurance';
        
        return "Analyze this transaction for a couple and categorize it:

Transaction Details:
- Merchant: {$merchant}
- Amount: \${$amount}
- Items: {$items}

Couple Profile:
- {$partner1} and {$partner2}
- Typically shared categories: {$sharedCategories}

Please respond with JSON containing:
1. category: The most appropriate category
2. subcategory: More specific classification if applicable
3. confidence: Confidence score (0-1)
4. reasoning: Brief explanation for the categorization

Categories to consider: Groceries, Dining, Transportation, Entertainment, Shopping, Utilities, Healthcare, Personal Care, Home Improvement, Travel, Education, Insurance, Investments, Gifts, Other";
    }

    /**
     * Build assignment prompt for AI
     */
    private function buildAssignmentPrompt(array $transactionData, array $couplesProfile): string
    {
        $merchant = $transactionData['merchant'] ?? $transactionData['description'] ?? 'Unknown';
        $amount = $transactionData['amount'] ?? 0;
        $category = $transactionData['category'] ?? 'Unknown';
        $items = isset($transactionData['items']) ? implode(', ', $transactionData['items']) : '';
        
        $partner1 = $couplesProfile['partner1_name'] ?? 'Partner 1';
        $partner2 = $couplesProfile['partner2_name'] ?? 'Partner 2';
        
        return "Suggest who should be assigned this expense in a couple's budget:

Transaction:
- Merchant: {$merchant}
- Amount: \${$amount}
- Category: {$category}
- Items: {$items}

Couple: {$partner1} and {$partner2}

Assignment options:
- partner1: Only {$partner1}'s responsibility
- partner2: Only {$partner2}'s responsibility  
- shared: Split between both partners

Please respond with JSON containing:
1. assignment: 'partner1', 'partner2', or 'shared'
2. split_percentage: If shared, suggest split (e.g., 50/50, 70/30)
3. confidence: Confidence score (0-1)
4. reasoning: Brief explanation for the assignment

Consider: Is this typically a personal expense (clothing, personal care, individual hobbies) or shared expense (groceries, utilities, dining together, household items)?";
    }

    /**
     * Create Python script for categorization
     */
    private function createCategorizationScript(string $prompt): string
    {
        $escapedPrompt = addslashes($prompt);
        
        return <<<PYTHON
import json
import sys
import requests

try:
    # Call Ollama API directly
    response = requests.post(
        'http://localhost:11434/api/generate',
        json={
            'model': 'gemma3:4b',
            'prompt': '{$escapedPrompt}',
            'stream': False,
            'format': 'json'
        },
        timeout=30
    )
    
    if response.status_code == 200:
        result = response.json()
        ai_response = result.get('response', '{}')
        
        # Parse the AI response
        try:
            categorization = json.loads(ai_response)
            print(json.dumps(categorization))
        except json.JSONDecodeError:
            # Fallback if AI doesn't return valid JSON
            fallback = {
                'category': 'Other',
                'subcategory': '',
                'confidence': 0.5,
                'reasoning': 'AI response parsing failed'
            }
            print(json.dumps(fallback))
    else:
        raise Exception(f"Ollama API error: {response.status_code}")
        
except Exception as e:
    error_result = {
        'category': 'Other',
        'subcategory': '',
        'confidence': 0.0,
        'reasoning': f'Error: {str(e)}'
    }
    print(json.dumps(error_result))
PYTHON;
    }

    /**
     * Create Python script for assignment suggestion
     */
    private function createAssignmentScript(string $prompt): string
    {
        $escapedPrompt = addslashes($prompt);
        
        return <<<PYTHON
import json
import sys
import requests

try:
    response = requests.post(
        'http://localhost:11434/api/generate',
        json={
            'model': 'gemma3:4b',
            'prompt': '{$escapedPrompt}',
            'stream': False,
            'format': 'json'
        },
        timeout=30
    )
    
    if response.status_code == 200:
        result = response.json()
        ai_response = result.get('response', '{}')
        
        try:
            assignment = json.loads(ai_response)
            print(json.dumps(assignment))
        except json.JSONDecodeError:
            fallback = {
                'assignment': 'shared',
                'split_percentage': {'partner1': 50, 'partner2': 50},
                'confidence': 0.5,
                'reasoning': 'AI response parsing failed, defaulting to shared'
            }
            print(json.dumps(fallback))
    else:
        raise Exception(f"Ollama API error: {response.status_code}")
        
except Exception as e:
    error_result = {
        'assignment': 'shared',
        'split_percentage': {'partner1': 50, 'partner2': 50},
        'confidence': 0.0,
        'reasoning': f'Error: {str(e)}'
    }
    print(json.dumps(error_result))
PYTHON;
    }

    /**
     * Normalize categorization result
     */
    private function normalizeCategorizationResult(array $result): array
    {
        return [
            'category' => $result['category'] ?? 'Other',
            'subcategory' => $result['subcategory'] ?? '',
            'confidence' => floatval($result['confidence'] ?? 0.0),
            'reasoning' => $result['reasoning'] ?? 'No reasoning provided'
        ];
    }

    /**
     * Normalize assignment result
     */
    private function normalizeAssignmentResult(array $result): array
    {
        $splitPercentage = $result['split_percentage'] ?? ['partner1' => 50, 'partner2' => 50];
        
        return [
            'assignment' => $result['assignment'] ?? 'shared',
            'split_percentage' => $splitPercentage,
            'confidence' => floatval($result['confidence'] ?? 0.0),
            'reasoning' => $result['reasoning'] ?? 'No reasoning provided'
        ];
    }

    /**
     * Fallback categorization
     */
    private function fallbackCategorization(array $transactionData): array
    {
        // Simple rule-based fallback
        $merchant = strtolower($transactionData['merchant'] ?? $transactionData['description'] ?? '');
        
        if (strpos($merchant, 'grocery') !== false || strpos($merchant, 'supermarket') !== false) {
            $category = 'Groceries';
        } elseif (strpos($merchant, 'gas') !== false || strpos($merchant, 'fuel') !== false) {
            $category = 'Transportation';
        } elseif (strpos($merchant, 'restaurant') !== false || strpos($merchant, 'cafe') !== false) {
            $category = 'Dining';
        } else {
            $category = 'Other';
        }
        
        return [
            'category' => $category,
            'subcategory' => '',
            'confidence' => 0.6,
            'reasoning' => 'Rule-based fallback categorization'
        ];
    }

    /**
     * Fallback assignment
     */
    private function fallbackAssignment(array $transactionData): array
    {
        return [
            'assignment' => 'shared',
            'split_percentage' => ['partner1' => 50, 'partner2' => 50],
            'confidence' => 0.5,
            'reasoning' => 'Default shared assignment due to processing error'
        ];
    }
}