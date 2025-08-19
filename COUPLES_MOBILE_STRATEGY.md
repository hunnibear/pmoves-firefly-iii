# Couples Mobile App - React + Supabase Integration

## Architecture Overview

This mobile-first couples budgeting app leverages your existing infrastructure:

### Backend: Supabase (Already Configured)
- **Real-time Database**: PostgreSQL with real-time subscriptions
- **Authentication**: User management with RLS (Row Level Security)
- **Edge Functions**: Server-side logic for AI integration
- **Storage**: Receipt uploads and document management

### Frontend: React PWA
- **Mobile-First Design**: Responsive, touch-friendly interface
- **Real-time Updates**: Live data synchronization
- **Offline Support**: Service worker for offline functionality
- **AI Integration**: Connected to your existing AI services

### AI Services: Your Existing Infrastructure
- **Ollama (Local)**: Privacy-focused transaction categorization
- **OpenAI/Groq**: Advanced insights and chat assistant
- **Background Processing**: Laravel queues for heavy AI tasks

## Quick Setup

### 1. Create React App with PWA Template
```bash
cd c:/Users/russe/Documents/GitHub/pmoves-firefly-iii
npx create-react-app couples-mobile --template pwa
cd couples-mobile

# Install dependencies
npm install @supabase/supabase-js
npm install @mui/material @emotion/react @emotion/styled
npm install chart.js react-chartjs-2
npm install framer-motion
npm install workbox-webpack-plugin
```

### 2. Configure Supabase Connection
```javascript
// src/lib/supabase.js
import { createClient } from '@supabase/supabase-js'

const supabaseUrl = process.env.REACT_APP_SUPABASE_URL || 'http://localhost:54321'
const supabaseAnonKey = process.env.REACT_APP_SUPABASE_ANON_KEY

export const supabase = createClient(supabaseUrl, supabaseAnonKey)

// Real-time couples data
export const subscribeToCouplesData = (userId, callback) => {
  return supabase
    .channel('couples-data')
    .on('postgres_changes', {
      event: '*',
      schema: 'public',
      table: 'transactions',
      filter: `user_id=eq.${userId}`
    }, callback)
    .on('postgres_changes', {
      event: '*',
      schema: 'public',
      table: 'accounts',
      filter: `user_id=eq.${userId}`
    }, callback)
    .subscribe()
}
```

## Implementation Recommendation

Based on your existing infrastructure and the attachments you've shared, I recommend:

### **Option 1: Enhanced Firefly III Integration (Recommended)**
- **Leverage your existing CouplesController** with a mobile-optimized Twig template
- **Use Supabase for real-time features** while keeping Firefly III as the primary backend
- **Integrate with your AI dashboard** for seamless AI-powered categorization
- **Mobile-first responsive design** within the existing AdminLTE framework

### Benefits:
✅ **Builds on existing work** - Your Phase 1 couples backend is already complete  
✅ **Leverages Firefly III's enterprise features** - Accounts, transactions, budgets, goals  
✅ **Integrates with your AI system** - Existing Ollama/OpenAI/Groq setup  
✅ **Uses Supabase real-time** - For live updates and mobile responsiveness  
✅ **Faster implementation** - Enhance existing rather than rebuild  

Would you like me to:
1. **Implement the enhanced Firefly III couples dashboard** with mobile-first design?
2. **Create the standalone React app** that connects to your Supabase backend?
3. **Show you how to integrate the existing AI system** with either approach?

The key insight from your documents is that you already have a robust foundation - we just need to make it mobile-friendly and leverage the real-time capabilities you've built!