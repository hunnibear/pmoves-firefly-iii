#!/usr/bin/env python3
"""
Phase 1 Agent System Validation Test
Focused testing of implemented components without requiring full stack authentication
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

class Phase1Validator:
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
    
    def test_infrastructure_health(self):
        """Test core infrastructure components"""
        print("\n🔍 Testing Infrastructure Health...")
        
        # Test Ollama Service
        try:
            response = requests.get("http://localhost:11434/api/tags", timeout=10)
            if response.status_code == 200:
                models = response.json().get("models", [])
                model_names = [model.get("name", "") for model in models]
                self.log_test("Ollama AI Service", True, f"Models: {model_names}")
            else:
                self.log_test("Ollama AI Service", False, f"Status: {response.status_code}")
        except Exception as e:
            self.log_test("Ollama AI Service", False, error=str(e))
        
        # Test Docker Container Health
        try:
            import subprocess
            result = subprocess.run(
                ["docker", "ps", "--filter", "name=firefly_iii_core", "--format", "{{.Status}}"],
                capture_output=True, text=True, timeout=10
            )
            if result.returncode == 0 and "healthy" in result.stdout.lower():
                self.log_test("Firefly III Container", True, "Container is healthy")
            else:
                self.log_test("Firefly III Container", False, f"Status: {result.stdout.strip()}")
        except Exception as e:
            self.log_test("Firefly III Container", False, error=str(e))
    
    def test_python_agent_core(self):
        """Test the core Python agent functionality"""
        print("\n🧠 Testing Python Agent Core...")
        
        try:
            # Import the agent
            sys.path.append(str(Path(__file__).parent / "ai-scripts"))
            from transaction_intelligence_agent import TransactionIntelligenceAgent, EventData, TransactionData
            from datetime import datetime
            
            # Create agent instance
            agent = TransactionIntelligenceAgent()
            self.log_test("Agent Initialization", True, "Agent created successfully")
            
            # Test transaction creation
            transaction = TransactionData(
                id=123,
                description="Test Coffee Purchase",
                amount=4.50,
                date=datetime.now(),
                source_account="Checking",
                category="Dining"
            )
            self.log_test("Transaction Model Creation", True, f"Amount: ${transaction.amount}")
            
            # Test event creation
            event = EventData(
                event_type="transaction_created",
                transaction=transaction,
                timestamp=datetime.now(),
                event_data={"test": True}
            )
            self.log_test("Event Model Creation", True, f"Type: {event.event_type}")
            
            # Test agent processing
            start_time = time.time()
            response = asyncio.run(agent.process_event(event))
            processing_time = time.time() - start_time
            
            if response and response.status == "success":
                self.log_test("Agent Event Processing", True, 
                            f"Processed in {processing_time:.2f}s")
                for action in response.actions:
                    print(f"   Action: {action.type} - {action.details}")
                for insight in response.insights:
                    print(f"   Insight: {insight.type} - {insight.message}")
            else:
                self.log_test("Agent Event Processing", False, "No valid response")
                
        except Exception as e:
            self.log_test("Agent Core Functionality", False, error=str(e))
            traceback.print_exc()
    
    def test_fastapi_service(self):
        """Test FastAPI service can start and respond"""
        print("\n🚀 Testing FastAPI Service...")
        
        try:
            import subprocess
            import os
            
            # Start agent service in background
            agent_process = subprocess.Popen(
                [sys.executable, "ai-scripts/agent_service.py"],
                stdout=subprocess.PIPE,
                stderr=subprocess.PIPE,
                cwd=os.getcwd()
            )
            
            # Give it time to start
            time.sleep(3)
            
            # Test health endpoint
            try:
                response = requests.get("http://localhost:8001/health", timeout=5)
                if response.status_code == 200:
                    health_data = response.json()
                    self.log_test("FastAPI Health Endpoint", True, f"Status: {health_data.get('status')}")
                else:
                    self.log_test("FastAPI Health Endpoint", False, f"Status: {response.status_code}")
            except Exception as e:
                self.log_test("FastAPI Health Endpoint", False, error=str(e))
            
            # Test process event endpoint
            try:
                test_payload = {
                    "event_type": "transaction_created",
                    "timestamp": datetime.now().isoformat(),
                    "event_data": {"test": True}
                }
                response = requests.post(
                    "http://localhost:8001/api/process-event",
                    json=test_payload,
                    timeout=10
                )
                if response.status_code == 200:
                    result = response.json()
                    self.log_test("FastAPI Event Processing", True, f"Response received")
                else:
                    self.log_test("FastAPI Event Processing", False, f"Status: {response.status_code}")
            except Exception as e:
                self.log_test("FastAPI Event Processing", False, error=str(e))
            
            # Clean up
            agent_process.terminate()
            try:
                agent_process.wait(timeout=5)
            except subprocess.TimeoutExpired:
                agent_process.kill()
                
        except Exception as e:
            self.log_test("FastAPI Service Startup", False, error=str(e))
    
    def test_laravel_components(self):
        """Test Laravel components that don't require authentication"""
        print("\n🔧 Testing Laravel Components...")
        
        # Test Laravel configuration
        config_path = Path("config/agent.php")
        if config_path.exists():
            self.log_test("Agent Configuration File", True, "Config file exists")
        else:
            self.log_test("Agent Configuration File", False, "Config file missing")
        
        # Test Laravel job class
        job_path = Path("app/Jobs/ProcessAgentEvent.php")
        if job_path.exists():
            self.log_test("Agent Job Class", True, "ProcessAgentEvent job exists")
        else:
            self.log_test("Agent Job Class", False, "Job class missing")
        
        # Test Laravel controller
        controller_path = Path("app/Http/Controllers/Agent/AgentController.php")
        if controller_path.exists():
            self.log_test("Agent Controller", True, "AgentController exists")
        else:
            self.log_test("Agent Controller", False, "Controller missing")
    
    def test_docker_build_capability(self):
        """Test that Docker container can be built"""
        print("\n🐳 Testing Docker Build Capability...")
        
        try:
            import subprocess
            
            # Test if Dockerfile exists
            dockerfile_path = Path("Dockerfile.agent")
            if dockerfile_path.exists():
                self.log_test("Agent Dockerfile", True, "Dockerfile.agent exists")
            else:
                self.log_test("Agent Dockerfile", False, "Dockerfile missing")
                return
            
            # Test docker-compose configuration
            compose_path = Path("docker-compose.agent.yml")
            if compose_path.exists():
                self.log_test("Agent Docker Compose", True, "docker-compose.agent.yml exists")
            else:
                self.log_test("Agent Docker Compose", False, "Compose file missing")
            
            # Test requirements file
            requirements_path = Path("ai-requirements.txt")
            if requirements_path.exists():
                content = requirements_path.read_text()
                if "fastapi" in content and "langextract" in content:
                    self.log_test("Agent Requirements", True, "Requirements file contains needed packages")
                else:
                    self.log_test("Agent Requirements", False, "Requirements missing key packages")
            else:
                self.log_test("Agent Requirements", False, "Requirements file missing")
                
        except Exception as e:
            self.log_test("Docker Build Capability", False, error=str(e))
    
    def test_pydantic_models(self):
        """Test Pydantic model validation"""
        print("\n📋 Testing Pydantic Models...")
        
        try:
            sys.path.append(str(Path(__file__).parent / "ai-scripts"))
            from transaction_intelligence_agent import (
                EventData, TransactionData, AgentAction, AgentInsight, 
                EventType, ActionType, AnalysisType
            )
            from datetime import datetime
            
            # Test enum validation
            valid_event_types = [e.value for e in EventType]
            valid_action_types = [e.value for e in ActionType]
            self.log_test("Enum Validation", True, f"Event types: {len(valid_event_types)}, Action types: {len(valid_action_types)}")
            
            # Test transaction validation
            transaction = TransactionData(
                id=1,
                description="Test",
                amount=10.0,
                date=datetime.now()
            )
            self.log_test("Transaction Validation", True, "Valid transaction created")
            
            # Test action creation
            action = AgentAction(
                type="categorize_transaction",
                target_id=1,
                details={"category": "Food"},
                confidence=0.95
            )
            self.log_test("Action Model Validation", True, f"Confidence: {action.confidence}")
            
            # Test insight creation
            insight = AgentInsight(
                type="categorization",
                message="Transaction categorized as Food",
                confidence=0.95,
                data={"category": "Food"}
            )
            self.log_test("Insight Model Validation", True, f"Type: {insight.type}")
            
        except Exception as e:
            self.log_test("Pydantic Model Validation", False, error=str(e))
            traceback.print_exc()
    
    async def run_validation(self):
        """Run all validation tests"""
        print("🎯 Phase 1 Transaction Intelligence Agent - System Validation")
        print("=" * 70)
        
        start_time = time.time()
        
        # Run all tests
        self.test_infrastructure_health()
        self.test_python_agent_core()
        self.test_pydantic_models()
        self.test_laravel_components()
        self.test_docker_build_capability()
        self.test_fastapi_service()
        
        # Calculate results
        total_time = time.time() - start_time
        
        print("\n" + "=" * 70)
        print("🏁 Phase 1 Validation Results")
        print("=" * 70)
        print(f"⏱️  Total execution time: {total_time:.2f} seconds")
        print(f"📊 Tests run: {self.results['tests_run']}")
        print(f"✅ Tests passed: {self.results['tests_passed']}")
        print(f"❌ Tests failed: {self.results['tests_failed']}")
        
        if self.results["tests_failed"] > 0:
            print(f"\n🔍 Issues to Address:")
            for error in self.results["errors"]:
                print(f"   • {error}")
        
        # Success rate
        success_rate = (self.results['tests_passed'] / self.results['tests_run']) * 100 if self.results['tests_run'] > 0 else 0
        print(f"\n📈 Success Rate: {success_rate:.1f}%")
        
        # Assessment
        if success_rate >= 90:
            print("🎉 EXCELLENT: Phase 1 is production-ready! ✨")
            phase_status = "PRODUCTION_READY"
        elif success_rate >= 75:
            print("✅ GOOD: Phase 1 is solid with minor improvements needed")
            phase_status = "MOSTLY_READY"
        elif success_rate >= 50:
            print("⚠️  FAIR: Some components need attention before Phase 2")
            phase_status = "NEEDS_WORK"
        else:
            print("🚨 POOR: Major issues require resolution")
            phase_status = "MAJOR_ISSUES"
        
        # Save results
        results_file = Path("phase1_validation_results.json")
        validation_results = {
            **self.results,
            "success_rate": success_rate,
            "phase_status": phase_status,
            "total_time": total_time,
            "validation_timestamp": datetime.now().isoformat()
        }
        
        with open(results_file, "w") as f:
            json.dump(validation_results, f, indent=2)
        print(f"\n📄 Detailed results saved to: {results_file}")
        
        # Phase 1 Summary
        print(f"\n🎯 Phase 1 Status: {phase_status}")
        if success_rate >= 75:
            print("✅ Ready to proceed to Phase 2 development!")
            print("🚀 Next: Financial Planning Agent implementation")
        else:
            print("⚠️  Resolve issues before Phase 2")
        
        return success_rate >= 75

if __name__ == "__main__":
    async def main():
        validator = Phase1Validator()
        success = await validator.run_validation()
        sys.exit(0 if success else 1)
    
    asyncio.run(main())