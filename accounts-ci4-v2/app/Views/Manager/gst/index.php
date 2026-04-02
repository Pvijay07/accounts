<?= $this->extend('layouts/manager') ?>

<?= $this->section('title') ?>GST & Taxes<?= $this->endSection() ?>
<?= $this->section('page_title') ?>GST Management<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Strategy Control Header -->
<div class="card shadow-sm mb-4 border-0" style="border-radius: 20px; background: rgba(255,255,255,0.7); backdrop-filter: blur(12px);">
    <div class="card-body p-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h5 class="mb-1 fw-extrabold text-dark">Tax Dashboard</h5>
            <p class="text-muted small mb-0">Overview of Output GST, Input Claimable, and Settlements.</p>
        </div>
        <div class="nav-pills-premium d-flex gap-2 bg-light p-1 rounded-pill border">
            <a class="nav-link-premium active shadow-sm" href="<?= base_url('manager/gst') ?>">Overview</a>
            <a class="nav-link-premium" href="<?= base_url('manager/gst-collected') ?>">Output GST</a>
            <a class="nav-link-premium" href="<?= base_url('manager/taxes') ?>">Input GST</a>
            <a class="nav-link-premium" href="<?= base_url('manager/settlement') ?>">Settlements</a>
        </div>
    </div>
</div>

<!-- High-Fidelity Statutory KPI Grid -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card h-100 border-0 shadow-sm premium-card">
            <div class="card-body p-4 text-center">
                <div class="icon-circle bg-soft-primary text-primary mx-auto mb-3"><i class="fas fa-calendar-check"></i></div>
                <div class="text-muted text-uppercase fw-bold small mb-1" style="letter-spacing:1px;">Filing Period</div>
                <h4 class="fw-extrabold text-dark mb-0"><?= date('F Y', strtotime($selectedPeriod??date('Y-m'))) ?></h4>
                <div class="text-muted x-small mt-2">Current Tax Month</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100 border-0 shadow-sm premium-card">
            <div class="card-body p-4 text-center">
                <div class="icon-circle bg-soft-success text-success mx-auto mb-3"><i class="fas fa-hand-holding-usd"></i></div>
                <div class="text-muted text-uppercase fw-bold small mb-1" style="letter-spacing:1px;">Output GST (Collected)</div>
                <h4 class="fw-extrabold text-success mb-0">₹<?= number_format($totalOutputGST??0, 2) ?></h4>
                <div class="text-muted x-small mt-2">Tax collected on income</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100 border-0 shadow-sm premium-card">
            <div class="card-body p-4 text-center">
                <div class="icon-circle bg-soft-danger text-danger mx-auto mb-3"><i class="fas fa-file-invoice"></i></div>
                <div class="text-muted text-uppercase fw-bold small mb-1" style="letter-spacing:1px;">Input GST (Paid)</div>
                <h4 class="fw-extrabold text-danger mb-0">₹<?= number_format($totalInputGST??0, 2) ?></h4>
                <div class="text-muted x-small mt-2">Tax paid on expenses (ITC)</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100 border-0 shadow-sm premium-card overflow-hidden">
            <div class="card-body p-4 text-center position-relative">
                <div class="icon-circle bg-soft-<?= ($isGSTPayable??false) ? 'warning' : 'success' ?> text-<?= ($isGSTPayable??false) ? 'warning' : 'success' ?> mx-auto mb-3"><i class="fas fa-balance-scale"></i></div>
                <div class="text-muted text-uppercase fw-bold small mb-1" style="letter-spacing:1px;">Net Transfer Liability</div>
                <h4 class="fw-extrabold mb-0 <?= ($isGSTPayable??false) ? 'text-warning' : 'text-success' ?>">
                    ₹<?= number_format(abs($netGSTPayable??0), 2) ?>
                </h4>
                <div class="badge rounded-pill mt-2 bg-soft-<?= ($isGSTPayable??false) ? 'warning' : 'success' ?> text-<?= ($isGSTPayable??false) ? 'warning' : 'success' ?> px-3">
                    <?= ($isGSTPayable??false) ? 'PAYABLE' : 'REFUND / CARRY-FORWARD' ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Strategy Filters Dashboard -->
<div class="card shadow-sm mb-4 border-0" style="border-radius: 20px;">
    <div class="card-body p-4">
        <form class="row g-3 align-items-end" method="GET" action="<?= base_url('manager/gst') ?>">
            <div class="col-md-4">
                <label class="form-label small fw-bold text-muted text-uppercase ms-2">Company</label>
                <select name="company_id" class="form-select border-0 bg-light rounded-pill px-4 shadow-none">
                    <option value="all">All Companies</option>
                    <?php if(!empty($companies)): foreach($companies as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= ($companyId??'') == $c['id'] ? 'selected' : '' ?>><?= esc($c['name']) ?></option>
                    <?php endforeach; endif; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold text-muted text-uppercase ms-2">Month</label>
                <input type="month" name="period" class="form-control border-0 bg-light rounded-pill px-4 shadow-none" value="<?= $selectedPeriod??date('Y-m') ?>">
            </div>
            <div class="col-md-4 d-grid">
                <button type="submit" class="btn btn-primary rounded-pill py-2 fw-bold shadow-sm"><i class="fas fa-filter me-2"></i>Apply Filters</button>
            </div>
        </form>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <!-- Structural Tax Ledger -->
        <div class="card shadow-sm h-100 border-0" style="border-radius: 20px;">
            <div class="card-header bg-white border-0 p-4 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold text-dark">Tax Summary Ledger</h6>
                <div class="badge bg-soft-primary text-primary rounded-pill px-3">Summary Data</div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light opacity-75">
                            <tr class="small text-muted text-uppercase" style="letter-spacing:1px;">
                                <th class="ps-4">Tax Type</th>
                                <th>Description</th>
                                <th class="text-end pe-4">Total Amount (₹)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="border-start border-4 border-success">
                                <td class="ps-4"><span class="badge bg-soft-success text-success rounded-pill px-3 py-2 fw-bold" style="font-size:0.65rem">OUTPUT GST</span></td>
                                <td class="small fw-medium">Output Tax collected from Incomes.</td>
                                <td class="text-end pe-4 fw-extrabold text-success">₹<?= number_format($totalOutputGST??0, 2) ?></td>
                            </tr>
                            <tr class="border-start border-4 border-danger">
                                <td class="ps-4"><span class="badge bg-soft-danger text-danger rounded-pill px-3 py-2 fw-bold" style="font-size:0.65rem">INPUT GST (ITC)</span></td>
                                <td class="small fw-medium">Input Tax paid on Expenses.</td>
                                <td class="text-end pe-4 fw-extrabold text-danger">₹<?= number_format($totalInputGST??0, 2) ?></td>
                            </tr>
                            <tr class="border-start border-4 border-primary">
                                <td class="ps-4"><span class="badge bg-soft-primary text-primary rounded-pill px-3 py-2 fw-bold" style="font-size:0.65rem">TDS</span></td>
                                <td class="small fw-medium">TDS deductions and adjustments.</td>
                                <td class="text-end pe-4 fw-extrabold text-primary">₹<?= number_format($totalTDS??0, 2) ?></td>
                            </tr>
                            <tr class="table-light">
                                <td class="ps-4 fw-bold">Net Payable / Refundable</td>
                                <td class="small fw-bold text-muted">Final status for the month</td>
                                <td class="text-end pe-4 fw-extrabold" style="font-size:1.3rem">₹<?= number_format(abs($netPosition??0), 2) ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Compliance Action Hub -->
        <div class="card shadow-sm border-0 mb-4 h-100" style="border-radius: 20px;">
            <div class="card-header bg-white border-0 p-4">
                <h6 class="mb-0 fw-bold text-dark">Actions</h6>
            </div>
            <div class="card-body p-4 pt-0">
                <div class="d-grid gap-3">
                    <button class="btn btn-outline-secondary rounded-pill py-3 text-start fw-bold small shadow-none"><i class="fas fa-file-excel text-success me-2"></i> Download Excel Report</button>
                    <button class="btn btn-outline-secondary rounded-pill py-3 text-start fw-bold small shadow-none"><i class="fas fa-file-pdf text-danger me-2"></i> Download PDF Summary</button>
                    <a href="<?= base_url('manager/settlement') ?>" class="btn btn-success rounded-pill py-3 fw-bold shadow-sm d-flex align-items-center justify-content-center">
                        <i class="fas fa-check-double me-2"></i> CREATE SETTLEMENT
                    </a>
                </div>

                <div class="mt-5 pt-4 border-top">
                    <h6 class="fw-bold small text-muted text-uppercase mb-3" style="letter-spacing:1px;">Summary</h6>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="x-small text-muted fw-medium">Last Data Refresh</span>
                        <span class="x-small fw-bold text-dark"><?= date('d M, h:i A') ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .premium-card { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); border: 1px solid rgba(0,0,0,0.01) !important; }
    .premium-card:hover { transform: translateY(-7px); box-shadow: 0 15px 45px -10px rgba(0,0,0,0.1) !important; }
    .fw-extrabold { font-weight: 800; letter-spacing: -1px; }
    .icon-circle { width: 48px; height: 48px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
    .bg-soft-primary { background: rgba(59, 130, 246, 0.1); }
    .bg-soft-success { background: rgba(16, 185, 129, 0.1); }
    .bg-soft-danger { background: rgba(239, 68, 68, 0.1); }
    .bg-soft-warning { background: rgba(245, 158, 11, 0.1); }
    .bg-soft-light { background: rgba(248, 250, 252, 0.8); }
    .x-small { font-size: 0.72rem; }
    .nav-link-premium { padding: 8px 20px; border-radius: 50px; text-decoration: none; font-size: 0.85rem; font-weight: 700; color: #64748b; transition: all 0.25s ease; }
    .nav-link-premium:hover { color: #2563eb; background: rgba(37, 99, 235, 0.05); }
    .nav-link-premium.active { background: #fff; color: #2563eb; }
</style>
<?= $this->endSection() ?>
