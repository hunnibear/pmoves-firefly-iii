@extends('layout.default')
@section('content')
    @component('components.messages')
    @endcomponent

    <div class="row">
        <div class="col-lg-3 col-md-6">
            <!-- AI Status Card -->
            <div class="small-box bg-info">
                <div class="inner">
                    <h3 id="ai-status">Checking...</h3>
                    <p>AI Service Status</p>
                </div>
                <div class="icon">
                    <i class="fas fa-robot"></i>
                </div>
                <div class="small-box-footer">
                    <button id="test-connectivity" class="btn btn-primary btn-sm">
                        <i class="fas fa-sync"></i> Test Connection
                    </button>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <!-- Transaction Categorization -->
            <div class="small-box bg-success">
                <div class="inner">
                    <h3><i class="fas fa-tags"></i></h3>
                    <p>Smart Categorization</p>
                </div>
                <div class="icon">
                    <i class="fas fa-magic"></i>
                </div>
                <div class="small-box-footer">
                    Auto-categorize transactions
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <!-- Financial Insights -->
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3><i class="fas fa-chart-line"></i></h3>
                    <p>Financial Insights</p>
                </div>
                <div class="icon">
                    <i class="fas fa-brain"></i>
                </div>
                <div class="small-box-footer">
                    <button id="get-insights" class="btn btn-warning btn-sm">
                        <i class="fas fa-lightbulb"></i> Get Insights
                    </button>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <!-- Anomaly Detection -->
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3 id="anomaly-count">?</h3>
                    <p>Spending Anomalies</p>
                </div>
                <div class="icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="small-box-footer">
                    <button id="detect-anomalies" class="btn btn-danger btn-sm">
                        <i class="fas fa-search"></i> Detect Anomalies
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- AI Chat Assistant -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-comments"></i> AI Financial Assistant
                    </h3>
                </div>
                <div class="card-body">
                    <div id="chat-messages" style="height: 300px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; margin-bottom: 10px;">
                        <div class="message ai-message">
                            <strong>AI Assistant:</strong> Hello! I'm your AI financial assistant. I can help you understand your spending patterns, categorize transactions, and provide financial insights. How can I help you today?
                        </div>
                    </div>
                    <div class="input-group">
                        <input type="text" id="chat-input" class="form-control" placeholder="Ask me about your finances...">
                        <div class="input-group-append">
                            <button id="send-chat" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> Send
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transaction Categorization Tool -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-magic"></i> Smart Categorization
                    </h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="transaction-description">Transaction Description:</label>
                        <input type="text" id="transaction-description" class="form-control" placeholder="e.g., Starbucks Coffee">
                    </div>
                    <div class="form-group">
                        <label for="transaction-amount">Amount:</label>
                        <input type="number" id="transaction-amount" class="form-control" placeholder="0.00" step="0.01">
                    </div>
                    <button id="categorize-transaction" class="btn btn-success btn-block">
                        <i class="fas fa-tags"></i> Suggest Category
                    </button>
                    <div id="category-suggestion" class="mt-3" style="display: none;">
                        <div class="alert alert-info">
                            <strong>AI Suggestion:</strong>
                            <span id="suggested-category"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Insights Panel -->
    <div class="row">
        <div class="col-12">
            <div class="card" id="insights-panel" style="display: none;">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar"></i> AI Financial Insights
                    </h3>
                </div>
                <div class="card-body">
                    <div id="insights-content">
                        <!-- Insights will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Anomalies Panel -->
    <div class="row">
        <div class="col-12">
            <div class="card" id="anomalies-panel" style="display: none;">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-exclamation-triangle"></i> Spending Anomalies
                    </h3>
                </div>
                <div class="card-body">
                    <div id="anomalies-content">
                        <!-- Anomalies will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
<style>
.message {
    margin-bottom: 10px;
    padding: 8px;
    border-radius: 4px;
}
.user-message {
    background-color: #e3f2fd;
    text-align: right;
}
.ai-message {
    background-color: #f5f5f5;
}
.loading {
    opacity: 0.6;
}
</style>

<script>
$(document).ready(function() {
    // Test AI connectivity on page load
    testConnectivity();

    // Test connectivity button
    $('#test-connectivity').click(function() {
        testConnectivity();
    });

    // Chat functionality
    $('#send-chat').click(function() {
        sendChatMessage();
    });

    $('#chat-input').keypress(function(e) {
        if (e.which === 13) {
            sendChatMessage();
        }
    });

    // Transaction categorization
    $('#categorize-transaction').click(function() {
        categorizeTransaction();
    });

    // Get insights
    $('#get-insights').click(function() {
        getInsights();
    });

    // Detect anomalies
    $('#detect-anomalies').click(function() {
        detectAnomalies();
    });

    function testConnectivity() {
        $('#ai-status').text('Testing...');
        
        $.get('{{ route("ai.test-connectivity") }}')
            .done(function(data) {
                if (data.success) {
                    $('#ai-status').html('<i class="fas fa-check text-success"></i> Online');
                } else {
                    $('#ai-status').html('<i class="fas fa-times text-danger"></i> Offline');
                }
            })
            .fail(function() {
                $('#ai-status').html('<i class="fas fa-times text-danger"></i> Error');
            });
    }

    function sendChatMessage() {
        const message = $('#chat-input').val().trim();
        if (!message) return;

        // Add user message to chat
        $('#chat-messages').append(`
            <div class="message user-message">
                <strong>You:</strong> ${message}
            </div>
        `);

        // Clear input
        $('#chat-input').val('');

        // Add loading message
        $('#chat-messages').append(`
            <div class="message ai-message loading" id="loading-message">
                <strong>AI Assistant:</strong> <i class="fas fa-spinner fa-spin"></i> Thinking...
            </div>
        `);

        // Scroll to bottom
        $('#chat-messages').scrollTop($('#chat-messages')[0].scrollHeight);

        // Send to AI
        $.post('{{ route("ai.chat") }}', {
            message: message,
            _token: '{{ csrf_token() }}'
        })
        .done(function(data) {
            $('#loading-message').remove();
            $('#chat-messages').append(`
                <div class="message ai-message">
                    <strong>AI Assistant:</strong> ${data.response}
                </div>
            `);
            $('#chat-messages').scrollTop($('#chat-messages')[0].scrollHeight);
        })
        .fail(function() {
            $('#loading-message').remove();
            $('#chat-messages').append(`
                <div class="message ai-message">
                    <strong>AI Assistant:</strong> Sorry, I'm having trouble processing your request right now.
                </div>
            `);
            $('#chat-messages').scrollTop($('#chat-messages')[0].scrollHeight);
        });
    }

    function categorizeTransaction() {
        const description = $('#transaction-description').val().trim();
        const amount = $('#transaction-amount').val();

        if (!description) {
            alert('Please enter a transaction description');
            return;
        }

        $('#categorize-transaction').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Analyzing...');

        $.post('{{ route("ai.categorize-transaction") }}', {
            description: description,
            amount: amount,
            _token: '{{ csrf_token() }}'
        })
        .done(function(data) {
            $('#suggested-category').text(data.category);
            $('#category-suggestion').show();
        })
        .fail(function() {
            alert('Error categorizing transaction');
        })
        .always(function() {
            $('#categorize-transaction').prop('disabled', false).html('<i class="fas fa-tags"></i> Suggest Category');
        });
    }

    function getInsights() {
        $('#get-insights').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Analyzing...');

        $.get('{{ route("ai.insights") }}')
            .done(function(data) {
                $('#insights-content').html(data.insights);
                $('#insights-panel').show();
                $('html, body').animate({
                    scrollTop: $('#insights-panel').offset().top
                }, 500);
            })
            .fail(function() {
                alert('Error getting insights');
            })
            .always(function() {
                $('#get-insights').prop('disabled', false).html('<i class="fas fa-lightbulb"></i> Get Insights');
            });
    }

    function detectAnomalies() {
        $('#detect-anomalies').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Detecting...');

        $.get('{{ route("ai.anomalies") }}')
            .done(function(data) {
                $('#anomaly-count').text(data.anomalies.length);
                
                if (data.anomalies.length > 0) {
                    let anomaliesHtml = '<ul>';
                    data.anomalies.forEach(function(anomaly) {
                        anomaliesHtml += `<li>${anomaly}</li>`;
                    });
                    anomaliesHtml += '</ul>';
                    $('#anomalies-content').html(anomaliesHtml);
                    $('#anomalies-panel').show();
                } else {
                    $('#anomalies-content').html('<p class="text-success">No spending anomalies detected. Your spending patterns look normal!</p>');
                    $('#anomalies-panel').show();
                }
                
                $('html, body').animate({
                    scrollTop: $('#anomalies-panel').offset().top
                }, 500);
            })
            .fail(function() {
                alert('Error detecting anomalies');
            })
            .always(function() {
                $('#detect-anomalies').prop('disabled', false).html('<i class="fas fa-search"></i> Detect Anomalies');
            });
    }
});
</script>
@endsection
