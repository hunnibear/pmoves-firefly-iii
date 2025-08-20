import React, { useState, useEffect } from 'react';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Bot, CheckCircle2, XCircle, Loader2 } from 'lucide-react';
import { Badge } from '@/components/ui/badge';

const AiAgentWidget = () => {
    const [status, setStatus] = useState({ status: 'offline', jobs_processed: 0, jobs_failed: 0 });
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    const fetchStatus = async () => {
        try {
            const response = await fetch('/api/v1/ai-agent/status');
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            const data = await response.json();
            setStatus(data.data);
            setError(null);
        } catch (err) {
            setError('Failed to fetch AI agent status.');
            console.error(err);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchStatus();
        const interval = setInterval(fetchStatus, 30000); // Refresh every 30 seconds
        return () => clearInterval(interval);
    }, []);

    const getStatusBadge = () => {
        if (status.status === 'online') {
            return <Badge variant="default" className="bg-green-500">Online</Badge>;
        }
        return <Badge variant="destructive">Offline</Badge>;
    };

    return (
        <Card>
            <CardHeader>
                <CardTitle className="flex items-center justify-between">
                    <div className="flex items-center gap-2">
                        <Bot className="h-5 w-5 text-gray-500" />
                        <span>AI Agent</span>
                    </div>
                    { !loading && !error && getStatusBadge() }
                </CardTitle>
                <CardDescription>Autonomous financial processing agent.</CardDescription>
            </CardHeader>
            <CardContent>
                {loading ? (
                    <div className="flex justify-center items-center h-24">
                        <Loader2 className="h-8 w-8 animate-spin text-gray-400" />
                    </div>
                ) : error ? (
                    <div className="flex justify-center items-center h-24">
                        <p className="text-red-500">{error}</p>
                    </div>
                ) : (
                    <div className="grid grid-cols-2 gap-4 text-center">
                        <div>
                            <CheckCircle2 className="h-6 w-6 mx-auto text-green-500" />
                            <p className="text-2xl font-bold">{status.jobs_processed}</p>
                            <p className="text-xs text-muted-foreground">Processed</p>
                        </div>
                        <div>
                            <XCircle className="h-6 w-6 mx-auto text-red-500" />
                            <p className="text-2xl font-bold">{status.jobs_failed}</p>
                            <p className="text-xs text-muted-foreground">Failed</p>
                        </div>
                    </div>
                )}
            </CardContent>
        </Card>
    );
};

export default AiAgentWidget;
