<?= $this->extend('layouts/admin') ?>
<?= $this->section('title') ?>Standard Expenses<?= $this->endSection() ?>
<?= $this->section('page_title') ?>Standard Expenses<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div id="standard-expenses" class="page">
    <div class="table-container">
        <div class="table-header">
            <div>
                <div class="table-title">Standard Expenses</div>
                <p class="text-muted small mb-0 mt-1">Manage recurring expense templates and generate monthly entries.</p>
            </div>
            <div class="table-actions">
                <button class="btn btn-primary" onclick="openAddTemplateModal()">
                    <i class="fas fa-plus"></i> Add Expense
                </button>
                <button class="btn btn-success" id="generate-expenses-btn">
                    <i class="fas fa-sync-alt"></i> Generate Monthly Expenses
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Expense Name</th>
                        <th>Company</th>
                        <th>Category</th>
                        <th>Amount</th>
                        <th>Frequency</th>
                        <th>Due Day</th>
                        <th>Tax</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (! $expenses): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="fas fa-money-bill-wave fa-2x mb-3 d-block"></i>
                                No standard expenses defined yet.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($expenses as $expense): ?>
                            <tr>
                                <td><strong><?= esc($expense['expense_name']) ?></strong></td>
                                <td><?= esc($expense['company_name'] ?? 'N/A') ?></td>
                                <td><?= esc($expense['category_name'] ?? 'N/A') ?></td>
                                <td class="fw-bold">Rs. <?= number_format((float) $expense['planned_amount'], 2) ?></td>
                                <td><span class="status active"><?= ucfirst(esc($expense['frequency'] ?? 'monthly')) ?></span></td>
                                <td><?= (int) ($expense['due_day'] ?? 1) ?></td>
                                <td><?= esc($expense['tax_type'] ?? 'None') ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn btn-icon btn-sm btn-outline" type="button" onclick="editTemplate(<?= $expense['id'] ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-icon btn-sm btn-danger" type="button" onclick="deleteTemplate(<?= $expense['id'] ?>)">
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
                <?= $pager ? $pager->getTotal() : count($expenses) ?> entries
            </div>
            <div class="pagination-links"><?= $pager ? $pager->links() : '' ?></div>
        </div>
    </div>
</div>

<div class="modal" id="addTemplateModal">
    <div class="modal-content" style="max-width: 800px;">
        <div class="modal-header">
            <div class="modal-title">Add Standard Expense</div>
            <button type="button" class="close-modal" onclick="closeAddTemplateModal()">&times;</button>
        </div>
        <form id="standardExpenseForm">
            <?= csrf_field() ?>
            <div class="p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Expense Name <span class="required">*</span></label>
                        <input type="text" name="expense_name" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Company <span class="required">*</span></label>
                        <select name="company_id" class="form-control" required>
                            <option value="">Select Company</option>
                            <?php foreach ($companies as $company): ?>
                                <option value="<?= $company['id'] ?>"><?= esc($company['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Category <span class="required">*</span></label>
                        <select name="category_id" class="form-control" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category['id'] ?>"><?= esc($category['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Planned Amount <span class="required">*</span></label>
                        <input type="number" name="planned_amount" id="planned_amount" class="form-control" step="0.01" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Actual Amount</label>
                        <input type="number" name="actual_amount" class="form-control" step="0.01">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Frequency</label>
                        <select name="frequency" class="form-control">
                            <option value="monthly">Monthly</option>
                            <option value="quarterly">Quarterly</option>
                            <option value="yearly">Yearly</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Due Day</label>
                        <input type="number" name="due_day" class="form-control" min="1" max="31" value="1">
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded-3" style="background:#f8fafc; border:1px dashed #e2e8f0;">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" id="applyGst" name="apply_gst" value="1">
                                <label class="form-check-label fw-bold" for="applyGst">Apply GST</label>
                            </div>
                            <div id="gstFields" class="row g-2" style="display:none;">
                                <div class="col-6">
                                    <input type="number" name="gst_percentage" class="form-control" value="18" placeholder="GST %">
                                </div>
                                <div class="col-6">
                                    <input type="number" name="gst_amount" class="form-control" placeholder="GST Amount" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded-3" style="background:#f8fafc; border:1px dashed #e2e8f0;">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" id="applyTds" name="apply_tds" value="1">
                                <label class="form-check-label fw-bold" for="applyTds">Apply TDS</label>
                            </div>
                            <div id="tdsFields" class="row g-2" style="display:none;">
                                <div class="col-6">
                                    <input type="number" name="tds_percentage" class="form-control" value="10" placeholder="TDS %">
                                </div>
                                <div class="col-6">
                                    <input type="number" name="tds_amount" class="form-control" placeholder="TDS Amount" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer px-4 pb-4">
                <button type="button" class="btn btn-outline" onclick="closeAddTemplateModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" id="saveTemplateBtn">
                    <i class="fas fa-save"></i> Save Expense
                </button>
            </div>
        </form>
    </div>
</div>

<div class="modal" id="editTemplateModal">
    <div class="modal-content">
        <div class="modal-header">
            <div class="modal-title">Edit Standard Expense</div>
            <button type="button" class="close-modal" onclick="closeEditTemplateModal()">&times;</button>
        </div>
        <form id="editTemplateForm">
            <?= csrf_field() ?>
            <input type="hidden" name="id" id="edit_id">
            <div class="p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Expense Name</label>
                        <input type="text" name="expense_name" id="edit_expense_name" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Frequency</label>
                        <select name="frequency" id="edit_frequency" class="form-control">
                            <option value="monthly">Monthly</option>
                            <option value="quarterly">Quarterly</option>
                            <option value="yearly">Yearly</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Planned Amount</label>
                        <input type="number" name="planned_amount" id="edit_planned_amount" class="form-control" step="0.01" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Due Day</label>
                        <input type="number" name="due_day" id="edit_due_day" class="form-control" min="1" max="31" required>
                    </div>
                </div>
            </div>
            <div class="modal-footer px-4 pb-4">
                <button type="button" class="btn btn-outline" onclick="closeEditTemplateModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" id="updateTemplateBtn">
                    <i class="fas fa-save"></i> Update Expense
                </button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    const standardExpenseBaseUrl = '<?= base_url('admin/standard-expenses') ?>';
    const standardExpenseUpdateUrl = '<?= base_url('admin/standard-expenses/update') ?>';

    function closeEditTemplateModal() {
        $('#editTemplateModal').hide();
        $('body').css('overflow', '');
    }

    function openAddTemplateModal() {
        $('#standardExpenseForm')[0].reset();
        $('#gstFields, #tdsFields').hide();
        $('#addTemplateModal').css('display', 'flex');
        $('body').css('overflow', 'hidden');
    }

    function closeAddTemplateModal() {
        $('#addTemplateModal').hide();
        $('body').css('overflow', '');
    }

    function editTemplate(id) {
        fetch(`${standardExpenseBaseUrl}/${id}`)
            .then((response) => response.json())
            .then((template) => {
                $('#edit_id').val(template.id);
                $('#edit_expense_name').val(template.expense_name);
                $('#edit_planned_amount').val(template.planned_amount);
                $('#edit_frequency').val(template.frequency);
                $('#edit_due_day').val(template.due_day);
                $('#editTemplateModal').css('display', 'flex');
                $('body').css('overflow', 'hidden');
            })
            .catch(() => Swal.fire('Error', 'Unable to load template.', 'error'));
    }

    function deleteTemplate(id) {
        Swal.fire({
            title: 'Delete standard expense?',
            text: 'This template will be permanently removed.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'Delete',
        }).then((result) => {
            if (!result.isConfirmed) {
                return;
            }

            fetch(`${standardExpenseBaseUrl}/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
            })
                .then((response) => response.json())
                .then((data) => {
                    if (!data.success) {
                        throw new Error(data.message || 'Failed to delete template');
                    }
                    Swal.fire('Deleted', data.message, 'success').then(() => window.location.reload());
                })
                .catch((error) => Swal.fire('Error', error.message, 'error'));
        });
    }

    function recalculateTaxes() {
        const base = parseFloat($('#planned_amount').val()) || 0;
        const gstEnabled = $('#applyGst').is(':checked');
        const tdsEnabled = $('#applyTds').is(':checked');
        const gstRate = parseFloat($('input[name="gst_percentage"]').val()) || 0;
        const tdsRate = parseFloat($('input[name="tds_percentage"]').val()) || 0;

        $('#gstFields').toggle(gstEnabled);
        $('#tdsFields').toggle(tdsEnabled);

        $('input[name="gst_amount"]').val(gstEnabled ? ((base * gstRate) / 100).toFixed(2) : '');
        $('input[name="tds_amount"]').val(tdsEnabled ? ((base * tdsRate) / 100).toFixed(2) : '');
    }

    $('#applyGst, #applyTds, #planned_amount, input[name="gst_percentage"], input[name="tds_percentage"]').on('change input', recalculateTaxes);

    $('#standardExpenseForm').on('submit', function (event) {
        event.preventDefault();
        const button = $('#saveTemplateBtn');
        button.prop('disabled', true).html('<span class="spinner"></span> Saving...');

        fetch(standardExpenseBaseUrl, {
            method: 'POST',
            body: new FormData(this),
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
                Swal.fire('Saved', data.message, 'success').then(() => window.location.reload());
            })
            .catch((error) => {
                button.prop('disabled', false).html('<i class="fas fa-save"></i> Save Expense');
                Swal.fire('Error', error.message, 'error');
            });
    });

    $('#editTemplateForm').on('submit', function (event) {
        event.preventDefault();
        const id = $('#edit_id').val();
        const button = $('#updateTemplateBtn');
        button.prop('disabled', true).html('<span class="spinner"></span> Updating...');

        fetch(`${standardExpenseUpdateUrl}/${id}`, {
            method: 'POST',
            body: new FormData(this),
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        })
            .then(async (response) => {
                const data = await response.json();
                if (!response.ok) {
                    throw new Error(Object.values(data.messages || data).join('\n') || 'Failed to update expense');
                }
                return data;
            })
            .then((data) => {
                Swal.fire('Updated', data.message, 'success').then(() => window.location.reload());
            })
            .catch((error) => {
                button.prop('disabled', false).html('<i class="fas fa-save"></i> Update Expense');
                Swal.fire('Error', error.message, 'error');
            });
    });

    $('#generate-expenses-btn').on('click', function () {
        const button = $(this);
        Swal.fire({
            title: 'Generate monthly expenses?',
            text: 'This will create this month\'s expense entries from all active templates.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            confirmButtonText: 'Generate',
        }).then((result) => {
            if (!result.isConfirmed) {
                return;
            }

            button.prop('disabled', true).html('<span class="spinner"></span> Generating...');
            fetch('<?= base_url('admin/generate-expenses') ?>', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
            })
                .then((response) => response.json())
                .then((data) => {
                    if (!data.success) {
                        throw new Error(data.message || 'Generation failed');
                    }
                    Swal.fire('Done', data.message, 'success').then(() => window.location.reload());
                })
                .catch((error) => {
                    button.prop('disabled', false).html('<i class="fas fa-sync-alt"></i> Generate Monthly Expenses');
                    Swal.fire('Error', error.message, 'error');
                });
        });
    });

    $(window).on('click', function (event) {
        if ($(event.target).is('#editTemplateModal')) {
            closeEditTemplateModal();
        }
        if ($(event.target).is('#addTemplateModal')) {
            closeAddTemplateModal();
        }
    });
</script>
<?= $this->endSection() ?>
