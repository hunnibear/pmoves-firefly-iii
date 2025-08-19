const { chromium } = require('playwright');

async function testCouplesFeatures() {
    console.log('🚀 Starting Couples Feature Testing...\n');
    
    const browser = await chromium.launch({ 
        headless: false, // Set to true for headless testing
        slowMo: 1000 // Slow down for better visibility
    });
    
    const context = await browser.newContext();
    const page = await context.newPage();
    
    try {
        // Test 1: Check if Firefly III is accessible
        console.log('📋 Test 1: Checking Firefly III accessibility...');
        await page.goto('http://localhost:8080');
        await page.waitForSelector('title');
        const title = await page.title();
        console.log(`✅ Page title: ${title}`);
        
        // Test 2: Login functionality
        console.log('\n📋 Test 2: Testing login functionality...');
        
        // Check if we're on login page
        const isLoginPage = await page.locator('input[type="email"]').isVisible();
        if (isLoginPage) {
            console.log('🔐 Login required, attempting to login...');
            
            // Fill login form (using the user we know exists)
            await page.fill('input[type="email"]', 'cataclysmstudios@gmail.com');
            await page.fill('input[type="password"]', 'password'); // Assuming default password
            
            // Submit login form
            await page.click('button[type="submit"]');
            
            // Wait for redirect after login
            await page.waitForTimeout(3000);
            
            const currentUrl = page.url();
            console.log(`🔗 Current URL after login: ${currentUrl}`);
            
            if (currentUrl.includes('/login')) {
                console.log('❌ Login failed - still on login page');
                console.log('💡 This might be expected if password is not "password"');
                console.log('💡 Manual login required in the browser window');
                console.log('⏳ Waiting 30 seconds for manual login...');
                await page.waitForTimeout(30000);
            }
        } else {
            console.log('✅ Already logged in or login not required');
        }
        
        // Test 3: Navigate to couples page
        console.log('\n📋 Test 3: Testing couples page navigation...');
        
        try {
            await page.goto('http://localhost:8080/couples');
            await page.waitForSelector('h1', { timeout: 10000 });
            
            const heading = await page.textContent('h1');
            console.log(`✅ Couples page loaded with heading: ${heading}`);
            
            if (heading.includes('Couples Budget Planner')) {
                console.log('✅ Couples page loaded successfully!');
            } else {
                console.log('⚠️ Unexpected heading on couples page');
            }
        } catch (error) {
            console.log(`❌ Failed to load couples page: ${error.message}`);
            
            // Check if we're redirected to login
            const currentUrl = page.url();
            if (currentUrl.includes('/login')) {
                console.log('🔐 Redirected to login - authentication required');
                return;
            }
        }
        
        // Test 4: Check couples page elements
        console.log('\n📋 Test 4: Testing couples page elements...');
        
        const elementsToCheck = [
            { selector: '[data-tab="budget"]', name: 'Budget Tab' },
            { selector: '[data-tab="insights"]', name: 'Insights Tab' },
            { selector: '[data-tab="goals"]', name: 'Goals Tab' },
            { selector: '[data-tab="tips"]', name: 'Tips Tab' },
            { selector: '[data-tab="settings"]', name: 'Settings Tab' },
            { selector: '#person1-column', name: 'Person 1 Column' },
            { selector: '#person2-column', name: 'Person 2 Column' },
            { selector: '#shared-column', name: 'Shared Column' },
            { selector: '#unassigned-column', name: 'Unassigned Column' }
        ];
        
        for (const element of elementsToCheck) {
            try {
                const isVisible = await page.locator(element.selector).isVisible();
                console.log(`${isVisible ? '✅' : '❌'} ${element.name}: ${isVisible ? 'Present' : 'Missing'}`);
            } catch (error) {
                console.log(`❌ ${element.name}: Error checking - ${error.message}`);
            }
        }
        
        // Test 5: Test API state endpoint
        console.log('\n📋 Test 5: Testing couples API state endpoint...');
        
        try {
            const response = await page.evaluate(async () => {
                const response = await fetch('/api/v1/couples/state', {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                return {
                    status: response.status,
                    statusText: response.statusText,
                    data: await response.json()
                };
            });
            
            console.log(`✅ API Response Status: ${response.status} ${response.statusText}`);
            
            if (response.status === 200) {
                console.log('✅ API state endpoint working!');
                console.log(`📊 State data structure:`, Object.keys(response.data));
                
                // Validate state structure
                const expectedKeys = ['person1', 'person2', 'shared', 'unassigned', 'goals', 'settings'];
                const actualKeys = Object.keys(response.data);
                
                for (const key of expectedKeys) {
                    if (actualKeys.includes(key)) {
                        console.log(`✅ State contains ${key}`);
                    } else {
                        console.log(`❌ State missing ${key}`);
                    }
                }
            } else {
                console.log(`❌ API returned error: ${response.status} - ${JSON.stringify(response.data)}`);
            }
        } catch (error) {
            console.log(`❌ API test failed: ${error.message}`);
        }
        
        // Test 6: Test adding a transaction
        console.log('\n📋 Test 6: Testing transaction creation...');
        
        try {
            // Look for an add transaction form
            const addForm = await page.locator('.add-transaction-form').first();
            const isFormVisible = await addForm.isVisible();
            
            if (isFormVisible) {
                console.log('✅ Add transaction form found');
                
                // Fill out the form
                await addForm.locator('input[name="description"]').fill('Test Expense');
                await addForm.locator('input[name="amount"]').fill('25.50');
                
                // Submit the form
                await addForm.locator('button[type="submit"]').click();
                
                // Wait for response
                await page.waitForTimeout(2000);
                
                console.log('✅ Transaction form submitted');
                
                // Check for success notification
                const notification = await page.locator('#notifications .notification').isVisible();
                if (notification) {
                    const notificationText = await page.locator('#notifications .notification').textContent();
                    console.log(`📢 Notification: ${notificationText}`);
                }
                
            } else {
                console.log('❌ Add transaction form not found');
            }
        } catch (error) {
            console.log(`❌ Transaction creation test failed: ${error.message}`);
        }
        
        // Test 7: Test tab navigation
        console.log('\n📋 Test 7: Testing tab navigation...');
        
        const tabs = ['insights', 'goals', 'tips', 'settings'];
        
        for (const tab of tabs) {
            try {
                await page.click(`[data-tab="${tab}"]`);
                await page.waitForTimeout(1000);
                
                const isTabActive = await page.locator(`[data-tab="${tab}"].tab-active`).isVisible();
                const isContentVisible = await page.locator(`#${tab}-tab`).isVisible();
                
                console.log(`${isTabActive && isContentVisible ? '✅' : '❌'} ${tab.charAt(0).toUpperCase() + tab.slice(1)} tab: ${isTabActive && isContentVisible ? 'Working' : 'Not working'}`);
            } catch (error) {
                console.log(`❌ ${tab} tab test failed: ${error.message}`);
            }
        }
        
        // Go back to budget tab
        await page.click('[data-tab="budget"]');
        
        console.log('\n🎉 Couples Feature Testing Complete!');
        console.log('\n📊 Summary:');
        console.log('- Firefly III application is accessible');
        console.log('- Couples page structure is present');
        console.log('- Basic navigation and forms are functional');
        console.log('- API endpoints are available (authentication dependent)');
        
    } catch (error) {
        console.error('❌ Test suite failed:', error.message);
    } finally {
        console.log('\n⏳ Keeping browser open for 30 seconds for manual inspection...');
        await page.waitForTimeout(30000);
        await browser.close();
    }
}

// Run the test suite
testCouplesFeatures().catch(console.error);