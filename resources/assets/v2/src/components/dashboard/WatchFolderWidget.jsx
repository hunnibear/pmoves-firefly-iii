import React, { useState, useEffect } from 'react';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { FolderKanban, FileClock, FileCheck2, FileX2, Loader2 } from 'lucide-react';

const WatchFolderWidget = () => {
    const [stats, setStats] = useState({ incoming: 0, processed: 0, failed: 0 });
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    const fetchStats = async () => {
        try {
            const response = await fetch('/api/v1/watch-folders/status');
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            const data = await response.json();
            setStats(data.data);
            setError(null);
        } catch (err) {
            setError('Failed to fetch watch folder status.');
            console.error(err);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchStats();
        const interval = setInterval(fetchStats, 30000); // Refresh every 30 seconds
        return () => clearInterval(interval);
    }, []);

    return (
        <Card>
            <CardHeader>
                <CardTitle className="flex items-center gap-2">
                    <FolderKanban className="h-5 w-5 text-gray-500" />
                    <span>Watch Folder</span>
                </CardTitle>
                <CardDescription>Automated document processing status.</CardDescription>
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
                    <div className="grid grid-cols-3 gap-4 text-center">
                        <div>
                            <FileClock className="h-6 w-6 mx-auto text-blue-500" />
                            <p className="text-2xl font-bold">{stats.incoming}</p>
                            <p className="text-xs text-muted-foreground">Incoming</p>
                        </div>
                        <div>
                            <FileCheck2 className="h-6 w-6 mx-auto text-green-500" />
                            <p className="text-2xl font-bold">{stats.processed}</p>
                            <p className="text-xs text-muted-foreground">Processed</p>
                        </div>
                        <div>
                            <FileX2 className="h-6 w-6 mx-auto text-red-500" />
                            <p className="text-2xl font-bold">{stats.failed}</p>
                            <p className="text-xs text-muted-foreground">Failed</p>
                        </div>
                    </div>
                )}
            </CardContent>
        </Card>
    );
};

export default WatchFolderWidget;
