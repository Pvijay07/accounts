<?= $this->extend('layouts/manager') ?>
<?= $this->section('title') ?>Output GST Collected<?= $this->endSection() ?>
<?= $this->section('page_title') ?>Output GST Collected<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Header -->
<div class="card shadow-sm mb-4 border-0" style="border-radius: 16px; background: rgba(255,255,255,0.7); backdrop-filter: blur(10px);">
    <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h5 class="mb-1 fw-bold text-dark">Output GST Collected</h5>
            <p class="text-muted small mb-0">Breakdown of GST collected from income.</p>
        </div>
        <div class="d-flex gap-2">
            <a class="btn btn-sm btn-outline-primary rounded-pill px-3" href="<?= base_url('manager/gst') ?>">
                <i class="fas fa-th-large me-1"></i> Tax Dashboard
            </a>
            <button class="btn btn-sm btn-primary rounded-pill px-3">
                <i class="fas fa-file-export me-1"></i> Export Excel
            </button>
        </div>
    </div>
</div>

<!-- KPI Section -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="stat-card p-4">
            <div class="d-flex justify-content-between mb-3 text-primary bg-primary-light p-2 rounded-3 w-mc">
                <i class="fas fa-calendar-check-o fa-lg"></i>
            </div>
            <p class="text-muted small fw-bold text-uppercase mb-1">Period</p>
            <h4 class="fw-bold mb-0"><?= date('F Y', strtotime($selectedPeriod??date('Y-m'))) ?></h4>
            <div class="badge bg-soft-primary text-primary mt-2">Active</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card p-4">
            <div class="d-flex justify-content-between mb-3 text-success bg-success-light p-2 rounded-3 w-mc">
                <i class="fas fa-hand-holding-usd fa-lg"></i>
            </div>
            <p class="text-muted small fw-bold text-uppercase mb-1">GST Collected</p>
            <h4 class="fw-bold text-success mb-0">₹<?= number_format($totalGSTCollected??0, 2) ?></h4>
            <div class="progress mt-2" style="height: 4px;">
                <div class="progress-bar bg-success" style="width: 75%"></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card p-4">
            <div class="d-flex justify-content-between mb-3 text-info bg-info-light p-2 rounded-3 w-mc">
                <i class="fas fa-stamp fa-lg"></i>
            </div>
            <p class="text-muted small fw-bold text-uppercase mb-1">TDS Withheld</p>
            <h4 class="fw-bold text-info mb-0">₹<?= number_format($totalTDSCollected??0, 2) ?></h4>
            <div class="progress mt-2" style="height: 4px;">
                <div class="progress-bar bg-info" style="width: 45%"></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card p-4">
            <div class="d-flex justify-content-between mb-3 text-dark bg-light p-2 rounded-3 w-mc">
                <i class="fas fa-chart-line fa-lg"></i>
            </div>
            <p class="text-muted small fw-bold text-uppercase mb-1">Base Income</p>
            <h4 class="fw-bold mb-0">₹<?= number_format($totalTaxableAmount??0, 2) ?></h4>
            <small class="text-muted mt-2 d-block">Without Tax</small>
        </div>
    </div>
</div>

<!-- Filter & Table Grid -->
<div class="row g-4">
    <div class="col-lg-3">
        <div class="card shadow-sm border-0 sticky-top" style="border-radius: 16px; top: 90px;">
            <div class="card-header bg-white border-0 py-3"><h6 class="mb-0 fw-bold">Filters</h6></div>
            <div class="card-body">
                <form method="GET">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Company</label>
                        <select name="company_id" class="form-select border-0 bg-light rounded-3 shadow-none">
                            <option value="all">All Companies</option>
                            <?php if(!empty($companies)): foreach($companies as $c): ?>
                                <option value="<?= $c['id'] ?>" <?= ($companyId??'') == $c['id'] ? 'selected' : '' ?>><?= esc($c['name']) ?></option>
                            <?php endforeach; endif; ?>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-muted">Month</label>
                        <input type="month" name="period" class="form-control border-0 bg-light rounded-3 shadow-none" value="<?= $selectedPeriod??date('Y-m') ?>">
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary rounded-3 py-2 fw-bold">Update View</button>
                        <a href="?" class="btn btn-link text-muted small mt-2" style="text-decoration:none">Clear Filters</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-9">
        <div class="card shadow-sm border-0 mb-5" style="border-radius: 16px;">
            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold">Income Tax Journal</h6>
                <div class="dropdown">
                    <button class="btn btn-sm btn-light border-0 rounded-circle" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-v"></i></button>
                    <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg" style="border-radius: 12px;">
                        <li><a class="dropdown-item" href="#"><i class="fas fa-file-excel me-2 text-success"></i> Download Excel</a></li>
                        <li><a class="dropdown-item" href="#"><i class="fas fa-file-pdf me-2 text-danger"></i> Download PDF</a></li>
                    </ul>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Date</th>
                                <th>Company</th>
                                <th>Details</th>
                                <th class="text-end">Base Amount (₹)</th>
                                <th class="text-end pe-4">GST (₹)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($incomes)): ?>
                                <tr><td colspan="5" class="text-center py-5 text-muted opacity-50"><i class="fas fa-inbox fa-3x mb-3 d-block"></i>No entries found for this period.</td></tr>
                            <?php else: foreach($incomes as $income): 
                                $gstAmount = 0;
                                foreach($income['taxes'] as $tax) {
                                    if($tax['tax_type'] === 'gst') $gstAmount += $tax['tax_amount'];
                                }
                            ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold small"><?= date('d M, Y', strtotime($income['income_date'])) ?></div>
                                    </td>
                                    <td><span class="badge bg-soft-primary text-primary rounded-pill"><?= esc($income['company_name']) ?></span></td>
                                    <td>
                                        <div class="small fw-medium"><?= esc($income['description'] ?: ($income['client_name'] ?? 'Income Record')) ?></div>
                                        <div class="text-muted" style="font-size: 0.7rem;">Ref: INC-<?= $income['id'] ?></div>
                                    </td>
                                    <td class="text-end">₹<?= number_format($income['amount'], 2) ?></td>
                                    <td class="text-end pe-4 fw-bold text-success">₹<?= number_format($gstAmount, 2) ?></td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .stat-card { background: white; border-radius: 20px; border: 1px solid rgba(0,0,0,0.02); transition: all 0.3s ease; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
    .stat-card:hover { transform: translateY(-5px); box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); }
    .bg-primary-light { background: rgba(59, 130, 246, 0.1); }
    .bg-success-light { background: rgba(34, 197, 94, 0.1); }
    .bg-info-light { background: rgba(6, 182, 212, 0.1); }
    .w-mc { width: fit-content; }
    .bg-soft-primary { background: rgba(59, 130, 246, 0.1); }
</style>
<?= $this->endSection() ?>
