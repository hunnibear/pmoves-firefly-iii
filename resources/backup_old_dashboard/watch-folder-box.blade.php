<div class="col-xl-3 col-lg-6 col-md-12 col-sm-12" style="flex-grow: 1;" x-data="watchFolderBox" x-bind="eventListeners">
    <!--begin::Watch Folder Widget-->
    <div class="small-box text-bg-info">
        <div class="inner">
            <h4 class="hover-expand" x-text="watchFolderBox.totalFiles || '0'"></h4>
            
            <template x-if="loading">
                <p class="d-none d-sm-block">
                    <em class="fa-solid fa-spinner fa-spin"></em>
                </p>
            </template>
            
            <template x-if="!loading">
                <p class="d-none d-sm-block">
                    <a href="{{ route('watch-folders.index') }}">{{ __('firefly.watch_folder_files') }}</a>
                </p>
            </template>
        </div>
        
        <span class="small-box-icon">
            <i class="fa-solid fa-folder-open"></i>
        </span>
        
        <div class="small-box-footer hover-footer d-none d-xl-block">
            <template x-if="watchFolderBox.processingFiles > 0">
                <span x-text="'Processing: ' + watchFolderBox.processingFiles"></span>
            </template>
            <template x-if="watchFolderBox.processingFiles === 0">
                <span x-text="'Processed: ' + watchFolderBox.processedFiles"></span>
            </template>
        </div>
    </div>
    <!--end::Watch Folder Widget-->
</div>