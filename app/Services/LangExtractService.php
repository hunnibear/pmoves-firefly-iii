<?php

declare(strict_types=1);

namespace FireflyIII\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class LangExtractService
{
    private string $pythonPath;
    private string $tempPath;
    private array $config;
    
    public function __construct()
    {
        // Use the configured Python environment from the project
        $this->pythonPath = 'C:/Users/russe/Documents/GitHub/pmoves-firefly-iii/.venv/Scripts/python.exe';
        $this->tempPath = storage_path('app/temp');
        $this->config = config('ai.langextract', []);
        
        // Ensure temp directory exists
        if (!file_exists($this->tempPath)) {
            mkdir($this->tempPath, 0755, true);
        }
    }

    /**
     * Process receipt content directly (for raw uploads)
     */
    public function processReceiptContent(string $content, string $fileName = 'receipt.txt', string $mimeType = 'text/plain'): array
    {
        try {
            // Save content to temp file
            $tempFileName = uniqid('receipt_content_') . '_' . $fileName;
            $tempFilePath = $this->tempPath . '/' . $tempFileName;
            file_put_contents($tempFilePath, $content);

            // Default schema for receipt processing
            $schema = [
                'merchant' => 'string',
                'amount' => 'number', 
                'date' => 'date',
                'category' => 'string',
                'items' => 'array',
                'tax_amount' => 'number',
                'payment_method' => 'string'
            ];
            
            // Create Python script for LangExtract processing
            $pythonScript = $this->createReceiptProcessingScript($tempFilePath, $schema);
            $scriptPath = $this->tempPath . '/process_receipt_content_' . uniqid() . '.py';
            file_put_contents($scriptPath, $pythonScript);

            // Execute LangExtract processing
            $process = new Process([
                $this->pythonPath,
                $scriptPath
            ]);
            
            $process->setTimeout(60); // 60 second timeout
            $process->run();

            // Clean up temp files
            unlink($tempFilePath);
            unlink($scriptPath);

            if (!$process->isSuccessful()) {
                Log::error('LangExtract content processing failed', [
                    'error' => $process->getErrorOutput(),
                    'output' => $process->getOutput(),
                    'content_length' => strlen($content),
                    'mime_type' => $mimeType
                ]);
                
                return $this->fallbackContentProcessing($content);
            }

            $result = json_decode($process->getOutput(), true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Failed to parse LangExtract content output', [
                    'output' => $process->getOutput(),
                    'json_error' => json_last_error_msg()
                ]);
                
                return $this->fallbackContentProcessing($content);
            }

            return $this->normalizeReceiptData($result);
            
        } catch (\Exception $e) {
            Log::error('Receipt content processing error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'content_length' => strlen($content)
            ]);
            
            return $this->fallbackContentProcessing($content);
        }
    }

    /**
     * Process receipt using LangExtract with Ollama
     */
    public function processReceipt(UploadedFile $file, array $schema = []): array
    {
        try {
            // Save uploaded file temporarily
            $tempFileName = uniqid('receipt_') . '.' . $file->getClientOriginalExtension();
            $tempFilePath = $this->tempPath . '/' . $tempFileName;
            $file->move($this->tempPath, $tempFileName);

            // Default schema for receipt processing
            $defaultSchema = [
                'merchant' => 'string',
                'amount' => 'number', 
                'date' => 'date',
                'category' => 'string',
                'items' => 'array',
                'tax_amount' => 'number',
                'payment_method' => 'string'
            ];
            
            $schema = array_merge($defaultSchema, $schema);
            
            // Create Python script for LangExtract processing
            $pythonScript = $this->createReceiptProcessingScript($tempFilePath, $schema);
            $scriptPath = $this->tempPath . '/process_receipt_' . uniqid() . '.py';
            file_put_contents($scriptPath, $pythonScript);

            // Execute LangExtract processing
            $process = new Process([
                $this->pythonPath,
                $scriptPath
            ]);
            
            $process->setTimeout(60); // 60 second timeout
            $process->run();

            // Clean up temp files
            unlink($tempFilePath);
            unlink($scriptPath);

            if (!$process->isSuccessful()) {
                Log::error('LangExtract processing failed', [
                    'error' => $process->getErrorOutput(),
                    'output' => $process->getOutput()
                ]);
                
                // Fallback to basic OCR processing
                return $this->fallbackReceiptProcessing($file);
            }

            $result = json_decode($process->getOutput(), true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Failed to parse LangExtract output', [
                    'output' => $process->getOutput(),
                    'json_error' => json_last_error_msg()
                ]);
                
                return $this->fallbackReceiptProcessing($file);
            }

            return $this->normalizeReceiptData($result);
            
        } catch (\Exception $e) {
            Log::error('Receipt processing error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return $this->fallbackReceiptProcessing($file);
        }
    }

    /**
     * Process bank statement using LangExtract
     */
    // Removed duplicate processBankStatement method - keeping the one with bool parameter

    /**
     * Create Python script for receipt processing
     */
    private function createReceiptProcessingScript(string $filePath, array $schema): string
    {
        $provider = $this->config['provider'] ?? 'ollama';
        $model = $this->config['model'] ?? 'gemma2:2b';
        $baseUrl = $this->config['base_url'] ?? 'http://localhost:11434';
        $apiKey = $this->config['api_key'] ?? null;
        $extractionPasses = $this->config['extraction_passes'] ?? 2;
        $maxCharBuffer = $this->config['max_char_buffer'] ?? 2000;
        $fenceOutput = ($this->config['fence_output'] ?? true) ? 'True' : 'False';
        $useSchemaConstraints = ($this->config['use_schema_constraints'] ?? true) ? 'True' : 'False';
        
        $schemaJson = json_encode($schema);
        
        // Build the extraction parameters based on provider
        $extractionParams = $this->buildExtractionParams($provider, $model, $baseUrl, $apiKey, $extractionPasses, $maxCharBuffer, $fenceOutput, $useSchemaConstraints);
        
        return <<<PYTHON
import json
import sys
import textwrap
from pathlib import Path

try:
    import langextract as lx
    
    # Define comprehensive prompt for financial receipt processing
    prompt = textwrap.dedent("""
        Extract financial information from receipts including merchant details, transaction amounts, 
        items purchased, payment information, and tax details.
        
        Use exact text for extractions. Do not paraphrase or overlap entities.
        Extract entities in order of appearance.
        Provide meaningful attributes for each entity to add context.
        
        For receipts, extract:
        - Merchant/store name and address
        - Total amount and subtotal
        - Individual items with prices
        - Tax amounts and rates
        - Payment method information
        - Date and time of transaction
        - Receipt/transaction number if available
    """)
    
    # Define comprehensive examples for receipt processing
    examples = [
        lx.data.ExampleData(
            text=textwrap.dedent("""
                WHOLE FOODS MARKET
                123 Main St, Anytown USA
                Phone: (555) 123-4567
                
                Date: 03/15/2024  Time: 2:30 PM
                Transaction #: 789012
                
                Organic Bananas       \$3.49
                Almond Milk 1L        \$4.99
                Free Range Eggs       \$5.29
                Sourdough Bread       \$3.99
                
                Subtotal:            \$17.76
                Tax (8.5%):          \$1.51
                Total:               \$19.27
                
                Payment: VISA ****1234
                Thank you for shopping!
            """),
            extractions=[
                lx.data.Extraction(
                    extraction_class="merchant",
                    extraction_text="WHOLE FOODS MARKET",
                    attributes={"type": "store_name"}
                ),
                lx.data.Extraction(
                    extraction_class="address",
                    extraction_text="123 Main St, Anytown USA",
                    attributes={"type": "store_address"}
                ),
                lx.data.Extraction(
                    extraction_class="date",
                    extraction_text="03/15/2024",
                    attributes={"type": "transaction_date"}
                ),
                lx.data.Extraction(
                    extraction_class="time",
                    extraction_text="2:30 PM",
                    attributes={"type": "transaction_time"}
                ),
                lx.data.Extraction(
                    extraction_class="transaction_id",
                    extraction_text="789012",
                    attributes={"type": "receipt_number"}
                ),
                lx.data.Extraction(
                    extraction_class="item",
                    extraction_text="Organic Bananas",
                    attributes={"price": "3.49", "category": "produce"}
                ),
                lx.data.Extraction(
                    extraction_class="item",
                    extraction_text="Almond Milk 1L", 
                    attributes={"price": "4.99", "category": "dairy_alternative"}
                ),
                lx.data.Extraction(
                    extraction_class="item",
                    extraction_text="Free Range Eggs",
                    attributes={"price": "5.29", "category": "dairy"}
                ),
                lx.data.Extraction(
                    extraction_class="item",
                    extraction_text="Sourdough Bread",
                    attributes={"price": "3.99", "category": "bakery"}
                ),
                lx.data.Extraction(
                    extraction_class="subtotal",
                    extraction_text="\$17.76",
                    attributes={"amount": "17.76", "type": "pre_tax"}
                ),
                lx.data.Extraction(
                    extraction_class="tax",
                    extraction_text="\$1.51",
                    attributes={"rate": "8.5%", "amount": "1.51"}
                ),
                lx.data.Extraction(
                    extraction_class="total",
                    extraction_text="\$19.27",
                    attributes={"amount": "19.27", "type": "final_amount"}
                ),
                lx.data.Extraction(
                    extraction_class="payment_method",
                    extraction_text="VISA ****1234",
                    attributes={"type": "credit_card", "last_four": "1234"}
                )
            ]
        )
    ]
    
    # Process receipt using LangExtract with configuration
    result = lx.extract(
        text_or_documents="{$filePath}",
        prompt_description=prompt,
        examples=examples,
        {$extractionParams}
    )
    
    # Convert result to structured format
    extracted_data = {
        'merchant': None,
        'amount': 0.0,
        'date': None,
        'items': [],
        'tax_amount': 0.0,
        'payment_method': None,
        'subtotal': 0.0,
        'transaction_id': None,
        'confidence': 0.0,
        'processing_metadata': {
            'model_used': '{$model}',
            'provider': '{$provider}',
            'extraction_passes': {$extractionPasses},
            'total_extractions': len(result.extractions)
        }
    }
    
    # Process extractions
    total_confidence = 0
    extraction_count = 0
    
    for extraction in result.extractions:
        extraction_count += 1
        
        if extraction.extraction_class == "merchant":
            extracted_data['merchant'] = extraction.extraction_text
        elif extraction.extraction_class == "total":
            # Extract numeric value from total
            amount_str = extraction.extraction_text.replace('\$', '').replace(',', '')
            try:
                extracted_data['amount'] = float(amount_str)
            except ValueError:
                if extraction.attributes and 'amount' in extraction.attributes:
                    extracted_data['amount'] = float(extraction.attributes['amount'])
        elif extraction.extraction_class == "date":
            extracted_data['date'] = extraction.extraction_text
        elif extraction.extraction_class == "item":
            item_data = {
                'name': extraction.extraction_text,
                'price': 0.0
            }
            if extraction.attributes and 'price' in extraction.attributes:
                try:
                    item_data['price'] = float(extraction.attributes['price'])
                except ValueError:
                    pass
            extracted_data['items'].append(item_data)
        elif extraction.extraction_class == "tax":
            if extraction.attributes and 'amount' in extraction.attributes:
                try:
                    extracted_data['tax_amount'] = float(extraction.attributes['amount'])
                except ValueError:
                    pass
        elif extraction.extraction_class == "payment_method":
            extracted_data['payment_method'] = extraction.extraction_text
        elif extraction.extraction_class == "subtotal":
            if extraction.attributes and 'amount' in extraction.attributes:
                try:
                    extracted_data['subtotal'] = float(extraction.attributes['amount'])
                except ValueError:
                    pass
        elif extraction.extraction_class == "transaction_id":
            extracted_data['transaction_id'] = extraction.extraction_text
    
    # Calculate average confidence (simplified)
    extracted_data['confidence'] = 0.85 if extraction_count > 5 else 0.7
    
    print(json.dumps(extracted_data))
    
except Exception as e:
    error_result = {
        'status': 'error',
        'message': str(e),
        'fallback_required': True
    }
    print(json.dumps(error_result))
    sys.exit(1)
PYTHON;
    }

    /**
     * Build extraction parameters based on provider configuration
     */
    private function buildExtractionParams(string $provider, string $model, string $baseUrl, ?string $apiKey, int $extractionPasses, int $maxCharBuffer, string $fenceOutput, string $useSchemaConstraints): string
    {
        $params = [
            "extraction_passes={$extractionPasses}",
            "max_char_buffer={$maxCharBuffer}",
            "fence_output={$fenceOutput}",
            "use_schema_constraints={$useSchemaConstraints}"
        ];

        switch ($provider) {
            case 'ollama':
                $params[] = "language_model_type=lx.inference.OllamaLanguageModel";
                $params[] = "model_id=\"{$model}\"";
                $params[] = "model_url=\"{$baseUrl}\"";
                break;

            case 'openai':
                $params[] = "language_model_type=lx.inference.OpenAILanguageModel";
                $params[] = "model_id=\"{$model}\"";
                if ($apiKey) {
                    $params[] = "api_key=\"{$apiKey}\"";
                }
                break;

            case 'google':
                $params[] = "model_id=\"{$model}\"";
                if ($apiKey) {
                    $params[] = "api_key=\"{$apiKey}\"";
                }
                break;

            case 'anthropic':
                $params[] = "language_model_type=lx.inference.AnthropicLanguageModel";
                $params[] = "model_id=\"{$model}\"";
                if ($apiKey) {
                    $params[] = "api_key=\"{$apiKey}\"";
                }
                break;

            default:
                // Fallback to default LangExtract behavior
                $params[] = "model_id=\"{$model}\"";
                break;
        }

        return implode(",\n        ", $params);
    }

    /**
     * Create Python script for bank statement processing
     */
    private function createStatementProcessingScript(string $filePath, array $schema): string
    {
        $provider = $this->config['provider'] ?? 'ollama';
        $model = $this->config['model'] ?? 'gemma2:2b';
        $baseUrl = $this->config['base_url'] ?? 'http://localhost:11434';
        $apiKey = $this->config['api_key'] ?? null;
        $extractionPasses = $this->config['extraction_passes'] ?? 2;
        $maxCharBuffer = 3000; // Larger buffer for statements
        $maxWorkers = $this->config['max_workers'] ?? 4;
        $fenceOutput = ($this->config['fence_output'] ?? true) ? 'True' : 'False';
        $useSchemaConstraints = ($this->config['use_schema_constraints'] ?? true) ? 'True' : 'False';
        
        $schemaJson = json_encode($schema);
        
        // Build the extraction parameters based on provider
        $extractionParams = $this->buildExtractionParams($provider, $model, $baseUrl, $apiKey, $extractionPasses, $maxCharBuffer, $fenceOutput, $useSchemaConstraints);
        
        return <<<PYTHON
import json
import sys
import textwrap
from pathlib import Path

try:
    import langextract as lx
    
    # Define comprehensive prompt for bank statement processing
    prompt = textwrap.dedent("""
        Extract transaction information from bank statements including transaction details,
        amounts, dates, descriptions, account information, and balance information.
        
        Use exact text for extractions. Do not paraphrase or overlap entities.
        Extract entities in order of appearance in the document.
        Provide meaningful attributes for each entity to add context.
        
        For bank statements, extract:
        - Account holder name and account number
        - Statement period (from date to date)
        - Opening and closing balances
        - Individual transactions with dates, descriptions, amounts
        - Transaction types (debit, credit, fee, interest)
        - Running balances if available
        - Bank name and routing information
    """)
    
    # Define comprehensive examples for bank statement processing
    examples = [
        lx.data.ExampleData(
            text=textwrap.dedent("""
                FIRST NATIONAL BANK
                Statement Period: March 1, 2024 - March 31, 2024
                Account: John Doe - Checking ****5678
                
                Beginning Balance: \$2,450.00
                
                03/02  Direct Deposit PAYROLL ACME CORP        +\$3,200.00  \$5,650.00
                03/05  ATM Withdrawal 123 MAIN ST              -\$100.00    \$5,550.00  
                03/08  Check #1234 ELECTRIC COMPANY            -\$120.50    \$5,429.50
                03/12  Debit Card WHOLE FOODS #456             -\$87.32     \$5,342.18
                03/15  Online Transfer TO SAVINGS               -\$500.00    \$4,842.18
                03/20  Fee: Overdraft Protection                -\$15.00     \$4,827.18
                03/25  Interest Earned                          +\$2.15      \$4,829.33
                
                Ending Balance: \$4,829.33
            """),
            extractions=[
                lx.data.Extraction(
                    extraction_class="bank_name",
                    extraction_text="FIRST NATIONAL BANK",
                    attributes={"type": "financial_institution"}
                ),
                lx.data.Extraction(
                    extraction_class="statement_period",
                    extraction_text="March 1, 2024 - March 31, 2024",
                    attributes={"start_date": "March 1, 2024", "end_date": "March 31, 2024"}
                ),
                lx.data.Extraction(
                    extraction_class="account_info",
                    extraction_text="John Doe - Checking ****5678",
                    attributes={"account_holder": "John Doe", "account_type": "Checking", "account_number": "****5678"}
                ),
                lx.data.Extraction(
                    extraction_class="opening_balance",
                    extraction_text="\$2,450.00",
                    attributes={"amount": "2450.00", "type": "beginning_balance"}
                ),
                lx.data.Extraction(
                    extraction_class="transaction",
                    extraction_text="03/02  Direct Deposit PAYROLL ACME CORP        +\$3,200.00  \$5,650.00",
                    attributes={
                        "date": "03/02", 
                        "description": "Direct Deposit PAYROLL ACME CORP",
                        "amount": "3200.00",
                        "type": "credit",
                        "balance": "5650.00"
                    }
                ),
                lx.data.Extraction(
                    extraction_class="transaction",
                    extraction_text="03/05  ATM Withdrawal 123 MAIN ST              -\$100.00    \$5,550.00",
                    attributes={
                        "date": "03/05",
                        "description": "ATM Withdrawal 123 MAIN ST", 
                        "amount": "100.00",
                        "type": "debit",
                        "balance": "5550.00"
                    }
                ),
                lx.data.Extraction(
                    extraction_class="transaction",
                    extraction_text="03/08  Check #1234 ELECTRIC COMPANY            -\$120.50    \$5,429.50",
                    attributes={
                        "date": "03/08",
                        "description": "Check #1234 ELECTRIC COMPANY",
                        "amount": "120.50", 
                        "type": "debit",
                        "balance": "5429.50",
                        "check_number": "1234"
                    }
                ),
                lx.data.Extraction(
                    extraction_class="closing_balance",
                    extraction_text="\$4,829.33",
                    attributes={"amount": "4829.33", "type": "ending_balance"}
                )
            ]
        )
    ]
    
    # Process bank statement using LangExtract with configuration
    result = lx.extract(
        text_or_documents="{$filePath}",
        prompt_description=prompt,
        examples=examples,
        max_workers={$maxWorkers},
        {$extractionParams}
    )
    
    # Convert result to structured format
    extracted_data = {
        'transactions': [],
        'account_number': None,
        'account_holder': None,
        'statement_period': None,
        'opening_balance': 0.0,
        'closing_balance': 0.0,
        'bank_name': None,
        'processing_metadata': {
            'model_used': '{$model}',
            'provider': '{$provider}',
            'extraction_passes': {$extractionPasses},
            'total_extractions': len(result.extractions),
            'document_type': 'bank_statement'
        }
    }
    
    # Process extractions
    for extraction in result.extractions:
        if extraction.extraction_class == "bank_name":
            extracted_data['bank_name'] = extraction.extraction_text
        elif extraction.extraction_class == "statement_period":
            extracted_data['statement_period'] = extraction.extraction_text
        elif extraction.extraction_class == "account_info":
            extracted_data['account_number'] = extraction.extraction_text
            if extraction.attributes:
                if 'account_holder' in extraction.attributes:
                    extracted_data['account_holder'] = extraction.attributes['account_holder']
        elif extraction.extraction_class == "opening_balance":
            if extraction.attributes and 'amount' in extraction.attributes:
                try:
                    extracted_data['opening_balance'] = float(extraction.attributes['amount'])
                except ValueError:
                    pass
        elif extraction.extraction_class == "closing_balance":
            if extraction.attributes and 'amount' in extraction.attributes:
                try:
                    extracted_data['closing_balance'] = float(extraction.attributes['amount'])
                except ValueError:
                    pass
        elif extraction.extraction_class == "transaction":
            if extraction.attributes:
                transaction = {
                    'date': extraction.attributes.get('date', ''),
                    'description': extraction.attributes.get('description', extraction.extraction_text),
                    'amount': 0.0,
                    'type': extraction.attributes.get('type', 'unknown'),
                    'balance': 0.0,
                    'check_number': extraction.attributes.get('check_number', None)
                }
                
                # Parse amount
                if 'amount' in extraction.attributes:
                    try:
                        transaction['amount'] = float(extraction.attributes['amount'])
                    except ValueError:
                        pass
                
                # Parse balance
                if 'balance' in extraction.attributes:
                    try:
                        transaction['balance'] = float(extraction.attributes['balance'])
                    except ValueError:
                        pass
                
                extracted_data['transactions'].append(transaction)
    
    print(json.dumps(extracted_data))
    
except Exception as e:
    error_result = {
        'status': 'error',
        'message': str(e),
        'fallback_required': True
    }
    print(json.dumps(error_result))
    sys.exit(1)
PYTHON;
    }

    /**
     * Normalize receipt data to consistent format
     */
    private function normalizeReceiptData(array $data): array
    {
        return [
            'merchant_name' => $data['merchant'] ?? $data['merchant_name'] ?? 'Unknown Merchant',
            'total_amount' => floatval($data['amount'] ?? $data['total_amount'] ?? 0),
            'date' => $data['date'] ?? date('Y-m-d'),
            'category' => $data['category'] ?? 'Uncategorized',
            'items' => $data['items'] ?? [],
            'tax_amount' => floatval($data['tax_amount'] ?? 0),
            'payment_method' => $data['payment_method'] ?? 'Unknown',
            'confidence' => $data['confidence'] ?? 0.0,
            'description' => $data['description'] ?? (($data['merchant'] ?? $data['merchant_name'] ?? 'Unknown Merchant') . ' receipt'),
            'processing_metadata' => $data['processing_metadata'] ?? []
        ];
    }

    /**
     * Normalize bank statement data
     */
    // Removed duplicate normalizeBankStatementData method - keeping the 3-parameter version

    /**
     * Fallback processing when LangExtract fails
     */
    private function fallbackReceiptProcessing(UploadedFile $file): array
    {
        Log::info('Using fallback receipt processing');
        
        return [
            'merchant_name' => 'Unknown Merchant',
            'total_amount' => 0.0,
            'date' => date('Y-m-d'),
            'category' => 'Uncategorized',
            'items' => [],
            'tax_amount' => 0.0,
            'payment_method' => 'Unknown',
            'confidence' => 0.0,
            'description' => 'Processed receipt upload',
            'processing_metadata' => [
                'fallback_used' => true,
                'reason' => 'LangExtract processing failed',
                'original_filename' => $file->getClientOriginalName()
            ]
        ];
    }

    /**
     * Fallback content processing when LangExtract fails
     */
    private function fallbackContentProcessing(string $content): array
    {
        Log::info('Using fallback content processing');
        
        // Basic extraction attempt from content
        $extractedData = [
            'merchant_name' => 'Unknown Merchant',
            'total_amount' => 0.0,
            'date' => date('Y-m-d'),
            'category' => 'Uncategorized',
            'items' => [],
            'tax_amount' => 0.0,
            'payment_method' => 'Unknown',
            'confidence' => 0.3,
            'description' => 'Receipt upload',
            'processing_metadata' => [
                'fallback_used' => true,
                'reason' => 'LangExtract processing failed',
                'content_length' => strlen($content)
            ]
        ];

        // Try basic pattern matching for amount
        if (preg_match('/\$(\d+\.?\d*)/', $content, $matches)) {
            $extractedData['total_amount'] = floatval($matches[1]);
            $extractedData['confidence'] = 0.5;
        }

        // Try to extract merchant name from first few lines
        $lines = explode("\n", $content);
        foreach ($lines as $line) {
            $line = trim($line);
            if (strlen($line) > 3 && strlen($line) < 50 && !preg_match('/\d{2}[\/\-]\d{2}/', $line)) {
                $extractedData['merchant_name'] = $line;
                break;
            }
        }

        return $extractedData;
    }
    
    /**
     * Process bank statement with enhanced document analysis
     */
    public function processBankStatement(UploadedFile $file, bool $useVisionModel = false): array
    {
        try {
            $startTime = microtime(true);
            
            // Save file to temp location
            $tempFileName = uniqid('bank_statement_') . '_' . $file->getClientOriginalName();
            $tempFilePath = $this->tempPath . '/' . $tempFileName;
            $file->move($this->tempPath, $tempFileName);

            // Bank statement schema
            $schema = [
                'account_number' => 'string',
                'account_holder' => 'string',
                'statement_period' => 'string',
                'opening_balance' => 'number',
                'closing_balance' => 'number',
                'transactions' => 'array',
                'transaction_count' => 'number',
                'date_range' => 'string'
            ];
            
            // Create Python script for bank statement processing
            $pythonScript = $this->createBankStatementProcessingScript($tempFilePath, $schema, $useVisionModel);
            $scriptPath = $this->tempPath . '/process_bank_statement_' . uniqid() . '.py';
            file_put_contents($scriptPath, $pythonScript);

            // Execute processing
            $process = new Process([
                $this->pythonPath,
                $scriptPath
            ]);
            
            $process->setTimeout(120); // 2 minute timeout for larger files
            $process->run();

            // Clean up temp files
            unlink($tempFilePath);
            unlink($scriptPath);

            $processingTime = round(microtime(true) - $startTime, 2) . 's';

            if (!$process->isSuccessful()) {
                Log::error('Bank statement processing failed', [
                    'error' => $process->getErrorOutput(),
                    'output' => $process->getOutput(),
                    'file' => $file->getClientOriginalName()
                ]);
                
                return $this->fallbackBankStatementProcessing($file);
            }

            $result = json_decode($process->getOutput(), true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Failed to parse bank statement output', [
                    'output' => $process->getOutput(),
                    'json_error' => json_last_error_msg()
                ]);
                
                return $this->fallbackBankStatementProcessing($file);
            }

            return $this->normalizeBankStatementData($result, $processingTime, $useVisionModel);
            
        } catch (\Exception $e) {
            Log::error('Bank statement processing error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $file->getClientOriginalName()
            ]);
            
            return $this->fallbackBankStatementProcessing($file);
        }
    }
    
    /**
     * Process generic document with vision model support
     */
    public function processDocument(UploadedFile $file, string $documentType = 'document', bool $useVisionModel = false): array
    {
        try {
            $startTime = microtime(true);
            
            // Save file to temp location
            $tempFileName = uniqid($documentType . '_') . '_' . $file->getClientOriginalName();
            $tempFilePath = $this->tempPath . '/' . $tempFileName;
            $file->move($this->tempPath, $tempFileName);

            // Dynamic schema based on document type
            $schema = $this->getDocumentSchema($documentType);
            
            // Create Python script for document processing
            $pythonScript = $this->createDocumentProcessingScript($tempFilePath, $schema, $documentType, $useVisionModel);
            $scriptPath = $this->tempPath . '/process_document_' . uniqid() . '.py';
            file_put_contents($scriptPath, $pythonScript);

            // Execute processing
            $process = new Process([
                $this->pythonPath,
                $scriptPath
            ]);
            
            $process->setTimeout(90); // 1.5 minute timeout
            $process->run();

            // Clean up temp files
            unlink($tempFilePath);
            unlink($scriptPath);

            $processingTime = round(microtime(true) - $startTime, 2) . 's';

            if (!$process->isSuccessful()) {
                Log::error('Document processing failed', [
                    'error' => $process->getErrorOutput(),
                    'output' => $process->getOutput(),
                    'file' => $file->getClientOriginalName(),
                    'type' => $documentType
                ]);
                
                return $this->fallbackDocumentProcessing($file, $documentType);
            }

            $result = json_decode($process->getOutput(), true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Failed to parse document output', [
                    'output' => $process->getOutput(),
                    'json_error' => json_last_error_msg(),
                    'type' => $documentType
                ]);
                
                return $this->fallbackDocumentProcessing($file, $documentType);
            }

            return $this->normalizeDocumentData($result, $processingTime, $documentType, $useVisionModel);
            
        } catch (\Exception $e) {
            Log::error('Document processing error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $file->getClientOriginalName(),
                'type' => $documentType
            ]);
            
            return $this->fallbackDocumentProcessing($file, $documentType);
        }
    }

    /**
     * Create Python script for bank statement processing
     */
    private function createBankStatementProcessingScript(string $filePath, array $schema, bool $useVisionModel = false): string
    {
        $schemaJson = json_encode($schema, JSON_PRETTY_PRINT);
        $visionModelCode = $useVisionModel ? $this->getVisionModelCode() : '';
        
        return <<<PYTHON
#!/usr/bin/env python3
import os
import sys
import json
import ollama
from datetime import datetime
import re
{$visionModelCode}

def process_bank_statement():
    try:
        file_path = "{$filePath}"
        schema = {$schemaJson}
        
        # Read file content
        if file_path.lower().endswith(('.png', '.jpg', '.jpeg', '.pdf')) and {$useVisionModel}:
            # Use vision model for image/PDF processing
            content = extract_text_with_vision(file_path)
        else:
            # Read text content directly
            with open(file_path, 'r', encoding='utf-8', errors='ignore') as f:
                content = f.read()
        
        # Enhanced prompt for bank statement processing
        prompt = f'''
        You are an expert financial document processor. Analyze this bank statement and extract structured data.
        
        Document Content:
        {content[:3000]}
        
        Extract the following information in JSON format matching this schema:
        {schema}
        
        For transactions array, include:
        - date: transaction date (YYYY-MM-DD format)
        - description: transaction description
        - amount: transaction amount (negative for debits, positive for credits)
        - balance: running balance after transaction
        - category: inferred transaction category
        - reference: any reference numbers
        
        Focus on:
        1. Accurate transaction extraction with proper amounts and signs
        2. Date parsing and normalization
        3. Balance tracking and verification
        4. Transaction categorization based on merchant/description
        5. Account information extraction
        
        Return only valid JSON matching the schema.
        '''
        
        # Process with Ollama
        response = ollama.chat(
            model='gemma2:9b-instruct-q4_K_M',
            messages=[{
                'role': 'user',
                'content': prompt
            }],
            options={
                'temperature': 0.1,
                'top_p': 0.9,
                'num_predict': 2048
            }
        )
        
        # Extract and clean JSON response
        response_text = response['message']['content']
        
        # Try to extract JSON from response
        json_match = re.search(r'\{.*\}', response_text, re.DOTALL)
        if json_match:
            result = json.loads(json_match.group())
        else:
            result = json.loads(response_text)
        
        # Add processing metadata
        result['processing_metadata'] = {
            'model_used': 'gemma2:9b-instruct-q4_K_M',
            'vision_model': {$useVisionModel},
            'processing_time': datetime.now().isoformat(),
            'file_type': 'bank_statement'
        }
        
        print(json.dumps(result, indent=2))
        
    except Exception as e:
        error_result = {
            "error": str(e),
            "account_number": "Unknown",
            "account_holder": "Unknown",
            "statement_period": "Unknown",
            "opening_balance": 0.0,
            "closing_balance": 0.0,
            "transactions": [],
            "transaction_count": 0,
            "date_range": "Unknown",
            "processing_metadata": {
                "error": True,
                "model_used": "gemma2:9b-instruct-q4_K_M",
                "vision_model": {$useVisionModel}
            }
        }
        print(json.dumps(error_result, indent=2))

if __name__ == "__main__":
    process_bank_statement()
PYTHON;
    }

    /**
     * Create Python script for generic document processing
     */
    private function createDocumentProcessingScript(string $filePath, array $schema, string $documentType, bool $useVisionModel = false): string
    {
        $schemaJson = json_encode($schema, JSON_PRETTY_PRINT);
        $visionModelCode = $useVisionModel ? $this->getVisionModelCode() : '';
        
        return <<<PYTHON
#!/usr/bin/env python3
import os
import sys
import json
import ollama
from datetime import datetime
import re
{$visionModelCode}

def process_document():
    try:
        file_path = "{$filePath}"
        schema = {$schemaJson}
        document_type = "{$documentType}"
        
        # Read file content
        if file_path.lower().endswith(('.png', '.jpg', '.jpeg', '.pdf')) and {$useVisionModel}:
            # Use vision model for image/PDF processing
            content = extract_text_with_vision(file_path)
        else:
            # Read text content directly
            with open(file_path, 'r', encoding='utf-8', errors='ignore') as f:
                content = f.read()
        
        # Dynamic prompt based on document type
        prompt = f'''
        You are an expert financial document processor. Analyze this {document_type} and extract structured data.
        
        Document Content:
        {content[:3000]}
        
        Extract the following information in JSON format matching this schema:
        {schema}
        
        Document Type: {document_type}
        
        Focus on:
        1. Accurate data extraction relevant to document type
        2. Date parsing and normalization  
        3. Amount extraction with proper formatting
        4. Entity recognition (merchants, accounts, etc.)
        5. Categorization based on content
        6. Confidence scoring based on data clarity
        
        Return only valid JSON matching the schema.
        '''
        
        # Process with Ollama
        response = ollama.chat(
            model='gemma2:9b-instruct-q4_K_M',
            messages=[{
                'role': 'user',
                'content': prompt
            }],
            options={
                'temperature': 0.1,
                'top_p': 0.9,
                'num_predict': 1536
            }
        )
        
        # Extract and clean JSON response
        response_text = response['message']['content']
        
        # Try to extract JSON from response
        json_match = re.search(r'\{.*\}', response_text, re.DOTALL)
        if json_match:
            result = json.loads(json_match.group())
        else:
            result = json.loads(response_text)
        
        # Add processing metadata
        result['processing_metadata'] = {
            'model_used': 'gemma2:9b-instruct-q4_K_M',
            'vision_model': {$useVisionModel},
            'processing_time': datetime.now().isoformat(),
            'file_type': document_type
        }
        
        print(json.dumps(result, indent=2))
        
    except Exception as e:
        error_result = {
            "error": str(e),
            "merchant_name": "Unknown",
            "total_amount": 0.0,
            "date": datetime.now().strftime('%Y-%m-%d'),
            "category": "Uncategorized",
            "confidence": 0.0,
            "processing_metadata": {
                "error": True,
                "model_used": "gemma2:9b-instruct-q4_K_M",
                "vision_model": {$useVisionModel}
            }
        }
        print(json.dumps(error_result, indent=2))

if __name__ == "__main__":
    process_document()
PYTHON;
    }

    /**
     * Get vision model code for image processing
     */
    private function getVisionModelCode(): string
    {
        return <<<PYTHON

def extract_text_with_vision(file_path):
    """Extract text from images using vision model"""
    try:
        # Use Ollama vision model for image-to-text extraction
        with open(file_path, 'rb') as image_file:
            image_data = image_file.read()
        
        # Convert to base64 for vision model
        import base64
        image_b64 = base64.b64encode(image_data).decode('utf-8')
        
        # Use vision model (e.g., llava) for text extraction
        vision_response = ollama.chat(
            model='llava:7b-v1.6',
            messages=[{
                'role': 'user',
                'content': 'Extract all text from this financial document. Focus on numbers, dates, merchant names, and transaction details. Return the text exactly as it appears.',
                'images': [image_b64]
            }],
            options={
                'temperature': 0.0,
                'top_p': 0.9
            }
        )
        
        return vision_response['message']['content']
        
    except Exception as e:
        # Fallback to filename if vision fails
        return f"Vision processing failed for {file_path}: {str(e)}"
PYTHON;
    }

    /**
     * Get document schema based on type
     */
    private function getDocumentSchema(string $documentType): array
    {
        switch ($documentType) {
            case 'receipt':
                return [
                    'merchant_name' => 'string',
                    'total_amount' => 'number',
                    'date' => 'date',
                    'category' => 'string',
                    'items' => 'array',
                    'tax_amount' => 'number',
                    'payment_method' => 'string',
                    'confidence' => 'number'
                ];
                
            case 'statement':
                return [
                    'account_number' => 'string',
                    'account_holder' => 'string',
                    'statement_period' => 'string',
                    'transactions' => 'array',
                    'transaction_count' => 'number',
                    'date_range' => 'string'
                ];
                
            case 'photo':
                return [
                    'content_type' => 'string',
                    'extracted_text' => 'string',
                    'merchant_name' => 'string',
                    'total_amount' => 'number',
                    'date' => 'date',
                    'confidence' => 'number',
                    'image_quality' => 'string'
                ];
                
            default:
                return [
                    'document_type' => 'string',
                    'extracted_data' => 'object',
                    'key_entities' => 'array',
                    'amounts' => 'array',
                    'dates' => 'array',
                    'confidence' => 'number'
                ];
        }
    }

    /**
     * Normalize bank statement data
     */
    private function normalizeBankStatementData(array $data, string $processingTime, bool $usedVisionModel): array
    {
        return [
            'status' => 'success',
            'extracted_data' => [
                'account_number' => $data['account_number'] ?? 'Unknown',
                'account_holder' => $data['account_holder'] ?? 'Unknown',
                'statement_period' => $data['statement_period'] ?? 'Unknown',
                'opening_balance' => floatval($data['opening_balance'] ?? 0),
                'closing_balance' => floatval($data['closing_balance'] ?? 0),
                'transactions' => $data['transactions'] ?? [],
                'transaction_count' => intval($data['transaction_count'] ?? count($data['transactions'] ?? [])),
                'date_range' => $data['date_range'] ?? 'Unknown',
                'processing_metadata' => array_merge($data['processing_metadata'] ?? [], [
                    'vision_model' => $usedVisionModel ? 'llava:7b-v1.6' : null
                ])
            ],
            'ai_suggestions' => [
                'confidence' => floatval($data['confidence'] ?? 0.8),
                'document_type' => 'bank_statement',
                'processing_notes' => 'Bank statement processed with transaction extraction'
            ],
            'processing_time' => $processingTime
        ];
    }

    /**
     * Normalize document data
     */
    private function normalizeDocumentData(array $data, string $processingTime, string $documentType, bool $usedVisionModel): array
    {
        if ($documentType === 'photo') {
            return [
                'status' => 'success',
                'extracted_data' => [
                    'content_type' => $data['content_type'] ?? 'photo',
                    'extracted_text' => $data['extracted_text'] ?? '',
                    'merchant_name' => $data['merchant_name'] ?? 'Unknown',
                    'total_amount' => floatval($data['total_amount'] ?? 0),
                    'date' => $data['date'] ?? date('Y-m-d'),
                    'image_quality' => $data['image_quality'] ?? 'Good',
                    'processing_metadata' => array_merge($data['processing_metadata'] ?? [], [
                        'vision_model' => $usedVisionModel ? 'llava:7b-v1.6' : null
                    ])
                ],
                'ai_suggestions' => [
                    'confidence' => floatval($data['confidence'] ?? 0.7),
                    'category' => $this->inferCategory($data['merchant_name'] ?? ''),
                    'document_type' => 'photo_capture'
                ],
                'processing_time' => $processingTime
            ];
        }

        // Default document normalization
        return [
            'status' => 'success',
            'extracted_data' => array_merge([
                'merchant_name' => 'Unknown',
                'total_amount' => 0.0,
                'date' => date('Y-m-d'),
                'category' => 'Uncategorized'
            ], $data),
            'ai_suggestions' => [
                'confidence' => floatval($data['confidence'] ?? 0.6),
                'category' => $this->inferCategory($data['merchant_name'] ?? ''),
                'document_type' => $documentType
            ],
            'processing_time' => $processingTime
        ];
    }

    /**
     * Fallback bank statement processing
     */
    private function fallbackBankStatementProcessing(UploadedFile $file): array
    {
        return [
            'status' => 'partial_success',
            'extracted_data' => [
                'account_number' => 'Unknown',
                'account_holder' => 'Unknown', 
                'statement_period' => 'Unknown',
                'opening_balance' => 0.0,
                'closing_balance' => 0.0,
                'transactions' => [],
                'transaction_count' => 0,
                'date_range' => 'Unknown',
                'processing_metadata' => [
                    'fallback_used' => true,
                    'reason' => 'LangExtract processing failed',
                    'original_filename' => $file->getClientOriginalName()
                ]
            ],
            'ai_suggestions' => [
                'confidence' => 0.2,
                'document_type' => 'bank_statement',
                'processing_notes' => 'Manual review recommended'
            ],
            'processing_time' => '0.1s'
        ];
    }

    /**
     * Fallback document processing
     */
    private function fallbackDocumentProcessing(UploadedFile $file, string $documentType): array
    {
        return [
            'status' => 'partial_success',
            'extracted_data' => [
                'merchant_name' => 'Unknown',
                'total_amount' => 0.0,
                'date' => date('Y-m-d'),
                'category' => 'Uncategorized',
                'processing_metadata' => [
                    'fallback_used' => true,
                    'reason' => 'LangExtract processing failed',
                    'original_filename' => $file->getClientOriginalName(),
                    'document_type' => $documentType
                ]
            ],
            'ai_suggestions' => [
                'confidence' => 0.2,
                'category' => 'Uncategorized',
                'document_type' => $documentType
            ],
            'processing_time' => '0.1s'
        ];
    }
}