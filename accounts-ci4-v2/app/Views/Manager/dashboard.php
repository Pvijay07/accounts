<?= $this->extend('layouts/manager') ?>

<?= $this->section('title') ?>Dashboard<?= $this->endSection() ?>
<?= $this->section('page_title') ?>Manager Dashboard<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Filter Bar -->
<div class="filter-bar mb-4">
    <div class="row g-3 w-100 align-items-end">
        <div class="col-md-3">
            <div class="filter-group">
                <div class="filter-label">Date Range</div>
                <select id="dateRange" class="form-select border-0 shadow-none" onchange="updateFilters()">
                    <option value="today" <?= ($dateRange??'') == 'today' ? 'selected' : '' ?>>Today</option>
                    <option value="week" <?= ($dateRange??'week') == 'week' ? 'selected' : '' ?>>This Week</option>
                    <option value="month" <?= ($dateRange??'') == 'month' ? 'selected' : '' ?>>This Month</option>
                </select>
            </div>
        </div>
        <div class="col-md-3">
            <div class="filter-group">
                <div class="filter-label">Company</div>
                <select id="companyFilter" class="form-select border-0 shadow-none" onchange="updateFilters()">
                    <option value="">All Companies</option>
                    <?php foreach($companies as $company): ?>
                        <option value="<?= $company['id'] ?>" <?= ($companyId??'') == $company['id'] ? 'selected' : '' ?>><?= esc($company['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="col-md-6 text-end">
            <button class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm" onclick="window.location.reload()">
                <i class="fas fa-sync-alt me-2"></i> Refresh
            </button>
        </div>
    </div>
</div>

<!-- KPI Cards -->
<div class="dashboard-grid mb-5">
    <!-- Income -->
    <div class="card h-100 border-0 shadow-sm premium-card">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-start mb-4">
                <div>
                    <div class="card-title text-muted text-uppercase fw-bold small mb-2" style="letter-spacing:1px;">Total Income</div>
                    <div class="card-value fw-extrabold mb-0">₹<?= number_format($currentStats['totalIncome']??0, 2) ?></div>
                </div>
                <div class="card-icon income shadow-sm">
                    <i class="fas fa-arrow-up"></i>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 p-0 mt-3 d-flex justify-content-between align-items-center">
                <span class="text-muted small"><?= $currentStats['periodLabel'] ?? 'Current Period' ?></span>
                <?php 
                    $incomeChange = ($previousStats['totalIncome']??0) > 0 
                        ? ((($currentStats['totalIncome']??0) - $previousStats['totalIncome']) / $previousStats['totalIncome']) * 100 
                        : 0;
                ?>
                <span class="<?= $incomeChange >= 0 ? 'text-success' : 'text-danger' ?> fw-bold small">
                    <i class="fas fa-caret-<?= $incomeChange >= 0 ? 'up' : 'down' ?> me-1"></i>
                    <?= number_format(abs($incomeChange), 1) ?>% vs last period
                </span>
            </div>
        </div>
    </div>

    <!-- Expenses -->
    <div class="card h-100 border-0 shadow-sm premium-card">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-start mb-4">
                <div>
                    <div class="card-title text-muted text-uppercase fw-bold small mb-2" style="letter-spacing:1px;">Total Expenses</div>
                    <div class="card-value fw-extrabold mb-0">₹<?= number_format($currentStats['totalExpenses']??0, 2) ?></div>
                </div>
                <div class="card-icon expense shadow-sm">
                    <i class="fas fa-arrow-down"></i>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 p-0 mt-3 d-flex justify-content-between align-items-center">
                <span class="text-muted small"><?= $currentStats['periodLabel'] ?? 'Current Period' ?></span>
                <?php 
                    $expenseChange = ($previousStats['totalExpenses']??0) > 0 
                        ? ((($currentStats['totalExpenses']??0) - $previousStats['totalExpenses']) / $previousStats['totalExpenses']) * 100 
                        : 0;
                ?>
                <span class="<?= $expenseChange <= 0 ? 'text-success' : 'text-danger' ?> fw-bold small">
                    <i class="fas fa-caret-<?= $expenseChange >= 0 ? 'up' : 'down' ?> me-1"></i>
                    <?= number_format(abs($expenseChange), 1) ?>% change
                </span>
            </div>
        </div>
    </div>

    <!-- Net Profit -->
    <div class="card h-100 border-0 shadow-sm premium-card">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-start mb-4">
                <?php $netProfit = ($currentStats['totalIncome']??0) - ($currentStats['totalExpenses']??0); ?>
                <div>
                    <div class="card-title text-muted text-uppercase fw-bold small mb-2" style="letter-spacing:1px;">Net Profit</div>
                    <div class="card-value fw-extrabold mb-0 <?= $netProfit >= 0 ? 'text-success' : 'text-danger' ?>">
                        ₹<?= number_format(abs($netProfit), 2) ?>
                    </div>
                </div>
                <div class="card-icon profit shadow-sm">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 p-0 mt-3 d-flex justify-content-between align-items-center">
                <span class="text-muted small"><?= $currentStats['periodLabel'] ?? 'Current Period' ?></span>
                <?php 
                    $prevProfit = ($previousStats['totalIncome']??0) - ($previousStats['totalExpenses']??0);
                    $profitChange = $prevProfit != 0 
                        ? (($netProfit - $prevProfit) / abs($prevProfit)) * 100 
                        : ($netProfit > 0 ? 100 : -100);
                ?>
                <span class="<?= $profitChange >= 0 ? 'text-success' : 'text-danger' ?> fw-bold small">
                    <i class="fas fa-caret-<?= $profitChange >= 0 ? 'up' : 'down' ?> me-1"></i>
                    <?= number_format(abs($profitChange), 1) ?>% growth
                </span>
            </div>
        </div>
    </div>

    <!-- Upcoming Payments -->
    <div class="card h-100 border-0 shadow-sm premium-card">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-start mb-4">
                <div>
                    <div class="card-title text-muted text-uppercase fw-bold small mb-2" style="letter-spacing:1px;">Upcoming Payments</div>
                    <div class="card-value fw-extrabold mb-0 text-warning">₹<?= number_format($currentStats['upcomingPayments']??0, 2) ?></div>
                </div>
                <div class="card-icon upcoming shadow-sm">
                    <i class="fas fa-calendar-alt"></i>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 p-0 mt-3 d-flex justify-content-between align-items-center">
                <span class="text-muted small">Due soon</span>
                <span class="badge bg-soft-warning text-warning rounded-pill px-3 fw-bold"><?= $immediatePayments->count() ?? 0 ?> Pending</span>
            </div>
        </div>
    </div>
</div>

<!-- Tables Section -->
<div class="row g-4 mb-5">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm" style="border-radius: 20px;">
            <div class="card-header bg-transparent border-0 p-4 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold mb-1">Upcoming Payments</h5>
                    <p class="text-muted small mb-0">Payments due within the next few days</p>
                </div>
                <a href="<?= base_url('manager/expenses') ?>" class="btn btn-soft-primary rounded-pill px-3 fw-bold small shadow-none">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Due Date</th>
                                <th>Description</th>
                                <th class="text-end">Amount (₹)</th>
                                <th class="text-center">Status</th>
                                <th class="text-end pe-4">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($immediatePayments->count() > 0): foreach($immediatePayments as $payment): ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold"><?= date('d M', strtotime($payment['date'])) ?></div>
                                    <div class="text-muted small"><?= date('Y', strtotime($payment['date'])) ?></div>
                                </td>
                                <td>
                                    <div class="fw-bold"><?= esc($payment['name']) ?></div>
                                    <span class="badge bg-soft-info text-info rounded-pill small"><?= esc($payment['company']) ?></span>
                                </td>
                                <td class="text-end fw-bold text-primary">₹<?= number_format($payment['amount'], 2) ?></td>
                                <td class="text-center">
                                    <?php 
                                        $s = match($payment['status']) {
                                            'pending' => ['label' => 'Pending', 'color' => 'warning'],
                                            'upcoming' => ['label' => 'Upcoming', 'color' => 'info'],
                                            'overdue' => ['label' => 'Overdue', 'color' => 'danger'],
                                            default => ['label' => 'Paid', 'color' => 'success']
                                        };
                                    ?>
                                    <span class="badge bg-soft-<?= $s['color'] ?> text-<?= $s['color'] ?> rounded-pill px-3 py-2 fw-bold" style="font-size:0.7rem;"><?= strtoupper($s['label']) ?></span>
                                </td>
                                <td class="text-end pe-4">
                                    <button class="btn btn-sm btn-success rounded-pill px-3 fw-bold shadow-sm" onclick="markAsPaid(<?= $payment['id'] ?>)">
                                        <i class="fas fa-check-circle me-1"></i> Mark Paid
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; else: ?>
                                <tr><td colspan="5" class="text-center py-5 text-muted">
                                    <i class="fas fa-clipboard-check fa-2x mb-3 d-block"></i>
                                    No upcoming payments
                                </td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Company Performance Chart -->
        <div class="card border-0 shadow-sm h-100" style="border-radius: 20px;">
            <div class="card-header bg-transparent border-0 p-4">
                <h5 class="fw-bold mb-1">Company Performance</h5>
                <p class="text-muted small mb-0">Income vs Expenses by company</p>
            </div>
            <div class="card-body px-4 pb-4">
                <canvas id="yieldChart" style="min-height: 300px;"></canvas>
            </div>
            <div class="card-footer bg-light border-0 py-3 text-center" style="border-radius: 0 0 20px 20px;">
                <div class="row g-2">
                    <div class="col-6 border-end">
                        <div class="text-muted small text-uppercase fw-bold">Expenses Due</div>
                        <div class="fw-bold text-danger">₹<?= number_format($upcomingStats['debits']['amount']??0, 0) ?></div>
                    </div>
                    <div class="col-6">
                        <div class="text-muted small text-uppercase fw-bold">Income Expected</div>
                        <div class="fw-bold text-success">₹<?= number_format($upcomingStats['credits']['amount']??0, 0) ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .premium-card { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); border: 1px solid rgba(0,0,0,0.01) !important; border-radius: 20px; }
    .premium-card:hover { transform: translateY(-5px); box-shadow: 0 15px 45px -10px rgba(0,0,0,0.1) !important; }
    .fw-extrabold { font-weight: 800; letter-spacing: -1px; }
    .card-icon { width: 48px; height: 48px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; color: white; }
    .card-icon.income { background: linear-gradient(135deg, #10b981, #059669); }
    .card-icon.expense { background: linear-gradient(135deg, #ef4444, #dc2626); }
    .card-icon.profit { background: linear-gradient(135deg, #6366f1, #4f46e5); }
    .card-icon.upcoming { background: linear-gradient(135deg, #f59e0b, #d97706); }
    .bg-soft-primary { background: rgba(79, 70, 229, 0.1); }
    .bg-soft-success { background: rgba(16, 185, 129, 0.1); }
    .bg-soft-danger { background: rgba(239, 68, 68, 0.1); }
    .bg-soft-warning { background: rgba(245, 158, 11, 0.1); }
    .bg-soft-info { background: rgba(59, 130, 246, 0.1); }
    .btn-soft-primary { background: rgba(79, 70, 229, 0.08); color: #4f46e5; }
    .dashboard-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; }
    .filter-bar { background: rgba(255,255,255,0.7); backdrop-filter: blur(12px); border-radius: 20px; padding: 24px; box-shadow: 0 10px 30px rgba(0,0,0,0.02); border: 1px solid rgba(255,255,255,0.5); }
    .filter-label { font-size: 0.75rem; font-weight: 800; color: #64748b; text-transform: uppercase; margin-bottom: 8px; letter-spacing: 0.5px; }
</style>

<script>
function updateFilters() {
    const range = document.getElementById('dateRange').value;
    const company = document.getElementById('companyFilter').value;
    window.location.href = `<?= base_url('manager/dashboard') ?>?range=${range}&company=${company}`;
}

document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('yieldChart').getContext('2d');
    const companyData = <?= json_encode($companyProfitLoss??[]) ?>;
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: companyData.map(c => c.name),
            datasets: [{
                label: 'Income',
                data: companyData.map(c => c.income),
                backgroundColor: 'rgba(16, 185, 129, 0.8)',
                borderRadius: 8
            }, {
                label: 'Expenses',
                data: companyData.map(c => c.expenses),
                backgroundColor: 'rgba(239, 68, 68, 0.8)',
                borderRadius: 8
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { grid: { borderDash: [5, 5], color: '#f1f5f9' }, ticks: { font: { size: 10 }, callback: v => '₹' + v.toLocaleString() } },
                x: { grid: { display: false }, ticks: { font: { size: 10, weight: 'bold' } } }
            }
        }
    });
});

function markAsPaid(id) {
    Swal.fire({
        title: 'Mark as Paid?',
        text: 'This will mark the payment as completed.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, mark as paid',
        confirmButtonColor: '#10b981'
    }).then(res => {
        if(res.isConfirmed) {
            $.post(`<?= base_url('manager/expenses/') ?>/${id}/mark-paid`, {csrf_test_name: '<?= csrf_hash() ?>'}, (data) => {
                if(data.success) Swal.fire({icon: 'success', title: 'Paid!', text: 'Payment marked as completed.', timer: 1500, showConfirmButton: false}).then(() => location.reload());
            });
        }
    });
}
</script>
<?= $this->endSection() ?>
