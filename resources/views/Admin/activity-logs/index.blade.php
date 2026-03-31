@extends('Admin.layouts.app')

@section('content')
    <div class="container-fluid py-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h2 class="mb-1">Activity Logs</h2>
                <p class="text-muted mb-0">Track admin and manager actions across the system.</p>
            </div>

            <div class="d-flex gap-2">
                <a href="{{ route('admin.activity-logs.export', request()->query()) }}" class="btn btn-outline-primary">
                    <i class="fas fa-download me-1"></i> Export
                </a>
                <form method="POST" action="{{ route('admin.activity-logs.clear') }}">
                    @csrf
                    <input type="hidden" name="days" value="30">
                    <button type="submit" class="btn btn-outline-danger"
                        onclick="return confirm('Clear activity logs older than 30 days?')">
                        <i class="fas fa-trash-alt me-1"></i> Clear Old Logs
                    </button>
                </form>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.activity-logs.index') }}" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">User</label>
                        <select name="user_id" class="form-select">
                            <option value="">All Users</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Action</label>
                        <input type="text" name="action" value="{{ request('action') }}" class="form-control"
                            placeholder="Search action...">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">From</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">To</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control">
                    </div>

                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-fill">Filter</button>
                        <a href="{{ route('admin.activity-logs.index') }}" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>When</th>
                                <th>User</th>
                                <th>Action</th>
                                <th>Resource</th>
                                <th>IP Address</th>
                                <th class="text-end">Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($logs as $log)
                                <tr>
                                    <td>#{{ $log->id }}</td>
                                    <td>{{ optional($log->created_at)->format('d M Y, h:i A') ?? 'N/A' }}</td>
                                    <td>{{ $log->user->name ?? 'System' }}</td>
                                    <td>{{ $log->action }}</td>
                                    <td>
                                        {{ $log->model_type ?: 'N/A' }}
                                        @if ($log->model_id)
                                            <span class="text-muted">#{{ $log->model_id }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $log->ip_address ?: 'N/A' }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.activity-logs.show', $log->id) }}"
                                            class="btn btn-sm btn-outline-primary">
                                            View
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">No activity logs found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if ($logs->hasPages())
                <div class="card-footer">
                    {{ $logs->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
