<?= $this->extend('layouts/manager') ?>
<?= $this->section('title') ?>GST Settlements<?= $this->endSection() ?>
<?= $this->section('page_title') ?>GST Settlements<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Header -->
<div class="card shadow-sm mb-4 border-0" style="border-radius: 16px; background: rgba(255,255,255,0.7); backdrop-filter: blur(10px);">
    <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h5 class="mb-1 fw-bold text-dark">GST Settlements</h5>
            <p class="text-muted small mb-0">Record and monitor all GST payments made to the government.</p>
        </div>
        <div class="d-flex gap-2">
            <a class="btn btn-sm btn-outline-primary rounded-pill px-3" href="<?= base_url('manager/gst') ?>">
                <i class="fas fa-th-large me-1"></i> Dashboard
            </a>
            <button class="btn btn-sm btn-primary rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#addSettlementModal">
                <i class="fas fa-plus-circle me-1"></i> Record Settlement
            </button>
        </div>
    </div>
</div>

<!-- Settlement Grid -->
<div class="card shadow-sm border-0 mb-5" style="border-radius: 16px;">
    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold">Settlement History</h6>
        <div class="dropdown">
            <button class="btn btn-sm btn-light border-0 rounded-circle" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-v"></i></button>
            <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg" style="border-radius: 12px;">
                <li><a class="dropdown-item" href="#"><i class="fas fa-file-excel me-2 text-success"></i> Export Excel</a></li>
                <li><a class="dropdown-item" href="#"><i class="fas fa-print me-2 text-primary"></i> Print Log</a></li>
            </ul>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Period</th>
                        <th>Company</th>
                        <th class="text-end">Amount (₹)</th>
                        <th>Payment Date</th>
                        <th>Payment Mode</th>
                        <th>Reference Data</th>
                        <th class="text-end pe-4">Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php if(empty($settlements)): ?>
                    <tr><td colspan="7" class="text-center py-5 text-muted opacity-50"><i class="fas fa-receipt fa-3x mb-3 d-block"></i>No settlements recorded for the current view.</td></tr>
                <?php else: foreach($settlements as $s): ?>
                    <tr>
                        <td class="ps-4">
                            <div class="fw-bold small text-dark"><?= date('M Y', strtotime($s['tax_period'] . '-01')) ?></div>
                        </td>
                        <td><span class="badge bg-soft-primary text-primary rounded-pill fw-bold" style="font-size: 0.7rem;"><?= esc($s['company_name']) ?></span></td>
                        <td class="text-end fw-bold text-dark">₹<?= number_format($s['amount'], 2) ?></td>
                        <td>
                            <div class="small fw-medium"><?= date('d M, Y', strtotime($s['payment_date'])) ?></div>
                        </td>
                        <td><span class="badge bg-light text-muted border px-2 py-1"><?= ucfirst(str_replace('_', ' ', $s['payment_mode'])) ?></span></td>
                        <td>
                            <div class="small" style="line-height: 1.3;">
                                <span class="text-muted d-block" style="font-size: 0.7rem;">Challan: <span class="text-dark fw-medium"><?= $s['challan_number'] ?: 'PENDING' ?></span></span>
                                <span class="text-muted d-block" style="font-size: 0.7rem;">UTR: <span class="text-dark fw-medium"><?= $s['utr_number'] ?: 'PENDING' ?></span></span>
                            </div>
                        </td>
                        <td class="text-end pe-4">
                            <span class="badge bg-success shadow-none rounded-pill px-3 py-2" style="font-size: 0.65rem;">
                                <i class="fas fa-check-circle me-1"></i> <?= ucfirst($s['status']) ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Settlement Modal -->
<div class="modal fade" id="addSettlementModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 24px;">
            <div class="modal-header border-0 p-4 pb-0">
                <div>
                    <h5 class="modal-title fw-bold text-dark">Record Settlement</h5>
                    <p class="text-muted small mb-0">Record a new GST payment made to the government.</p>
                </div>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <form id="settlementForm" action="<?= base_url('manager/gst/settlement/store') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted text-uppercase">Company *</label>
                            <select name="company_id" class="form-select border-0 bg-light rounded-3 py-2 shadow-none" required>
                                <option value="">Select Company</option>
                                <?php if(!empty($companies)): foreach($companies as $c): ?>
                                    <option value="<?= $c['id'] ?>"><?= esc($c['name']) ?></option>
                                <?php endforeach; endif; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted text-uppercase">Period *</label>
                            <input type="month" name="tax_period" class="form-control border-0 bg-light rounded-3 py-2 shadow-none" value="<?= date('Y-m') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted text-uppercase">Amount (₹) *</label>
                            <input type="number" name="amount" class="form-control border-0 bg-light rounded-3 py-2 shadow-none fw-bold text-primary" step="0.01" placeholder="0.00" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted text-uppercase">Payment Date *</label>
                            <input type="date" name="payment_date" class="form-control border-0 bg-light rounded-3 py-2 shadow-none" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted text-uppercase">Payment Mode *</label>
                            <select name="payment_mode" class="form-select border-0 bg-light rounded-3 py-2 shadow-none" required>
                                <option value="netbanking">Net Banking</option>
                                <option value="upi">UPI</option>
                                <option value="neft_rtgs">NEFT / RTGS</option>
                                <option value="cheque">Cheque</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted text-uppercase">Challan No.</label>
                            <input type="text" name="challan_number" class="form-control border-0 bg-light rounded-3 py-2 shadow-none" placeholder="CPIN/CIN">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted text-uppercase">Bank UTR</label>
                            <input type="text" name="utr_number" class="form-control border-0 bg-light rounded-3 py-2 shadow-none" placeholder="Transaction ID">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted text-uppercase">Notes</label>
                            <textarea name="purpose_comment" class="form-control border-0 bg-light rounded-3 py-2 shadow-none" rows="2" placeholder="Internal notes..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-3 px-4 fw-bold text-muted" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-3 px-4 py-2 fw-bold shadow-sm" id="saveBtn">Save Settlement</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .bg-soft-primary { background: rgba(59, 130, 246, 0.1); }
</style>
<?= $this->endSection() ?>
