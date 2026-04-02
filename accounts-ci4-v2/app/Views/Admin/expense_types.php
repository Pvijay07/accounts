<?= $this->extend('layouts/admin') ?>
<?= $this->section('title') ?>Expense Types<?= $this->endSection() ?>
<?= $this->section('page_title') ?>Admin Panel<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div id="expense-types" class="page">
    <div class="table-container">
        <div class="table-header">
            <div class="table-title">Expense Types</div>
            <div class="table-actions">
                <button class="btn btn-primary" onclick="openExpenseTypeModal()">
                    <i class="fas fa-plus"></i> Add Expense Type
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Amount Type</th>
                        <th>Default Amount (₹)</th>
                        <th>Lifecycle</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if(empty($expenseTypes)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <i class="fas fa-list-alt fa-2x text-muted mb-3 d-block"></i>
                            <p class="text-muted">No expense types defined yet</p>
                        </td>
                    </tr>
                <?php else: foreach($expenseTypes as $t): ?>
                    <tr>
                        <td><strong><?= esc($t['name']) ?></strong></td>
                        <td>
                            <span class="badge" style="background: #f1f5f9; color: #475569;">
                                <?= esc($t['category'] ?? 'Uncategorized') ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge" style="background: #e0f2fe; color: #0284c7;">
                                <?= ucfirst($t['amount_type'] ?? 'fixed') ?>
                            </span>
                        </td>
                        <td class="fw-bold">₹<?= number_format($t['default_amount'] ?? 0, 2) ?></td>
                        <td>
                            <?= ($t['is_recurring'] ?? 0) ?
                                '<span class="status active"><i class="fas fa-sync-alt me-1"></i>Recurring</span>' :
                                '<span class="status" style="background: #f1f5f9; color: #64748b;"><i class="fas fa-bolt me-1"></i>One-time</span>'
                            ?>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn btn-icon btn-sm btn-outline edit-type-btn" data-id="<?= $t['id'] ?>" onclick="editExpenseType(<?= $t['id'] ?>, this)" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-icon btn-sm btn-danger" onclick="deleteExpenseType(<?= $t['id'] ?>)" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add/Edit Expense Type Modal -->
<div class="modal" id="expenseTypeModal">
    <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header">
            <div class="modal-title" id="typeModalTitle">Add Expense Type</div>
            <button type="button" class="close-modal" onclick="closeExpenseTypeModal()">&times;</button>
        </div>
        <form id="expenseTypeForm">
            <?= csrf_field() ?>
            <input type="hidden" name="id" id="typeId">
            <div class="p-4">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Name <span class="required">*</span></label>
                        <input type="text" name="name" id="typeName" class="form-control" placeholder="e.g. Office Rent" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Category <span class="required">*</span></label>
                        <input type="text" name="category" id="typeCategory" class="form-control" placeholder="Category name" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Amount Type</label>
                        <select name="amount_type" id="typeAmountType" class="form-control">
                            <option value="fixed">Fixed</option>
                            <option value="variable">Variable</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Default Amount (₹)</label>
                        <input type="number" name="default_amount" id="typeAmount" class="form-control" step="0.01" value="0">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Reminder Days</label>
                        <input type="number" name="reminder_days" id="typeReminder" class="form-control" value="5">
                    </div>
                    <div class="col-md-6 d-flex align-items-center">
                        <div class="form-check form-switch mt-4">
                            <input type="checkbox" name="is_recurring" class="form-check-input" id="isRec" value="1">
                            <label class="form-check-label fw-bold" for="isRec">Recurring</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer px-4 pb-4">
                <button type="button" class="btn btn-outline" onclick="closeExpenseTypeModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" id="saveTypeBtn">Save Expense Type</button>
            </div>
        </form>
    </div>
</div>

<script>
    const baseUrl = '<?= base_url('admin/expense-types') ?>';
    const updateUrl = '<?= base_url('admin/expense-types/update') ?>';

    function openExpenseTypeModal() {
        document.getElementById('expenseTypeForm').reset();
        document.getElementById('typeId').value = '';
        document.getElementById('typeModalTitle').innerText = 'Add Expense Type';
        document.getElementById('expenseTypeModal').style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function closeExpenseTypeModal() {
        document.getElementById('expenseTypeModal').style.display = 'none';
        document.body.style.overflow = '';
    }

    function editExpenseType(id, btnElement) {
        const originalHtml = btnElement.innerHTML;
        btnElement.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        btnElement.disabled = true;

        fetch(`${baseUrl}/${id}/edit`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.json())
        .then(data => {
            btnElement.innerHTML = originalHtml;
            btnElement.disabled = false;
            if (data.success && data.expenseType) {
                const t = data.expenseType;
                document.getElementById('typeId').value = t.id;
                document.getElementById('typeName').value = t.name;
                document.getElementById('typeCategory').value = t.category;
                document.getElementById('typeAmountType').value = t.amount_type;
                document.getElementById('typeAmount').value = t.default_amount;
                document.getElementById('typeReminder').value = t.reminder_days;
                document.getElementById('isRec').checked = t.is_recurring == 1;
                document.getElementById('typeModalTitle').innerText = 'Edit Expense Type';
                document.getElementById('expenseTypeModal').style.display = 'flex';
                document.body.style.overflow = 'hidden';
            } else {
                Swal.fire('Error', 'Unable to fetch expense type.', 'error');
            }
        })
        .catch(error => {
            btnElement.innerHTML = originalHtml;
            btnElement.disabled = false;
            Swal.fire('Error', 'Failed to load data.', 'error');
        });
    }

    function deleteExpenseType(id) {
        Swal.fire({
            title: 'Delete Expense Type?',
            text: 'This will permanently remove this expense type.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'Yes, delete it'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`${baseUrl}/${id}`, {
                    method: 'DELETE',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => location.reload());
                    } else {
                        Swal.fire('Error', data.message || 'Failed to delete', 'error');
                    }
                });
            }
        });
    }

    document.getElementById('expenseTypeForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const id = document.getElementById('typeId').value;
        const url = id ? `${updateUrl}/${id}` : baseUrl;
        const btn = document.getElementById('saveTypeBtn');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner"></span> Saving...';

        fetch(url, {
            method: 'POST',
            body: new FormData(this),
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: data.message || 'Expense Type saved successfully',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => location.reload());
            } else {
                Swal.fire('Error', Object.values(data.messages || {}).join('\n') || 'Failed to save', 'error');
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        })
        .catch(err => {
            Swal.fire('Error', 'An unexpected error occurred.', 'error');
            btn.disabled = false;
            btn.innerHTML = originalText;
        });
    });

    window.addEventListener('click', function (event) {
        if (event.target === document.getElementById('expenseTypeModal')) {
            closeExpenseTypeModal();
        }
    });
</script>
<?= $this->endSection() ?>
