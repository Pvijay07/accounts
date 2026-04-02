<?= $this->extend('layouts/manager') ?>

<?= $this->section('title') ?>TDS on Expenses<?= $this->endSection() ?>
<?= $this->section('page_title') ?>TDS Management<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Control Header -->
<div class="card shadow-sm mb-4 border-0" style="border-radius: 20px; background: rgba(255,255,255,0.7); backdrop-filter: blur(12px);">
    <div class="card-body p-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h5 class="mb-1 fw-extrabold text-dark">TDS Dashboard</h5>
            <p class="text-muted small mb-0">Monitor TDS deductions on income and expenses.</p>
        </div>
        <div class="nav-pills-premium d-flex gap-2 bg-light p-1 rounded-pill border shadow-sm">
            <a class="nav-link-premium shadow-sm" href="<?= base_url('manager/tds') ?>">TDS on Income (Inward)</a>
            <a class="nav-link-premium active" href="<?= base_url('manager/tds/expense') ?>">TDS on Expenses (Outward)</a>
        </div>
    </div>
</div>

<!-- TDS KPI Visualization -->
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card h-100 border-0 shadow-sm premium-card overflow-hidden">
            <div class="card-body p-4 position-relative">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <div class="text-muted text-uppercase fw-bold small mb-1" style="letter-spacing:1px;">Current Month</div>
                        <h4 class="fw-extrabold text-dark mb-0"><?= $currentPeriod??date('F Y') ?></h4>
                    </div>
                    <div class="icon-shape bg-soft-danger text-danger rounded-4 shadow-sm"><i class="fas fa-calendar-day"></i></div>
                </div>
                <div class="text-muted x-small fw-medium">Active period for TDS tracking.</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100 border-0 shadow-sm premium-card overflow-hidden" style="border-left: 5px solid #ef4444 !important;">
            <div class="card-body p-4 position-relative">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <div class="text-muted text-uppercase fw-bold small mb-1" style="letter-spacing:1px;">TDS Deducted on Expenses</div>
                        <h3 class="fw-extrabold mb-0" style="color: #ef4444;">₹<?= number_format($totalTDSAmount??0, 2) ?></h3>
                    </div>
                    <div class="icon-shape text-white rounded-4 shadow-sm" style="background: linear-gradient(135deg, #ef4444, #dc2626);"><i class="fas fa-file-invoice-dollar"></i></div>
                </div>
                <div class="text-muted x-small fw-medium">Total TDS withheld from vendor payments.</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100 border-0 shadow-sm premium-card overflow-hidden">
            <div class="card-body p-4 position-relative d-flex align-items-center justify-content-center text-center">
                <div class="w-100">
                    <button class="btn btn-outline-danger rounded-pill px-4 fw-bold shadow-none w-100 py-3 d-flex align-items-center justify-content-center">
                        <i class="fas fa-file-export me-2"></i> DOWNLOAD REPORT
                    </button>
                    <div class="text-muted x-small mt-2 fw-medium">Generate monthly TDS report.</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main TDS Ledger Presentation -->
<div class="card shadow-sm border-0 mb-5" style="border-radius: 20px;">
    <div class="card-header bg-white border-0 p-4">
        <h6 class="mb-0 fw-bold text-dark">Expense TDS Records</h6>
    </div>
    <div class="card-body p-0">
        <!-- Structural Placeholder for Live Data -->
        <div class="text-center py-5">
            <div class="registry-empty-state opacity-50 mb-3">
                <div class="icon-circle bg-light mx-auto mb-3" style="width:80px; height:80px;"><i class="fas fa-receipt fa-2x text-muted"></i></div>
            </div>
            <h6 class="fw-bold text-dark mb-1">TDS Records Automatically Synced</h6>
            <p class="text-muted small mb-4 mx-auto" style="max-width:400px;">TDS values are shown directly in the Expense ledgers and aggregated here.</p>
        </div>
    </div>
    <div class="card-footer bg-soft-light border-0 py-3 px-4 rounded-bottom-4">
        <p class="x-small text-muted fw-bold mb-0">
            <i class="fas fa-info-circle text-info me-1"></i> NOTE: Track these records for depositing TDS.
        </p>
    </div>
</div>

<style>
    .premium-card { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); border: 1px solid rgba(0,0,0,0.01) !important; }
    .premium-card:hover { transform: translateY(-7px); box-shadow: 0 15px 45px -10px rgba(0,0,0,0.1) !important; }
    .fw-extrabold { font-weight: 800; letter-spacing: -1px; }
    .icon-shape { width: 42px; height: 42px; display: flex; align-items: center; justify-content: center; }
    .icon-circle { border-radius: 50%; display: flex; align-items: center; justify-content: center; }
    .bg-soft-danger { background: rgba(239, 68, 68, 0.1); }
    .bg-soft-light { background: rgba(248, 250, 252, 0.8); }
    .x-small { font-size: 0.72rem; }
    .nav-link-premium { padding: 8px 20px; border-radius: 50px; text-decoration: none; font-size: 0.85rem; font-weight: 700; color: #64748b; transition: all 0.25s ease; }
    .nav-link-premium:hover { color: #8b5cf6; background: rgba(139, 92, 246, 0.05); }
    .nav-link-premium.active { background: #fff; color: #8b5cf6; }
</style>
<?= $this->endSection() ?>
