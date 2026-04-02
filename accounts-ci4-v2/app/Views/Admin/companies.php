<?= $this->extend('layouts/admin') ?>
<?= $this->section('title') ?>Company Management<?= $this->endSection() ?>
<?= $this->section('page_title') ?>Company Management<?= $this->endSection() ?>

<?php
$filters = $filters ?? ['search' => '', 'status' => '', 'sort_by' => 'created_at', 'sort_order' => 'desc'];
$buildUrl = static function (array $changes = []) use ($filters): string {
    $query = array_merge($filters, $changes);
    $query = array_filter(
        $query,
        static fn ($value) => $value !== '' && $value !== null
    );
    $qs = http_build_query($query);
    return base_url('admin/companies') . ($qs ? '?' . $qs : '');
};
$paginationQuery = array_filter([
    'search' => $filters['search'],
    'status' => $filters['status'],
    'sort_by' => $filters['sort_by'],
    'sort_order' => $filters['sort_order'],
], static fn ($value) => $value !== '' && $value !== null);
?>

<?= $this->section('content') ?>
<div class="table-container">
    <div class="table-header">
        <div class="table-title">Company Management</div>
        <div class="table-actions flex-wrap">
            <form method="get" action="<?= base_url('admin/companies') ?>" class="search-container">
                <i class="fas fa-search search-icon"></i>
                <input
                    type="text"
                    name="search"
                    class="search-input"
                    placeholder="Search companies..."
                    value="<?= esc($filters['search']) ?>"
                >
                <input type="hidden" name="status" value="<?= esc($filters['status']) ?>">
                <input type="hidden" name="sort_by" value="<?= esc($filters['sort_by']) ?>">
                <input type="hidden" name="sort_order" value="<?= esc($filters['sort_order']) ?>">
            </form>
            <button class="btn btn-primary" type="button" onclick="openAddCompanyModal()">
                <i class="fas fa-plus"></i> Add Company
            </button>
        </div>
    </div>

    <div class="filter-container">
        <div class="filter-buttons">
            <a href="<?= $buildUrl(['status' => '', 'page' => null]) ?>" class="filter-btn <?= $filters['status'] === '' ? 'active' : '' ?>">
                All (<?= (int) ($counts['all'] ?? 0) ?>)
            </a>
            <a href="<?= $buildUrl(['status' => 'active', 'page' => null]) ?>" class="filter-btn <?= $filters['status'] === 'active' ? 'active' : '' ?>">
                Active (<?= (int) ($counts['active'] ?? 0) ?>)
            </a>
            <a href="<?= $buildUrl(['status' => 'inactive', 'page' => null]) ?>" class="filter-btn <?= $filters['status'] === 'inactive' ? 'active' : '' ?>">
                Inactive (<?= (int) ($counts['inactive'] ?? 0) ?>)
            </a>
        </div>

        <?php if ($filters['search'] || $filters['status']): ?>
            <div class="filter-results">
                <span>
                    <?= count($companies) ?> result(s) on this page
                    <?= $filters['search'] ? ' for "' . esc($filters['search']) . '"' : '' ?>
                </span>
                <a href="<?= base_url('admin/companies') ?>" class="clear-filters-btn">
                    <i class="fas fa-times"></i> Clear filters
                </a>
            </div>
        <?php endif; ?>
    </div>

    <table>
        <thead>
            <tr>
                <th>
                    <a href="<?= $buildUrl([
                        'sort_by' => 'name',
                        'sort_order' => $filters['sort_by'] === 'name' && $filters['sort_order'] === 'asc' ? 'desc' : 'asc',
                    ]) ?>" class="sortable-header">
                        Company Name
                        <?php if ($filters['sort_by'] === 'name'): ?>
                            <i class="fas fa-sort-<?= $filters['sort_order'] === 'asc' ? 'up' : 'down' ?>"></i>
                        <?php else: ?>
                            <i class="fas fa-sort"></i>
                        <?php endif; ?>
                    </a>
                </th>
                <th>Manager</th>
                <th>
                    <a href="<?= $buildUrl([
                        'sort_by' => 'status',
                        'sort_order' => $filters['sort_by'] === 'status' && $filters['sort_order'] === 'asc' ? 'desc' : 'asc',
                    ]) ?>" class="sortable-header">
                        Status
                        <?php if ($filters['sort_by'] === 'status'): ?>
                            <i class="fas fa-sort-<?= $filters['sort_order'] === 'asc' ? 'up' : 'down' ?>"></i>
                        <?php else: ?>
                            <i class="fas fa-sort"></i>
                        <?php endif; ?>
                    </a>
                </th>
                <th>
                    <a href="<?= $buildUrl([
                        'sort_by' => 'created_at',
                        'sort_order' => $filters['sort_by'] === 'created_at' && $filters['sort_order'] === 'asc' ? 'desc' : 'asc',
                    ]) ?>" class="sortable-header">
                        Created Date
                        <?php if ($filters['sort_by'] === 'created_at'): ?>
                            <i class="fas fa-sort-<?= $filters['sort_order'] === 'asc' ? 'up' : 'down' ?>"></i>
                        <?php else: ?>
                            <i class="fas fa-sort"></i>
                        <?php endif; ?>
                    </a>
                </th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (! $companies): ?>
                <tr>
                    <td colspan="5" class="text-center py-5 text-muted">
                        <i class="fas fa-building fa-2x mb-3 d-block"></i>
                        No companies found.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($companies as $company): ?>
                    <tr>
                        <td>
                            <strong><?= esc($company['name']) ?></strong>
                            <?php if (! empty($company['email'])): ?>
                                <div class="search-match"><?= esc($company['email']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td><?= esc($company['manager_name'] ?? 'Unassigned') ?></td>
                        <td>
                            <span class="status <?= $company['status'] === 'active' ? 'active' : 'inactive' ?>">
                                <?= ucfirst(esc($company['status'])) ?>
                            </span>
                        </td>
                        <td><?= ! empty($company['created_at']) ? date('d M Y', strtotime($company['created_at'])) : 'N/A' ?></td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn btn-outline btn-sm" type="button" onclick="editCompany(<?= $company['id'] ?>)">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="btn btn-danger btn-sm" type="button" onclick="deleteCompany(<?= $company['id'] ?>)">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="pagination-container">
        <div class="pagination-info">
            Showing <?= $pager ? ($pager->getCurrentPage() - 1) * $pager->getPerPage() + 1 : 0 ?> to
            <?= $pager ? min($pager->getCurrentPage() * $pager->getPerPage(), $pager->getTotal()) : 0 ?> of
            <?= $pager ? $pager->getTotal() : count($companies) ?> entries
        </div>
        <div class="pagination-links">
            <?= $pager ? $pager->links() : '' ?>
        </div>
    </div>
</div>

<div class="modal" id="companyModal">
    <div class="modal-content">
        <div class="modal-header">
            <div class="modal-title" id="companyModalTitle">Add New Company</div>
            <button type="button" class="close-modal" onclick="closeCompanyModal()">&times;</button>
        </div>
        <form id="companyForm" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <input type="hidden" id="companyId" name="id">
            <div class="p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Company Name <span class="required">*</span></label>
                        <input type="text" id="company_name" name="name" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" id="company_email" name="email" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Assigned Manager</label>
                        <select id="manager_id" name="manager_id" class="form-control">
                            <option value="">Unassigned</option>
                            <?php foreach ($managers as $manager): ?>
                                <option value="<?= $manager['id'] ?>"><?= esc($manager['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Currency <span class="required">*</span></label>
                        <select id="currency" name="currency" class="form-control" required>
                            <option value="INR">INR</option>
                            <option value="USD">USD</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status <span class="required">*</span></label>
                        <select id="company_status" name="status" class="form-control" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Website</label>
                        <input type="text" id="website" name="website" class="form-control" placeholder="https://example.com">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Logo</label>
                        <input type="file" id="logo" name="logo" class="form-control" accept="image/*">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Address</label>
                        <textarea id="address" name="address" class="form-control" rows="3"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer px-4 pb-4">
                <button type="button" class="btn btn-outline" onclick="closeCompanyModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" id="saveCompanyBtn">
                    <i class="fas fa-save"></i> Save Company
                </button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    const companyBaseUrl = '<?= base_url('admin/companies') ?>';
    const companyUpdateBaseUrl = '<?= base_url('admin/companies/update') ?>';
    const companyPaginationQuery = <?= json_encode($paginationQuery) ?>;

    function openAddCompanyModal() {
        $('#companyModalTitle').text('Add New Company');
        $('#companyForm')[0].reset();
        $('#companyId').val('');
        $('#companyModal').css('display', 'flex');
        $('body').css('overflow', 'hidden');
    }

    function closeCompanyModal() {
        $('#companyModal').hide();
        $('body').css('overflow', '');
    }

    function editCompany(companyId) {
        fetch(`${companyBaseUrl}/${companyId}`)
            .then((response) => response.json())
            .then((data) => {
                if (!data.success) {
                    throw new Error(data.message || 'Unable to load company');
                }

                const company = data.company;
                $('#companyModalTitle').text('Edit Company');
                $('#companyId').val(company.id);
                $('#company_name').val(company.name || '');
                $('#company_email').val(company.email || '');
                $('#manager_id').val(company.manager_id || '');
                $('#currency').val(company.currency || 'INR');
                $('#company_status').val(company.status || 'active');
                $('#website').val(company.website || '');
                $('#address').val(company.address || '');
                $('#companyModal').css('display', 'flex');
                $('body').css('overflow', 'hidden');
            })
            .catch((error) => Swal.fire('Error', error.message, 'error'));
    }

    $('#companyForm').on('submit', function (event) {
        event.preventDefault();

        const companyId = $('#companyId').val();
        const url = companyId ? `${companyUpdateBaseUrl}/${companyId}` : companyBaseUrl;
        const formData = new FormData(this);
        const button = $('#saveCompanyBtn');

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
                    throw new Error(Object.values(data.messages || data).join('\n') || 'Unable to save company');
                }
                return data;
            })
            .then((data) => {
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: data.message,
                    timer: 1600,
                    showConfirmButton: false,
                }).then(() => window.location.reload());
            })
            .catch((error) => {
                button.prop('disabled', false).html('<i class="fas fa-save"></i> Save Company');
                Swal.fire('Validation Error', error.message, 'error');
            });
    });

    function deleteCompany(companyId) {
        Swal.fire({
            icon: 'warning',
            title: 'Delete company?',
            text: 'This action cannot be undone.',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'Delete',
        }).then((result) => {
            if (!result.isConfirmed) {
                return;
            }

            fetch(`${companyBaseUrl}/${companyId}`, {
                method: 'DELETE',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
            })
                .then(async (response) => {
                    const data = await response.json();
                    if (!response.ok) {
                        throw new Error(data.message || 'Unable to delete company');
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
        if ($(event.target).is('#companyModal')) {
            closeCompanyModal();
        }
    });

    document.querySelectorAll('.pagination-links a').forEach((link) => {
        const url = new URL(link.href);
        Object.entries(companyPaginationQuery).forEach(([key, value]) => {
            url.searchParams.set(key, value);
        });
        link.href = url.toString();
    });
</script>
<?= $this->endSection() ?>
