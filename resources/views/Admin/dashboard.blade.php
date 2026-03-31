@extends('Admin.layouts.app')
@section('content')
    <div id="dashboard" class="page active">
        <!-- Filter Bar -->
        <div class="filter-bar">
            <form method="GET" action="{{ route('admin.dashboard') }}" id="dashboardFilter">
                <div class="filter-group">
                    <div class="filter-label">Date Range</div>
                    <select name="range" onchange="applyFilters()">
                        <option value="today" {{ request('range') == 'today' ? 'selected' : '' }}>Today</option>
                        <option value="week" {{ request('range') == 'week' || !request('range') ? 'selected' : '' }}>This
                            Week</option>
                        <option value="month" {{ request('range') == 'month' ? 'selected' : '' }}>This Month</option>
                        <option value="custom" {{ request('range') == 'custom' ? 'selected' : '' }}>Custom Range</option>
                    </select>
                </div>
                <div class="filter-group">
                    <div class="filter-label">Company</div>
                    <select name="company" onchange="applyFilters()">
                        <option value="">All Companies</option>
                        @foreach ($companies as $company)
                            <option value="{{ $company->id }}" {{ request('company') == $company->id ? 'selected' : '' }}>
                                {{ $company->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group" style="flex-grow: 1;"></div>
                <div class="filter-group" style="align-self: flex-end;">
                    <button type="button" class="btn btn-outline-secondary" onclick="resetFilters()">
                        <i class="fas fa-redo"></i> Reset
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
            </form>
        </div>

        <!-- KPI Cards -->
        <div class="dashboard-grid">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Total Companies</div>
                    <div class="card-icon users">
                        <i class="fas fa-building"></i>
                    </div>
                </div>
                <div class="card-value">{{ $stats['total_companies'] }}</div>
                <div>Active companies in system</div>
                <div class="card-footer">
                    <span>{{ $stats['active_companies'] }} active, {{ $stats['pending_companies'] }} pending</span>
                    <a href="{{ route('admin.companies') }}" class="btn btn-outline"
                        style="padding: 5px 10px; font-size: 0.8rem;">
                        Manage
                    </a>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <div class="card-title">System Users</div>
                    <div class="card-icon users">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
                <div class="card-value">{{ $stats['total_users'] }}</div>
                <div>Active users in system</div>
                <div class="card-footer">
                    <span>{{ $stats['admin_users'] }} admin, {{ $stats['manager_users'] }} managers,
                        {{ $stats['regular_users'] }} users</span>
                    <a href="{{ route('admin.users') }}" class="btn btn-outline"
                        style="padding: 5px 10px; font-size: 0.8rem;">
                        Manage
                    </a>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Expense Types</div>
                    <div class="card-icon settings">
                        <i class="fas fa-list-alt"></i>
                    </div>
                </div>
                <div class="card-value">{{ $stats['expense_types'] }}</div>
                <div>Active expense categories</div>
                <div class="card-footer">
                    <span>{{ $stats['active_expense_types'] }} active</span>
                    <a href="{{ route('admin.expensetypes') }}" class="btn btn-outline"
                        style="padding: 5px 10px; font-size: 0.8rem;">
                        Manage
                    </a>
                </div>
            </div>

        </div>

        <!-- Additional Stats Row -->
        <div class="dashboard-grid mt-4">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Total Transactions</div>
                    <div class="card-icon">
                        <i class="fas fa-exchange-alt"></i>
                    </div>
                </div>
                <div class="card-value">{{ $stats['total_transactions'] }}</div>
                <div>₹{{ number_format($stats['total_amount']) }} total amount</div>
                <div class="card-footer">
                    <span>{{ $stats['income_count'] }} income, {{ $stats['expense_count'] }} expense</span>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Pending Items</div>
                    <div class="card-icon warning">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
                <div class="card-value">{{ $stats['pending_items'] }}</div>
                <div>Items requiring attention</div>
                <div class="card-footer">
                    <span>{{ $stats['pending_invoices'] }} invoices, {{ $stats['pending_expenses'] }} expenses</span>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Overdue Payments</div>
                    <div class="card-icon danger">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
                <div class="card-value">{{ $stats['overdue_payments'] }}</div>
                <div>₹{{ number_format($stats['overdue_amount']) }} total</div>
                <div class="card-footer">
                    <span>Requires immediate attention</span>
                </div>
            </div>

        </div>

        <!-- Recent Activity Table -->
        <div class="table-container mt-4">
            <div class="table-header">
                <div class="table-title">Recent System Activity</div>
                <div class="table-actions">
                    <button class="btn btn-outline" data-bs-toggle="modal" data-bs-target="#activityFilterModal">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <a href="{{ route('admin.activity-logs.index') }}" class="btn btn-primary">
                        <i class="fas fa-history"></i> View All Logs
                    </a>
                </div>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Timestamp</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Resource</th>
                        <th>Details</th>
                        <th>IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentActivities as $activity)
                        <tr>
                            <td>
                                <div class="timestamp">
                                    <div class="date">{{ $activity['date'] }}</div>
                                    <div class="time">{{ $activity['time'] }}</div>
                                </div>
                            </td>
                            <td>
                                <div class="user-info">
                                    <div class="user-avatar">
                                        {{ substr($activity['user'], 0, 1) }}
                                    </div>
                                    <div class="user-details">
                                        <div class="user-name">{{ $activity['user'] }}</div>
                                        <div class="user-role">{{ $activity['role'] }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="action-badge action-{{ $activity['action_color'] }}">
                                    {{ $activity['action'] }}
                                </span>
                            </td>
                            <td>{{ $activity['resource'] }}</td>
                            <td>{{ $activity['details'] }}</td>
                            <td><code>{{ $activity['ip'] }}</code></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <i class="fas fa-history fa-2x text-muted"></i>
                                <p class="text-muted mt-2">No activity logs found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Company Performance & Top Users -->
        <div class="dashboard-grid mt-4">
            <div class="chart-container">
                <div class="chart-header">
                    <div class="chart-title">Company Performance</div>
                    <div class="chart-actions">
                        <select class="form-select form-select-sm" onchange="updateCompanyChart(this.value)">
                            <option value="income">Income</option>
                            <option value="expenses">Expenses</option>
                            <option value="balance">Net Balance</option>
                        </select>
                    </div>
                </div>
                <div class="chart">
                    <canvas id="companyPerformanceChart"></canvas>
                </div>
            </div>
            <div class="chart-container">
                <div class="chart-header">
                    <div class="chart-title">Top Active Users</div>
                    <div class="chart-subtitle">Last 7 Days</div>
                </div>
                <div class="chart">
                    <div class="top-users-list">
                        @forelse($topUsers as $index => $user)
                            <div class="top-user-item">
                                <div class="user-rank">{{ $index + 1 }}</div>
                                <div class="user-avatar">
                                    {{ substr($user['name'], 0, 1) }}
                                </div>
                                <div class="user-details">
                                    <div class="user-name">{{ $user['name'] }}</div>
                                    <div class="user-role">{{ $user['role'] }}</div>
                                    <div class="user-stats">
                                        <span class="action-count">{{ $user['count'] }} actions</span>
                                        <span class="last-active">{{ $user['last_active'] }}</span>
                                    </div>
                                </div>
                                <div class="user-progress">
                                    <div class="progress-bar" style="width: {{ $user['percentage'] }}%"></div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4">
                                <i class="fas fa-users fa-2x text-muted"></i>
                                <p class="text-muted mt-2">No user activity data</p>
                            </div>
                        @endforelse
                    </div>
                    <div class="total-actions-card">
                        <div class="card-value">{{ $stats['today_actions'] }}</div>
                        <div class="card-label">Total Actions Today</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Financial Overview Chart -->
        <div class="table-container mt-4">
            <div class="table-header">
                <div class="table-title">Financial Overview</div>
                <div class="table-actions">
                    <select class="form-select form-select-sm" onchange="updateFinancialChart(this.value)">
                        <option value="weekly" {{ request('range') == 'week' ? 'selected' : '' }}>This Week</option>
                        <option value="monthly" {{ request('range') == 'month' ? 'selected' : '' }}>This Month</option>
                        <option value="yearly" {{ request('range') == 'yearly' ? 'selected' : '' }}>This Year</option>
                    </select>
                </div>
            </div>
            <div class="chart-large">
                <canvas id="financialOverviewChart"></canvas>
            </div>
        </div>
    </div>



    <!-- Activity Filter Modal -->
    <div class="modal fade" id="activityFilterModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="GET" action="{{ route('admin.activity-logs.index') }}">
                    <div class="modal-header">
                        <h5 class="modal-title">Filter Activity Logs</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Action Type</label>
                            <select class="form-select" name="action">
                                <option value="">All Actions</option>
                                <option value="created">Created</option>
                                <option value="updated">Updated</option>
                                <option value="deleted">Deleted</option>
                                <option value="paid">Paid</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">User</label>
                            <select class="form-select" name="user_id">
                                <option value="">All Users</option>
                                @foreach ($topUsers as $user)
                                    <option value="{{ $user['id'] ?? '' }}">{{ $user['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Date From</label>
                                <input type="date" class="form-control" name="date_from">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Date To</label>
                                <input type="date" class="form-control" name="date_to">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Apply Filters</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Charts
            initializeFinancialChart();
            initializeCompanyChart();

            // Filter Functions
            window.applyFilters = function() {
                document.getElementById('dashboardFilter').submit();
            };

            window.resetFilters = function() {
                window.location.href = "{{ route('admin.dashboard') }}";
            };

            window.updateFinancialChart = function(range) {
                // Update chart data based on range
                console.log('Updating financial chart for:', range);
                // In a real app, you would make an AJAX call here
            };

            window.updateCompanyChart = function(type) {
                // Update company chart based on type
                console.log('Updating company chart for:', type);
                // In a real app, you would make an AJAX call here
            };
        });

        function initializeFinancialChart() {
            const ctx = document.getElementById('financialOverviewChart').getContext('2d');
            const chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: @json($financialData['labels']),
                    datasets: [{
                        label: 'Income',
                        data: @json($financialData['income']),
                        backgroundColor: 'rgba(40, 167, 69, 0.7)',
                        borderColor: 'rgba(40, 167, 69, 1)',
                        borderWidth: 1
                    }, {
                        label: 'Expenses',
                        data: @json($financialData['expenses']),
                        backgroundColor: 'rgba(220, 53, 69, 0.7)',
                        borderColor: 'rgba(220, 53, 69, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': ₹' + context.raw.toLocaleString();
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '₹' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        }

        function initializeCompanyChart() {
            const ctx = document.getElementById('companyPerformanceChart').getContext('2d');
            const companies = @json($companyPerformance);

            const chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: companies.map(c => c.name),
                    datasets: [{
                        label: 'Monthly Income',
                        data: companies.map(c => c.monthly_income),
                        backgroundColor: 'rgba(0, 123, 255, 0.7)',
                        borderColor: 'rgba(0, 123, 255, 1)',
                        borderWidth: 1
                    }, {
                        label: 'Monthly Expenses',
                        data: companies.map(c => c.monthly_expenses),
                        backgroundColor: 'rgba(220, 53, 69, 0.7)',
                        borderColor: 'rgba(220, 53, 69, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': ₹' + context.raw.toLocaleString();
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '₹' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        }
    </script>

    <style>
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .card-title {
            font-weight: 600;
            font-size: 14px;
            color: #6c757d;
        }

        .card-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }

        .card-icon.users {
            background: #e3f2fd;
            color: #1976d2;
        }

        .card-icon.settings {
            background: #fff3e0;
            color: #f57c00;
        }

        .card-icon.success {
            background: #e8f5e9;
            color: #388e3c;
        }

        .card-icon.warning {
            background: #fff3e0;
            color: #f57c00;
        }

        .card-icon.danger {
            background: #ffebee;
            color: #d32f2f;
        }

        .card-value {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 5px;
            color: #2c3e50;
        }

        .card-footer {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 12px;
            color: #6c757d;
        }

        .table-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .table-title {
            font-size: 18px;
            font-weight: 600;
            color: #2c3e50;
        }

        .table-actions {
            display: flex;
            gap: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead th {
            text-align: left;
            padding: 12px 15px;
            border-bottom: 2px solid #eee;
            color: #6c757d;
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
        }

        tbody td {
            padding: 15px;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }

        tbody tr:hover {
            background: #f8f9fa;
        }

        .timestamp {
            font-size: 13px;
        }

        .timestamp .date {
            color: #6c757d;
        }

        .timestamp .time {
            font-weight: 600;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #007bff;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        .user-name {
            font-weight: 600;
            font-size: 14px;
        }

        .user-role {
            font-size: 12px;
            color: #6c757d;
        }

        .action-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .action-success {
            background: #d4edda;
            color: #155724;
        }

        .action-info {
            background: #d1ecf1;
            color: #0c5460;
        }

        .action-warning {
            background: #fff3cd;
            color: #856404;
        }

        .action-primary {
            background: #cce5ff;
            color: #004085;
        }

        .chart-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .chart-title {
            font-size: 16px;
            font-weight: 600;
            color: #2c3e50;
        }

        .chart-subtitle {
            font-size: 12px;
            color: #6c757d;
        }

        .chart-large {
            height: 300px;
            margin-top: 20px;
        }

        .top-users-list {
            max-height: 250px;
            overflow-y: auto;
        }

        .top-user-item {
            display: flex;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }

        .user-rank {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            margin-right: 10px;
        }

        .user-progress {
            flex: 1;
            height: 6px;
            background: #f8f9fa;
            border-radius: 3px;
            overflow: hidden;
            margin-left: 10px;
        }

        .progress-bar {
            height: 100%;
            background: #007bff;
            border-radius: 3px;
        }

        .total-actions-card {
            margin-top: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            text-align: center;
        }

        .total-actions-card .card-value {
            font-size: 28px;
            margin-bottom: 5px;
        }

        .total-actions-card .card-label {
            font-size: 12px;
            color: #6c757d;
        }

        .system-health-stats {
            display: grid;
            gap: 15px;
        }

        .health-stat {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
        }

        .stat-label {
            font-size: 12px;
            color: #6c757d;
            margin-bottom: 5px;
        }

        .stat-value {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .stat-bar {
            height: 6px;
            background: #e9ecef;
            border-radius: 3px;
            overflow: hidden;
        }

        .bar-fill {
            height: 100%;
            background: #28a745;
            border-radius: 3px;
        }

        .check-item {
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }

        @media (max-width: 768px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }

            .table-header {
                flex-direction: column;
                gap: 10px;
                align-items: flex-start;
            }

            .table-actions {
                width: 100%;
            }
        }
    </style>
@endsection
