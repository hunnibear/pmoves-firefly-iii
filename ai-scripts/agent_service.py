#!/usr/bin/env python3
"""
FastAPI Agent Service - Phase 1

HTTP API service for the Transaction Intelligence Agent.
Provides endpoints for processing events from the Laravel application.
"""

from fastapi import FastAPI, HTTPException, BackgroundTasks
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel
from typing import Dict, Any, Optional
import logging
import json
from datetime import datetime

from transaction_intelligence_agent import get_agent, AgentResponse

# Configure logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

app = FastAPI(
    title="Transaction Intelligence Agent API",
    description="AI-powered transaction analysis and automation service",
    version="1.0.0"
)

# Add CORS middleware for development
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],  # Configure appropriately for production
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)


class ProcessEventRequest(BaseModel):
    """Request model for processing events"""
    event: Dict[str, Any]
    user_context: Optional[Dict[str, Any]] = None
    firefly_config: Optional[Dict[str, Any]] = None


class HealthResponse(BaseModel):
    """Health check response"""
    status: str
    timestamp: datetime
    version: str = "1.0.0"
    components: Dict[str, str]


@app.get("/health", response_model=HealthResponse)
async def health_check():
    """Health check endpoint"""
    return HealthResponse(
        status="healthy",
        timestamp=datetime.now(),
        components={
            "agent": "active",
            "langextract": "connected",  # TODO: Check actual status
            "ollama": "connected"  # TODO: Check actual status
        }
    )


@app.post("/api/process-event", response_model=AgentResponse)
async def process_event(request: ProcessEventRequest, background_tasks: BackgroundTasks):
    """
    Process an event through the Transaction Intelligence Agent
    
    This is the main endpoint that receives events from the Laravel application
    and processes them through the AI agent.
    """
    try:
        logger.info(f"Received event for processing: {request.event.get('event_type', 'unknown')}")
        
        # Get the agent instance
        agent = get_agent()
        
        # Process the event
        response = await agent.process_event({
            'event': request.event,
            'user_context': request.user_context or {},
            'firefly_config': request.firefly_config or {}
        })
        
        logger.info(f"Event processed successfully in {response.processing_time_ms}ms")
        return response
        
    except Exception as e:
        logger.error(f"Error processing event: {str(e)}", exc_info=True)
        raise HTTPException(status_code=500, detail=str(e))


@app.post("/api/categorize")
async def categorize_transaction(request: Dict[str, Any]):
    """
    Categorize a transaction using AI
    
    This endpoint is called by the agent for AI-powered categorization
    through the LangExtract service.
    """
    try:
        # TODO: Implement actual LangExtract integration
        # For now, return a mock response
        
        prompt = request.get('prompt', '')
        context = request.get('context', {})
        
        # Mock categorization logic
        description = context.get('description', '').lower()
        
        if 'grocery' in description or 'supermarket' in description:
            category = 'Groceries'
            confidence = 0.9
        elif 'gas' in description or 'fuel' in description:
            category = 'Transportation'
            confidence = 0.85
        elif 'restaurant' in description or 'coffee' in description:
            category = 'Dining'
            confidence = 0.8
        else:
            category = 'Miscellaneous'
            confidence = 0.6
        
        return {
            'category': category,
            'confidence': confidence,
            'reasoning': f'Categorized based on description keywords in "{description}"'
        }
        
    except Exception as e:
        logger.error(f"Categorization error: {str(e)}")
        raise HTTPException(status_code=500, detail=str(e))


@app.get("/api/agent/status")
async def get_agent_status():
    """Get current agent status and metrics"""
    try:
        agent = get_agent()
        
        return {
            "status": "active",
            "uptime": "unknown",  # TODO: Track uptime
            "processed_events": "unknown",  # TODO: Track metrics
            "error_rate": 0.0,
            "last_activity": datetime.now().isoformat()
        }
        
    except Exception as e:
        logger.error(f"Status check error: {str(e)}")
        raise HTTPException(status_code=500, detail=str(e))


@app.post("/api/agent/analyze")
async def manual_analysis(request: Dict[str, Any]):
    """Trigger manual analysis for specific transactions"""
    try:
        agent = get_agent()
        
        # Create manual analysis event
        event_data = {
            'event': {
                'event_type': 'manual_analysis',
                'event_data': request,
                'timestamp': datetime.now().isostring(),
                'source': 'manual_api'
            },
            'user_context': request.get('user_context', {}),
            'firefly_config': request.get('firefly_config', {})
        }
        
        response = await agent.process_event(event_data)
        return response
        
    except Exception as e:
        logger.error(f"Manual analysis error: {str(e)}")
        raise HTTPException(status_code=500, detail=str(e))


@app.on_event("startup")
async def startup_event():
    """Initialize the agent service"""
    logger.info("Starting Transaction Intelligence Agent service")
    
    # Initialize the agent
    agent = get_agent()
    logger.info("Agent initialized successfully")


@app.on_event("shutdown")
async def shutdown_event():
    """Clean up on shutdown"""
    logger.info("Shutting down Transaction Intelligence Agent service")
    
    agent = get_agent()
    await agent.close()


if __name__ == "__main__":
    import uvicorn
    uvicorn.run(app, host="0.0.0.0", port=8000, log_level="info")