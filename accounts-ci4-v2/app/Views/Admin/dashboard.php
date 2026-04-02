<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Dashboard<?= $this->endSection() ?>
<?= $this->section('page_title') ?>Admin Panel<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div id="dashboard" class="page active">
    <!-- Filter Bar -->
    <div class="filter-bar">
        <form method="GET" action="<?= base_url('admin/dashboard') ?>" id="dashboardFilter">
            <div class="filter-group">
                <div class="filter-label">Date Range</div>
                <select name="range" onchange="applyFilters()">
                    <option value="today" <?= ($dateRange??'') == 'today' ? 'selected' : '' ?>>Today</option>
                    <option value="week" <?= ($dateRange??'week') == 'week' ? 'selected' : '' ?>>This Week</option>
                    <option value="month" <?= ($dateRange??'') == 'month' ? 'selected' : '' ?>>This Month</option>
                    <option value="custom" <?= ($dateRange??'') == 'custom' ? 'selected' : '' ?>>Custom Range</option>
                </select>
            </div>
            <div class="filter-group">
                <div class="filter-label">Company</div>
                <select name="company" onchange="applyFilters()">
                    <option value="">All Companies</option>
                    <?php foreach ($companies as $company): ?>
                    <option value="<?= $company['id'] ?>" <?= ($companyId??'') == $company['id'] ? 'selected' : '' ?>>
                        <?= esc($company['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-group" style="flex-grow: 1;"></div>
            <div class="filter-group" style="align-self: flex-end;">
                <button type="button" class="btn btn-outline" onclick="resetFilters()">
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
            <div class="card-value"><?= $stats['total_companies'] ?></div>
            <div>Active companies in system</div>
            <div class="card-footer">
                <span><?= $stats['active_companies'] ?> active, <?= $stats['pending_companies'] ?> pending</span>
                <a href="<?= base_url('admin/companies') ?>" class="btn btn-outline"
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
            <div class="card-value"><?= $stats['total_users'] ?></div>
            <div>Active users in system</div>
            <div class="card-footer">
                <span><?= $stats['admin_users'] ?> admin, <?= $stats['manager_users'] ?> managers,
                    <?= $stats['regular_users'] ?> users</span>
                <a href="<?= base_url('admin/users') ?>" class="btn btn-outline"
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
            <div class="card-value"><?= $stats['expense_types'] ?></div>
            <div>Active expense categories</div>
            <div class="card-footer">
                <span><?= $stats['active_expense_types'] ?> active</span>
                <a href="<?= base_url('admin/expense-types') ?>" class="btn btn-outline"
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
            <div class="card-value"><?= $stats['total_transactions'] ?></div>
            <div>₹<?= number_format($stats['total_amount'] ?? 0) ?> total amount</div>
            <div class="card-footer">
                <span><?= $stats['income_count'] ?> income, <?= $stats['expense_count'] ?> expense</span>
            </div>
        </div>
        <div class="card">
            <div class="card-header">
                <div class="card-title">Pending Items</div>
                <div class="card-icon warning">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
            <div class="card-value"><?= $stats['pending_items'] ?></div>
            <div>Items requiring attention</div>
            <div class="card-footer">
                <span><?= $stats['pending_invoices'] ?> invoices, <?= $stats['pending_expenses'] ?> expenses</span>
            </div>
        </div>
        <div class="card">
            <div class="card-header">
                <div class="card-title">Overdue Payments</div>
                <div class="card-icon danger">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
            </div>
            <div class="card-value"><?= $stats['overdue_payments'] ?></div>
            <div>₹<?= number_format($stats['overdue_amount'] ?? 0) ?> total</div>
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
                <a href="<?= base_url('admin/activity-logs') ?>" class="btn btn-primary">
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
                <?php if (!empty($recent_logs)): foreach($recent_logs as $activity): ?>
                <tr>
                    <td>
                        <div class="timestamp">
                            <div class="date"><?= date('d M Y', strtotime($activity['created_at'])) ?></div>
                            <div class="time"><?= date('h:i A', strtotime($activity['created_at'])) ?></div>
                        </div>
                    </td>
                    <td>
                        <div class="user-info-cell">
                            <div class="user-avatar-sm">
                                <?= substr($activity['user_name'] ?? 'S', 0, 1) ?>
                            </div>
                            <div class="user-details">
                                <div class="user-name"><?= esc($activity['user_name'] ?? 'System') ?></div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="action-badge action-<?= $activity['action'] === 'deleted' ? 'warning' : ($activity['action'] === 'created' ? 'success' : 'info') ?>">
                            <?= ucfirst($activity['action']) ?>
                        </span>
                    </td>
                    <td><?= esc($activity['module'] ?? '') ?></td>
                    <td><?= esc($activity['details'] ?? '') ?></td>
                    <td><code><?= esc($activity['ip_address'] ?? '127.0.0.1') ?></code></td>
                </tr>
                <?php endforeach; else: ?>
                <tr>
                    <td colspan="6" class="text-center py-4">
                        <i class="fas fa-history fa-2x text-muted"></i>
                        <p class="text-muted mt-2">No activity logs found</p>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Company Performance & Financial Overview -->
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
                    <?php if (!empty($topUsers)): foreach($topUsers as $index => $user): ?>
                    <div class="top-user-item">
                        <div class="user-rank"><?= $index + 1 ?></div>
                        <div class="user-avatar-sm">
                            <?= substr($user['name'], 0, 1) ?>
                        </div>
                        <div class="user-details">
                            <div class="user-name"><?= esc($user['name']) ?></div>
                            <div class="user-role"><?= $user['role'] ?? 'User' ?></div>
                            <div class="user-stats">
                                <span class="action-count"><?= $user['count'] ?? 0 ?> actions</span>
                                <span class="last-active"><?= $user['last_active'] ?? '' ?></span>
                            </div>
                        </div>
                        <div class="user-progress">
                            <div class="progress-bar" style="width: <?= $user['percentage'] ?? 0 ?>%"></div>
                        </div>
                    </div>
                    <?php endforeach; else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-users fa-2x text-muted"></i>
                        <p class="text-muted mt-2">No user activity data</p>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="total-actions-card">
                    <div class="card-value"><?= $stats['today_actions'] ?? 0 ?></div>
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
                    <option value="weekly">This Week</option>
                    <option value="monthly">This Month</option>
                    <option value="yearly">This Year</option>
                </select>
            </div>
        </div>
        <div class="chart-large">
            <canvas id="financialOverviewChart"></canvas>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        initializeFinancialChart();
        initializeCompanyChart();

        window.applyFilters = function() {
            document.getElementById('dashboardFilter').submit();
        };

        window.resetFilters = function() {
            window.location.href = "<?= base_url('admin/dashboard') ?>";
        };

        window.updateFinancialChart = function(range) {
            console.log('Updating financial chart for:', range);
        };

        window.updateCompanyChart = function(type) {
            console.log('Updating company chart for:', type);
        };
    });

    function initializeFinancialChart() {
        const ctx = document.getElementById('financialOverviewChart').getContext('2d');
        const chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($financialData['labels']) ?>,
                datasets: [{
                    label: 'Income',
                    data: <?= json_encode($financialData['income']) ?>,
                    backgroundColor: 'rgba(40, 167, 69, 0.7)',
                    borderColor: 'rgba(40, 167, 69, 1)',
                    borderWidth: 1
                }, {
                    label: 'Expenses',
                    data: <?= json_encode($financialData['expenses']) ?>,
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
        const companies = <?= json_encode($companyPerformance ?? []) ?>;

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
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 24px;
        margin-bottom: 24px;
    }

    .card {
        background: #ffffff;
        border-radius: 20px;
        padding: 24px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.04);
        border: 1px solid rgba(226, 232, 240, 0.8);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 50px rgba(0, 0, 0, 0.08);
        border-color: #cbd5e1;
    }

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding: 0 !important;
        border: none !important;
    }

    .card-title {
        font-weight: 700;
        font-size: 15px;
        color: #475569;
        letter-spacing: 0.3px;
    }

    .card-icon {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        background: #f1f5f9;
        color: #64748b;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
    }

    .card:hover .card-icon {
        transform: scale(1.1);
    }

    .card-icon.users {
        background: linear-gradient(135deg, #e0f2fe, #bae6fd);
        color: #0284c7;
    }

    .card-icon.settings {
        background: linear-gradient(135deg, #ffedd5, #fed7aa);
        color: #ea580c;
    }

    .card-icon.success {
        background: linear-gradient(135deg, #dcfce7, #bbf7d0);
        color: #16a34a;
    }

    .card-icon.warning {
        background: linear-gradient(135deg, #fef9c3, #fef08a);
        color: #ca8a04;
    }

    .card-icon.danger {
        background: linear-gradient(135deg, #fee2e2, #fecaca);
        color: #dc2626;
    }

    .card-value {
        font-size: 36px;
        font-weight: 800;
        margin-bottom: 8px;
        color: #1e293b;
        letter-spacing: -1px;
    }

    .card-footer {
        margin-top: auto;
        padding-top: 20px;
        border-top: 1px solid #f1f5f9;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 13px;
        color: #64748b;
        font-weight: 500;
    }

    /* Filter Bar inside Dashboard */
    .filter-bar {
        background: white;
        padding: 20px 24px;
        border-radius: 16px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.04);
        border: 1px solid rgba(226, 232, 240, 0.8);
        margin-bottom: 30px;
    }

    #dashboardFilter {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        align-items: flex-end;
    }

    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
        min-width: 200px;
    }

    .filter-label {
        font-weight: 600;
        color: #475569;
        font-size: 0.9rem;
    }

    .filter-group select.form-select,
    .filter-group select {
        border-radius: 12px;
        border: 2px solid #e2e8f0;
        padding: 10px 14px;
        background-color: #f8fafc;
        color: #1e293b;
        font-weight: 500;
        transition: all 0.2s;
        width: 100%;
    }

    .filter-group select:focus {
        background-color: white;
        border-color: #3b82f6;
        outline: none;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15);
    }

    .timestamp {
        font-size: 13px;
    }

    .timestamp .date {
        color: #64748b;
    }

    .timestamp .time {
        font-weight: 600;
        color: #334155;
    }

    .user-info-cell {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .user-avatar-sm {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        background: linear-gradient(135deg, #3b82f6, #8b5cf6);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.85rem;
        box-shadow: 0 4px 10px rgba(59, 130, 246, 0.3);
    }

    .user-name {
        font-weight: 700;
        font-size: 14px;
        color: #1e293b;
    }

    .user-role {
        font-size: 12px;
        color: #64748b;
        font-weight: 500;
    }

    .action-badge {
        padding: 6px 12px;
        border-radius: 30px;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .action-success {
        background: #dcfce7;
        color: #16a34a;
    }

    .action-info {
        background: #e0f2fe;
        color: #0284c7;
    }

    .action-warning {
        background: #fef9c3;
        color: #ca8a04;
    }

    .action-primary {
        background: #dbeafe;
        color: #1d4ed8;
    }

    .chart-container {
        background: white;
        border-radius: 20px;
        padding: 28px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.04);
        border: 1px solid rgba(226, 232, 240, 0.8);
    }

    .chart-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
    }

    .chart-title {
        font-size: 1.15rem;
        font-weight: 700;
        color: #1e293b;
    }

    .chart-subtitle {
        font-size: 13px;
        color: #64748b;
        font-weight: 500;
    }

    .chart-large {
        height: 350px;
        margin-top: 24px;
    }

    .top-users-list {
        max-height: 280px;
        overflow-y: auto;
        padding-right: 10px;
    }

    .top-users-list::-webkit-scrollbar {
        width: 4px;
    }

    .top-user-item {
        display: flex;
        align-items: center;
        padding: 16px 0;
        border-bottom: 1px solid #f1f5f9;
        transition: background 0.2s;
        border-radius: 12px;
    }

    .top-user-item:hover {
        background-color: #f8fafc;
    }

    .user-rank {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: #f1f5f9;
        color: #475569;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        margin-right: 12px;
        margin-left: 8px;
    }

    .user-progress {
        flex: 1;
        height: 8px;
        background: #f1f5f9;
        border-radius: 4px;
        overflow: hidden;
        margin-left: 16px;
        margin-right: 8px;
    }

    .progress-bar {
        height: 100%;
        background: linear-gradient(90deg, #3b82f6, #60a5fa);
        border-radius: 4px;
    }

    .total-actions-card {
        margin-top: 24px;
        padding: 20px;
        background: #f8fafc;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        text-align: center;
    }

    .total-actions-card .card-value {
        font-size: 32px;
        margin-bottom: 4px;
        color: #3b82f6;
    }

    .total-actions-card .card-label {
        font-size: 13px;
        color: #64748b;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    @media (max-width: 768px) {
        .dashboard-grid {
            grid-template-columns: 1fr;
        }

        .table-header {
            flex-direction: column;
            gap: 16px;
            align-items: flex-start;
        }

        .table-actions {
            width: 100%;
            justify-content: space-between;
        }

        #dashboardFilter {
            flex-direction: column;
            align-items: stretch;
        }
    }
</style>
<?= $this->endSection() ?>
