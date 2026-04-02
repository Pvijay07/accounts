<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Expense Categories<?= $this->endSection() ?>
<?= $this->section('page_title') ?>Admin Panel<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div id="categories" class="page">
    <div class="table-container">
        <div class="table-header">
            <div class="table-title">Expense Categories</div>
            <div class="table-actions">
                <div class="search-container">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" class="search-input" placeholder="Search categories..." id="catSearch" onkeyup="filterCategories()">
                </div>
                <button class="btn btn-primary" onclick="openCategoryModal()">
                    <i class="fas fa-plus"></i> Add Category
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover" id="categoriesTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Category Name</th>
                        <th>Type</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($categories)): ?>
                        <tr>
                            <td colspan="4" class="text-center py-5">
                                <i class="fas fa-tags fa-2x text-muted mb-3 d-block"></i>
                                <p class="text-muted">No categories found</p>
                            </td>
                        </tr>
                    <?php else: foreach ($categories as $cat): ?>
                        <tr>
                            <td><strong>CAT-<?= str_pad($cat['id'], 3, '0', STR_PAD_LEFT) ?></strong></td>
                            <td><strong><?= esc($cat['name']) ?></strong></td>
                            <td>
                                <span class="status <?= $cat['main_type'] === 'income' ? 'active' : 'admin' ?>">
                                    <?= ucfirst($cat['main_type']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn btn-icon btn-sm btn-outline"
                                        onclick="editCategory(<?= $cat['id'] ?>, '<?= esc(addslashes($cat['name'])) ?>', '<?= $cat['main_type'] ?>')" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-icon btn-sm btn-danger" onclick="deleteCategory(<?= $cat['id'] ?>)" title="Delete">
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

<!-- Add/Edit Category Modal -->
<div class="modal" id="categoryModal">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <div class="modal-title" id="catModalTitle">Add Category</div>
            <button type="button" class="close-modal" onclick="closeCategoryModal()">&times;</button>
        </div>
        <form id="categoryForm">
            <?= csrf_field() ?>
            <input type="hidden" name="id" id="catId">
            <div class="p-4">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Category Name <span class="required">*</span></label>
                        <input type="text" name="name" id="catName" class="form-control" placeholder="e.g. Office Supplies" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Type <span class="required">*</span></label>
                        <select name="main_type" id="catType" class="form-control" required>
                            <option value="expense">Expense</option>
                            <option value="income">Income</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer px-4 pb-4">
                <button type="button" class="btn btn-outline" onclick="closeCategoryModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" id="saveCategoryBtn">Save Category</button>
            </div>
        </form>
    </div>
</div>

<script>
    const baseUrl = '<?= base_url('admin/categories') ?>';
    const updateUrl = '<?= base_url('admin/categories/update') ?>';

    function openCategoryModal() {
        document.getElementById('categoryForm').reset();
        document.getElementById('catId').value = '';
        document.getElementById('catModalTitle').innerText = 'Add Category';
        document.getElementById('categoryModal').style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function closeCategoryModal() {
        document.getElementById('categoryModal').style.display = 'none';
        document.body.style.overflow = '';
    }

    function editCategory(id, name, type) {
        document.getElementById('catId').value = id;
        document.getElementById('catName').value = name;
        document.getElementById('catType').value = type;
        document.getElementById('catModalTitle').innerText = 'Edit Category';
        document.getElementById('categoryModal').style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    document.getElementById('categoryForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const id = document.getElementById('catId').value;
        const url = id ? `${updateUrl}/${id}` : baseUrl;
        const btn = document.getElementById('saveCategoryBtn');
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
                    text: data.message || 'Category saved successfully',
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

    function deleteCategory(id) {
        Swal.fire({
            title: 'Delete Category?',
            text: 'This will permanently remove this category.',
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

    function filterCategories() {
        const search = document.getElementById('catSearch').value.toLowerCase();
        document.querySelectorAll('#categoriesTable tbody tr').forEach(row => {
            row.style.display = row.textContent.toLowerCase().includes(search) ? '' : 'none';
        });
    }

    window.addEventListener('click', function (event) {
        if (event.target === document.getElementById('categoryModal')) {
            closeCategoryModal();
        }
    });
</script>
<?= $this->endSection() ?>
