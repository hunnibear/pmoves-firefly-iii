#!/usr/bin/env python3
"""
End-to-End Testing Suite for Phase 1 Transaction Intelligence Agent
Tests all components from webhook reception to agent processing
"""

import asyncio
import json
import sys
import time
import traceback
from datetime import datetime
from pathlib import Path

import httpx
import requests

# Test configuration
FIREFLY_BASE_URL = "http://localhost:8080"
AGENT_SERVICE_URL = "http://localhost:8001"
WEBHOOK_URL = "http://localhost:8080/webhooks/firefly"
TEST_TIMEOUT = 30

class EndToEndTester:
    def __init__(self):
        self.results = {
            "tests_run": 0,
            "tests_passed": 0,
            "tests_failed": 0,
            "errors": [],
            "details": []
        }
        
    def log_test(self, test_name, passed, details="", error=None):
        """Log test result"""
        self.results["tests_run"] += 1
        if passed:
            self.results["tests_passed"] += 1
            print(f"✅ {test_name}")
        else:
            self.results["tests_failed"] += 1
            print(f"❌ {test_name}")
            if error:
                self.results["errors"].append(f"{test_name}: {error}")
                print(f"   Error: {error}")
        
        if details:
            print(f"   Details: {details}")
            
        self.results["details"].append({
            "test": test_name,
            "passed": passed,
            "details": details,
            "error": str(error) if error else None,
            "timestamp": datetime.now().isoformat()
        })
    
    def test_system_health(self):
        """Test that all required services are running"""
        print("\n🔍 Testing System Health...")
        
        # Test Firefly III Core
        try:
            response = requests.get(f"{FIREFLY_BASE_URL}/api/v1/about", timeout=10)
            if response.status_code == 200:
                data = response.json()
                self.log_test("Firefly III Core Health", True, f"Version: {data.get('data', {}).get('version', 'unknown')}")
            else:
                self.log_test("Firefly III Core Health", False, f"Status code: {response.status_code}")
        except Exception as e:
            self.log_test("Firefly III Core Health", False, error=str(e))
        
        # Test Ollama Service
        try:
            response = requests.get("http://localhost:11434/api/tags", timeout=10)
            if response.status_code == 200:
                models = response.json().get("models", [])
                model_names = [model.get("name", "") for model in models]
                self.log_test("Ollama Service Health", True, f"Models available: {len(models)}")
                print(f"   Available models: {model_names}")
            else:
                self.log_test("Ollama Service Health", False, f"Status code: {response.status_code}")
        except Exception as e:
            self.log_test("Ollama Service Health", False, error=str(e))
    
    async def test_agent_service_startup(self):
        """Test that the agent service can start and respond"""
        print("\n🚀 Testing Agent Service Startup...")
        
        # Try to start agent service in background
        import subprocess
        import os
        
        try:
            # Start agent service
            agent_process = subprocess.Popen(
                [sys.executable, "ai-scripts/agent_service.py"],
                stdout=subprocess.PIPE,
                stderr=subprocess.PIPE,
                cwd=os.getcwd()
            )
            
            # Wait a moment for startup
            await asyncio.sleep(3)
            
            # Test health endpoint
            async with httpx.AsyncClient() as client:
                try:
                    response = await client.get(f"{AGENT_SERVICE_URL}/health", timeout=5)
                    if response.status_code == 200:
                        health_data = response.json()
                        self.log_test("Agent Service Startup", True, f"Status: {health_data.get('status')}")
                    else:
                        self.log_test("Agent Service Startup", False, f"Health check failed: {response.status_code}")
                except Exception as e:
                    self.log_test("Agent Service Startup", False, error=f"Health check error: {str(e)}")
            
            # Clean up
            agent_process.terminate()
            try:
                agent_process.wait(timeout=5)
            except subprocess.TimeoutExpired:
                agent_process.kill()
                
        except Exception as e:
            self.log_test("Agent Service Startup", False, error=str(e))
    
    def test_laravel_components(self):
        """Test Laravel components (AgentController, routes)"""
        print("\n🔧 Testing Laravel Components...")
        
        # Test agent status endpoint
        try:
            response = requests.get(f"{FIREFLY_BASE_URL}/api/v1/agent/status", timeout=10)
            if response.status_code == 200:
                status_data = response.json()
                self.log_test("Laravel Agent Status Endpoint", True, f"Response received")
            else:
                self.log_test("Laravel Agent Status Endpoint", False, f"Status code: {response.status_code}")
        except Exception as e:
            self.log_test("Laravel Agent Status Endpoint", False, error=str(e))
        
        # Test webhook endpoint structure
        try:
            # This should return 405 Method Not Allowed for GET (expects POST)
            response = requests.get(f"{WEBHOOK_URL}", timeout=10)
            if response.status_code == 405:
                self.log_test("Laravel Webhook Endpoint", True, "Endpoint exists (405 for GET is expected)")
            else:
                self.log_test("Laravel Webhook Endpoint", False, f"Unexpected status: {response.status_code}")
        except Exception as e:
            self.log_test("Laravel Webhook Endpoint", False, error=str(e))
    
    async def test_agent_processing(self):
        """Test direct agent processing capabilities"""
        print("\n🧠 Testing Agent Processing...")
        
        # Import and test the transaction intelligence agent directly
        try:
            sys.path.append(str(Path(__file__).parent / "ai-scripts"))
            from transaction_intelligence_agent import TransactionIntelligenceAgent, EventData
            
            # Create agent instance
            agent = TransactionIntelligenceAgent()
            
            # Test sample transaction event
            from datetime import datetime
            sample_transaction = {
                "id": 123,
                "description": "Coffee Shop Purchase",
                "amount": 25.50,
                "date": datetime.now(),
                "source_account": "Checking Account",
                "destination_account": "Coffee Expenses"
            }
            
            sample_event = EventData(
                event_type="transaction_created",
                transaction=sample_transaction,
                timestamp=datetime.now(),
                event_data={
                    "transaction_id": "test-123",
                    "webhook_id": "test-webhook"
                }
            )
            
            # Process the event
            start_time = time.time()
            response = await agent.process_event(sample_event)
            processing_time = time.time() - start_time
            
            # Validate response
            if response and response.status == "success":
                self.log_test("Agent Event Processing", True, 
                            f"Processed in {processing_time:.2f}s, {len(response.actions)} actions, {len(response.insights)} insights")
                
                # Log actions and insights
                for action in response.actions:
                    print(f"   Action: {action.type} - {action.details}")
                for insight in response.insights:
                    print(f"   Insight: {insight.type} - {insight.message}")
            else:
                self.log_test("Agent Event Processing", False, "Invalid response format")
                
        except Exception as e:
            self.log_test("Agent Event Processing", False, error=str(e))
            traceback.print_exc()
    
    def test_queue_system(self):
        """Test Laravel queue system integration"""
        print("\n⚙️ Testing Queue System...")
        
        try:
            # Check if we can access the queue system via artisan
            import subprocess
            
            result = subprocess.run(
                ["docker", "exec", "firefly_iii_core", "php", "artisan", "queue:work", "--once", "--timeout=5"],
                capture_output=True,
                text=True,
                timeout=10
            )
            
            if result.returncode == 0 or "No jobs" in result.stdout:
                self.log_test("Laravel Queue System", True, "Queue worker accessible")
            else:
                self.log_test("Laravel Queue System", False, f"Queue worker error: {result.stderr}")
                
        except Exception as e:
            self.log_test("Laravel Queue System", False, error=str(e))
    
    async def test_webhook_simulation(self):
        """Simulate a webhook event and test end-to-end processing"""
        print("\n📡 Testing Webhook Simulation...")
        
        # Create a sample webhook payload that mimics Firefly III
        webhook_payload = {
            "uuid": "test-webhook-123",
            "type": "transaction.created",
            "data": {
                "id": "456",
                "type": "transactions",
                "attributes": {
                    "transactions": [{
                        "transaction_journal_id": "789",
                        "amount": "42.99",
                        "description": "Test Grocery Store",
                        "source_name": "Checking Account",
                        "destination_name": "Groceries",
                        "date": "2024-01-15T10:00:00Z"
                    }]
                }
            }
        }
        
        try:
            # Send webhook to Laravel
            response = requests.post(
                f"{WEBHOOK_URL}",
                json=webhook_payload,
                headers={"Content-Type": "application/json"},
                timeout=15
            )
            
            if response.status_code in [200, 202]:
                self.log_test("Webhook Processing", True, f"Webhook accepted (status: {response.status_code})")
            else:
                self.log_test("Webhook Processing", False, f"Webhook rejected (status: {response.status_code})")
                print(f"   Response: {response.text}")
                
        except Exception as e:
            self.log_test("Webhook Processing", False, error=str(e))
    
    def test_docker_integration(self):
        """Test Docker containerization and health checks"""
        print("\n🐳 Testing Docker Integration...")
        
        try:
            # Check if we can build the agent container
            import subprocess
            
            result = subprocess.run(
                ["docker", "build", "-f", "Dockerfile.agent", "-t", "firefly-agent-test", "."],
                capture_output=True,
                text=True,
                timeout=60
            )
            
            if result.returncode == 0:
                self.log_test("Docker Agent Build", True, "Agent container builds successfully")
                
                # Clean up test image
                subprocess.run(["docker", "rmi", "firefly-agent-test"], capture_output=True)
            else:
                self.log_test("Docker Agent Build", False, f"Build failed: {result.stderr}")
                
        except Exception as e:
            self.log_test("Docker Agent Build", False, error=str(e))
    
    def test_config_system(self):
        """Test agent configuration system"""
        print("\n⚙️ Testing Configuration System...")
        
        try:
            # Check if config file exists and is readable
            config_path = Path("config/agent.php")
            if config_path.exists():
                content = config_path.read_text()
                if "agent_service_url" in content and "enabled" in content:
                    self.log_test("Agent Configuration", True, "Config file exists and contains required settings")
                else:
                    self.log_test("Agent Configuration", False, "Config file missing required settings")
            else:
                self.log_test("Agent Configuration", False, "Config file not found")
                
        except Exception as e:
            self.log_test("Agent Configuration", False, error=str(e))
    
    async def run_all_tests(self):
        """Run the complete end-to-end test suite"""
        print("🎯 Starting End-to-End Testing Suite for Phase 1 Transaction Intelligence Agent")
        print("=" * 80)
        
        start_time = time.time()
        
        # Run all tests
        self.test_system_health()
        await self.test_agent_service_startup()
        self.test_laravel_components()
        await self.test_agent_processing()
        self.test_queue_system()
        await self.test_webhook_simulation()
        self.test_docker_integration()
        self.test_config_system()
        
        # Calculate results
        total_time = time.time() - start_time
        
        print("\n" + "=" * 80)
        print("🏁 End-to-End Test Results Summary")
        print("=" * 80)
        print(f"⏱️  Total execution time: {total_time:.2f} seconds")
        print(f"📊 Tests run: {self.results['tests_run']}")
        print(f"✅ Tests passed: {self.results['tests_passed']}")
        print(f"❌ Tests failed: {self.results['tests_failed']}")
        
        if self.results["tests_failed"] > 0:
            print(f"\n🔍 Failure Details:")
            for error in self.results["errors"]:
                print(f"   • {error}")
        
        # Success rate
        success_rate = (self.results['tests_passed'] / self.results['tests_run']) * 100 if self.results['tests_run'] > 0 else 0
        print(f"\n📈 Success Rate: {success_rate:.1f}%")
        
        # Overall assessment
        if success_rate >= 90:
            print("🎉 EXCELLENT: System ready for Phase 2 development!")
        elif success_rate >= 75:
            print("✅ GOOD: System mostly functional, minor issues to address")
        elif success_rate >= 50:
            print("⚠️  FAIR: Some significant issues need addressing")
        else:
            print("🚨 POOR: Major issues need resolution before Phase 2")
        
        # Save detailed results
        results_file = Path("test_results_end_to_end.json")
        with open(results_file, "w") as f:
            json.dump(self.results, f, indent=2)
        print(f"\n📄 Detailed results saved to: {results_file}")
        
        return success_rate >= 75

if __name__ == "__main__":
    async def main():
        tester = EndToEndTester()
        success = await tester.run_all_tests()
        sys.exit(0 if success else 1)
    
    asyncio.run(main())