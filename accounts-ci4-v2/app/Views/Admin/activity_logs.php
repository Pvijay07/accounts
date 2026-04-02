<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Audit Logs<?= $this->endSection() ?>
<?= $this->section('page_title') ?>Admin Panel<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div id="audit-logs" class="page">
    <div class="table-container">
        <div class="table-header">
            <div class="table-title">Audit Logs</div>
            <div class="table-actions">
                <div class="search-container">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" class="search-input" placeholder="Search activity logs..." id="logSearch" onkeyup="filterLogs()">
                </div>
                <a href="<?= base_url('admin/activity-logs/export') ?>" class="btn btn-outline">
                    <i class="fas fa-download"></i> Export CSV
                </a>
                <form action="<?= base_url('admin/activity-logs/clear') ?>" method="POST" class="d-inline" id="clearLogsForm">
                    <?= csrf_field() ?>
                    <button type="button" class="btn btn-danger" onclick="confirmClearLogs()">
                        <i class="fas fa-eraser"></i> Clear All Logs
                    </button>
                </form>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover" id="logsTable">
                <thead>
                    <tr>
                        <th>Timestamp</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Details</th>
                        <th>IP Address</th>
                    </tr>
                </thead>
                <tbody>
                <?php if(empty($logs)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <i class="fas fa-history fa-2x text-muted mb-3 d-block"></i>
                            <p class="text-muted">No activity logs found</p>
                        </td>
                    </tr>
                <?php else: foreach($logs as $log): ?>
                    <tr>
                        <td>
                            <div class="timestamp">
                                <div class="date"><?= date('d M, Y', strtotime($log['created_at'])) ?></div>
                                <div class="time"><?= date('h:i A', strtotime($log['created_at'])) ?></div>
                            </div>
                        </td>
                        <td>
                            <div class="user-info-cell">
                                <div class="user-avatar-sm">
                                    <?= substr($log['user_name'] ?? 'S', 0, 1) ?>
                                </div>
                                <div class="user-details">
                                    <div class="user-name"><?= esc($log['user_name'] ?? 'System') ?></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="action-badge action-<?= $log['action'] === 'deleted' ? 'warning' : ($log['action'] === 'created' ? 'success' : 'info') ?>">
                                <?= strtoupper(esc($log['action'] ?? 'event')) ?>
                            </span>
                        </td>
                        <td>
                            <div style="max-width: 400px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"
                                title="<?= esc($log['details'] ?? '') ?>">
                                <?= esc($log['details'] ?? 'No details available') ?>
                            </div>
                        </td>
                        <td><code><?= esc($log['ip_address'] ?? '127.0.0.1') ?></code></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>

        <?php if(isset($pager)): ?>
        <div class="pagination-container">
            <div class="pagination-info">
                Showing activity logs
            </div>
            <?= $pager->links() ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
    .timestamp {
        font-size: 13px;
    }
    .timestamp .date {
        color: #64748b;
    }
    .timestamp .time {
        font-weight: 600;
        color: #334155;
    }
    .user-info-cell {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .user-avatar-sm {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        background: linear-gradient(135deg, #3b82f6, #8b5cf6);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.85rem;
        box-shadow: 0 4px 10px rgba(59, 130, 246, 0.3);
    }
    .user-name {
        font-weight: 700;
        font-size: 14px;
        color: #1e293b;
    }
    .action-badge {
        padding: 6px 12px;
        border-radius: 30px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .action-success {
        background: #dcfce7;
        color: #16a34a;
    }
    .action-info {
        background: #e0f2fe;
        color: #0284c7;
    }
    .action-warning {
        background: #fef9c3;
        color: #ca8a04;
    }
</style>

<script>
function filterLogs() {
    const search = document.getElementById('logSearch').value.toLowerCase();
    document.querySelectorAll('#logsTable tbody tr').forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(search) ? '' : 'none';
    });
}

function confirmClearLogs() {
    Swal.fire({
        title: 'Clear All Logs?',
        text: 'This will permanently remove all activity logs. This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        confirmButtonText: 'Yes, clear all'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('clearLogsForm').submit();
        }
    });
}
</script>
<?= $this->endSection() ?>
