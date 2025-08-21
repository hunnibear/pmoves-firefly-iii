@php
    $title = __('Watch Folders');
    $mainTitleIcon = 'fa-folder-open';
    $subTitle = __('Monitor watch folder processing and recent files');
@endphp

@extends('v2.layout.v2')

@section('content')
    <div id="v2-watch-folders-root" class="container-fluid">
        <div class="row">
            <div class="col">
                <h4>{{ $title }}</h4>
                <p class="text-muted">{{ $subTitle }}</p>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-12">
                <div id="wf-status" class="card p-3">
                    <p>Loading watch-folder status...</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function () {
            async function fetchStatus() {
                const el = document.getElementById('wf-status');
                try {
                    const resp = await fetch('/api/v1/watch-folders/status', { credentials: 'same-origin' });
                    if (resp.status === 401) {
                        el.innerHTML = '<div class="text-danger">Unauthorized. Please sign in to view watch folder status.</div>';
                        return;
                    }
                    const json = await resp.json();
                    if (!json || !json.data) {
                        el.innerHTML = '<div class="text-danger">Invalid response from server.</div>';
                        return;
                    }
                    const s = json.data.statistics || {};
                    const recent = (json.data.recent_files || []).slice(0,5);
                    el.innerHTML = `
                        <div class="mb-2"><strong>Processed files:</strong> ${s.processed_files ?? 'n/a'}</div>
                        <div class="mb-2"><strong>Queue length:</strong> ${s.queue_length ?? 'n/a'}</div>
                        <div class="mb-2"><strong>Last run:</strong> ${s.last_run ?? 'n/a'}</div>
                        <h5 class="mt-3">Recent files</h5>
                        <ul class="list-group">
                            ${recent.length ? recent.map(f => `<li class="list-group-item">${f.filename} <small class="text-muted">(${f.status})</small></li>`).join('') : '<li class="list-group-item text-muted">No recent files</li>'}
                        </ul>
                    `;
                } catch (e) {
                    el.innerHTML = '<div class="text-danger">Error fetching status: ' + (e.message || e) + '</div>';
                }
            }

            document.addEventListener('DOMContentLoaded', fetchStatus);
        })();
    </script>

@endsection
