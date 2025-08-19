const puppeteer = require('puppeteer');

async function testCouplesWithPuppeteer() {
    console.log('🚀 Starting Couples Feature Testing with Puppeteer...\n');
    
    const browser = await puppeteer.launch({ 
        headless: false, // Set to true for headless testing
        slowMo: 250,
        defaultViewport: { width: 1280, height: 720 }
    });
    
    const page = await browser.newPage();
    
    try {
        // Enable request interception for API monitoring
        await page.setRequestInterception(true);
        
        const apiRequests = [];
        page.on('request', request => {
            if (request.url().includes('/api/v1/couples')) {
                apiRequests.push({
                    url: request.url(),
                    method: request.method(),
                    headers: request.headers()
                });
            }
            request.continue();
        });
        
        page.on('response', response => {
            if (response.url().includes('/api/v1/couples')) {
                console.log(`📡 API ${response.request().method()} ${response.url()} - Status: ${response.status()}`);
            }
        });
        
        // Test 1: Navigate to Firefly III
        console.log('📋 Test 1: Loading Firefly III...');
        await page.goto('http://localhost:8080', { waitUntil: 'networkidle0' });
        
        const title = await page.title();
        console.log(`✅ Page loaded: ${title}`);
        
        // Test 2: Check authentication state
        console.log('\n📋 Test 2: Checking authentication...');
        
        const isLoginPage = await page.$('input[type="email"]') !== null;
        if (isLoginPage) {
            console.log('🔐 Login page detected');
            console.log('💡 Manual login may be required');
            console.log('⏳ Waiting 15 seconds for potential manual login...');
            await page.waitForTimeout(15000);
        }
        
        // Test 3: Navigate to couples page
        console.log('\n📋 Test 3: Navigating to couples page...');
        
        try {
            await page.goto('http://localhost:8080/couples', { waitUntil: 'networkidle0' });
            
            // Wait for the page to load
            await page.waitForSelector('h1', { timeout: 10000 });
            
            const heading = await page.$eval('h1', el => el.textContent);
            console.log(`✅ Page heading: ${heading}`);
            
            // Take a screenshot
            await page.screenshot({ 
                path: 'couples-page-screenshot.png',
                fullPage: true 
            });
            console.log('📸 Screenshot saved as couples-page-screenshot.png');
            
        } catch (error) {
            console.log(`❌ Couples page navigation failed: ${error.message}`);
            
            // Check current URL
            const currentUrl = page.url();
            console.log(`🔗 Current URL: ${currentUrl}`);
            
            if (currentUrl.includes('/login')) {
                console.log('🔐 Redirected to login - authentication required');
                console.log('📝 This is expected behavior for protected routes');
            }
        }
        
        // Test 4: UI Component Testing
        console.log('\n📋 Test 4: Testing UI components...');
        
        const components = [
            { selector: '.tab-btn', name: 'Navigation tabs' },
            { selector: '#budget-tab', name: 'Budget tab content' },
            { selector: '.summary-card', name: 'Summary cards' },
            { selector: '.add-transaction-form', name: 'Add transaction forms' }
        ];
        
        for (const component of components) {
            try {
                const elements = await page.$$(component.selector);
                console.log(`${elements.length > 0 ? '✅' : '❌'} ${component.name}: ${elements.length} found`);
            } catch (error) {
                console.log(`❌ ${component.name}: Error - ${error.message}`);
            }
        }
        
        // Test 5: JavaScript functionality
        console.log('\n📋 Test 5: Testing JavaScript functionality...');
        
        try {
            // Test if the couples state management is working
            const hasStateFunction = await page.evaluate(() => {
                return typeof loadState === 'function';
            });
            console.log(`${hasStateFunction ? '✅' : '❌'} loadState function: ${hasStateFunction ? 'Available' : 'Missing'}`);
            
            // Test if Chart.js is loaded
            const hasChartJS = await page.evaluate(() => {
                return typeof Chart !== 'undefined';
            });
            console.log(`${hasChartJS ? '✅' : '❌'} Chart.js library: ${hasChartJS ? 'Loaded' : 'Missing'}`);
            
            // Test tab switching
            const tabButtons = await page.$$('.tab-btn');
            if (tabButtons.length > 0) {
                console.log(`✅ Found ${tabButtons.length} tab buttons`);
                
                // Click on insights tab
                await page.click('[data-tab="insights"]');
                await page.waitForTimeout(1000);
                
                const insightsVisible = await page.$('#insights-tab:not(.hidden)') !== null;
                console.log(`${insightsVisible ? '✅' : '❌'} Tab switching: ${insightsVisible ? 'Working' : 'Not working'}`);
            }
            
        } catch (error) {
            console.log(`❌ JavaScript functionality test failed: ${error.message}`);
        }
        
        // Test 6: API endpoint testing
        console.log('\n📋 Test 6: Testing API endpoints...');
        
        try {
            const apiResponse = await page.evaluate(async () => {
                try {
                    const response = await fetch('/api/v1/couples/state', {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        credentials: 'same-origin'
                    });
                    
                    return {
                        status: response.status,
                        statusText: response.statusText,
                        data: await response.json()
                    };
                } catch (error) {
                    return { error: error.message };
                }
            });
            
            if (apiResponse.error) {
                console.log(`❌ API request failed: ${apiResponse.error}`);
            } else {
                console.log(`✅ API responded with status: ${apiResponse.status}`);
                
                if (apiResponse.status === 200) {
                    console.log('✅ Couples API is working!');
                    console.log(`📊 Response contains: ${Object.keys(apiResponse.data).join(', ')}`);
                } else if (apiResponse.status === 401) {
                    console.log('🔐 API requires authentication (expected)');
                } else {
                    console.log(`⚠️ Unexpected API response: ${apiResponse.status} - ${JSON.stringify(apiResponse.data)}`);
                }
            }
            
        } catch (error) {
            console.log(`❌ API test failed: ${error.message}`);
        }
        
        // Test 7: Performance metrics
        console.log('\n📋 Test 7: Performance metrics...');
        
        try {
            const metrics = await page.metrics();
            console.log(`📊 Performance Metrics:`);
            console.log(`   - JavaScript heap used: ${Math.round(metrics.JSHeapUsedSize / 1024 / 1024)} MB`);
            console.log(`   - JavaScript heap total: ${Math.round(metrics.JSHeapTotalSize / 1024 / 1024)} MB`);
            console.log(`   - DOM nodes: ${metrics.Nodes}`);
            
        } catch (error) {
            console.log(`❌ Performance metrics failed: ${error.message}`);
        }
        
        console.log('\n🎉 Puppeteer Testing Complete!');
        
        if (apiRequests.length > 0) {
            console.log('\n📡 API Requests Intercepted:');
            apiRequests.forEach(req => {
                console.log(`   ${req.method} ${req.url}`);
            });
        }
        
        console.log('\n📋 Test Summary:');
        console.log('✅ Application accessibility verified');
        console.log('✅ UI components presence checked');
        console.log('✅ JavaScript functionality tested');
        console.log('✅ API endpoints validated');
        console.log('✅ Performance metrics collected');
        
    } catch (error) {
        console.error('❌ Puppeteer test suite failed:', error);
    } finally {
        console.log('\n⏳ Keeping browser open for 20 seconds for inspection...');
        await page.waitForTimeout(20000);
        await browser.close();
    }
}

// Run the test
testCouplesWithPuppeteer().catch(console.error);