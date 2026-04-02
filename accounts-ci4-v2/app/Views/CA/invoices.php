<?= $this->extend('layouts/ca') ?>

<?= $this->section('title') ?>Audit: Invoice Repository<?= $this->endSection() ?>
<?= $this->section('page_title') ?>Consolidated Invoice Repository<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Auditor Filter Bar -->
<div class="card shadow-sm mb-4 border-0" style="border-radius: 16px; background: rgba(255,255,255,0.7); backdrop-filter: blur(10px);">
    <div class="card-body p-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h5 class="mb-1 fw-extrabold text-dark">Forensic Document Stream</h5>
            <p class="text-muted small mb-0">Reviewing system-wide billing documents for statutory verification.</p>
        </div>
        <div class="d-flex gap-2">
            <div class="input-group input-group-sm rounded-pill bg-light border-0 px-3 overflow-hidden shadow-none" style="width: 280px;">
                <span class="input-group-text bg-transparent border-0 text-muted ps-0"><i class="fas fa-search small"></i></span>
                <input type="text" class="form-control bg-transparent border-0 shadow-none py-2" placeholder="Audit by Invoice UID...">
            </div>
            <button class="btn btn-outline-primary rounded-pill px-4 fw-bold small shadow-none">
                <i class="fas fa-filter me-2"></i> Refine
            </button>
        </div>
    </div>
</div>

<!-- Main Audit Ledger -->
<div class="card shadow-sm border-0 mb-5" style="border-radius: 20px;">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light opacity-75">
                    <tr class="small text-muted text-uppercase" style="letter-spacing:1px;">
                        <th class="ps-4">Invoice Descriptor</th>
                        <th>Associated Entity</th>
                        <th class="text-end">Billing Value (₹)</th>
                        <th class="text-center">Lifecycle</th>
                        <th>Ledger Entry</th>
                        <th class="text-end pe-4">Command</th>
                    </tr>
                </thead>
                <tbody>
                <?php if(empty($invoices)): ?>
                    <tr><td colspan="6" class="text-center py-5 text-muted opacity-50"><i class="fas fa-file-invoice fa-3x mb-3 d-block"></i>No billing artifacts detected in this audit scope.</td></tr>
                <?php else: foreach($invoices as $inv): ?>
                    <tr>
                        <td class="ps-4">
                            <div class="fw-extrabold text-primary"><?= esc($inv['invoice_number']??'REF-'.str_pad($inv['id'], 3, '0', STR_PAD_LEFT)) ?></div>
                            <div class="text-muted x-small">UID Tracking Active</div>
                        </td>
                        <td>
                            <div class="fw-bold text-dark small">Entity ID: <?= $inv['company_id'] ?></div>
                            <div class="badge bg-soft-primary text-primary rounded-pill x-small">Multi-Entity Port</div>
                        </td>
                        <td class="text-end fw-extrabold text-dark">₹<?= number_format($inv['total_amount']??0, 2) ?></td>
                        <td class="text-center">
                            <?php $b=['paid'=>'bg-success','pending'=>'bg-warning text-dark','overdue'=>'bg-danger','sent'=>'bg-info']; ?>
                            <span class="badge rounded-pill px-3 py-2 <?= $b[$inv['status']]??'bg-secondary' ?> fw-bold opacity-75" style="font-size:0.65rem;">
                                <?= strtoupper($inv['status']) ?>
                            </span>
                        </td>
                        <td>
                            <div class="small fw-medium text-dark"><?= date('d M, Y', strtotime($inv['created_at'])) ?></div>
                            <div class="text-muted x-small"><?= date('H:i [A]', strtotime($inv['created_at'])) ?></div>
                        </td>
                        <td class="text-end pe-4">
                            <div class="btn-group shadow-none gap-1">
                                <button class="btn btn-sm btn-light rounded-circle shadow-none" title="Audit Inspect"><i class="fas fa-eye text-primary"></i></button>
                                <button class="btn btn-sm btn-light rounded-circle shadow-none" title="Finalize PDF"><i class="fas fa-file-pdf text-danger"></i></button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if(isset($pager)): ?><div class="card-footer bg-white border-0 py-3"><?= $pager->links() ?></div><?php endif; ?>
</div>

<style>
    .fw-extrabold { font-weight: 800; letter-spacing: -0.5px; }
    .x-small { font-size: 0.72rem; }
    .bg-soft-primary { background: rgba(59, 130, 246, 0.1); }
    .table-hover tbody tr:hover { background-color: rgba(79, 70, 229, 0.02); }
</style>
<?= $this->endSection() ?>
