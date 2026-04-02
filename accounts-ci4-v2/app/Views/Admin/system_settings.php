<?= $this->extend('layouts/admin') ?>
<?= $this->section('title') ?>System Settings<?= $this->endSection() ?>
<?= $this->section('page_title') ?>Admin Panel<?= $this->endSection() ?>

<?= $this->section('content') ?>

<style>
    /* Premium UI/UX Enhancements for System Settings */
    #system-settings {
        animation: fadeIn 0.6s cubic-bezier(0.16, 1, 0.3, 1);
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(15px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Modern Pill Tabs Navigation */
    #settingsTabs.nav-tabs {
        border-bottom: none;
        gap: 12px;
        background: #ffffff;
        padding: 12px;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
        margin-bottom: 2rem !important;
        flex-wrap: nowrap;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    #settingsTabs .nav-item {
        margin: 0;
        white-space: nowrap;
    }

    #settingsTabs .nav-link {
        border: none;
        border-radius: 12px;
        color: #64748b;
        font-weight: 600;
        padding: 12px 24px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        align-items: center;
        background: transparent;
    }

    #settingsTabs .nav-link:hover:not(.active) {
        background: #f1f5f9;
        color: #334155;
        transform: translateY(-1px);
    }

    #settingsTabs .nav-link.active {
        background: linear-gradient(135deg, #3b82f6, #2563eb);
        color: #ffffff;
        box-shadow: 0 8px 16px rgba(37, 99, 235, 0.25);
        transform: translateY(-2px);
    }

    /* Settings Card Footer */
    .settings-card-footer {
        background-color: #f8fafc;
        border-top: 1px solid #f1f5f9;
        padding: 20px 32px;
        border-radius: 0 0 20px 20px !important;
        display: flex;
        gap: 16px;
        align-items: center;
    }

    /* Maintenance tools */
    .maintenance-tool {
        transition: all 0.2s ease;
        cursor: pointer;
        border-color: transparent !important;
    }
    .maintenance-tool:hover {
        background: #fff !important;
        transform: translateX(5px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    }
    .form-range::-webkit-slider-thumb { background: #4f46e5; }
    .form-range::-moz-range-thumb { background: #4f46e5; }
</style>

<!-- System Settings Page -->
<div id="system-settings" class="page">
    <!-- Tabs for different setting categories -->
    <ul class="nav nav-tabs mb-4" id="settingsTabs" role="tablist" style="border-bottom:none; gap:12px; background:#fff; padding:12px; border-radius:16px; box-shadow:0 4px 20px rgba(0,0,0,0.04); flex-wrap:nowrap; overflow-x:auto;">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" style="border:none; border-radius:12px; font-weight:600; padding:12px 24px; white-space:nowrap;">
                <i class="fas fa-cog me-2"></i> General
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="email-tab" data-bs-toggle="tab" data-bs-target="#email" type="button" style="border:none; border-radius:12px; font-weight:600; padding:12px 24px; white-space:nowrap;">
                <i class="fas fa-envelope me-2"></i> Email & Notifications
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tax-tab" data-bs-toggle="tab" data-bs-target="#tax" type="button" style="border:none; border-radius:12px; font-weight:600; padding:12px 24px; white-space:nowrap;">
                <i class="fas fa-percentage me-2"></i> Tax Settings
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="backup-tab" data-bs-toggle="tab" data-bs-target="#backup" type="button" style="border:none; border-radius:12px; font-weight:600; padding:12px 24px; white-space:nowrap;">
                <i class="fas fa-database me-2"></i> Backup & Maintenance
            </button>
        </li>
    </ul>

    <div class="tab-content" id="settingsTabsContent">
        <!-- General Settings Tab -->
        <div class="tab-pane fade show active" id="general" role="tabpanel">
            <div class="table-container">
                <div class="table-header">
                    <div class="table-title">General Settings</div>
                </div>
                <form class="ajax-setting-form" method="POST" action="<?= base_url('admin/system-settings/save') ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="group" value="general">
                    <div class="p-4 border-bottom">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-bold text-uppercase mb-2">Application Name *</label>
                                <input type="text" class="form-control" name="app_name"
                                    value="<?= esc($settings['app_name'] ?? 'Finance Manager') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-bold text-uppercase mb-2">Default Currency *</label>
                                <select class="form-control" name="default_currency" required>
                                    <option value="INR" <?= ($settings['default_currency'] ?? 'INR') == 'INR' ? 'selected' : '' ?>>Indian Rupee (₹)</option>
                                    <option value="USD" <?= ($settings['default_currency'] ?? 'INR') == 'USD' ? 'selected' : '' ?>>US Dollar ($)</option>
                                    <option value="EUR" <?= ($settings['default_currency'] ?? 'INR') == 'EUR' ? 'selected' : '' ?>>Euro (€)</option>
                                    <option value="GBP" <?= ($settings['default_currency'] ?? 'INR') == 'GBP' ? 'selected' : '' ?>>British Pound (£)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-bold text-uppercase mb-2">Financial Year Start *</label>
                                <select class="form-control" name="financial_year_start" required>
                                    <?php for($m=1;$m<=12;$m++): ?>
                                    <option value="<?= $m ?>" <?= ($settings['financial_year_start']??4)==$m?'selected':'' ?>>
                                        <?= date('F', mktime(0,0,0,$m,1)) ?>
                                    </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-bold text-uppercase mb-2">Date Format *</label>
                                <select class="form-control" name="date_format" required>
                                    <option value="d/m/Y" <?= ($settings['date_format'] ?? 'd/m/Y') == 'd/m/Y' ? 'selected' : '' ?>>DD/MM/YYYY</option>
                                    <option value="m/d/Y" <?= ($settings['date_format'] ?? 'd/m/Y') == 'm/d/Y' ? 'selected' : '' ?>>MM/DD/YYYY</option>
                                    <option value="Y-m-d" <?= ($settings['date_format'] ?? 'd/m/Y') == 'Y-m-d' ? 'selected' : '' ?>>YYYY-MM-DD</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="p-4" style="background-color: #f8fafc; border-radius: 0 0 16px 16px;">
                        <button type="submit" class="btn btn-primary d-flex align-items-center gap-2">
                            <i class="fas fa-save"></i> Save General Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Email & Notification Settings Tab -->
        <div class="tab-pane fade" id="email" role="tabpanel">
            <div class="table-container">
                <div class="table-header">
                    <div class="table-title">Email & Notification Settings</div>
                </div>
                <form class="ajax-setting-form" method="POST" action="<?= base_url('admin/system-settings/save') ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="group" value="email">
                    <div class="p-4 border-bottom">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-bold text-uppercase mb-2">SMTP Host</label>
                                <input type="text" class="form-control" name="smtp_host"
                                    value="<?= esc($settings['smtp_host'] ?? '') ?>" placeholder="smtp.provider.com">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-bold text-uppercase mb-2">SMTP Port</label>
                                <input type="number" class="form-control" name="smtp_port"
                                    value="<?= esc($settings['smtp_port'] ?? '587') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-bold text-uppercase mb-2">SMTP Username</label>
                                <input type="text" class="form-control" name="smtp_user"
                                    value="<?= esc($settings['smtp_user'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-bold text-uppercase mb-2">SMTP Password</label>
                                <input type="password" class="form-control" name="smtp_password"
                                    value="<?= esc($settings['smtp_password'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-bold text-uppercase mb-2">Mail From Address *</label>
                                <input type="email" class="form-control" name="mail_from_address"
                                    value="<?= esc($settings['mail_from_address'] ?? 'noreply@example.com') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-bold text-uppercase mb-2">Encryption</label>
                                <select class="form-control" name="mail_encryption">
                                    <option value="" <?= ($settings['mail_encryption'] ?? 'tls') == '' ? 'selected' : '' ?>>None</option>
                                    <option value="ssl" <?= ($settings['mail_encryption'] ?? 'tls') == 'ssl' ? 'selected' : '' ?>>SSL</option>
                                    <option value="tls" <?= ($settings['mail_encryption'] ?? 'tls') == 'tls' ? 'selected' : '' ?>>TLS</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="p-4 d-flex gap-3" style="background-color: #f8fafc; border-radius: 0 0 16px 16px;">
                        <button type="submit" class="btn btn-primary d-flex align-items-center gap-2">
                            <i class="fas fa-save"></i> Save Email Settings
                        </button>
                        <button type="button" class="btn btn-outline d-flex align-items-center gap-2" onclick="testEmail()">
                            <i class="fas fa-paper-plane"></i> Test Email
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tax Settings Tab -->
        <div class="tab-pane fade" id="tax" role="tabpanel">
            <div class="table-container">
                <div class="table-header">
                    <div class="table-title">Tax Settings</div>
                </div>
                <form class="ajax-setting-form" method="POST" action="<?= base_url('admin/system-settings/save') ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="group" value="tax">
                    <div class="p-4 border-bottom">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-bold text-uppercase mb-3">Default GST Percentage</label>
                                <div class="d-flex align-items-center gap-4">
                                    <input type="range" name="default_gst_percentage" class="form-range flex-grow-1"
                                        min="0" max="28" step="1"
                                        value="<?= esc($settings['default_gst_percentage'] ?? 18) ?>"
                                        oninput="document.getElementById('gstValue').textContent = this.value + '%'">
                                    <div id="gstValue" class="d-flex align-items-center justify-content-center fw-bold" style="background: #fef3c7; color: #d97706; padding: 6px 12px; border-radius: 8px; min-width: 64px;">
                                        <?= esc($settings['default_gst_percentage'] ?? 18) ?>%
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-bold text-uppercase mb-3">Default TDS Percentage</label>
                                <div class="d-flex align-items-center gap-4">
                                    <input type="range" name="default_tds_percentage" class="form-range flex-grow-1"
                                        min="0" max="20" step="0.5"
                                        value="<?= esc($settings['default_tds_percentage'] ?? 10) ?>"
                                        oninput="document.getElementById('tdsValue').textContent = this.value + '%'">
                                    <div id="tdsValue" class="d-flex align-items-center justify-content-center fw-bold" style="background: #e0f2fe; color: #0284c7; padding: 6px 12px; border-radius: 8px; min-width: 64px;">
                                        <?= esc($settings['default_tds_percentage'] ?? 10) ?>%
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="p-4" style="background-color: #f8fafc; border-radius: 0 0 16px 16px;">
                        <button type="submit" class="btn btn-primary d-flex align-items-center gap-2">
                            <i class="fas fa-save"></i> Save Tax Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Backup & Maintenance Tab -->
        <div class="tab-pane fade" id="backup" role="tabpanel">
            <div class="table-container">
                <div class="table-header border-bottom">
                    <div class="table-title">Backup & Maintenance</div>
                </div>
                <div class="p-4">
                    <div class="d-grid gap-4">
                        <div class="d-flex align-items-center justify-content-between p-4" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; cursor: pointer; transition: all 0.2s;" onclick="clearCache()" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.05)';" onmouseout="this.style.transform=''; this.style.boxShadow='';">
                            <div class="d-flex align-items-center gap-4">
                                <div class="icon-sm bg-warning text-white rounded-circle d-flex align-items-center justify-content-center" style="width:48px; height:48px; font-size: 1.25rem;"><i class="fas fa-broom"></i></div>
                                <div>
                                    <div class="fw-bold fs-6 mb-1 text-dark">Clear Application Cache</div>
                                    <div class="text-muted small">Flush all temporary application cache files and view storage</div>
                                </div>
                            </div>
                            <i class="fas fa-chevron-right text-muted"></i>
                        </div>
                        
                        <div class="d-flex align-items-center justify-content-between p-4" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; cursor: pointer; transition: all 0.2s;" onclick="optimizeDb()" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.05)';" onmouseout="this.style.transform=''; this.style.boxShadow='';">
                            <div class="d-flex align-items-center gap-4">
                                <div class="icon-sm bg-info text-white rounded-circle d-flex align-items-center justify-content-center" style="width:48px; height:48px; font-size: 1.25rem;"><i class="fas fa-database"></i></div>
                                <div>
                                    <div class="fw-bold fs-6 mb-1 text-dark">Optimize Database</div>
                                    <div class="text-muted small">Re-index and optimize database tables for better performance</div>
                                </div>
                            </div>
                            <i class="fas fa-chevron-right text-muted"></i>
                        </div>
                        
                        <div class="d-flex align-items-center justify-content-between p-4" style="background: #fff5f5; border: 1px dashed #fecaca; border-radius: 12px; cursor: pointer; transition: all 0.2s;" onclick="clearLogs()" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(239,68,68,0.1)';" onmouseout="this.style.transform=''; this.style.boxShadow='';">
                            <div class="d-flex align-items-center gap-4">
                                <div class="icon-sm bg-danger text-white rounded-circle d-flex align-items-center justify-content-center" style="width:48px; height:48px; font-size: 1.25rem;"><i class="fas fa-trash-alt"></i></div>
                                <div>
                                    <div class="fw-bold fs-6 mb-1 text-danger">Clear Activity Logs</div>
                                    <div class="text-danger small opacity-75">Permanently remove all historical system activity and audit logs</div>
                                </div>
                            </div>
                            <i class="fas fa-chevron-right text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.querySelectorAll('.ajax-setting-form').forEach(form => {
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const btn = this.querySelector('button[type="submit"]');
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<span class="spinner"></span> Saving...';
        btn.disabled = true;

        fetch(this.action, {
            method: 'POST',
            body: new FormData(this),
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            btn.innerHTML = originalHtml;
            btn.disabled = false;
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Saved!',
                    text: data.message || 'Settings updated successfully.',
                    timer: 1500,
                    showConfirmButton: false
                });
            } else {
                Swal.fire('Error', data.message || 'Failed to update settings.', 'error');
            }
        })
        .catch(error => {
            btn.innerHTML = originalHtml;
            btn.disabled = false;
            Swal.fire('Error', 'An unexpected error occurred.', 'error');
        });
    });
});

function clearCache(){
    Swal.fire({title: 'Clearing Cache', text: 'Flushing application cache...', icon: 'info', showConfirmButton: false, timer: 1000});
    fetch('<?= base_url("admin/system-settings/clear-cache") ?>',{method:'POST',headers:{'X-Requested-With':'XMLHttpRequest'}})
    .then(r=>r.json()).then(d=>Swal.fire('Success', d.message || 'Cache cleared successfully', 'success'));
}
function optimizeDb(){
    Swal.fire({title: 'Optimizing Database', text: 'Re-indexing tables...', icon: 'info', showConfirmButton: false, timer: 1000});
    fetch('<?= base_url("admin/system-settings/optimize-db") ?>',{method:'POST',headers:{'X-Requested-With':'XMLHttpRequest'}})
    .then(r=>r.json()).then(d=>Swal.fire('Success', d.message || 'Database optimized', 'success'));
}
function clearLogs(){
    Swal.fire({
        title: 'Clear All Logs?',
        text: 'This will permanently remove all activity logs.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        confirmButtonText: 'Yes, clear all logs'
    }).then(r=>{
        if(r.isConfirmed) fetch('<?= base_url("admin/system-settings/clear-logs") ?>',{method:'POST',headers:{'X-Requested-With':'XMLHttpRequest'}})
        .then(r=>r.json()).then(d=>Swal.fire('Cleared', d.message || 'Logs cleared successfully', 'success'));
    });
}
function testEmail() {
    Swal.fire({title: 'Sending Test Email', text: 'Please wait...', icon: 'info', showConfirmButton: false, timer: 2000});
}
</script>
<?= $this->endSection() ?>
