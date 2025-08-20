#!/usr/bin/env python3
"""
Test the Agent Service HTTP API
"""

import requests
import json
from datetime import datetime

def test_agent_service():
    """Test the FastAPI agent service endpoints"""
    base_url = "http://localhost:8000"
    
    print("=== Agent Service API Test ===")
    
    # Test 1: Health check
    try:
        print("\n1. Testing health endpoint...")
        response = requests.get(f"{base_url}/health", timeout=5)
        if response.status_code == 200:
            health_data = response.json()
            print(f"✅ Health check passed: {health_data['status']}")
            print(f"   Version: {health_data['version']}")
            print(f"   Timestamp: {health_data['timestamp']}")
        else:
            print(f"❌ Health check failed: {response.status_code}")
            return False
    except Exception as e:
        print(f"❌ Health check failed: {str(e)}")
        return False
    
    # Test 2: Process event endpoint
    try:
        print("\n2. Testing process event endpoint...")
        
        test_event = {
            "event": {
                "event_type": "transaction_created",
                "transaction": {
                    "id": 123,
                    "description": "TEST GROCERY STORE",
                    "amount": -45.67,
                    "date": datetime.now().isoformat(),
                    "currency_code": "USD"
                },
                "timestamp": datetime.now().isoformat(),
                "source": "api_test"
            },
            "user_context": {
                "user_id": 1,
                "categories": ["Groceries", "Transportation", "Dining"],
                "rules": []
            }
        }
        
        response = requests.post(
            f"{base_url}/api/process-event", 
            json=test_event,
            timeout=10
        )
        
        if response.status_code == 200:
            result = response.json()
            print(f"✅ Event processing passed")
            print(f"   Status: {result['status']}")
            print(f"   Processing time: {result.get('processing_time_ms', 0)}ms")
            print(f"   Actions generated: {len(result.get('actions', []))}")
            print(f"   Insights generated: {len(result.get('insights', []))}")
        else:
            print(f"❌ Event processing failed: {response.status_code}")
            print(f"   Response: {response.text}")
            return False
            
    except Exception as e:
        print(f"❌ Event processing failed: {str(e)}")
        return False
    
    # Test 3: Agent status
    try:
        print("\n3. Testing agent status endpoint...")
        response = requests.get(f"{base_url}/api/agent/status", timeout=5)
        if response.status_code == 200:
            status_data = response.json()
            print(f"✅ Agent status check passed")
            print(f"   Status: {status_data['status']}")
            print(f"   Last activity: {status_data['last_activity']}")
        else:
            print(f"❌ Agent status failed: {response.status_code}")
            return False
    except Exception as e:
        print(f"❌ Agent status failed: {str(e)}")
        return False
    
    print("\n✅ All agent service tests passed!")
    return True

if __name__ == "__main__":
    # First, start the service in background if not running
    print("Testing Agent Service API...")
    print("Make sure the agent service is running: python ai-scripts/agent_service.py")
    print()
    
    test_agent_service()