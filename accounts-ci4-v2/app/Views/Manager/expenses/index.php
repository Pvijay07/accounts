<?= $this->extend('layouts/manager') ?>
<?= $this->section('title') ?>Expense Management<?= $this->endSection() ?>
<?= $this->section('page_title') ?>Expense Management<?= $this->endSection() ?>

<?php
$paginationQuery = array_filter([
    'company' => $companyId ?? '',
    'category' => $categoryId ?? '',
    'status' => $status ?? 'all',
    'type' => $type ?? 'all',
    'date_range' => $dateRange ?? 'month',
], static fn ($value) => $value !== '' && $value !== null);
?>

<?= $this->section('content') ?>
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card">
            <div class="d-flex justify-content-between align-items-end">
                <div>
                    <div class="text-muted small fw-bold text-uppercase mb-1">Total Expenses</div>
                    <h3 class="fw-bold mb-0">Rs. <?= number_format((float) ($totalPayments ?? 0), 2) ?></h3>
                    <small class="text-muted"><?= (int) ($totalItems ?? 0) ?> entries</small>
                </div>
                <i class="fas fa-money-bill-wave fa-2x text-primary"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="d-flex justify-content-between align-items-end">
                <div>
                    <div class="text-muted small fw-bold text-uppercase mb-1">Paid</div>
                    <h3 class="fw-bold mb-0 text-success">Rs. <?= number_format((float) ($paidAmount ?? 0), 2) ?></h3>
                    <small class="text-muted"><?= (int) ($paidCount ?? 0) ?> items</small>
                </div>
                <i class="fas fa-check-circle fa-2x text-success"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="d-flex justify-content-between align-items-end">
                <div>
                    <div class="text-muted small fw-bold text-uppercase mb-1">Pending</div>
                    <h3 class="fw-bold mb-0 text-warning">Rs. <?= number_format((float) ($pendingAmount ?? 0), 2) ?></h3>
                    <small class="text-muted"><?= (int) ($pendingCount ?? 0) ?> items</small>
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
                    <h3 class="fw-bold mb-0 text-danger">Rs. <?= number_format((float) ($overdueAmount ?? 0), 2) ?></h3>
                    <small class="text-muted"><?= (int) ($overdueCount ?? 0) ?> items</small>
                </div>
                <i class="fas fa-exclamation-circle fa-2x text-danger"></i>
            </div>
        </div>
    </div>
</div>

<div class="filter-section">
    <form class="row g-3 align-items-end" method="get" action="<?= base_url('manager/expenses') ?>">
        <div class="col-md-2">
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
                <option value="all">All Categories</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= $category['id'] ?>" <?= (string) ($categoryId ?? '') === (string) $category['id'] ? 'selected' : '' ?>>
                        <?= esc($category['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-bold text-uppercase">Type</label>
            <select name="type" class="form-control">
                <option value="all" <?= ($type ?? 'all') === 'all' ? 'selected' : '' ?>>All Types</option>
                <option value="standard" <?= ($type ?? '') === 'standard' ? 'selected' : '' ?>>Standard</option>
                <option value="non-standard" <?= ($type ?? '') === 'non-standard' ? 'selected' : '' ?>>Non-standard</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-bold text-uppercase">Status</label>
            <select name="status" class="form-control">
                <option value="all" <?= ($status ?? 'all') === 'all' ? 'selected' : '' ?>>All Status</option>
                <option value="paid" <?= ($status ?? '') === 'paid' ? 'selected' : '' ?>>Paid</option>
                <option value="pending" <?= ($status ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="upcoming" <?= ($status ?? '') === 'upcoming' ? 'selected' : '' ?>>Upcoming</option>
                <option value="overdue" <?= ($status ?? '') === 'overdue' ? 'selected' : '' ?>>Overdue</option>
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
        <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary flex-fill">
                <i class="fas fa-search"></i> Apply
            </button>
            <a href="<?= base_url('manager/expenses') ?>" class="btn btn-outline flex-fill">
                <i class="fas fa-redo"></i> Reset
            </a>
        </div>
    </form>
</div>

<div class="table-container">
    <div class="table-header">
        <div class="table-title">All Payments</div>
        <div class="table-actions">
            <button class="btn btn-success" type="button" onclick="openExpenseModal()">
                <i class="fas fa-plus"></i> Add Non-standard Expense
            </button>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Expense</th>
                    <th>Company</th>
                    <th>Category</th>
                    <th>Type</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (! $allExpenses): ?>
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="fas fa-receipt fa-2x mb-3 d-block"></i>
                            No expenses found.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($allExpenses as $expense): ?>
                        <tr>
                            <td>
                                <strong><?= esc($expense['expense_name']) ?></strong>
                                <?php if (! empty($expense['party_name'])): ?>
                                    <div class="search-match"><?= esc($expense['party_name']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td><?= esc($expense['company_name']) ?></td>
                            <td><?= esc($expense['category_name']) ?></td>
                            <td>
                                <span class="badge <?= ($expense['source'] ?? '') === 'standard' ? 'bg-primary text-white' : 'bg-warning text-dark' ?>">
                                    <?= ($expense['source'] ?? '') === 'standard' ? 'Standard' : 'Non-standard' ?>
                                </span>
                            </td>
                            <td>
                                <strong>Rs. <?= number_format((float) ($expense['planned_amount'] ?? 0), 2) ?></strong>
                                <?php if (! empty($expense['actual_amount']) && (float) $expense['actual_amount'] !== (float) $expense['planned_amount']): ?>
                                    <div class="search-match">Base: Rs. <?= number_format((float) $expense['actual_amount'], 2) ?></div>
                                <?php endif; ?>
                            </td>
                            <td><span class="status <?= esc($expense['status']) ?>"><?= ucfirst(esc($expense['status'])) ?></span></td>
                            <td><?= ! empty($expense['created_at']) ? date('d M Y', strtotime($expense['created_at'])) : 'N/A' ?></td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn btn-icon btn-outline btn-sm" type="button" onclick="editExpense(<?= $expense['id'] ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <?php if (($expense['status'] ?? '') !== 'paid'): ?>
                                        <button class="btn btn-icon btn-success btn-sm" type="button" onclick="markPaid(<?= $expense['id'] ?>)">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    <?php endif; ?>
                                    <?php if (! empty($expense['is_split']) || ! empty($expense['parent_id'])): ?>
                                        <button class="btn btn-icon btn-outline btn-sm" type="button" onclick="viewSplitHistory(<?= $expense['id'] ?>)">
                                            <i class="fas fa-sitemap"></i>
                                        </button>
                                    <?php endif; ?>
                                    <button class="btn btn-icon btn-danger btn-sm" type="button" onclick="deleteExpense(<?= $expense['id'] ?>)">
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
            <?= $pager ? $pager->getTotal() : count($allExpenses) ?> entries
        </div>
        <div class="pagination-links"><?= $pager ? $pager->links() : '' ?></div>
    </div>
</div>

<div class="modal" id="expenseModal">
    <div class="modal-content">
        <div class="modal-header">
            <div class="modal-title" id="expenseModalTitle">Add Expense</div>
            <button type="button" class="close-modal" onclick="closeExpenseModal()">&times;</button>
        </div>
        <form id="expenseForm" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <input type="hidden" id="expense_id" name="id">
            <div class="p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Expense Name <span class="required">*</span></label>
                        <input type="text" id="expense_name" name="expense_name" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Company <span class="required">*</span></label>
                        <select id="company_id" name="company_id" class="form-control" required>
                            <option value="">Select Company</option>
                            <?php foreach ($companies as $company): ?>
                                <option value="<?= $company['id'] ?>"><?= esc($company['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Category <span class="required">*</span></label>
                        <select id="category_id" name="category_id" class="form-control" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category['id'] ?>"><?= esc($category['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Vendor / Party</label>
                        <input type="text" id="party_name" name="party_name" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Base Amount <span class="required">*</span></label>
                        <input type="number" id="actual_amount" name="actual_amount" class="form-control" step="0.01" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Status <span class="required">*</span></label>
                        <select id="expense_status" name="status" class="form-control" required>
                            <option value="pending">Pending</option>
                            <option value="paid">Paid</option>
                            <option value="upcoming">Upcoming</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Payment Mode</label>
                        <select id="payment_mode" name="payment_mode" class="form-control">
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="upi">UPI</option>
                            <option value="cheque">Cheque</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded-3" style="background:#f8fafc; border:1px dashed #e2e8f0;">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" id="apply_gst" name="apply_gst" value="1">
                                <label class="form-check-label fw-bold" for="apply_gst">Apply GST</label>
                            </div>
                            <div id="gst_fields" class="row g-2" style="display:none;">
                                <div class="col-6">
                                    <input type="number" id="gst_percentage" name="gst_percentage" class="form-control" value="18" placeholder="GST %">
                                </div>
                                <div class="col-6">
                                    <input type="number" id="gst_amount" name="gst_amount" class="form-control" readonly placeholder="GST Amount">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded-3" style="background:#f8fafc; border:1px dashed #e2e8f0;">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" id="apply_tds" name="apply_tds" value="1">
                                <label class="form-check-label fw-bold" for="apply_tds">Apply TDS</label>
                            </div>
                            <div id="tds_fields" class="row g-2" style="display:none;">
                                <div class="col-6">
                                    <input type="number" id="tds_percentage" name="tds_percentage" class="form-control" value="10" placeholder="TDS %">
                                </div>
                                <div class="col-6">
                                    <input type="number" id="tds_amount" name="tds_amount" class="form-control" readonly placeholder="TDS Amount">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Grand Total <span class="required">*</span></label>
                        <input type="number" id="grand_total" name="grand_total" class="form-control" readonly required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Receipts</label>
                        <input type="file" name="receipts[]" class="form-control" multiple>
                    </div>
                </div>
            </div>
            <div class="modal-footer px-4 pb-4">
                <button type="button" class="btn btn-outline" onclick="closeExpenseModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" id="saveExpenseBtn">
                    <i class="fas fa-save"></i> Save Expense
                </button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    const expenseBaseUrl = '<?= base_url('manager/expenses') ?>';
    const expenseUpdateBaseUrl = '<?= base_url('manager/expenses/update') ?>';
    const expensePaginationQuery = <?= json_encode($paginationQuery) ?>;

    function recalculateExpenseTotals() {
        const base = parseFloat($('#actual_amount').val()) || 0;
        const gstEnabled = $('#apply_gst').is(':checked');
        const tdsEnabled = $('#apply_tds').is(':checked');
        const gstRate = parseFloat($('#gst_percentage').val()) || 0;
        const tdsRate = parseFloat($('#tds_percentage').val()) || 0;
        const gstAmount = gstEnabled ? (base * gstRate) / 100 : 0;
        const tdsAmount = tdsEnabled ? (base * tdsRate) / 100 : 0;

        $('#gst_fields').toggle(gstEnabled);
        $('#tds_fields').toggle(tdsEnabled);
        $('#gst_amount').val(gstEnabled ? gstAmount.toFixed(2) : '');
        $('#tds_amount').val(tdsEnabled ? tdsAmount.toFixed(2) : '');
        $('#grand_total').val((base + gstAmount - tdsAmount).toFixed(2));
    }

    $('#apply_gst, #apply_tds, #actual_amount, #gst_percentage, #tds_percentage').on('change input', recalculateExpenseTotals);

    function openExpenseModal() {
        $('#expenseModalTitle').text('Add Expense');
        $('#expenseForm')[0].reset();
        $('#expense_id').val('');
        $('#gst_fields, #tds_fields').hide();
        $('#saveExpenseBtn').html('<i class="fas fa-save"></i> Save Expense');
        $('#expenseModal').css('display', 'flex');
        $('body').css('overflow', 'hidden');
    }

    function closeExpenseModal() {
        $('#expenseModal').hide();
        $('body').css('overflow', '');
    }

    function editExpense(id) {
        fetch(`${expenseBaseUrl}/${id}/edit`)
            .then((response) => response.json())
            .then((data) => {
                if (!data.success) {
                    throw new Error(data.message || 'Unable to load expense');
                }

                const expense = data.expense;
                $('#expenseModalTitle').text('Edit Expense');
                $('#expense_id').val(expense.id);
                $('#expense_name').val(expense.expense_name || '');
                $('#company_id').val(expense.company_id || '');
                $('#category_id').val(expense.category_id || '');
                $('#party_name').val(expense.party_name || '');
                $('#actual_amount').val(expense.actual_amount || '');
                $('#expense_status').val(expense.status || 'pending');
                $('#payment_mode').val(expense.payment_mode || 'cash');
                $('#apply_gst').prop('checked', Number(expense.gst_amount || 0) > 0);
                $('#apply_tds').prop('checked', Number(expense.tds_amount || 0) > 0);
                $('#gst_percentage').val(expense.gst_percentage || 18);
                $('#gst_amount').val(expense.gst_amount || '');
                $('#tds_percentage').val(expense.tds_percentage || 10);
                $('#tds_amount').val(expense.tds_amount || '');
                $('#grand_total').val(expense.planned_amount || '');
                recalculateExpenseTotals();
                $('#saveExpenseBtn').html('<i class="fas fa-save"></i> Update Expense');
                $('#expenseModal').css('display', 'flex');
                $('body').css('overflow', 'hidden');
            })
            .catch((error) => Swal.fire('Error', error.message, 'error'));
    }

    $('#expenseForm').on('submit', function (event) {
        event.preventDefault();
        const expenseId = $('#expense_id').val();
        const url = expenseId ? `${expenseUpdateBaseUrl}/${expenseId}` : expenseBaseUrl;
        const button = $('#saveExpenseBtn');
        const formData = new FormData(this);

        button.prop('disabled', true).html('<span class="spinner"></span> Saving...');

        fetch(url, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        })
            .then(async (response) => {
                const data = await response.json();
                if (!response.ok) {
                    throw new Error(Object.values(data.messages || data).join('\n') || 'Failed to save expense');
                }
                return data;
            })
            .then((data) => {
                Swal.fire('Success', data.message, 'success').then(() => window.location.reload());
            })
            .catch((error) => {
                button.prop('disabled', false).html('<i class="fas fa-save"></i> Save Expense');
                Swal.fire('Error', error.message, 'error');
            });
    });

    function markPaid(id) {
        Swal.fire({
            title: 'Mark expense as paid?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Mark Paid',
            confirmButtonColor: '#10b981',
        }).then((result) => {
            if (!result.isConfirmed) {
                return;
            }

            fetch(`${expenseBaseUrl}/${id}/mark-paid`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
            })
                .then((response) => response.json())
                .then((data) => {
                    if (!data.success) {
                        throw new Error(data.message || 'Failed to mark paid');
                    }
                    Swal.fire('Updated', data.message, 'success').then(() => window.location.reload());
                })
                .catch((error) => Swal.fire('Error', error.message, 'error'));
        });
    }

    function deleteExpense(id) {
        Swal.fire({
            title: 'Delete expense?',
            text: 'This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'Delete',
        }).then((result) => {
            if (!result.isConfirmed) {
                return;
            }

            fetch(`${expenseBaseUrl}/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
            })
                .then(async (response) => {
                    const data = await response.json();
                    if (!response.ok) {
                        throw new Error(data.message || 'Failed to delete expense');
                    }
                    return data;
                })
                .then((data) => {
                    Swal.fire('Deleted', data.message, 'success').then(() => window.location.reload());
                })
                .catch((error) => Swal.fire('Error', error.message, 'error'));
        });
    }

    function viewSplitHistory(id) {
        fetch(`${expenseBaseUrl}/${id}/split-history`)
            .then((response) => response.json())
            .then((data) => {
                if (!data.success) {
                    throw new Error(data.message || 'Unable to load split history');
                }

                const rows = (data.children || []).map((child) => `
                    <tr>
                        <td>${child.expense_name || ''}</td>
                        <td>Rs. ${Number(child.planned_amount || 0).toFixed(2)}</td>
                        <td>${child.status || ''}</td>
                        <td>${child.created_at ? new Date(child.created_at).toLocaleDateString() : '-'}</td>
                    </tr>
                `).join('');

                Swal.fire({
                    title: 'Split History',
                    width: 760,
                    html: rows
                        ? `<div class="table-responsive"><table class="table table-sm mb-0"><thead><tr><th>Name</th><th>Amount</th><th>Status</th><th>Created</th></tr></thead><tbody>${rows}</tbody></table></div>`
                        : '<p class="text-muted mb-0">No split history available.</p>',
                });
            })
            .catch((error) => Swal.fire('Error', error.message, 'error'));
    }

    $(window).on('click', function (event) {
        if ($(event.target).is('#expenseModal')) {
            closeExpenseModal();
        }
    });

    document.querySelectorAll('.pagination-links a').forEach((link) => {
        const url = new URL(link.href);
        Object.entries(expensePaginationQuery).forEach(([key, value]) => {
            url.searchParams.set(key, value);
        });
        link.href = url.toString();
    });
</script>
<?= $this->endSection() ?>
