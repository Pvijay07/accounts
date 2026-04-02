<?= $this->extend('layouts/manager') ?>
<?= $this->section('title') ?>Advances & Loans<?= $this->endSection() ?>
<?= $this->section('page_title') ?>Advances & Loans<?= $this->endSection() ?>

<?php
$filters = $filters ?? ['status' => 'all', 'company_id' => 'all', 'search' => ''];
$paginationQuery = array_filter([
    'tab' => $activeTab ?? 'payable',
    'status' => $filters['status'] ?? 'all',
    'company_id' => $filters['company_id'] ?? 'all',
    'search' => $filters['search'] ?? '',
], static fn ($value) => $value !== '' && $value !== null);
?>

<?= $this->section('content') ?>
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="text-muted small fw-bold text-uppercase mb-1">Given Outstanding</div>
                    <h3 class="fw-bold text-danger mb-0">Rs. <?= number_format((float) ($stats['total_payable_outstanding'] ?? 0), 2) ?></h3>
                    <small class="text-muted">Total given: Rs. <?= number_format((float) ($stats['total_payable_issued'] ?? 0), 2) ?></small>
                </div>
                <i class="fas fa-hand-holding-usd fa-2x text-danger"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="text-muted small fw-bold text-uppercase mb-1">Received Outstanding</div>
                    <h3 class="fw-bold text-success mb-0">Rs. <?= number_format((float) ($stats['total_receivable_outstanding'] ?? 0), 2) ?></h3>
                    <small class="text-muted">Total received: Rs. <?= number_format((float) ($stats['total_receivable_issued'] ?? 0), 2) ?></small>
                </div>
                <i class="fas fa-coins fa-2x text-success"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="text-muted small fw-bold text-uppercase mb-1">Overdue Records</div>
                    <h3 class="fw-bold text-warning mb-0"><?= (int) ($stats['overdue_count'] ?? 0) ?></h3>
                    <small class="text-muted">Overdue amount: Rs. <?= number_format((float) ($stats['overdue_amount'] ?? 0), 2) ?></small>
                </div>
                <i class="fas fa-clock fa-2x text-warning"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card d-flex align-items-center justify-content-center">
            <button class="btn btn-primary w-100" type="button" onclick="openAdvanceModal()">
                <i class="fas fa-plus"></i> New Advance/Loan
            </button>
        </div>
    </div>
</div>

<div class="filter-section">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div class="filter-buttons mb-0">
            <a class="filter-btn <?= ($activeTab ?? 'payable') === 'payable' ? 'active' : '' ?>" href="<?= base_url('manager/loans?tab=payable') ?>">Given (Payable)</a>
            <a class="filter-btn <?= ($activeTab ?? '') === 'receivable' ? 'active' : '' ?>" href="<?= base_url('manager/loans?tab=receivable') ?>">Received (Receivable)</a>
        </div>
        <form class="row g-3 align-items-end flex-grow-1" method="get" action="<?= base_url('manager/loans') ?>">
            <input type="hidden" name="tab" value="<?= esc($activeTab ?? 'payable') ?>">
            <div class="col-md-4">
                <label class="form-label small fw-bold text-uppercase">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Reference or purpose" value="<?= esc($filters['search'] ?? '') ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold text-uppercase">Company</label>
                <select name="company_id" class="form-control">
                    <option value="all">All Companies</option>
                    <?php foreach ($companies as $company): ?>
                        <option value="<?= $company['id'] ?>" <?= (string) ($filters['company_id'] ?? 'all') === (string) $company['id'] ? 'selected' : '' ?>>
                            <?= esc($company['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold text-uppercase">Status</label>
                <select name="status" class="form-control">
                    <option value="all" <?= ($filters['status'] ?? 'all') === 'all' ? 'selected' : '' ?>>All Status</option>
                    <option value="outstanding" <?= ($filters['status'] ?? '') === 'outstanding' ? 'selected' : '' ?>>Outstanding</option>
                    <option value="partially_recovered" <?= ($filters['status'] ?? '') === 'partially_recovered' ? 'selected' : '' ?>>Partially Recovered</option>
                    <option value="recovered" <?= ($filters['status'] ?? '') === 'recovered' ? 'selected' : '' ?>>Recovered</option>
                    <option value="overdue" <?= ($filters['status'] ?? '') === 'overdue' ? 'selected' : '' ?>>Overdue</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-fill">
                    <i class="fas fa-search"></i> Apply
                </button>
                <a href="<?= base_url('manager/loans?tab=' . ($activeTab ?? 'payable')) ?>" class="btn btn-outline flex-fill">
                    <i class="fas fa-redo"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

<div class="table-container">
    <div class="table-header">
        <div class="table-title"><?= ($activeTab ?? 'payable') === 'receivable' ? 'Received Advances' : 'Given Advances' ?></div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Reference</th>
                    <th>Party / Company</th>
                    <th>Total Amount</th>
                    <th>Outstanding</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (! $advances): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="fas fa-coins fa-2x mb-3 d-block"></i>
                            No advances or loans found.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($advances as $advance): ?>
                        <tr>
                            <td>
                                <strong><?= esc($advance['reference_number'] ?: 'REF-' . str_pad((string) $advance['id'], 4, '0', STR_PAD_LEFT)) ?></strong>
                                <div class="search-match"><?= esc($advance['transaction_type']) ?></div>
                            </td>
                            <td>
                                <strong><?= esc($advance['party_name'] ?? 'N/A') ?></strong>
                                <div class="search-match"><?= esc($advance['company_name'] ?? 'N/A') ?></div>
                            </td>
                            <td>Rs. <?= number_format((float) ($advance['amount'] ?? 0), 2) ?></td>
                            <td>
                                <strong class="<?= (float) ($advance['outstanding_amount'] ?? 0) > 0 ? 'text-danger' : 'text-success' ?>">
                                    Rs. <?= number_format((float) ($advance['outstanding_amount'] ?? 0), 2) ?>
                                </strong>
                            </td>
                            <td><span class="status <?= esc($advance['status']) ?>"><?= ucwords(str_replace('_', ' ', esc($advance['status']))) ?></span></td>
                            <td><?= ! empty($advance['transaction_date']) ? date('d M Y', strtotime($advance['transaction_date'])) : 'N/A' ?></td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn btn-icon btn-outline btn-sm" type="button" onclick="viewAdvance(<?= $advance['id'] ?>)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <?php if ((float) ($advance['outstanding_amount'] ?? 0) > 0): ?>
                                        <button class="btn btn-icon btn-success btn-sm" type="button" onclick="recordRecovery(<?= $advance['id'] ?>, <?= (float) ($advance['outstanding_amount'] ?? 0) ?>)">
                                            <i class="fas fa-hand-holding-usd"></i>
                                        </button>
                                    <?php endif; ?>
                                    <button class="btn btn-icon btn-outline btn-sm" type="button" onclick="editAdvance(<?= $advance['id'] ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-icon btn-danger btn-sm" type="button" onclick="deleteAdvance(<?= $advance['id'] ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="pagination-container">
        <div class="pagination-info">
            Showing <?= $pager ? ($pager->getCurrentPage() - 1) * $pager->getPerPage() + 1 : 0 ?> to
            <?= $pager ? min($pager->getCurrentPage() * $pager->getPerPage(), $pager->getTotal()) : 0 ?> of
            <?= $pager ? $pager->getTotal() : count($advances) ?> entries
        </div>
        <div class="pagination-links"><?= $pager ? $pager->links() : '' ?></div>
    </div>
</div>

<div class="modal" id="advanceModal">
    <div class="modal-content">
        <div class="modal-header">
            <div class="modal-title" id="advanceModalTitle">Add Advance or Loan</div>
            <button type="button" class="close-modal" onclick="closeAdvanceModal()">&times;</button>
        </div>
        <form id="advanceForm" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <input type="hidden" id="advance_id" name="id">
            <div class="p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Type <span class="required">*</span></label>
                        <select id="advance_type" name="advance_type" class="form-control" required>
                            <option value="payable">Given (Payable)</option>
                            <option value="receivable">Received (Receivable)</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Party <span class="required">*</span></label>
                        <select id="party_id" name="party_id" class="form-control" required>
                            <option value="">Select Party</option>
                            <?php foreach ($parties as $party): ?>
                                <option value="<?= $party['id'] ?>" data-type="<?= esc($party['type'] ?? 'other') ?>">
                                    <?= esc($party['name']) ?> (<?= ucfirst(esc($party['type'] ?? 'other')) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Party Type</label>
                        <input type="text" id="party_type_display" class="form-control" readonly>
                        <input type="hidden" id="party_type" name="party_type">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Company <span class="required">*</span></label>
                        <select id="loan_company_id" name="company_id" class="form-control" required>
                            <option value="">Select Company</option>
                            <?php foreach ($companies as $company): ?>
                                <option value="<?= $company['id'] ?>"><?= esc($company['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Amount <span class="required">*</span></label>
                        <input type="number" id="amount" name="amount" class="form-control" step="0.01" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Transaction Date <span class="required">*</span></label>
                        <input type="date" id="transaction_date" name="transaction_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Expected Recovery Date</label>
                        <input type="date" id="expected_date" name="expected_date" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Reference Number</label>
                        <input type="text" id="reference_number" name="reference_number" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <select id="loan_status" name="status" class="form-control">
                            <option value="outstanding">Outstanding</option>
                            <option value="partially_recovered">Partially Recovered</option>
                            <option value="recovered">Recovered</option>
                            <option value="overdue">Overdue</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Purpose <span class="required">*</span></label>
                        <textarea id="purpose" name="purpose" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Comments</label>
                        <textarea id="comments" name="comments" class="form-control" rows="2"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer px-4 pb-4">
                <button type="button" class="btn btn-outline" onclick="closeAdvanceModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" id="saveAdvanceBtn">
                    <i class="fas fa-save"></i> Save Record
                </button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    const loansBaseUrl = '<?= base_url('manager/loans') ?>';
    const loansUpdateBaseUrl = '<?= base_url('manager/loans/update') ?>';
    const loanPaginationQuery = <?= json_encode($paginationQuery) ?>;

    $('#party_id').on('change', function () {
        const type = $(this).find(':selected').data('type') || '';
        $('#party_type_display').val(type ? type.charAt(0).toUpperCase() + type.slice(1) : '');
        $('#party_type').val(type);
    });

    function openAdvanceModal() {
        $('#advanceModalTitle').text('Add Advance or Loan');
        $('#advanceForm')[0].reset();
        $('#advance_id').val('');
        $('#advance_type').prop('disabled', false);
        $('#transaction_date').val('<?= date('Y-m-d') ?>');
        $('#saveAdvanceBtn').html('<i class="fas fa-save"></i> Save Record');
        $('#advanceModal').css('display', 'flex');
        $('body').css('overflow', 'hidden');
    }

    function closeAdvanceModal() {
        $('#advanceModal').hide();
        $('body').css('overflow', '');
    }

    function editAdvance(id) {
        fetch(`${loansBaseUrl}/${id}`)
            .then((response) => response.json())
            .then((data) => {
                if (!data.success) {
                    throw new Error(data.message || 'Unable to load record');
                }

                const advance = data.data;
                $('#advanceModalTitle').text('Edit Advance / Loan');
                $('#advance_id').val(advance.id);
                $('#advance_type').val(advance.transaction_type === 'receivable_advance' ? 'receivable' : 'payable');
                $('#advance_type').prop('disabled', true);
                $('#party_id').val(advance.party_id || '').trigger('change');
                $('#loan_company_id').val(advance.company_id || '');
                $('#amount').val(advance.amount || '');
                $('#transaction_date').val(advance.transaction_date || '');
                $('#expected_date').val(advance.expected_recovery_date || '');
                $('#reference_number').val(advance.reference_number || '');
                $('#loan_status').val(advance.status || 'outstanding');
                $('#purpose').val(advance.purpose || '');
                $('#comments').val(advance.comments || '');
                $('#saveAdvanceBtn').html('<i class="fas fa-save"></i> Update Record');
                $('#advanceModal').css('display', 'flex');
                $('body').css('overflow', 'hidden');
            })
            .catch((error) => Swal.fire('Error', error.message, 'error'));
    }

    $('#advanceForm').on('submit', function (event) {
        event.preventDefault();
        const advanceId = $('#advance_id').val();
        const url = advanceId ? `${loansUpdateBaseUrl}/${advanceId}` : loansBaseUrl;
        const button = $('#saveAdvanceBtn');

        button.prop('disabled', true).html('<span class="spinner"></span> Saving...');

        fetch(url, {
            method: 'POST',
            body: new FormData(this),
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        })
            .then(async (response) => {
                const data = await response.json();
                if (!response.ok) {
                    throw new Error(Object.values(data.messages || data).join('\n') || 'Failed to save record');
                }
                return data;
            })
            .then((data) => {
                Swal.fire('Success', data.message, 'success').then(() => window.location.reload());
            })
            .catch((error) => {
                button.prop('disabled', false).html('<i class="fas fa-save"></i> Save Record');
                Swal.fire('Error', error.message, 'error');
            });
    });

    function viewAdvance(id) {
        fetch(`${loansBaseUrl}/${id}`)
            .then((response) => response.json())
            .then((data) => {
                if (!data.success) {
                    throw new Error(data.message || 'Unable to load record');
                }

                const advance = data.data;
                const recoveries = (advance.recoveries || []).map((recovery) => `
                    <tr>
                        <td>${recovery.transaction_date || '-'}</td>
                        <td>Rs. ${Number(recovery.amount || 0).toFixed(2)}</td>
                        <td>${recovery.comments || '-'}</td>
                    </tr>
                `).join('');

                Swal.fire({
                    title: advance.reference_number || `Advance #${advance.id}`,
                    width: 760,
                    html: `
                        <div class="text-start">
                            <p><strong>Purpose:</strong> ${advance.purpose || '-'}</p>
                            <p><strong>Status:</strong> ${advance.status || '-'}</p>
                            <p><strong>Total Amount:</strong> Rs. ${Number(advance.amount || 0).toFixed(2)}</p>
                            <p><strong>Outstanding:</strong> Rs. ${Number(advance.outstanding_amount || 0).toFixed(2)}</p>
                            <p><strong>Expected Recovery:</strong> ${advance.expected_recovery_date || '-'}</p>
                            <div class="table-responsive mt-3">
                                <table class="table table-sm mb-0">
                                    <thead><tr><th>Date</th><th>Amount</th><th>Comments</th></tr></thead>
                                    <tbody>${recoveries || '<tr><td colspan="3" class="text-muted">No recoveries recorded.</td></tr>'}</tbody>
                                </table>
                            </div>
                        </div>
                    `,
                });
            })
            .catch((error) => Swal.fire('Error', error.message, 'error'));
    }

    function recordRecovery(id, maxAmount) {
        Swal.fire({
            title: 'Record Recovery',
            html: `
                <input type="number" id="recovery_amount" class="swal2-input" placeholder="Recovery amount" value="${Number(maxAmount).toFixed(2)}">
                <input type="date" id="recovery_date" class="swal2-input" value="${new Date().toISOString().split('T')[0]}">
                <textarea id="recovery_comments" class="swal2-textarea" placeholder="Comments"></textarea>
            `,
            showCancelButton: true,
            confirmButtonText: 'Save Recovery',
            preConfirm: () => {
                const amount = parseFloat(document.getElementById('recovery_amount').value || 0);
                const recoveryDate = document.getElementById('recovery_date').value;
                const comments = document.getElementById('recovery_comments').value;

                if (!amount || amount <= 0) {
                    Swal.showValidationMessage('Enter a valid recovery amount');
                    return false;
                }

                if (amount > maxAmount) {
                    Swal.showValidationMessage('Recovery amount cannot exceed outstanding amount');
                    return false;
                }

                return { amount, recoveryDate, comments };
            },
        }).then((result) => {
            if (!result.isConfirmed) {
                return;
            }

            const formData = new FormData();
            formData.append('recovery_amount', result.value.amount);
            formData.append('recovery_date', result.value.recoveryDate);
            formData.append('comments', result.value.comments);

            fetch(`${loansBaseUrl}/${id}/recovery`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
            })
                .then((response) => response.json())
                .then((data) => {
                    if (!data.success) {
                        throw new Error(data.message || 'Failed to save recovery');
                    }
                    Swal.fire('Success', data.message, 'success').then(() => window.location.reload());
                })
                .catch((error) => Swal.fire('Error', error.message, 'error'));
        });
    }

    function deleteAdvance(id) {
        Swal.fire({
            title: 'Delete this record?',
            text: 'If recoveries exist, the server will block the delete.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'Delete',
        }).then((result) => {
            if (!result.isConfirmed) {
                return;
            }

            fetch(`${loansBaseUrl}/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
            })
                .then(async (response) => {
                    const data = await response.json();
                    if (!response.ok) {
                        throw new Error(data.message || 'Failed to delete record');
                    }
                    return data;
                })
                .then((data) => {
                    Swal.fire('Deleted', data.message, 'success').then(() => window.location.reload());
                })
                .catch((error) => Swal.fire('Error', error.message, 'error'));
        });
    }

    $(window).on('click', function (event) {
        if ($(event.target).is('#advanceModal')) {
            closeAdvanceModal();
        }
    });

    document.querySelectorAll('.pagination-links a').forEach((link) => {
        const url = new URL(link.href);
        Object.entries(loanPaginationQuery).forEach(([key, value]) => {
            url.searchParams.set(key, value);
        });
        link.href = url.toString();
    });
</script>
<?= $this->endSection() ?>
