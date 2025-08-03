# 🔥 Firefly III AI Dashboard - Local Setup

A modern, AI-powered financial dashboard that runs **100% locally** on your machine using Supabase and local AI models.

## ⚡ **Quick Start (Local Only)**

### 1. **Prerequisites**
- **Supabase CLI**: `scoop install supabase`
- **Docker Desktop**: For local AI models (optional)
- **PowerShell**: For setup scripts

### 2. **One-Command Setup**
```powershell
# Run the quick local setup
.\quick-local-setup.ps1
```

This will:
- ✅ Start Supabase locally (all services)
- ✅ Create the financial database schema
- ✅ Set up Ollama with AI models (optional)
- ✅ Configure everything for local use
- ✅ Open the dashboard in your browser

### 3. **Access Your Dashboard**
- **Dashboard**: Open `firefly-ai-dashboard.html`
- **Database Admin**: http://localhost:54323
- **Analytics**: http://localhost:54327
- **API**: http://localhost:54321

## 🎯 **What You Get**

### **Core Features**
- 📊 **Financial Tracking**: Accounts, transactions, budgets
- 🤖 **AI Categorization**: Auto-categorize transactions
- 💬 **AI Chat Assistant**: Ask questions about your finances
- 📈 **Smart Analytics**: Spending insights and trends
- 🔍 **Anomaly Detection**: Spot unusual spending patterns

### **Local AI Models**
- **Ollama**: Runs Llama 3.2 locally for privacy
- **Optional Cloud APIs**: Add OpenAI/Groq keys if desired
- **Real-time Processing**: AI works as you add transactions

### **Data Privacy**
- 🏠 **Everything Local**: Your data never leaves your machine
- 🔒 **Secure by Default**: No external database connections
- 💾 **Your Control**: Full ownership of your financial data

## 🗄️ **Database Structure**

The setup creates these tables in your local Supabase:

```sql
accounts       -- Your bank accounts, credit cards, etc.
categories     -- Transaction categories (Food, Transport, etc.)
transactions   -- All your financial transactions
```

Each transaction gets:
- **AI Category**: Automatically suggested by AI
- **Confidence Score**: How sure the AI is
- **Manual Override**: You can always change AI suggestions

## 🤖 **AI Features**

### **Automatic Categorization**
```javascript
// When you add "Starbucks Coffee $4.50"
// AI automatically suggests: "Food & Dining" (95% confidence)
```

### **Smart Chat Assistant**
```
You: "How much did I spend on food this month?"
AI: "You spent $342.50 on Food & Dining this month, 
     which is 15% higher than last month."
```

### **Spending Insights**
- Monthly spending summaries
- Category breakdowns
- Trend analysis
- Budget recommendations

## 🔧 **Configuration**

### **Local Services**
All services run on your machine:
- **Supabase**: localhost:54321-54327
- **Ollama AI**: localhost:11434
- **Dashboard**: Local HTML file

### **Adding AI APIs (Optional)**
Edit `.env.local` to add cloud AI services:
```bash
OPENAI_API_KEY=your_key_here
GROQ_API_KEY=your_key_here
```

### **Database Access**
- **Studio**: http://localhost:54323
- **Direct**: postgresql://postgres:postgres@localhost:54322/postgres

## 📱 **Usage**

### **Adding Transactions**
1. Click "Add Transaction"
2. Enter description and amount
3. Watch AI auto-categorize
4. Confirm or adjust category

### **Chat with Your Data**
1. Go to "AI Assistant" tab
2. Ask questions like:
   - "What's my biggest expense category?"
   - "Am I spending more than last month?"
   - "Show me unusual transactions"

### **View Analytics**
1. Go to "Analytics" tab
2. See spending breakdowns
3. Track trends over time
4. Spot anomalies

## 🛠️ **Troubleshooting**

### **Supabase Issues**
```powershell
# Restart Supabase
supabase stop
supabase start
```

### **AI Not Working**
```powershell
# Check Ollama
docker ps | grep ollama
docker restart ollama
```

### **Database Problems**
```powershell
# Reset database
supabase db reset --local
.\quick-local-setup.ps1
```

### **Dashboard Issues**
- Clear browser cache
- Check browser console for errors
- Ensure Supabase is running

## 🎨 **Customization**

### **Adding Categories**
Use Supabase Studio (localhost:54323) to add custom categories:
```sql
INSERT INTO categories (name, color, icon) VALUES
('Crypto', '#f59e0b', 'fa-bitcoin');
```

### **Custom AI Models**
Replace Llama 3.2 with other Ollama models:
```bash
docker exec ollama ollama pull codellama
```

### **Dashboard Themes**
Edit `firefly-ai-dashboard.html` to customize colors and layout.

## 🚀 **Performance**

### **Local Benefits**
- ⚡ **Instant responses**: No network latency
- 🔒 **Complete privacy**: Data never leaves your machine
- 💰 **Zero costs**: No cloud API charges
- 🌐 **Offline capable**: Works without internet

### **Resource Usage**
- **Supabase**: ~200MB RAM
- **Ollama**: ~1-2GB RAM (with model loaded)
- **Dashboard**: Minimal browser resources

## 📊 **Data Import/Export**

### **Export Your Data**
```sql
-- Via Supabase Studio
COPY (SELECT * FROM transactions) TO '/tmp/transactions.csv' CSV HEADER;
```

### **Import Bank Data**
Add CSV import functionality by modifying the dashboard JavaScript.

## 🎯 **Next Steps**

### **Immediate Use**
1. Run `.\quick-local-setup.ps1`
2. Open `firefly-ai-dashboard.html`
3. Start adding your financial data
4. Explore AI features

### **Advanced Setup**
1. Add your cloud AI API keys
2. Customize categories and accounts
3. Import existing financial data
4. Set up automated backups

---

## 🏆 **Why This Approach?**

✅ **No Laravel complexity** - Pure web technologies  
✅ **Local privacy** - Your data stays on your machine  
✅ **Modern AI** - Multiple model support  
✅ **Real-time updates** - Live dashboard  
✅ **Easy setup** - One script does everything  
✅ **No costs** - Free local models  

Your financial AI assistant is ready in minutes! 🎉
