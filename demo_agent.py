#!/usr/bin/env python3
"""
Quick Agent Demo - Demonstrate Phase 1 is working
"""

import sys
import asyncio
from datetime import datetime
from pathlib import Path

# Add the ai-scripts directory to path
sys.path.append(str(Path(__file__).parent / "ai-scripts"))

from transaction_intelligence_agent import (
    TransactionIntelligenceAgent, 
    EventData, 
    TransactionData,
    UserContext
)

async def main():
    print("🚀 Phase 1 Transaction Intelligence Agent - Quick Demo")
    print("=" * 60)
    
    # Initialize agent
    agent = TransactionIntelligenceAgent()
    print("✅ Agent initialized successfully")
    
    # Create sample transaction
    transaction = TransactionData(
        id=42,
        description="Starbucks Coffee Purchase",
        amount=5.75,
        date=datetime.now(),
        source_account="Chase Checking",
        category="Food & Dining"
    )
    print(f"✅ Transaction created: ${transaction.amount} - {transaction.description}")
    
    # Create user context
    user_context = UserContext(
        user_id=1,
        preferences={"auto_categorize": True},
        categories=["Food & Dining", "Transportation", "Shopping", "Bills"]
    )
    print(f"✅ User context: {len(user_context.categories)} categories available")
    
    # Create event
    event = EventData(
        event_type="transaction_created",
        transaction=transaction,
        timestamp=datetime.now(),
        event_data={"webhook_id": "demo-webhook-123"},
        user_context=user_context
    )
    print(f"✅ Event created: {event.event_type}")
    
    # Process event with agent
    print("\n🧠 Processing transaction with agent...")
    start_time = datetime.now()
    
    response = await agent.process_event(event)
    
    processing_time = (datetime.now() - start_time).total_seconds()
    
    # Display results
    print(f"✅ Processing completed in {processing_time:.2f} seconds")
    print(f"📊 Status: {response.status}")
    print(f"🎯 Actions generated: {len(response.actions)}")
    print(f"💡 Insights generated: {len(response.insights)}")
    
    # Show actions
    if response.actions:
        print("\n🎯 Agent Actions:")
        for i, action in enumerate(response.actions, 1):
            print(f"   {i}. {action.type}")
            print(f"      Confidence: {action.confidence:.1%}")
            print(f"      Reason: {action.reason}")
    
    # Show insights  
    if response.insights:
        print("\n💡 Agent Insights:")
        for i, insight in enumerate(response.insights, 1):
            print(f"   {i}. {insight.title}")
            print(f"      {insight.description}")
            print(f"      Confidence: {insight.confidence:.1%}")
    
    print("\n" + "=" * 60)
    print("🎉 Phase 1 Agent Demo Complete!")
    print("✅ Transaction Intelligence Agent is functional and ready")
    print("🚀 Ready for Phase 2 development!")

if __name__ == "__main__":
    asyncio.run(main())