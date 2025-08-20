import React from 'react';
import { createRoot } from 'react-dom/client';
import WatchFolderWidget from '@/components/dashboard/WatchFolderWidget';
import AiAgentWidget from '@/components/dashboard/AiAgentWidget';

const Dashboard = () => {
    return (
        <div className="container-fluid">
            <div className="row mb-4">
                <div className="col-lg-6">
                    <WatchFolderWidget />
                </div>
                <div className="col-lg-6">
                    <AiAgentWidget />
                </div>
            </div>
            {/* Include other dashboard components here if needed */}
        </div>
    );
};

const container = document.getElementById('dashboard-root');
if (container) {
    const root = createRoot(container);
    root.render(
        <React.StrictMode>
            <Dashboard />
        </React.StrictMode>
    );
}
