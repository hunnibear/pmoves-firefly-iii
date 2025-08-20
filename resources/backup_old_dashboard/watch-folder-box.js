/*
 * watch-folder-box.js
 * Copyright (c) 2024 pmoves-firefly-iii
 *
 * This file manages the watch folder dashboard widget
 */

import {getCacheKey} from "../../support/get-cache-key.js";
import {cleanupCache} from "../../support/cleanup-cache.js";

let afterPromises = false;

export default () => ({
    watchFolderBox: {
        totalFiles: 0,
        processedFiles: 0,
        processingFiles: 0,
        failedFiles: 0
    },
    loading: false,
    boxData: null,
    
    eventListeners: {
        ['@watch-folder-refresh.window'](event) {
            console.log('Refreshing watch folder data');
            this.boxData = null;
            this.loadWatchFolderData();
        }
    },

    async getFreshData() {
        const cacheKey = getCacheKey('watch_folder_status', {});
        cleanupCache();

        let cachedData = window.store.get(cacheKey);
        const cacheValid = false; // force refresh for now

        if (cacheValid && typeof cachedData !== 'undefined') {
            this.boxData = cachedData;
            this.generateOptions(this.boxData);
            return;
        }

        try {
            // Call our API endpoint
            const response = await fetch('/api/v1/watch-folders/status', {
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
                console.error('Failed to fetch watch folder status:', response.status);
                this.loading = false;
            }
        } catch (error) {
            console.error('Error fetching watch folder status:', error);
            this.loading = false;
        }
    },

    generateOptions(data) {
        this.watchFolderBox = {
            totalFiles: data.incoming_files || 0,
            processedFiles: data.processed_files || 0,
            processingFiles: data.processing_files || 0,
            failedFiles: data.failed_files || 0
        };
        this.loading = false;
    },

    loadWatchFolderData() {
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
        console.log('watch-folder-box init');
        afterPromises = true;
        this.loadWatchFolderData();
        
        // Refresh every 30 seconds
        setInterval(() => {
            if (afterPromises) {
                this.boxData = null;
                this.loadWatchFolderData();
            }
        }, 30000);
    }
});