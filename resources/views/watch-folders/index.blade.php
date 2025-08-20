@extends('layout.v2')
@section('content')
<div class="app-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fa-solid fa-folder-open"></i>
                            {{ __('firefly.watch_folders') }}
                        </h3>
                        <div class="card-tools">
                            <a href="{{ route('watch-folders.upload') }}" class="btn btn-primary">
                                <i class="fa-solid fa-upload"></i>
                                {{ __('firefly.upload_document') }}
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <div class="info-box">
                                    <span class="info-box-icon bg-info">
                                        <i class="fa-solid fa-inbox"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Incoming Files</span>
                                        <span class="info-box-number" id="incoming-count">-</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-box">
                                    <span class="info-box-icon bg-success">
                                        <i class="fa-solid fa-check"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Processed</span>
                                        <span class="info-box-number" id="processed-count">-</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-box">
                                    <span class="info-box-icon bg-warning">
                                        <i class="fa-solid fa-spinner"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Processing</span>
                                        <span class="info-box-number" id="processing-count">-</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-box">
                                    <span class="info-box-icon bg-danger">
                                        <i class="fa-solid fa-exclamation-triangle"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Failed</span>
                                        <span class="info-box-number" id="failed-count">-</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>{{ __('firefly.file_name') }}</th>
                                        <th>{{ __('firefly.file_size') }}</th>
                                        <th>{{ __('firefly.uploaded_at') }}</th>
                                        <th>{{ __('firefly.status') }}</th>
                                        <th>{{ __('firefly.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody id="files-table">
                                    <tr>
                                        <td colspan="5" class="text-center">
                                            <i class="fa-solid fa-spinner fa-spin"></i>
                                            Loading files...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
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
    // Placeholder for watch folder functionality
    document.getElementById('incoming-count').textContent = '0';
    document.getElementById('processed-count').textContent = '0';
    document.getElementById('processing-count').textContent = '0';
    document.getElementById('failed-count').textContent = '0';
    
    document.getElementById('files-table').innerHTML = `
        <tr>
            <td colspan="5" class="text-center text-muted">
                <i class="fa-solid fa-folder-open"></i>
                No files found. Upload a document to get started.
            </td>
        </tr>
    `;
});
</script>
@endsection