@extends('layout.v2')
@section('content')
<div class="app-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fa-solid fa-robot"></i>
                            {{ __('firefly.ai_dashboard') }}
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="info-box">
                                    <span class="info-box-icon bg-primary">
                                        <i class="fa-solid fa-robot"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Agent Status</span>
                                        <span class="info-box-number" id="agent-status">
                                            <i class="fa-solid fa-spinner fa-spin"></i> Checking...
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-box">
                                    <span class="info-box-icon bg-success">
                                        <i class="fa-solid fa-calendar-day"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Processed Today</span>
                                        <span class="info-box-number" id="processed-today">-</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-box">
                                    <span class="info-box-icon bg-info">
                                        <i class="fa-solid fa-chart-line"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Total Processed</span>
                                        <span class="info-box-number" id="total-processed">-</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">Agent Configuration</h3>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-sm">
                                            <tr>
                                                <td><strong>Agent URL:</strong></td>
                                                <td id="agent-url">{{ config('firefly.ai_agent_url') }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Status:</strong></td>
                                                <td id="agent-detailed-status">
                                                    <i class="fa-solid fa-spinner fa-spin"></i> Checking...
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>Last Check:</strong></td>
                                                <td id="last-check">-</td>
                                            </tr>
                                        </table>
                                        
                                        <div class="mt-3">
                                            <button id="test-connection" class="btn btn-outline-primary">
                                                <i class="fa-solid fa-plug"></i>
                                                Test Connection
                                            </button>
                                            <a href="{{ route('ai-agent.settings') }}" class="btn btn-outline-secondary">
                                                <i class="fa-solid fa-cog"></i>
                                                Settings
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">Recent Activity</h3>
                                    </div>
                                    <div class="card-body">
                                        <div id="recent-activity">
                                            <p class="text-muted text-center">
                                                <i class="fa-solid fa-clock"></i>
                                                No recent activity
                                            </p>
                                        </div>
                                        
                                        <div class="mt-3">
                                            <a href="{{ route('ai-agent.logs') }}" class="btn btn-outline-info">
                                                <i class="fa-solid fa-file-text"></i>
                                                View All Logs
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    loadAgentStatus();
    
    // Refresh every 30 seconds
    setInterval(loadAgentStatus, 30000);
    
    // Test connection button
    document.getElementById('test-connection').addEventListener('click', function() {
        this.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Testing...';
        this.disabled = true;
        
        setTimeout(() => {
            loadAgentStatus();
            this.innerHTML = '<i class="fa-solid fa-plug"></i> Test Connection';
            this.disabled = false;
        }, 2000);
    });
});

async function loadAgentStatus() {
    try {
        const response = await fetch('/api/v1/ai-agent/status', {
            headers: {
                'Authorization': 'Bearer ' + (window.token || ''),
                'Accept': 'application/json'
            }
        });
        
        if (response.ok) {
            const data = await response.json();
            updateStatusDisplay(data.data);
        } else {
            updateStatusDisplay({
                status: 'error',
                processed_today: 0,
                total_processed: 0,
                last_checked: new Date().toISOString()
            });
        }
    } catch (error) {
        console.error('Error loading agent status:', error);
        updateStatusDisplay({
            status: 'offline',
            processed_today: 0,
            total_processed: 0,
            last_checked: new Date().toISOString()
        });
    }
}

function updateStatusDisplay(data) {
    // Update status indicators
    const statusElement = document.getElementById('agent-status');
    const detailedStatusElement = document.getElementById('agent-detailed-status');
    
    if (data.status === 'running') {
        statusElement.innerHTML = '<span class="text-success"><i class="fa-solid fa-circle-check"></i> Online</span>';
        detailedStatusElement.innerHTML = '<span class="badge bg-success">Running</span>';
    } else if (data.status === 'stopped' || data.status === 'offline') {
        statusElement.innerHTML = '<span class="text-danger"><i class="fa-solid fa-circle-xmark"></i> Offline</span>';
        detailedStatusElement.innerHTML = '<span class="badge bg-danger">Offline</span>';
    } else {
        statusElement.innerHTML = '<span class="text-warning"><i class="fa-solid fa-exclamation-triangle"></i> Error</span>';
        detailedStatusElement.innerHTML = '<span class="badge bg-warning">Error</span>';
    }
    
    // Update counts
    document.getElementById('processed-today').textContent = data.processed_today || '0';
    document.getElementById('total-processed').textContent = data.total_processed || '0';
    document.getElementById('last-check').textContent = new Date().toLocaleString();
}
</script>
@endsection