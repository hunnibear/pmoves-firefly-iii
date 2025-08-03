# AI Dashboard for Firefly III - User Guide

## Overview

The AI Dashboard extends Firefly III with intelligent financial management capabilities using local and cloud AI models. This integration provides smart transaction categorization, financial insights, anomaly detection, and an interactive AI assistant.

## Access the AI Dashboard

1. **Navigate to AI Dashboard**: Click on "AI Assistant" in the sidebar menu
2. **Direct URL**: Visit `/ai` in your Firefly III installation
3. **Authentication**: Requires standard Firefly III user authentication

## Features

### 1. AI Service Status

Check the connectivity status of your AI models:

- **Local Model (Ollama)**: Llama 3.2 running on localhost:11434
- **Cloud Models**: OpenAI GPT-4 and Groq integration
- **Test Button**: Click "Test AI Connectivity" to verify model availability

### 2. Financial Insights

Get AI-powered analysis of your financial data:

- Spending pattern analysis
- Personalized recommendations
- Monthly comparison insights
- Budget optimization suggestions

### 3. Smart Transaction Categorization

Automatically categorize transactions using AI:

1. Enter transaction description and amount
2. Select your preferred AI model
3. Get intelligent category suggestions
4. Categories include: Groceries, Transportation, Dining, Utilities, Housing, and more

### 4. Anomaly Detection

Identify unusual spending patterns:

- Duplicate transaction detection
- Spending spikes and unusual patterns
- Potential fraud detection
- Budget variance alerts

### 5. AI Chat Assistant

Interactive financial advisor:

1. Click "Open Chat" to start a conversation
2. Ask questions about your finances
3. Get personalized advice and insights
4. Context-aware responses based on your financial data

## Getting Started

### Step 1: Verify AI Model Setup

1. Go to the AI Dashboard
2. Click "Test AI Connectivity"
3. Ensure at least one AI model is available

### Step 2: Try Transaction Categorization

1. Use the "Transaction Categorization" section
2. Enter a sample transaction description
3. Add an amount
4. Click "Categorize" to see AI suggestions

### Step 3: Explore Financial Insights

1. Click "Generate Insights" in the Financial Insights card
2. Review AI-generated recommendations
3. Apply insights to improve your financial management

### Step 4: Chat with AI Assistant

1. Click "Open Chat" in the Chat Assistant card
2. Ask questions like:
   - "How much did I spend on groceries last month?"
   - "What are my biggest spending categories?"
   - "How can I reduce my expenses?"

## Troubleshooting

### AI Models Not Responding

1. **Check Ollama**: Ensure Ollama is running with `docker ps`
2. **Verify Network**: Test connectivity on port 11434
3. **Cloud Models**: Check API keys and internet connectivity

### Chat Not Working

1. **Authentication**: Ensure you're logged into Firefly III
2. **JavaScript**: Check browser console for errors
3. **CSRF**: Refresh the page if you get token errors

### Categories Not Appearing

1. **Model Selection**: Try different AI models
2. **Description**: Provide more detailed transaction descriptions
3. **Fallback**: System includes rule-based categorization

## Technical Details

### Supported AI Models

- **Ollama Llama 3.2**: Local inference for privacy
- **OpenAI GPT-4**: Cloud-based advanced reasoning
- **Groq**: High-speed inference for real-time responses

### Data Privacy

- **Local Processing**: Ollama runs on your server
- **Secure Communication**: All API calls use HTTPS
- **No Data Storage**: AI providers don't store your financial data

### Performance

- **Background Processing**: Long operations run in Laravel queues
- **Caching**: Insights and categories are cached for performance
- **Responsive Design**: Works on desktop and mobile devices

## Support

For technical issues:

1. Check the Docker logs: `docker logs firefly_iii_core`
2. Verify AI model status in the dashboard
3. Ensure all required services are running

For feature requests or bugs, please refer to the project documentation or create an issue in the repository.
