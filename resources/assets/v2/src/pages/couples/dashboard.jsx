import React from 'react';
import { createRoot } from 'react-dom/client';
import { CouplesDashboard } from '@/components/couples/CouplesDashboard';
import { Toaster } from '@/components/ui/toaster';
import '@/css/globals.css';

// Initialize the couples dashboard
const container = document.getElementById('couples-dashboard-root');
if (container) {
  const root = createRoot(container);
  root.render(
    <React.StrictMode>
      <CouplesDashboard />
      <Toaster />
    </React.StrictMode>
  );
}