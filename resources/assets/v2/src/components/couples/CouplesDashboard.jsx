import React, { useState, useEffect } from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Progress } from '@/components/ui/progress';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { ChartContainer, ChartTooltip, ChartTooltipContent } from '@/components/ui/chart';
import { PieChart, Pie, Cell, ResponsiveContainer, BarChart, Bar, XAxis, YAxis } from 'recharts';
import { Plus, Camera, TrendingUp, TrendingDown, Upload } from 'lucide-react';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import ReceiptUploadZone from './ReceiptUploadZone';

// Sample data for demonstration
const budgetData = [
  { category: 'Groceries', spent: 450, budget: 600, color: '#8884d8' },
  { category: 'Restaurants', spent: 280, budget: 300, color: '#82ca9d' },
  { category: 'Transportation', spent: 150, budget: 200, color: '#ffc658' },
  { category: 'Entertainment', spent: 120, budget: 150, color: '#ff7300' },
];

const recentTransactions = [
  { id: 1, description: 'Grocery Store', amount: -85.50, category: 'Groceries', partner: 'Alex', date: '2025-08-19' },
  { id: 2, description: 'Coffee Shop', amount: -12.75, category: 'Restaurants', partner: 'Jamie', date: '2025-08-19' },
  { id: 3, description: 'Gas Station', amount: -45.00, category: 'Transportation', partner: 'Alex', date: '2025-08-18' },
  { id: 4, description: 'Movie Theater', amount: -25.00, category: 'Entertainment', partner: 'Jamie', date: '2025-08-18' },
];

const chartConfig = {
  spent: {
    label: "Spent",
    color: "hsl(var(--chart-1))",
  },
  budget: {
    label: "Budget",
    color: "hsl(var(--chart-2))",
  },
};

export function CouplesDashboard() {
  const [budgetData, setBudgetData] = useState([
    { category: 'Groceries', spent: 450, budget: 600, color: '#8884d8' },
    { category: 'Restaurants', spent: 280, budget: 300, color: '#82ca9d' },
    { category: 'Transportation', spent: 150, budget: 200, color: '#ffc658' },
    { category: 'Entertainment', spent: 120, budget: 150, color: '#ff7300' },
  ]);
  
  const [recentTransactions, setRecentTransactions] = useState([
    { id: 1, description: 'Grocery Store', amount: -85.50, category: 'Groceries', partner: 'Alex', date: '2025-08-19' },
    { id: 2, description: 'Coffee Shop', amount: -12.75, category: 'Restaurants', partner: 'Jamie', date: '2025-08-19' },
    { id: 3, description: 'Gas Station', amount: -45.00, category: 'Transportation', partner: 'Alex', date: '2025-08-18' },
    { id: 4, description: 'Movie Theater', amount: -25.00, category: 'Entertainment', partner: 'Jamie', date: '2025-08-18' },
  ]);
  
  const [isReceiptDialogOpen, setIsReceiptDialogOpen] = useState(false);
  const [loading, setLoading] = useState(false);

  // Load data from API
  useEffect(() => {
    fetchDashboardData();
  }, []);

  const fetchDashboardData = async () => {
    setLoading(true);
    try {
      // Get CSRF token
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
      
      const response = await fetch('/api/v1/couples/dashboard', {
        headers: {
          'X-CSRF-TOKEN': csrfToken,
          'X-Requested-With': 'XMLHttpRequest',
        },
      });

      if (response.ok) {
        const data = await response.json();
        
        // Update budget data if available
        if (data.budget_categories) {
          setBudgetData(data.budget_categories);
        }
        
        // Update recent transactions if available
        if (data.recent_transactions) {
          setRecentTransactions(data.recent_transactions);
        }
      } else {
        console.warn('Failed to fetch dashboard data, using fallback data');
      }
    } catch (error) {
      console.warn('Dashboard API not available, using static data:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleReceiptUploadSuccess = (result) => {
    console.log('Receipt processed successfully:', result);
    
    // Add the new transaction to recent transactions
    if (result.extracted_data) {
      const newTransaction = {
        id: Date.now(), // Temporary ID
        description: result.extracted_data.merchant_name || 'New Purchase',
        amount: -(parseFloat(result.extracted_data.total_amount) || 0),
        category: result.ai_suggestions?.category || 'Uncategorized',
        partner: 'Current User', // Should be updated based on auth
        date: result.extracted_data.date || new Date().toISOString().split('T')[0],
        ai_processed: true,
        confidence: result.ai_suggestions?.confidence || 0,
      };
      
      setRecentTransactions(prev => [newTransaction, ...prev.slice(0, 9)]); // Keep last 10
      
      // Update budget data if category matches
      if (result.ai_suggestions?.category) {
        setBudgetData(prev => prev.map(cat => {
          if (cat.category.toLowerCase() === result.ai_suggestions.category.toLowerCase()) {
            return {
              ...cat,
              spent: cat.spent + (parseFloat(result.extracted_data.total_amount) || 0)
            };
          }
          return cat;
        }));
      }
    }
    
    setIsReceiptDialogOpen(false);
  };

  const handleReceiptUploadError = (error) => {
    console.error('Receipt upload failed:', error);
    // Error is already handled in the ReceiptUploadZone component
  };

  const totalSpent = budgetData.reduce((sum, item) => sum + item.spent, 0);
  const totalBudget = budgetData.reduce((sum, item) => sum + item.budget, 0);
  const budgetProgress = (totalSpent / totalBudget) * 100;

  return (
    <div className="min-h-screen bg-background p-4 space-y-6">
      {/* Header */}
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold">Couples Budget</h1>
          <p className="text-muted-foreground">August 2025</p>
        </div>
        <div className="flex items-center gap-2">
          <Avatar className="h-8 w-8">
            <AvatarImage src="/alex-avatar.jpg" />
            <AvatarFallback>A</AvatarFallback>
          </Avatar>
          <Avatar className="h-8 w-8">
            <AvatarImage src="/jamie-avatar.jpg" />
            <AvatarFallback>J</AvatarFallback>
          </Avatar>
        </div>
      </div>

      {/* Quick Actions */}
      <div className="grid grid-cols-2 gap-4">
        <Button className="h-16 flex flex-col gap-1">
          <Plus className="h-5 w-5" />
          <span className="text-sm">Add Transaction</span>
        </Button>
        
        <Dialog open={isReceiptDialogOpen} onOpenChange={setIsReceiptDialogOpen}>
          <DialogTrigger asChild>
            <Button variant="outline" className="h-16 flex flex-col gap-1">
              <Camera className="h-5 w-5" />
              <span className="text-sm">Process Documents</span>
            </Button>
          </DialogTrigger>
          <DialogContent className="max-w-4xl max-h-[90vh] overflow-y-auto">
            <DialogHeader>
              <DialogTitle className="flex items-center gap-2">
                <Upload className="h-5 w-5" />
                AI Document Processing
              </DialogTitle>
            </DialogHeader>
            <ReceiptUploadZone 
              onUploadSuccess={handleReceiptUploadSuccess}
              onUploadError={handleReceiptUploadError}
            />
          </DialogContent>
        </Dialog>
      </div>

      {/* Budget Overview */}
      <Card>
        <CardHeader>
          <CardTitle className="flex items-center justify-between">
            Monthly Budget
            <Badge variant={budgetProgress > 80 ? "destructive" : "secondary"}>
              {budgetProgress.toFixed(0)}% used
            </Badge>
          </CardTitle>
          <CardDescription>
            ${totalSpent.toFixed(2)} of ${totalBudget.toFixed(2)} spent
          </CardDescription>
        </CardHeader>
        <CardContent>
          <Progress value={budgetProgress} className="mb-4" />
          <div className="grid grid-cols-2 gap-4 text-sm">
            <div className="flex items-center gap-2">
              <TrendingDown className="h-4 w-4 text-green-500" />
              <span>Remaining: ${(totalBudget - totalSpent).toFixed(2)}</span>
            </div>
            <div className="flex items-center gap-2">
              <TrendingUp className="h-4 w-4 text-blue-500" />
              <span>Daily avg: ${(totalSpent / 19).toFixed(2)}</span>
            </div>
          </div>
        </CardContent>
      </Card>

      {/* Charts and Data */}
      <Tabs defaultValue="categories" className="w-full">
        <TabsList className="grid w-full grid-cols-2">
          <TabsTrigger value="categories">Categories</TabsTrigger>
          <TabsTrigger value="trends">Trends</TabsTrigger>
        </TabsList>
        
        <TabsContent value="categories" className="space-y-4">
          <Card>
            <CardHeader>
              <CardTitle>Spending by Category</CardTitle>
            </CardHeader>
            <CardContent>
              <ChartContainer config={chartConfig} className="h-[200px]">
                <ResponsiveContainer width="100%" height="100%">
                  <BarChart data={budgetData}>
                    <XAxis dataKey="category" />
                    <YAxis />
                    <ChartTooltip content={<ChartTooltipContent />} />
                    <Bar dataKey="spent" fill="var(--color-spent)" />
                    <Bar dataKey="budget" fill="var(--color-budget)" opacity={0.3} />
                  </BarChart>
                </ResponsiveContainer>
              </ChartContainer>
            </CardContent>
          </Card>

          {/* Category Progress */}
          <div className="space-y-3">
            {budgetData.map((category) => {
              const progress = (category.spent / category.budget) * 100;
              return (
                <Card key={category.category}>
                  <CardContent className="p-4">
                    <div className="flex items-center justify-between mb-2">
                      <span className="font-medium">{category.category}</span>
                      <Badge variant={progress > 80 ? "destructive" : "secondary"}>
                        ${category.spent} / ${category.budget}
                      </Badge>
                    </div>
                    <Progress value={progress} className="h-2" />
                  </CardContent>
                </Card>
              );
            })}
          </div>
        </TabsContent>

        <TabsContent value="trends" className="space-y-4">
          <Card>
            <CardHeader>
              <CardTitle>Spending Distribution</CardTitle>
            </CardHeader>
            <CardContent>
              <ChartContainer config={chartConfig} className="h-[200px]">
                <ResponsiveContainer width="100%" height="100%">
                  <PieChart>
                    <Pie
                      data={budgetData}
                      cx="50%"
                      cy="50%"
                      innerRadius={40}
                      outerRadius={80}
                      dataKey="spent"
                    >
                      {budgetData.map((entry, index) => (
                        <Cell key={`cell-${index}`} fill={entry.color} />
                      ))}
                    </Pie>
                    <ChartTooltip content={<ChartTooltipContent />} />
                  </PieChart>
                </ResponsiveContainer>
              </ChartContainer>
            </CardContent>
          </Card>
        </TabsContent>
      </Tabs>

      {/* Recent Transactions */}
      <Card>
        <CardHeader>
          <CardTitle>Recent Transactions</CardTitle>
          <CardDescription>Latest spending activity</CardDescription>
        </CardHeader>
        <CardContent className="space-y-3">
          {recentTransactions.map((transaction) => (
            <div key={transaction.id} className="flex items-center justify-between p-3 border rounded-lg">
              <div className="flex items-center gap-3">
                <Avatar className="h-8 w-8">
                  <AvatarFallback>
                    {transaction.partner.charAt(0)}
                  </AvatarFallback>
                </Avatar>
                <div>
                  <div className="flex items-center gap-2">
                    <p className="font-medium text-sm">{transaction.description}</p>
                    {transaction.ai_processed && (
                      <Badge variant="secondary" className="text-xs">
                        AI
                      </Badge>
                    )}
                  </div>
                  <p className="text-xs text-muted-foreground">{transaction.date}</p>
                  {transaction.confidence && (
                    <p className="text-xs text-muted-foreground">
                      Confidence: {Math.round(transaction.confidence * 100)}%
                    </p>
                  )}
                </div>
              </div>
              <div className="text-right">
                <p className="font-medium text-sm text-red-600">
                  ${Math.abs(transaction.amount).toFixed(2)}
                </p>
                <Badge variant="outline" className="text-xs">
                  {transaction.category}
                </Badge>
              </div>
            </div>
          ))}
          
          {loading && (
            <div className="text-center py-4 text-muted-foreground">
              Loading transactions...
            </div>
          )}
          
          {recentTransactions.length === 0 && !loading && (
            <div className="text-center py-8 text-muted-foreground">
              <Camera className="h-12 w-12 mx-auto mb-2 opacity-50" />
              <p>No transactions yet</p>
              <p className="text-sm">Upload a receipt to get started!</p>
            </div>
          )}
        </CardContent>
      </Card>
    </div>
  );
}