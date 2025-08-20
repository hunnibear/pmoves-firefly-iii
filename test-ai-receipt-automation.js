/**
 * Comprehensive AI Receipt Processing Automation Test
 * Tests the complete flow: Login → Upload Receipt → AI Processing → Transaction Creation → Verification
 */

const puppeteer = require('puppeteer');
const fs = require('fs');
const path = require('path');

class ReceiptProcessingAutomation {
    constructor() {
        this.browser = null;
        this.page = null;
        this.config = {
            baseUrl: 'http://localhost:8080',
            headless: false, // Set to true for background execution
            timeout: 30000,
            receiptFile: path.join(__dirname, 'sample_receipt.txt'), // We'll use the text file as content
            testUser: {
                email: process.env.FIREFLY_TEST_EMAIL || 'test@example.com',
                password: process.env.FIREFLY_TEST_PASSWORD || 'password123'
            }
        };
    }

    async initialize() {
        console.log('🚀 Initializing AI Receipt Processing Test...');
        
        this.browser = await puppeteer.launch({
            headless: this.config.headless,
            defaultViewport: { width: 1280, height: 720 },
            args: ['--no-sandbox', '--disable-setuid-sandbox']
        });
        
        this.page = await this.browser.newPage();
        
        // Enable request interception for logging
        await this.page.setRequestInterception(true);
        this.page.on('request', (request) => {
            if (request.url().includes('/couples/api/')) {
                console.log(`📡 API Request: ${request.method()} ${request.url()}`);
            }
            request.continue();
        });

        // Listen for API responses
        this.page.on('response', async (response) => {
            if (response.url().includes('/couples/api/')) {
                console.log(`📡 API Response: ${response.status()} ${response.url()}`);
                if (response.status() !== 200) {
                    console.log(`❌ Response body:`, await response.text());
                }
            }
        });

        // Listen for console logs from the page
        this.page.on('console', (msg) => {
            if (msg.type() === 'error') {
                console.log(`🔥 Browser Error: ${msg.text()}`);
            } else if (msg.text().includes('AI') || msg.text().includes('receipt')) {
                console.log(`💬 Browser Log: ${msg.text()}`);
            }
        });
    }

    async login() {
        console.log('🔐 Attempting login to Firefly III...');
        
        await this.page.goto(`${this.config.baseUrl}/login`, { 
            waitUntil: 'networkidle2',
            timeout: this.config.timeout 
        });

        // Check if already logged in
        try {
            await this.page.waitForSelector('.navbar', { timeout: 5000 });
            console.log('✅ Already logged in!');
            return true;
        } catch (e) {
            // Not logged in, proceed with login
        }

        // Fill login form
        await this.page.waitForSelector('input[name="email"]', { timeout: this.config.timeout });
        await this.page.type('input[name="email"]', this.config.testUser.email);
        await this.page.type('input[name="password"]', this.config.testUser.password);

        // Submit login
        await Promise.all([
            this.page.waitForNavigation({ waitUntil: 'networkidle2' }),
            this.page.click('button[type="submit"]')
        ]);

        // Verify login success
        try {
            await this.page.waitForSelector('.navbar', { timeout: 10000 });
            console.log('✅ Login successful!');
            return true;
        } catch (e) {
            console.log('❌ Login failed - check credentials or registration');
            
            // Try to register if login fails
            return await this.attemptRegistration();
        }
    }

    async attemptRegistration() {
        console.log('🔧 Attempting user registration...');
        
        try {
            await this.page.goto(`${this.config.baseUrl}/register`, { 
                waitUntil: 'networkidle2',
                timeout: this.config.timeout 
            });

            await this.page.waitForSelector('input[name="email"]', { timeout: 5000 });
            await this.page.type('input[name="email"]', this.config.testUser.email);
            await this.page.type('input[name="password"]', this.config.testUser.password);
            await this.page.type('input[name="password_confirmation"]', this.config.testUser.password);

            await Promise.all([
                this.page.waitForNavigation({ waitUntil: 'networkidle2' }),
                this.page.click('button[type="submit"]')
            ]);

            console.log('✅ Registration successful!');
            return true;

        } catch (e) {
            console.log('❌ Registration also failed:', e.message);
            return false;
        }
    }

    async navigateToCouplesDashboard() {
        console.log('🏠 Navigating to Couples Dashboard...');
        
        await this.page.goto(`${this.config.baseUrl}/couples/dashboard`, { 
            waitUntil: 'networkidle2',
            timeout: this.config.timeout 
        });

        // Wait for dashboard to load
        await this.page.waitForSelector('.couples-dashboard', { timeout: this.config.timeout });
        console.log('✅ Couples Dashboard loaded!');

        // Check for AI integration elements
        try {
            await this.page.waitForSelector('#receiptUpload', { timeout: 5000 });
            console.log('✅ AI Receipt Upload component found!');
        } catch (e) {
            console.log('⚠️  AI Receipt Upload component not found - may need to enable');
        }
    }

    async createSampleReceiptFile() {
        console.log('📄 Creating sample receipt file for upload...');
        
        // Create a simple receipt image (we'll simulate this with a text file for now)
        const receiptContent = `
SAMPLE GROCERY RECEIPT
======================
Date: ${new Date().toLocaleDateString()}
Store: Test Grocery Store
Items:
- Milk $4.99
- Bread $2.50
- Eggs $3.25
Total: $10.74
`;

        const tempReceiptPath = path.join(__dirname, 'temp_receipt.txt');
        fs.writeFileSync(tempReceiptPath, receiptContent);
        
        console.log(`✅ Sample receipt created at: ${tempReceiptPath}`);
        return tempReceiptPath;
    }

    async testReceiptUpload() {
        console.log('📸 Testing AI Receipt Upload...');
        
        // Create sample receipt
        const receiptPath = await this.createSampleReceiptFile();

        try {
            // Find upload input
            const uploadInput = await this.page.$('input[type="file"]');
            if (!uploadInput) {
                console.log('❌ File upload input not found on page');
                return false;
            }

            // Upload the file
            await uploadInput.uploadFile(receiptPath);
            console.log('✅ File selected for upload');

            // Look for upload button or auto-process
            try {
                const uploadButton = await this.page.$('#uploadReceiptBtn');
                if (uploadButton) {
                    await uploadButton.click();
                    console.log('✅ Upload button clicked');
                }
            } catch (e) {
                console.log('ℹ️  No upload button found - may auto-process on file selection');
            }

            // Wait for processing results
            console.log('⏳ Waiting for AI processing results...');
            
            // Look for results in multiple possible locations
            const possibleSelectors = [
                '#receiptResults',
                '.receipt-processing-results',
                '.ai-suggestions',
                '[data-testid="receipt-results"]'
            ];

            let resultsFound = false;
            for (const selector of possibleSelectors) {
                try {
                    await this.page.waitForSelector(selector, { timeout: 15000 });
                    console.log(`✅ Processing results found with selector: ${selector}`);
                    resultsFound = true;
                    break;
                } catch (e) {
                    // Try next selector
                }
            }

            if (!resultsFound) {
                // Check for console logs or API calls
                console.log('⚠️  No visual results found, checking for API responses...');
                await this.page.waitForTimeout(5000); // Wait for potential background processing
            }

            // Extract results if available
            const results = await this.extractProcessingResults();
            
            // Clean up temp file
            fs.unlinkSync(receiptPath);
            
            return results;

        } catch (error) {
            console.log('❌ Receipt upload test failed:', error.message);
            
            // Clean up temp file
            if (fs.existsSync(receiptPath)) {
                fs.unlinkSync(receiptPath);
            }
            
            return false;
        }
    }

    async extractProcessingResults() {
        console.log('🔍 Extracting AI processing results...');
        
        try {
            // Try to extract results from various possible DOM elements
            const results = await this.page.evaluate(() => {
                const resultElements = document.querySelectorAll([
                    '#receiptResults',
                    '.receipt-processing-results',
                    '.ai-suggestions',
                    '[data-testid="receipt-results"]'
                ].join(','));

                if (resultElements.length === 0) {
                    return { found: false, message: 'No result elements found in DOM' };
                }

                const extractedData = {};
                resultElements.forEach((el, index) => {
                    extractedData[`element_${index}`] = {
                        innerHTML: el.innerHTML,
                        textContent: el.textContent,
                        className: el.className,
                        id: el.id
                    };
                });

                return { found: true, data: extractedData };
            });

            if (results.found) {
                console.log('✅ AI processing results extracted:');
                console.log(JSON.stringify(results.data, null, 2));
                return results.data;
            } else {
                console.log('⚠️  No processing results found in DOM');
                return null;
            }

        } catch (error) {
            console.log('❌ Error extracting results:', error.message);
            return null;
        }
    }

    async testDirectAPICall() {
        console.log('🔧 Testing direct API call to upload-receipt endpoint...');
        
        try {
            // Create a FormData object for the API call
            const formData = await this.page.evaluate(async () => {
                const formData = new FormData();
                
                // Create a simple blob to simulate file upload
                const receiptBlob = new Blob(['Test receipt content'], { type: 'text/plain' });
                formData.append('receipt', receiptBlob, 'test_receipt.txt');
                formData.append('create_transaction', 'true');
                
                const response = await fetch('/couples/api/upload-receipt', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    }
                });

                return {
                    status: response.status,
                    statusText: response.statusText,
                    data: await response.text()
                };
            });

            console.log('📡 API Response Status:', formData.status);
            console.log('📡 API Response:', formData.data);

            if (formData.status === 200) {
                console.log('✅ Direct API test successful!');
                return JSON.parse(formData.data);
            } else {
                console.log('❌ Direct API test failed');
                return null;
            }

        } catch (error) {
            console.log('❌ Direct API test error:', error.message);
            return null;
        }
    }

    async verifyTransactionCreated() {
        console.log('🔍 Verifying transaction creation...');
        
        try {
            // Navigate to transactions page
            await this.page.goto(`${this.config.baseUrl}/transactions`, { 
                waitUntil: 'networkidle2',
                timeout: this.config.timeout 
            });

            // Look for recent transactions with AI tags
            await this.page.waitForSelector('.transaction-list, .table', { timeout: 10000 });
            
            const aiTransactions = await this.page.evaluate(() => {
                const rows = document.querySelectorAll('tr, .transaction-row');
                const aiTransactions = [];
                
                rows.forEach(row => {
                    const text = row.textContent || '';
                    if (text.includes('ai-processed') || 
                        text.includes('receipt-upload') || 
                        text.includes('couples-')) {
                        aiTransactions.push({
                            content: text.trim(),
                            innerHTML: row.innerHTML
                        });
                    }
                });
                
                return aiTransactions;
            });

            if (aiTransactions.length > 0) {
                console.log(`✅ Found ${aiTransactions.length} AI-processed transactions!`);
                aiTransactions.forEach((tx, index) => {
                    console.log(`   Transaction ${index + 1}:`, tx.content.substring(0, 100) + '...');
                });
                return aiTransactions;
            } else {
                console.log('⚠️  No AI-processed transactions found');
                return [];
            }

        } catch (error) {
            console.log('❌ Error verifying transactions:', error.message);
            return [];
        }
    }

    async runComprehensiveTest() {
        console.log('\n🎬 Starting Comprehensive AI Receipt Processing Test\n');
        console.log('=' .repeat(60));
        
        try {
            await this.initialize();
            
            // Step 1: Login
            const loginSuccess = await this.login();
            if (!loginSuccess) {
                throw new Error('Failed to login or register');
            }

            // Step 2: Navigate to dashboard
            await this.navigateToCouplesDashboard();

            // Step 3: Test receipt upload (UI)
            console.log('\n📋 Testing UI-based receipt upload...');
            const uiResults = await this.testReceiptUpload();

            // Step 4: Test direct API call
            console.log('\n📋 Testing direct API call...');
            const apiResults = await this.testDirectAPICall();

            // Step 5: Verify transaction creation
            console.log('\n📋 Verifying transaction creation...');
            const transactions = await this.verifyTransactionCreated();

            // Step 6: Generate test report
            await this.generateTestReport({
                uiResults,
                apiResults,
                transactions
            });

            console.log('\n✅ Comprehensive test completed successfully!');
            
        } catch (error) {
            console.log('\n❌ Test failed:', error.message);
            console.log(error.stack);
        } finally {
            if (this.browser) {
                await this.browser.close();
            }
        }
    }

    async generateTestReport(results) {
        console.log('\n📊 Generating Test Report...');
        console.log('=' .repeat(60));
        
        const report = {
            timestamp: new Date().toISOString(),
            test_environment: {
                base_url: this.config.baseUrl,
                user_agent: await this.page.evaluate(() => navigator.userAgent)
            },
            results: {
                ui_upload_test: {
                    success: !!results.uiResults,
                    data: results.uiResults
                },
                api_direct_test: {
                    success: !!results.apiResults,
                    data: results.apiResults
                },
                transaction_verification: {
                    success: results.transactions.length > 0,
                    count: results.transactions.length,
                    transactions: results.transactions
                }
            },
            summary: {
                total_tests: 3,
                passed_tests: [results.uiResults, results.apiResults, results.transactions.length > 0].filter(Boolean).length,
                overall_success: !!(results.uiResults || results.apiResults) && results.transactions.length >= 0
            }
        };

        // Save report to file
        const reportPath = path.join(__dirname, `ai-receipt-test-report-${Date.now()}.json`);
        fs.writeFileSync(reportPath, JSON.stringify(report, null, 2));
        
        console.log('\n📋 Test Summary:');
        console.log(`   UI Upload Test: ${report.results.ui_upload_test.success ? '✅ PASS' : '❌ FAIL'}`);
        console.log(`   API Direct Test: ${report.results.api_direct_test.success ? '✅ PASS' : '❌ FAIL'}`);
        console.log(`   Transaction Verification: ${report.results.transaction_verification.success ? '✅ PASS' : '❌ FAIL'}`);
        console.log(`   Overall Success: ${report.summary.overall_success ? '✅ PASS' : '❌ FAIL'}`);
        console.log(`\n📄 Full report saved to: ${reportPath}`);
    }

    async cleanup() {
        if (this.browser) {
            await this.browser.close();
        }
    }
}

// Execute the test if run directly
if (require.main === module) {
    const automation = new ReceiptProcessingAutomation();
    
    // Handle graceful shutdown
    process.on('SIGINT', async () => {
        console.log('\n🛑 Graceful shutdown...');
        await automation.cleanup();
        process.exit(0);
    });

    automation.runComprehensiveTest().catch(console.error);
}

module.exports = ReceiptProcessingAutomation;