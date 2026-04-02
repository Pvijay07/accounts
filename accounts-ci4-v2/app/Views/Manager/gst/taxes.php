<?= $this->extend('layouts/manager') ?>
<?= $this->section('title') ?>Taxes on Expenses<?= $this->endSection() ?>
<?= $this->section('page_title') ?>Taxes Paid on Expenses (Input Tax Credit)<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card shadow-sm mb-4">
    <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h5 class="mb-0 fw-bold">Taxes Paid on Expenses</h5>
            <div class="text-muted small">Track GST and TDS paid on your business expenses for Input Tax Credit.</div>
        </div>
        <div class="d-flex gap-2">
            <a class="btn btn-sm btn-outline-primary" href="<?= base_url('manager/gst') ?>">Dashboard</a>
            <a class="btn btn-sm btn-outline-primary" href="<?= base_url('manager/gst-collected') ?>">GST Collected</a>
            <a class="btn btn-sm btn-primary" href="<?= base_url('manager/taxes') ?>">Taxes on Expenses</a>
            <a class="btn btn-sm btn-outline-primary" href="<?= base_url('manager/settlement') ?>">Settlements</a>
            <a class="btn btn-sm btn-outline-primary" href="<?= base_url('manager/returns') ?>">Returns & Tasks</a>
        </div>
    </div>
</div>

<!-- KPI Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="stat-card">
            <p class="text-muted mb-1 small">Tax Period</p>
            <h4 class="fw-bold"><?= date('F Y', strtotime($selectedPeriod??date('Y-m'))) ?></h4>
            <small class="text-muted">Currently Viewing</small>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <p class="text-muted mb-1 small">Total Taxes Paid</p>
            <h4 class="fw-bold text-primary">₹<?= number_format($totalTaxPaid??0, 2) ?></h4>
            <small class="text-muted">GST/TDS from Expenses</small>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <p class="text-muted mb-1 small">Bills with Tax</p>
            <h4 class="fw-bold text-success"><?= $billsWithTax??0 ?></h4>
            <small class="text-muted">Total Expense Records</small>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <h6 class="mb-3 fw-bold">Filter Tax Entries</h6>
        <form class="row g-3 align-items-end" method="GET">
            <div class="col-md-3">
                <label class="form-label small">Company</label>
                <select name="company_id" class="form-select form-select-sm">
                    <option value="all">All Companies</option>
                    <?php if(!empty($companies)): foreach($companies as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= ($companyId??'') == $c['id'] ? 'selected' : '' ?>><?= esc($c['name']) ?></option>
                    <?php endforeach; endif; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small">Tax Period</label>
                <input type="month" name="period" class="form-control form-control-sm" value="<?= $selectedPeriod??date('Y-m') ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small">Tax Type</label>
                <select name="tax_type" class="form-select form-select-sm">
                    <option value="all" <?= ($selectedTaxType??'all') == 'all' ? 'selected' : '' ?>>All Taxes</option>
                    <option value="gst" <?= ($selectedTaxType??'all') == 'gst' ? 'selected' : '' ?>>GST Only</option>
                    <option value="tds" <?= ($selectedTaxType??'all') == 'tds' ? 'selected' : '' ?>>TDS Only</option>
                </select>
            </div>
            <div class="col-md-3 d-grid">
                <button type="submit" class="btn btn-sm btn-primary">Apply Filters</button>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold">Detailed Tax Payments from Expense Bills</h6>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary btn-sm"><i class="fas fa-file-excel me-1"></i>Excel</button>
            <button class="btn btn-outline-secondary btn-sm"><i class="fas fa-file-pdf me-1"></i>PDF</button>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Date</th>
                        <th>Company</th>
                        <th>Expense/Party</th>
                        <th>Type</th>
                        <th class="text-end">Expense (₹)</th>
                        <th class="text-end">Tax %</th>
                        <th class="text-end pe-4">Tax Amt (₹)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($expenseTaxes)): ?>
                        <tr><td colspan="7" class="text-center py-5 text-muted">No tax entries found for this period.</td></tr>
                    <?php else: foreach($expenseTaxes as $tax): ?>
                        <tr>
                            <td class="ps-4 text-muted"><?= date('d-m-Y', strtotime($tax['created_at'])) ?></td>
                            <td><?= esc($tax['company_name']) ?></td>
                            <td>
                                <div class="fw-medium text-dark"><?= esc($tax['expense_name'] ?? 'Expense Record') ?></div>
                                <div class="small text-muted"><?= esc($tax['party_name'] ?: ($tax['vendor_name'] ?? 'N/A')) ?></div>
                            </td>
                            <td>
                                <?php 
                                    $badge = 'bg-dark';
                                    if($tax['tax_type'] == 'gst') $badge = 'bg-primary';
                                    elseif($tax['tax_type'] == 'tds') $badge = 'bg-secondary';
                                ?>
                                <span class="badge <?= $badge ?>"><?= strtoupper($tax['tax_type']) ?></span>
                            </td>
                            <td class="text-end text-muted">₹<?= number_format($tax['actual_amount'], 2) ?></td>
                            <td class="text-end"><?= $tax['tax_percentage'] ?>%</td>
                            <td class="text-end pe-4 fw-bold">₹<?= number_format($tax['tax_amount'], 2) ?></td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
                <?php if(!empty($expenseTaxes)): ?>
                    <tfoot class="bg-light fw-bold">
                        <tr>
                            <td colspan="4" class="text-end- pe-4">Totals</td>
                            <td class="text-end">₹<?= number_format(array_sum(array_column($expenseTaxes, 'actual_amount')), 2) ?></td>
                            <td></td>
                            <td class="text-end pe-4 text-primary">₹<?= number_format($totalTaxPaid??0, 2) ?></td>
                        </tr>
                    </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>
</div>

<style>
    .stat-card { background: white; padding: 1.25rem; border-radius: 0.75rem; box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075); border: 1px solid rgba(0,0,0,0.05); }
</style>
<?= $this->endSection() ?>
