@extends('Admin.layouts.app')

@section('content')
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1">Activity Log #{{ $log->id }}</h2>
                <p class="text-muted mb-0">Detailed activity entry information.</p>
            </div>

            <a href="{{ route('admin.activity-logs.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Logs
            </a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100">
                            <div class="small text-muted mb-1">Timestamp</div>
                            <div>{{ optional($log->created_at)->format('d M Y, h:i A') ?? 'N/A' }}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100">
                            <div class="small text-muted mb-1">User</div>
                            <div>{{ $log->user->name ?? 'System' }}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100">
                            <div class="small text-muted mb-1">Action</div>
                            <div>{{ $log->action }}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100">
                            <div class="small text-muted mb-1">IP Address</div>
                            <div>{{ $log->ip_address ?: 'N/A' }}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100">
                            <div class="small text-muted mb-1">Model Type</div>
                            <div>{{ $log->model_type ?: 'N/A' }}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100">
                            <div class="small text-muted mb-1">Model ID</div>
                            <div>{{ $log->model_id ?: 'N/A' }}</div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="border rounded p-3">
                            <div class="small text-muted mb-2">User Agent</div>
                            <div class="text-break">{{ $log->user_agent ?: 'N/A' }}</div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="border rounded p-3">
                            <div class="small text-muted mb-2">Details</div>
                            <pre class="mb-0" style="white-space: pre-wrap;">{{ json_encode($log->details ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
