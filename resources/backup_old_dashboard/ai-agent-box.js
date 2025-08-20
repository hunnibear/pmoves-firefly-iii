/*
 * ai-agent-box.js
 * Copyright (c) 2024 pmoves-firefly-iii
 *
 * This file manages the AI agent dashboard widget
 */

import {getCacheKey} from "../../support/get-cache-key.js";
import {cleanupCache} from "../../support/cleanup-cache.js";

let afterPromises = false;

export default () => ({
    aiAgentBox: {
        status: 'loading',
        processedToday: 0,
        totalProcessed: 0,
        lastProcessed: null
    },
    loading: false,
    boxData: null,
    
    eventListeners: {
        ['@ai-agent-refresh.window'](event) {
            console.log('Refreshing AI agent data');
            this.boxData = null;
            this.loadAiAgentData();
        }
    },

    async getFreshData() {
        const cacheKey = getCacheKey('ai_agent_status', {});
        cleanupCache();

        let cachedData = window.store.get(cacheKey);
        const cacheValid = false; // force refresh for now

        if (cacheValid && typeof cachedData !== 'undefined') {
            this.boxData = cachedData;
            this.generateOptions(this.boxData);
            return;
        }

        try {
            // Call our AI agent health endpoint
            const response = await fetch('/api/v1/ai-agent/status', {
                headers: {
                    'Authorization': 'Bearer ' + window.token,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            });

            if (response.ok) {
                const data = await response.json();
                this.boxData = data.data;
                window.store.set(cacheKey, data.data);
                this.generateOptions(this.boxData);
            } else {
                console.error('Failed to fetch AI agent status:', response.status);
                // Set offline status if we can't reach the agent
                this.boxData = {
                    status: 'stopped',
                    processedToday: 0,
                    totalProcessed: 0,
                    lastProcessed: null
                };
                this.generateOptions(this.boxData);
            }
        } catch (error) {
            console.error('Error fetching AI agent status:', error);
            // Set offline status on network error
            this.boxData = {
                status: 'stopped',
                processedToday: 0,
                totalProcessed: 0,
                lastProcessed: null
            };
            this.generateOptions(this.boxData);
        }
    },

    generateOptions(data) {
        this.aiAgentBox = {
            status: data.status || 'stopped',
            processedToday: data.processed_today || 0,
            totalProcessed: data.total_processed || 0,
            lastProcessed: data.last_processed || null
        };
        this.loading = false;
    },

    loadAiAgentData() {
        if (this.loading) {
            return;
        }
        
        this.loading = true;
        
        if (this.boxData === null) {
            this.getFreshData();
            return;
        }
        
        this.generateOptions(this.boxData);
        this.loading = false;
    },

    init() {
        console.log('ai-agent-box init');
        afterPromises = true;
        this.loadAiAgentData();
        
        // Refresh every 30 seconds
        setInterval(() => {
            if (afterPromises) {
                this.boxData = null;
                this.loadAiAgentData();
            }
        }, 30000);
    }
});