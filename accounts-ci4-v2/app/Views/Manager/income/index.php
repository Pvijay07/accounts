<?= $this->extend('layouts/manager') ?>
<?= $this->section('title') ?>Income Management<?= $this->endSection() ?>
<?= $this->section('page_title') ?>Income Management<?= $this->endSection() ?>

<?php
$paginationQuery = array_filter([
    'company' => $companyId ?? '',
    'category' => $category ?? 'all',
    'status' => $status ?? 'all',
    'date_range' => $dateRange ?? 'month',
], static fn ($value) => $value !== '' && $value !== null);
?>

<?= $this->section('content') ?>
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card">
            <div class="d-flex justify-content-between align-items-end">
                <div>
                    <div class="text-muted small fw-bold text-uppercase mb-1">Total Expected</div>
                    <h3 class="fw-bold mb-0">Rs. <?= number_format((float) ($stats['totalPayments'] ?? 0), 2) ?></h3>
                    <small class="text-muted"><?= (int) ($stats['paymentItems'] ?? 0) ?> entries</small>
                </div>
                <i class="fas fa-file-invoice-dollar fa-2x text-primary"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="d-flex justify-content-between align-items-end">
                <div>
                    <div class="text-muted small fw-bold text-uppercase mb-1">Received</div>
                    <h3 class="fw-bold mb-0 text-success">Rs. <?= number_format((float) ($stats['totalReceived'] ?? 0), 2) ?></h3>
                    <small class="text-muted"><?= (int) ($stats['receivedItems'] ?? 0) ?> items</small>
                </div>
                <i class="fas fa-check-double fa-2x text-success"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="d-flex justify-content-between align-items-end">
                <div>
                    <div class="text-muted small fw-bold text-uppercase mb-1">Pending</div>
                    <h3 class="fw-bold mb-0 text-warning">Rs. <?= number_format((float) ($stats['totalPending'] ?? 0), 2) ?></h3>
                    <small class="text-muted"><?= (int) ($stats['pendingItems'] ?? 0) ?> items</small>
                </div>
                <i class="fas fa-clock fa-2x text-warning"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="d-flex justify-content-between align-items-end">
                <div>
                    <div class="text-muted small fw-bold text-uppercase mb-1">Overdue</div>
                    <h3 class="fw-bold mb-0 text-danger">Rs. <?= number_format((float) ($stats['allTimeOverdue'] ?? 0), 2) ?></h3>
                    <small class="text-muted"><?= (int) ($stats['allTimeOverdueItems'] ?? 0) ?> items</small>
                </div>
                <i class="fas fa-exclamation-triangle fa-2x text-danger"></i>
            </div>
        </div>
    </div>
</div>

<div class="filter-section">
    <form class="row g-3 align-items-end" method="get" action="<?= base_url('manager/income') ?>">
        <div class="col-md-3">
            <label class="form-label small fw-bold text-uppercase">Company</label>
            <select name="company" class="form-control">
                <option value="">All Companies</option>
                <?php foreach ($companies as $company): ?>
                    <option value="<?= $company['id'] ?>" <?= (string) ($companyId ?? '') === (string) $company['id'] ? 'selected' : '' ?>>
                        <?= esc($company['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-bold text-uppercase">Category</label>
            <select name="category" class="form-control">
                <option value="all" <?= ($category ?? 'all') === 'all' ? 'selected' : '' ?>>All</option>
                <option value="standard" <?= ($category ?? '') === 'standard' ? 'selected' : '' ?>>Standard</option>
                <option value="non-standard" <?= ($category ?? '') === 'non-standard' ? 'selected' : '' ?>>Non-standard</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-bold text-uppercase">Status</label>
            <select name="status" class="form-control">
                <option value="all" <?= ($status ?? 'all') === 'all' ? 'selected' : '' ?>>All Status</option>
                <option value="received" <?= ($status ?? '') === 'received' ? 'selected' : '' ?>>Received</option>
                <option value="pending" <?= ($status ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="overdue" <?= ($status ?? '') === 'overdue' ? 'selected' : '' ?>>Overdue</option>
                <option value="upcoming" <?= ($status ?? '') === 'upcoming' ? 'selected' : '' ?>>Upcoming</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-bold text-uppercase">Period</label>
            <select name="date_range" class="form-control">
                <?php foreach (['today' => 'Today', 'week' => 'This Week', 'month' => 'This Month', 'quarter' => 'This Quarter', 'year' => 'This Year'] as $key => $label): ?>
                    <option value="<?= $key ?>" <?= ($dateRange ?? 'month') === $key ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary flex-fill">
                <i class="fas fa-search"></i> Apply
            </button>
            <a href="<?= base_url('manager/income') ?>" class="btn btn-outline flex-fill">
                <i class="fas fa-redo"></i> Reset
            </a>
            <button type="button" class="btn btn-success flex-fill" onclick="openIncomeModal()">
                <i class="fas fa-plus"></i> Add Income
            </button>
        </div>
    </form>
</div>

<div class="table-container">
    <div class="table-header">
        <div class="table-title">Income Records</div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Client / Source</th>
                    <th>Company</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (! $incomes): ?>
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="fas fa-funnel-dollar fa-2x mb-3 d-block"></i>
                            No income records found.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($incomes as $income): ?>
                        <tr>
                            <td>
                                <strong><?= ! empty($income['income_date']) ? date('d M Y', strtotime($income['income_date'])) : 'N/A' ?></strong>
                                <div class="search-match">INC-<?= $income['id'] ?></div>
                            </td>
                            <td>
                                <strong><?= esc($income['party_name'] ?? 'N/A') ?></strong>
                                <?php if (! empty($income['notes'])): ?>
                                    <div class="search-match"><?= esc($income['notes']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td><?= esc($income['company_name'] ?? 'N/A') ?></td>
                            <td>
                                <strong>Rs. <?= number_format((float) ($income['amount'] ?? 0), 2) ?></strong>
                                <?php if (! empty($income['actual_amount']) && (float) $income['actual_amount'] !== (float) $income['amount']): ?>
                                    <div class="search-match">Base: Rs. <?= number_format((float) $income['actual_amount'], 2) ?></div>
                                <?php endif; ?>
                            </td>
                            <td><span class="status <?= esc($income['status']) ?>"><?= ucfirst(esc($income['status'])) ?></span></td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn btn-icon btn-outline btn-sm" type="button" onclick="editIncome(<?= $income['id'] ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <?php if (in_array($income['status'], ['pending', 'overdue'], true)): ?>
                                        <button class="btn btn-icon btn-success btn-sm" type="button" onclick="settleIncome(<?= $income['id'] ?>)">
                                            <i class="fas fa-check-double"></i>
                                        </button>
                                    <?php endif; ?>
                                    <button class="btn btn-icon btn-danger btn-sm" type="button" onclick="deleteIncome(<?= $income['id'] ?>)">
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
            <?= $pager ? $pager->getTotal() : count($incomes) ?> entries
        </div>
        <div class="pagination-links"><?= $pager ? $pager->links() : '' ?></div>
    </div>
</div>

<div class="modal" id="incomeModal">
    <div class="modal-content">
        <div class="modal-header">
            <div class="modal-title" id="incomeModalTitle">Add Income</div>
            <button type="button" class="close-modal" onclick="closeIncomeModal()">&times;</button>
        </div>
        <form id="incomeForm">
            <?= csrf_field() ?>
            <input type="hidden" id="income_id" name="id">
            <div class="p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Client / Source <span class="required">*</span></label>
                        <input type="text" id="client_name" name="client_name" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Company <span class="required">*</span></label>
                        <select id="income_company_id" name="company_id" class="form-control" required>
                            <option value="">Select Company</option>
                            <?php foreach ($companies as $company): ?>
                                <option value="<?= $company['id'] ?>"><?= esc($company['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Base Amount <span class="required">*</span></label>
                        <input type="number" id="income_amount" name="amount" class="form-control" step="0.01" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Income Date <span class="required">*</span></label>
                        <input type="date" id="income_date" name="income_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Status <span class="required">*</span></label>
                        <select id="income_status" name="status" class="form-control" required>
                            <option value="pending">Pending</option>
                            <option value="received">Received</option>
                            <option value="overdue">Overdue</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded-3" style="background:#f8fafc; border:1px dashed #e2e8f0;">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" id="income_apply_gst" name="apply_gst" value="1">
                                <label class="form-check-label fw-bold" for="income_apply_gst">Apply GST</label>
                            </div>
                            <div id="income_gst_fields" class="row g-2" style="display:none;">
                                <div class="col-6">
                                    <input type="number" id="income_gst_percentage" name="gst_percentage" class="form-control" value="18" placeholder="GST %">
                                </div>
                                <div class="col-6">
                                    <input type="number" id="income_gst_amount" name="gst_amount" class="form-control" readonly placeholder="GST Amount">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded-3" style="background:#f8fafc; border:1px dashed #e2e8f0;">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" id="income_apply_tds" name="apply_tds" value="1">
                                <label class="form-check-label fw-bold" for="income_apply_tds">Apply TDS</label>
                            </div>
                            <div id="income_tds_fields" class="row g-2" style="display:none;">
                                <div class="col-6">
                                    <input type="number" id="income_tds_percentage" name="tds_percentage" class="form-control" value="10" placeholder="TDS %">
                                </div>
                                <div class="col-6">
                                    <input type="number" id="income_tds_amount" name="tds_amount" class="form-control" readonly placeholder="TDS Amount">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Grand Total <span class="required">*</span></label>
                        <input type="number" id="income_grand_total" name="grand_total" class="form-control" readonly required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Notes</label>
                        <textarea id="income_notes" name="notes" class="form-control" rows="3"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer px-4 pb-4">
                <button type="button" class="btn btn-outline" onclick="closeIncomeModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" id="saveIncomeBtn">
                    <i class="fas fa-save"></i> Save Income
                </button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    const incomeBaseUrl = '<?= base_url('manager/income') ?>';
    const incomeUpdateBaseUrl = '<?= base_url('manager/income/update') ?>';
    const incomePaginationQuery = <?= json_encode($paginationQuery) ?>;

    function recalculateIncomeTotals() {
        const base = parseFloat($('#income_amount').val()) || 0;
        const gstEnabled = $('#income_apply_gst').is(':checked');
        const tdsEnabled = $('#income_apply_tds').is(':checked');
        const gstRate = parseFloat($('#income_gst_percentage').val()) || 0;
        const tdsRate = parseFloat($('#income_tds_percentage').val()) || 0;
        const gstAmount = gstEnabled ? (base * gstRate) / 100 : 0;
        const tdsAmount = tdsEnabled ? (base * tdsRate) / 100 : 0;

        $('#income_gst_fields').toggle(gstEnabled);
        $('#income_tds_fields').toggle(tdsEnabled);
        $('#income_gst_amount').val(gstEnabled ? gstAmount.toFixed(2) : '');
        $('#income_tds_amount').val(tdsEnabled ? tdsAmount.toFixed(2) : '');
        $('#income_grand_total').val((base + gstAmount - tdsAmount).toFixed(2));
    }

    $('#income_apply_gst, #income_apply_tds, #income_amount, #income_gst_percentage, #income_tds_percentage').on('change input', recalculateIncomeTotals);

    function openIncomeModal() {
        $('#incomeModalTitle').text('Add Income');
        $('#incomeForm')[0].reset();
        $('#income_id').val('');
        $('#income_date').val('<?= date('Y-m-d') ?>');
        $('#income_gst_fields, #income_tds_fields').hide();
        $('#saveIncomeBtn').html('<i class="fas fa-save"></i> Save Income');
        $('#incomeModal').css('display', 'flex');
        $('body').css('overflow', 'hidden');
    }

    function closeIncomeModal() {
        $('#incomeModal').hide();
        $('body').css('overflow', '');
    }

    function editIncome(id) {
        fetch(`${incomeBaseUrl}/${id}/edit`)
            .then((response) => response.json())
            .then((data) => {
                if (!data.success) {
                    throw new Error(data.message || 'Unable to load income');
                }

                const income = data.income;
                $('#incomeModalTitle').text('Edit Income');
                $('#income_id').val(income.id);
                $('#client_name').val(income.client_name || '');
                $('#income_company_id').val(income.company_id || '');
                $('#income_amount').val(income.actual_amount || income.planned_amount || '');
                $('#income_status').val(income.status || 'pending');
                $('#income_date').val(income.income_date || '<?= date('Y-m-d') ?>');
                $('#income_notes').val(income.notes || '');
                $('#income_apply_gst').prop('checked', Number(income.gst_amount || 0) > 0);
                $('#income_apply_tds').prop('checked', Number(income.tds_amount || 0) > 0);
                $('#income_gst_percentage').val(income.gst_percentage || 18);
                $('#income_gst_amount').val(income.gst_amount || '');
                $('#income_tds_percentage').val(income.tds_percentage || 10);
                $('#income_tds_amount').val(income.tds_amount || '');
                $('#income_grand_total').val(income.grand_total || income.planned_amount || '');
                recalculateIncomeTotals();
                $('#saveIncomeBtn').html('<i class="fas fa-save"></i> Update Income');
                $('#incomeModal').css('display', 'flex');
                $('body').css('overflow', 'hidden');
            })
            .catch((error) => Swal.fire('Error', error.message, 'error'));
    }

    $('#incomeForm').on('submit', function (event) {
        event.preventDefault();
        const incomeId = $('#income_id').val();
        const url = incomeId ? `${incomeUpdateBaseUrl}/${incomeId}` : incomeBaseUrl;
        const button = $('#saveIncomeBtn');

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
                    throw new Error(Object.values(data.messages || data).join('\n') || 'Failed to save income');
                }
                return data;
            })
            .then((data) => {
                Swal.fire('Success', data.message, 'success').then(() => window.location.reload());
            })
            .catch((error) => {
                button.prop('disabled', false).html('<i class="fas fa-save"></i> Save Income');
                Swal.fire('Error', error.message, 'error');
            });
    });

    function settleIncome(id) {
        Swal.fire({
            title: 'Mark as received?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            confirmButtonText: 'Mark Received',
        }).then((result) => {
            if (!result.isConfirmed) {
                return;
            }

            fetch(`${incomeBaseUrl}/${id}/settle`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
            })
                .then((response) => response.json())
                .then((data) => {
                    if (!data.success) {
                        throw new Error(data.message || 'Failed to settle income');
                    }
                    Swal.fire('Updated', data.message, 'success').then(() => window.location.reload());
                })
                .catch((error) => Swal.fire('Error', error.message, 'error'));
        });
    }

    function deleteIncome(id) {
        Swal.fire({
            title: 'Delete income record?',
            text: 'This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'Delete',
        }).then((result) => {
            if (!result.isConfirmed) {
                return;
            }

            fetch(`${incomeBaseUrl}/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
            })
                .then(async (response) => {
                    const data = await response.json();
                    if (!response.ok) {
                        throw new Error(data.message || 'Failed to delete income');
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
        if ($(event.target).is('#incomeModal')) {
            closeIncomeModal();
        }
    });

    document.querySelectorAll('.pagination-links a').forEach((link) => {
        const url = new URL(link.href);
        Object.entries(incomePaginationQuery).forEach(([key, value]) => {
            url.searchParams.set(key, value);
        });
        link.href = url.toString();
    });
</script>
<?= $this->endSection() ?>
