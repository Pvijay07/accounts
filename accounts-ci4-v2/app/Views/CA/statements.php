<?= $this->extend('layouts/ca') ?>

<?= $this->section('title') ?>Statutory Entity Statements<?= $this->endSection() ?>
<?= $this->section('page_title') ?>Financial Statement Generation Hub<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Strategy Formulation Header -->
<div class="card shadow-sm mb-5 border-0" style="border-radius: 20px; background: linear-gradient(135deg, #0e7490, #155e75); color: white;">
    <div class="card-body p-5 text-center">
        <div class="icon-circle bg-white text-info mx-auto mb-4 shadowed-icon" style="width:70px; height:70px; display:flex; align-items:center; justify-content:center; border-radius:50%;">
            <i class="fas fa-file-invoice-dollar fa-2x"></i>
        </div>
        <h3 class="fw-extrabold mb-2">Generate Statutory Artifacts</h3>
        <p class="text-white-50 small mb-0 mx-auto" style="max-width: 500px;">Initialize high-fidelity financial statements for selected entities. All generated artifacts are forensic-ready for regulatory audits.</p>
    </div>
</div>

<!-- Statement Construction Console -->
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card shadow-sm border-0 bg-white" style="border-radius: 24px; margin-top: -50px;">
            <div class="card-body p-4 p-md-5">
                <form class="row g-4 align-items-end" action="<?= base_url('ca/statements/generate') ?>" method="POST">
                    <?= csrf_field() ?>
                    <div class="col-md-5">
                        <label class="form-label small fw-bloder text-muted text-uppercase ms-2 mb-2" style="letter-spacing:1px;">Target Corporate Entity</label>
                        <select name="company_id" class="form-select border-0 bg-light rounded-pill px-4 py-3 shadow-none fw-bold" required>
                            <option value="">Select Audit Entity</option>
                            <?php foreach($companies??[] as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= esc($c['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bloder text-muted text-uppercase ms-2 mb-2" style="letter-spacing:1px;">Temporal Fiscal Horizon</label>
                        <input type="month" name="period" class="form-control border-0 bg-light rounded-pill px-4 py-3 shadow-none fw-bold" value="<?= date('Y-m') ?>" required>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100 rounded-pill py-3 fw-extrabold shadow-lg">
                            <i class="fas fa-magic me-2"></i> GENERATE LEDGER
                        </button>
                    </div>
                </form>

                <div class="row mt-5 pt-4 border-top">
                    <div class="col-md-4 mb-4 mb-md-0">
                        <div class="p-4 rounded-4 text-center h-100" style="background: rgba(14, 116, 144, 0.05);">
                            <div class="h3 fw-extrabold text-info mb-1">CSV</div>
                            <div class="text-muted small fw-bold text-uppercase">Raw Dataset</div>
                            <div class="text-muted x-small mt-2">Maximum granularity for internal analysis.</div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4 mb-md-0">
                        <div class="p-4 rounded-4 text-center h-100" style="background: rgba(16, 185, 129, 0.05);">
                            <div class="h3 fw-extrabold text-success mb-1">XLSX</div>
                            <div class="text-muted small fw-bold text-uppercase">Excel Structural</div>
                            <div class="text-muted x-small mt-2">Systematic formatting for CA review.</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-4 rounded-4 text-center h-100 shadow-sm border border-light" style="background: white;">
                            <div class="h3 fw-extrabold text-danger mb-1">PDF</div>
                            <div class="text-muted small fw-bold text-uppercase">Formal Statutory</div>
                            <div class="text-muted x-small mt-2">Authenticated artifact for regulatory bodies.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .fw-extrabold { font-weight: 800; letter-spacing: -0.5px; }
    .x-small { font-size: 0.72rem; }
    .shadowed-icon { box-shadow: 0 10px 25px rgba(0,0,0,0.15); }
    .btn-primary { background: linear-gradient(135deg, #0e7490, #155e75); border: none; }
    .btn-primary:hover { transform: translateY(-3px); box-shadow: 0 15px 35px rgba(14, 116, 144, 0.4) !important; }
</style>
<?= $this->endSection() ?>
