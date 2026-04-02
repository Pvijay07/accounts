<?= $this->extend('layouts/ca') ?>

<?= $this->section('title') ?>Auditor Governance Console<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Auditor KPI Matrix -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card h-100 border-0 shadow-sm premium-card">
            <div class="card-body p-4">
                <div class="text-muted text-uppercase fw-bold small mb-2" style="letter-spacing:1px;">Audited Entities</div>
                <div class="d-flex align-items-end gap-2">
                    <h2 class="fw-extrabold mb-0 text-primary"><?= $stats['total_companies'] ?? 0 ?></h2>
                    <span class="text-muted small pb-1">Companies</span>
                </div>
                <div class="text-muted x-small mt-2 fw-medium">Consolidated monitoring active.</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100 border-0 shadow-sm premium-card">
            <div class="card-body p-4">
                <div class="text-muted text-uppercase fw-bold small mb-2" style="letter-spacing:1px;">Pending Artifacts</div>
                <div class="d-flex align-items-end gap-2">
                    <h2 class="fw-extrabold mb-0 text-danger"><?= $stats['pending_docs'] ?? 0 ?></h2>
                    <span class="text-muted small pb-1">Files missing</span>
                </div>
                <div class="text-muted x-small mt-2 fw-medium">Missing invoice/bill attachments.</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100 border-0 shadow-sm premium-card">
            <div class="card-body p-4">
                <div class="text-muted text-uppercase fw-bold small mb-2" style="letter-spacing:1px;">Compliance Tasks</div>
                <div class="d-flex align-items-end gap-2">
                    <h2 class="fw-extrabold mb-0 text-warning"><?= $stats['open_tasks'] ?? 0 ?></h2>
                    <span class="text-muted small pb-1">Due soon</span>
                </div>
                <div class="text-muted x-small mt-2 fw-medium">Critical audit milestones.</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100 border-0 shadow-sm premium-card">
            <div class="card-body p-4">
                <div class="text-muted text-uppercase fw-bold small mb-2" style="letter-spacing:1px;">Forensic Sync</div>
                <div class="d-flex align-items-end gap-2">
                    <h2 class="fw-extrabold mb-0 text-success"><?= date('d-m') ?></h2>
                    <span class="text-muted small pb-1"><?= date('Y') ?></span>
                </div>
                <div class="text-muted x-small mt-2 fw-medium">Auto-synced from latest change.</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <!-- Forensic Export Hub -->
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 20px;">
            <div class="card-header bg-transparent border-0 p-4">
                <h6 class="fw-bold text-dark mb-1">Financial Intelligence Exports</h6>
                <p class="text-muted small mb-0">High-fidelity data extraction for statutory compliance.</p>
            </div>
            <div class="card-body p-4 pt-0">
                <div class="row g-3">
                    <div class="col-md-6">
                        <a href="<?= base_url('ca/statements') ?>" class="btn btn-primary w-100 rounded-pill py-3 fw-bold shadow-sm">
                            <i class="fas fa-file-invoice-dollar me-2"></i> Entity Statements
                        </a>
                    </div>
                    <div class="col-md-6">
                        <a href="<?= base_url('ca/invoices') ?>" class="btn btn-outline-primary w-100 rounded-pill py-3 fw-bold border-2">
                            <i class="fas fa-archive me-2"></i> Invoices Repository
                        </a>
                    </div>
                    <div class="col-md-6">
                        <a href="<?= base_url('ca/records') ?>" class="btn btn-outline-info w-100 rounded-pill py-3 fw-bold border-2">
                            <i class="fas fa-table me-2"></i> Transaction Registry
                        </a>
                    </div>
                    <div class="col-md-6">
                        <a href="#" class="btn btn-outline-secondary w-100 rounded-pill py-3 fw-bold border-2 opacity-50">
                            <i class="fas fa-user-shield me-2"></i> Salary Packs
                        </a>
                    </div>
                </div>
                <div class="alert bg-soft-info border-0 rounded-4 mt-4 mb-0 d-flex gap-3 align-items-start">
                    <i class="fas fa-info-circle text-info mt-1"></i>
                    <p class="small text-info fw-medium mb-0">
                        AUDITOR BOUNDARY: Secured read-only forensic access. Modification of fiscal values is restricted. Authorized only for task status updates.
                    </p>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm" style="border-radius: 20px;">
            <div class="card-header bg-transparent border-0 p-4">
                <h6 class="fw-bold text-dark mb-0">Compliance Vulnerabilities</h6>
            </div>
            <div class="card-body p-4 pt-0">
                <div class="d-flex flex-column gap-3">
                    <div class="vulnerability-item p-3 rounded-4 bg-soft-danger d-flex align-items-center gap-3">
                        <div class="bg-danger text-white rounded-circle p-2" style="width:36px; height:36px; display:flex; align-items:center; justify-content:center;">
                            <i class="fas fa-comment-slash"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-danger small">Missing Contextual Data</div>
                            <div class="text-muted x-small">3 expenses identified without mandatory purpose justifications.</div>
                        </div>
                    </div>
                    <div class="vulnerability-item p-3 rounded-4 bg-soft-warning d-flex align-items-center gap-3">
                        <div class="bg-warning text-white rounded-circle p-2" style="width:36px; height:36px; display:flex; align-items:center; justify-content:center;">
                            <i class="fas fa-file-contract"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-warning small">Attachment Friction</div>
                            <div class="text-muted x-small">9 expenditure entries awaiting validated bill/invoice uploads.</div>
                        </div>
                    </div>
                    <div class="vulnerability-item p-3 rounded-4 bg-soft-info d-flex align-items-center gap-3">
                        <div class="bg-info text-white rounded-circle p-2" style="width:36px; height:36px; display:flex; align-items:center; justify-content:center;">
                            <i class="fas fa-hand-holding-usd"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-info small">Income Validation Required</div>
                            <div class="text-muted x-small">2 receipts remaining unlinked to bank settlement artifacts.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 20px;">
            <div class="card-header bg-transparent border-0 p-4 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold text-dark mb-0">System Activity Stream</h6>
                <span class="badge bg-soft-primary text-primary rounded-pill px-3">Live Feed</span>
            </div>
            <div class="card-body p-0">
                <div class="activity-stream">
                    <?php foreach($recent_activity as $act): ?>
                    <div class="activity p-4 border-bottom border-light">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-bold text-dark small"><?= esc($act['entity']) ?></span>
                            <span class="text-muted x-small"><?= $act['time'] ?></span>
                        </div>
                        <div class="text-muted x-small"><?= esc($act['action']) ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 text-center py-3">
                <a href="#" class="small fw-bold text-primary text-decoration-none opacity-50">VIEW PERFORMANCE LOGS</a>
            </div>
        </div>

        <div class="card border-0 shadow-sm" style="border-radius: 20px; background: linear-gradient(135deg, #0891b2, #0e7490);">
            <div class="card-body p-4 text-white text-center">
                <i class="fas fa-bell fa-2x mb-3 opacity-50"></i>
                <h6 class="fw-bold mb-3">Statutory Reminders</h6>
                <div class="d-grid gap-2">
                    <button class="btn btn-white rounded-pill fw-bold text-primary py-2 shadow-sm">Sync Compliance Tasks</button>
                    <button class="btn btn-outline-white rounded-pill fw-bold py-2 border-2 text-white">Broadcast Alerts</button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .premium-card { transition: all 0.3s ease; }
    .premium-card:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(0,0,0,0.05) !important; }
    .fw-extrabold { font-weight: 800; font-size: 2rem; letter-spacing: -1px; }
    .x-small { font-size: 0.72rem; }
    .bg-soft-primary { background: rgba(8, 145, 178, 0.08); }
    .bg-soft-info { background: rgba(14, 165, 233, 0.08); }
    .bg-soft-danger { background: rgba(239, 68, 68, 0.08); }
    .bg-soft-warning { background: rgba(245, 158, 11, 0.08); }
    .btn-white { background: white; color: #0891b2; }
    .btn-outline-white { border-color: rgba(255,255,255,0.4); color: white; }
    .btn-outline-white:hover { background: rgba(255,255,255,0.1); color: white; border-color: white; }
</style>
<?= $this->endSection() ?>
