<?= $this->extend('layouts/manager') ?>
<?= $this->section('title') ?>Settlement Details<?= $this->endSection() ?>
<?= $this->section('page_title') ?>Settlement Details<?= $this->endSection() ?>
<?= $this->section('content') ?>
<div class="mb-4"><a href="<?= base_url('manager/gst-details/settlements') ?>" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back to Settlements</a></div>
<div class="card">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4"><label class="text-muted small">Period</label><p class="fw-bold"><?= esc($settlement['tax_period']??'') ?></p></div>
            <div class="col-md-4"><label class="text-muted small">Amount</label><p class="fw-bold text-success">₹<?= number_format($settlement['amount']??0,2) ?></p></div>
            <div class="col-md-4"><label class="text-muted small">Payment Date</label><p class="fw-bold"><?= date('d M Y', strtotime($settlement['payment_date']??'now')) ?></p></div>
            <div class="col-md-4"><label class="text-muted small">Payment Mode</label><p><?= ucfirst($settlement['payment_mode']??'') ?></p></div>
            <div class="col-md-4"><label class="text-muted small">Challan Number</label><p><?= esc($settlement['challan_number']??'N/A') ?></p></div>
            <div class="col-md-4"><label class="text-muted small">UTR Number</label><p><?= esc($settlement['utr_number']??'N/A') ?></p></div>
            <div class="col-md-12"><label class="text-muted small">Notes</label><p><?= esc($settlement['purpose_comment']??'No notes') ?></p></div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
