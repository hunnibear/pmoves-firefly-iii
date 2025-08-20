#!/usr/bin/env python3
"""
Quick test script to verify the Transaction Intelligence Agent Phase 1 implementation
"""

import asyncio
import json
from datetime import datetime
from transaction_intelligence_agent import TransactionIntelligenceAgent, EventData, TransactionData

async def test_agent():
    """Test the agent with sample data"""
    print("=== Transaction Intelligence Agent Test ===")
    
    # Initialize agent
    agent = TransactionIntelligenceAgent()
    
    # Create sample transaction event
    sample_event = {
        'event': {
            'event_type': 'transaction_created',
            'transaction': {
                'id': 123,
                'description': 'WALMART SUPERCENTER #1234',
                'amount': -85.67,
                'date': datetime.now().isoformat(),
                'currency_code': 'USD'
            },
            'timestamp': datetime.now().isoformat(),
            'source': 'test'
        },
        'user_context': {
            'user_id': 1,
            'categories': ['Groceries', 'Transportation', 'Dining', 'Entertainment'],
            'rules': [
                {
                    'id': 1,
                    'description_pattern': 'walmart',
                    'action': 'categorize',
                    'category': 'Groceries'
                }
            ]
        }
    }
    
    try:
        # Process the event
        print("Processing sample transaction event...")
        response = await agent.process_event(sample_event)
        
        print(f"\n✅ Agent Response:")
        print(f"Status: {response.status}")
        print(f"Processing Time: {response.processing_time_ms}ms")
        print(f"Actions: {len(response.actions)}")
        print(f"Insights: {len(response.insights)}")
        
        # Display actions
        for i, action in enumerate(response.actions):
            print(f"\nAction {i+1}:")
            print(f"  Type: {action.type}")
            print(f"  Confidence: {action.confidence:.2f}")
            print(f"  Reason: {action.reason}")
            print(f"  Requires Approval: {action.requires_approval}")
            print(f"  Data: {json.dumps(action.data, indent=4)}")
        
        # Display insights
        for i, insight in enumerate(response.insights):
            print(f"\nInsight {i+1}:")
            print(f"  Type: {insight.type}")
            print(f"  Title: {insight.title}")
            print(f"  Description: {insight.description}")
            print(f"  Confidence: {insight.confidence:.2f}")
        
        print("\n✅ Agent test completed successfully!")
        
    except Exception as e:
        print(f"\n❌ Agent test failed: {str(e)}")
        import traceback
        traceback.print_exc()
    
    finally:
        await agent.close()

if __name__ == "__main__":
    asyncio.run(test_agent())