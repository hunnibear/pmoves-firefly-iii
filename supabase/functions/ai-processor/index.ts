import { serve } from "https://deno.land/std@0.168.0/http/server.ts"
import { createClient } from 'https://esm.sh/@supabase/supabase-js@2'

const corsHeaders = {
  'Access-Control-Allow-Origin': '*',
  'Access-Control-Allow-Headers': 'authorization, x-client-info, apikey, content-type',
}

serve(async (req) => {
  // Handle CORS preflight requests
  if (req.method === 'OPTIONS') {
    return new Response('ok', { headers: corsHeaders })
  }

  try {
    // Get Supabase client
    const supabaseClient = createClient(
      Deno.env.get('SUPABASE_URL') ?? '',
      Deno.env.get('SUPABASE_ANON_KEY') ?? '',
    )

    const { record, action } = await req.json()

    switch (action) {
      case 'categorize_transaction':
        return await categorizeTransaction(record, supabaseClient)
      
      case 'generate_insights':
        return await generateInsights(record, supabaseClient)
      
      case 'detect_anomalies':
        return await detectAnomalies(record, supabaseClient)
      
      default:
        throw new Error(`Unknown action: ${action}`)
    }

  } catch (error) {
    console.error('Error:', error)
    return new Response(JSON.stringify({ error: error.message }), {
      status: 500,
      headers: { ...corsHeaders, 'Content-Type': 'application/json' }
    })
  }
})

async function categorizeTransaction(transaction, supabase) {
  const { id, description, amount } = transaction
  
  console.log(`Categorizing transaction ${id}: ${description}`)
  
  // Simple rule-based categorization for demo
  // In production, you'd call your AI service here
  let suggestedCategory = categorizeByRules(description, amount)
  let confidence = 0.8
  
  // Try to call local Ollama if available
  try {
    const ollamaResponse = await fetch('http://host.docker.internal:11434/api/generate', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        model: 'llama3.2',
        prompt: `Categorize this financial transaction into one of these categories: Food, Transport, Entertainment, Bills, Shopping, Healthcare, Income, Other. Transaction: "${description}" Amount: $${amount}. Reply with only the category name.`,
        stream: false
      })
    })
    
    if (ollamaResponse.ok) {
      const result = await ollamaResponse.json()
      suggestedCategory = result.response?.trim() || suggestedCategory
      confidence = 0.9
    }
  } catch (error) {
    console.log('Ollama not available, using rule-based categorization')
  }
  
  // Update transaction with AI suggestion
  const { error: updateError } = await supabase
    .from('transactions')
    .update({
      ai_category: suggestedCategory,
      ai_confidence_score: confidence,
      is_ai_processed: true
    })
    .eq('id', id)
  
  if (updateError) throw updateError
  
  return new Response(JSON.stringify({ 
    success: true,
    category: suggestedCategory,
    confidence: confidence
  }), {
    headers: { ...corsHeaders, 'Content-Type': 'application/json' }
  })
}

async function generateInsights(user, supabase) {
  const { user_id } = user
  
  // Get recent transactions for analysis
  const { data: transactions, error } = await supabase
    .from('transactions')
    .select('*')
    .eq('user_id', user_id)
    .order('date', { ascending: false })
    .limit(100)
  
  if (error) throw error
  
  // Generate simple insights
  const insights = []
  
  // Spending by category
  const categorySpending = {}
  transactions?.forEach(t => {
    if (t.amount < 0) { // Expenses
      const category = t.ai_category || t.category || 'Other'
      categorySpending[category] = (categorySpending[category] || 0) + Math.abs(t.amount)
    }
  })
  
  // Find top spending category
  const topCategory = Object.entries(categorySpending)
    .sort(([,a], [,b]) => b - a)[0]
  
  if (topCategory) {
    insights.push({
      type: 'spending_pattern',
      title: 'Top Spending Category',
      content: `Your highest spending category this month is ${topCategory[0]} with $${topCategory[1].toFixed(2)}`,
      confidence: 0.95
    })
  }
  
  // Recent spending trend
  const last7Days = transactions?.filter(t => {
    const transactionDate = new Date(t.date)
    const weekAgo = new Date()
    weekAgo.setDate(weekAgo.getDate() - 7)
    return transactionDate >= weekAgo && t.amount < 0
  })
  
  const last7DaysTotal = last7Days?.reduce((sum, t) => sum + Math.abs(t.amount), 0) || 0
  
  if (last7DaysTotal > 0) {
    insights.push({
      type: 'spending_pattern',
      title: 'Weekly Spending',
      content: `You spent $${last7DaysTotal.toFixed(2)} in the last 7 days`,
      confidence: 1.0
    })
  }
  
  // Save insights to database
  for (const insight of insights) {
    await supabase
      .from('ai_insights')
      .insert({
        user_id,
        insight_type: insight.type,
        title: insight.title,
        content: insight.content,
        confidence_score: insight.confidence
      })
  }
  
  return new Response(JSON.stringify({ 
    success: true,
    insights: insights
  }), {
    headers: { ...corsHeaders, 'Content-Type': 'application/json' }
  })
}

async function detectAnomalies(user, supabase) {
  const { user_id } = user
  
  // Call the database function to detect anomalies
  const { data: anomalies, error } = await supabase
    .rpc('detect_spending_anomalies', {
      target_user_id: user_id,
      days_back: 30,
      threshold_multiplier: 2.0
    })
  
  if (error) throw error
  
  return new Response(JSON.stringify({ 
    success: true,
    anomalies: anomalies || []
  }), {
    headers: { ...corsHeaders, 'Content-Type': 'application/json' }
  })
}

function categorizeByRules(description, amount) {
  const desc = description.toLowerCase()
  
  // Income patterns
  if (amount > 0) {
    if (desc.includes('salary') || desc.includes('payroll') || desc.includes('wage')) {
      return 'Income'
    }
    return 'Income'
  }
  
  // Expense patterns
  if (desc.includes('grocery') || desc.includes('food') || desc.includes('restaurant') || 
      desc.includes('starbucks') || desc.includes('mcdonalds') || desc.includes('pizza')) {
    return 'Food'
  }
  
  if (desc.includes('gas') || desc.includes('fuel') || desc.includes('uber') || 
      desc.includes('lyft') || desc.includes('metro') || desc.includes('bus')) {
    return 'Transport'
  }
  
  if (desc.includes('movie') || desc.includes('netflix') || desc.includes('spotify') || 
      desc.includes('game') || desc.includes('entertainment')) {
    return 'Entertainment'
  }
  
  if (desc.includes('electric') || desc.includes('water') || desc.includes('internet') || 
      desc.includes('phone') || desc.includes('rent') || desc.includes('insurance')) {
    return 'Bills'
  }
  
  if (desc.includes('amazon') || desc.includes('target') || desc.includes('walmart') || 
      desc.includes('shopping') || desc.includes('store')) {
    return 'Shopping'
  }
  
  if (desc.includes('doctor') || desc.includes('hospital') || desc.includes('pharmacy') || 
      desc.includes('medical') || desc.includes('health')) {
    return 'Healthcare'
  }
  
  return 'Other'
}
