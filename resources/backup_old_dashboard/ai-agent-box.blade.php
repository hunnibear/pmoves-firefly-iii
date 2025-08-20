<div class="col-xl-3 col-lg-6 col-md-12 col-sm-12" style="flex-grow: 1;" x-data="aiAgentBox" x-bind="eventListeners">
    <!--begin::AI Agent Widget-->
    <div class="small-box text-bg-secondary">
        <div class="inner">
            <h4 class="hover-expand">
                <template x-if="aiAgentBox.status === 'running'">
                    <span class="text-success">
                        <i class="fa-solid fa-circle-check"></i> Online
                    </span>
                </template>
                <template x-if="aiAgentBox.status === 'stopped'">
                    <span class="text-danger">
                        <i class="fa-solid fa-circle-xmark"></i> Offline
                    </span>
                </template>
                <template x-if="aiAgentBox.status === 'loading'">
                    <span class="text-warning">
                        <i class="fa-solid fa-spinner fa-spin"></i> Checking
                    </span>
                </template>
            </h4>
            
            <template x-if="loading">
                <p class="d-none d-sm-block">
                    <em class="fa-solid fa-spinner fa-spin"></em>
                </p>
            </template>
            
            <template x-if="!loading">
                <p class="d-none d-sm-block">
                    <a href="{{ route('ai-agent.dashboard') }}">{{ __('firefly.ai_transaction_agent') }}</a>
                </p>
            </template>
        </div>
        
        <span class="small-box-icon">
            <i class="fa-solid fa-robot"></i>
        </span>
        
        <div class="small-box-footer hover-footer d-none d-xl-block">
            <template x-if="aiAgentBox.processedToday > 0">
                <span x-text="'Today: ' + aiAgentBox.processedToday + ' docs'"></span>
            </template>
            <template x-if="aiAgentBox.processedToday === 0">
                <span>Ready for processing</span>
            </template>
        </div>
    </div>
    <!--end::AI Agent Widget-->
</div>