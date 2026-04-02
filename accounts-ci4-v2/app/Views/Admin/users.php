<?= $this->extend('layouts/admin') ?>
<?= $this->section('title') ?>User Management<?= $this->endSection() ?>
<?= $this->section('page_title') ?>User Management<?= $this->endSection() ?>

<?php
$filters = $filters ?? ['search' => '', 'role' => '', 'status' => '', 'company' => ''];
$buildUrl = static function (array $changes = []) use ($filters): string {
    $query = array_merge($filters, $changes);
    $query = array_filter(
        $query,
        static fn ($value) => $value !== '' && $value !== null
    );
    $qs = http_build_query($query);
    return base_url('admin/users') . ($qs ? '?' . $qs : '');
};
?>

<?= $this->section('content') ?>
<div class="table-container">
    <div class="table-header">
        <div class="table-title">User Management</div>
        <div class="table-actions flex-wrap">
            <form method="get" action="<?= base_url('admin/users') ?>" class="search-container">
                <i class="fas fa-search search-icon"></i>
                <input
                    type="text"
                    name="search"
                    class="search-input"
                    placeholder="Search users..."
                    value="<?= esc($filters['search']) ?>"
                >
                <input type="hidden" name="role" value="<?= esc($filters['role']) ?>">
                <input type="hidden" name="status" value="<?= esc($filters['status']) ?>">
                <input type="hidden" name="company" value="<?= esc($filters['company']) ?>">
            </form>
            <button class="btn btn-outline" type="button" onclick="toggleFilters()">
                <i class="fas fa-filter"></i> Filter
            </button>
            <button class="btn btn-primary" type="button" onclick="openAddUserModal()">
                <i class="fas fa-plus"></i> Add User
            </button>
        </div>
    </div>

    <div class="filter-container" id="filterRow" style="<?= ($filters['role'] || $filters['status'] || $filters['company']) ? '' : 'display:none;' ?>">
        <form method="get" action="<?= base_url('admin/users') ?>" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Role</label>
                <select class="form-control" name="role">
                    <option value="">All Roles</option>
                    <?php foreach ($roles as $role): ?>
                        <option value="<?= esc($role) ?>" <?= $filters['role'] === $role ? 'selected' : '' ?>>
                            <?= ucfirst($role) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select class="form-control" name="status">
                    <option value="">All Status</option>
                    <option value="active" <?= $filters['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= $filters['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Company</label>
                <select class="form-control" name="company">
                    <option value="">All Companies</option>
                    <?php foreach ($companies as $company): ?>
                        <option value="<?= $company['id'] ?>" <?= (string) $filters['company'] === (string) $company['id'] ? 'selected' : '' ?>>
                            <?= esc($company['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-fill">
                    <i class="fas fa-check"></i> Apply
                </button>
                <a href="<?= base_url('admin/users') ?>" class="btn btn-outline flex-fill">
                    <i class="fas fa-redo"></i> Reset
                </a>
            </div>
            <input type="hidden" name="search" value="<?= esc($filters['search']) ?>">
        </form>
    </div>

    <?php if ($filters['search'] || $filters['role'] || $filters['status'] || $filters['company']): ?>
        <div class="filter-results mb-3">
            <span><?= count($users) ?> result(s) on this page<?= $filters['search'] ? ' for "' . esc($filters['search']) . '"' : '' ?></span>
            <a href="<?= base_url('admin/users') ?>" class="clear-filters-btn">
                <i class="fas fa-times"></i> Clear filters
            </a>
        </div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table table-hover" id="usersTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Assigned Company</th>
                    <th>Status</th>
                    <th>Last Login</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (! $users): ?>
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="fas fa-users fa-2x mb-3 d-block"></i>
                            No users found.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $user): ?>
                        <?php
                        $roleBadge = match ($user['role']) {
                            'admin' => 'bg-danger text-white',
                            'manager' => 'bg-primary text-white',
                            'ca' => 'bg-info text-white',
                            default => 'bg-success text-white',
                        };
                        ?>
                        <tr>
                            <td>#<?= str_pad((string) $user['id'], 3, '0', STR_PAD_LEFT) ?></td>
                            <td><?= esc($user['name']) ?></td>
                            <td><?= esc($user['email']) ?></td>
                            <td><span class="badge <?= $roleBadge ?>"><?= strtoupper(esc($user['role'])) ?></span></td>
                            <td><?= esc($user['company_name'] ?? 'All Companies') ?></td>
                            <td>
                                <span class="status <?= $user['status'] === 'active' ? 'active' : 'inactive' ?>">
                                    <?= ucfirst(esc($user['status'])) ?>
                                </span>
                            </td>
                            <td>
                                <?= ! empty($user['last_login_at']) ? date('d M Y h:i A', strtotime($user['last_login_at'])) : 'Never' ?>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn btn-icon btn-outline btn-sm" type="button" onclick="editUser(<?= $user['id'] ?>)" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button
                                        class="btn btn-icon btn-outline btn-sm"
                                        type="button"
                                        onclick="changeStatus(<?= $user['id'] ?>, '<?= esc($user['status']) ?>')"
                                        title="<?= $user['status'] === 'active' ? 'Deactivate' : 'Activate' ?>"
                                    >
                                        <i class="fas fa-<?= $user['status'] === 'active' ? 'ban' : 'check' ?>"></i>
                                    </button>
                                    <?php if ((int) $user['id'] !== (int) session()->get('user_id')): ?>
                                        <button class="btn btn-icon btn-danger btn-sm" type="button" onclick="deleteUser(<?= $user['id'] ?>)" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    <?php endif; ?>
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
            <?= $pager ? $pager->getTotal() : count($users) ?> entries
        </div>
        <div class="pagination-links">
            <?= $pager ? $pager->links() : '' ?>
        </div>
    </div>
</div>

<div class="modal" id="userModal">
    <div class="modal-content">
        <div class="modal-header">
            <div class="modal-title" id="modalTitle">Add New User</div>
            <button type="button" class="close-modal" onclick="closeUserModal()">&times;</button>
        </div>
        <form id="userForm">
            <?= csrf_field() ?>
            <input type="hidden" id="userId" name="id">
            <div class="p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Full Name <span class="required">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email Address <span class="required">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Password <span id="passwordRequired" class="required">*</span></label>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <div class="hint-text d-none" id="passwordHint">Leave blank to keep the current password.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Role <span class="required">*</span></label>
                        <select class="form-control" id="role" name="role" required>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?= esc($role) ?>"><?= ucfirst($role) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Assign Company</label>
                        <select class="form-control" id="company_id" name="company_id">
                            <option value="">All Companies</option>
                            <?php foreach ($companies as $company): ?>
                                <option value="<?= $company['id'] ?>"><?= esc($company['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Status <span class="required">*</span></label>
                        <select class="form-control" id="status" name="status" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer px-4 pb-4">
                <button type="button" class="btn btn-outline" onclick="closeUserModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" id="submitBtn">
                    <i class="fas fa-save"></i> Save User
                </button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    const userBaseUrl = '<?= base_url('admin/users') ?>';
    const userUpdateBaseUrl = '<?= base_url('admin/users/update') ?>';
    const userStatusBaseUrl = '<?= base_url('admin/users/status') ?>';
    const filtersQuery = <?= json_encode(array_filter([
        'search' => $filters['search'],
        'role' => $filters['role'],
        'status' => $filters['status'],
        'company' => $filters['company'],
    ], static fn ($value) => $value !== '' && $value !== null)) ?>;

    function toggleFilters() {
        $('#filterRow').slideToggle(150);
    }

    function openAddUserModal() {
        $('#modalTitle').text('Add New User');
        $('#userForm')[0].reset();
        $('#userId').val('');
        $('#password').prop('required', true).val('');
        $('#passwordRequired').removeClass('d-none');
        $('#passwordHint').addClass('d-none');
        $('#userModal').css('display', 'flex');
        $('body').css('overflow', 'hidden');
    }

    function closeUserModal() {
        $('#userModal').hide();
        $('body').css('overflow', '');
    }

    function editUser(userId) {
        fetch(`${userBaseUrl}/${userId}/edit`)
            .then((response) => response.json())
            .then((data) => {
                if (!data.success) {
                    throw new Error(data.message || 'Failed to load user');
                }

                const user = data.user;
                $('#modalTitle').text('Edit User');
                $('#userId').val(user.id);
                $('#name').val(user.name);
                $('#email').val(user.email);
                $('#role').val(user.role);
                $('#company_id').val(user.company_id || '');
                $('#status').val(user.status);
                $('#password').prop('required', false).val('');
                $('#passwordRequired').addClass('d-none');
                $('#passwordHint').removeClass('d-none');
                $('#userModal').css('display', 'flex');
                $('body').css('overflow', 'hidden');
            })
            .catch((error) => {
                Swal.fire('Error', error.message, 'error');
            });
    }

    $('#userForm').on('submit', function (event) {
        event.preventDefault();

        const userId = $('#userId').val();
        const formData = new FormData(this);
        const url = userId ? `${userUpdateBaseUrl}/${userId}` : userBaseUrl;
        const button = $('#submitBtn');

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
                    throw new Error(Object.values(data.messages || data).join('\n') || 'Unable to save user');
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
                button.prop('disabled', false).html('<i class="fas fa-save"></i> Save User');
                Swal.fire('Validation Error', error.message, 'error');
            });
    });

    function changeStatus(userId, currentStatus) {
        const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
        const formData = new FormData();
        formData.append('status', newStatus);

        Swal.fire({
            icon: 'question',
            title: `${newStatus === 'active' ? 'Activate' : 'Deactivate'} user?`,
            showCancelButton: true,
            confirmButtonColor: '#3b82f6',
            confirmButtonText: 'Confirm',
        }).then((result) => {
            if (!result.isConfirmed) {
                return;
            }

            fetch(`${userStatusBaseUrl}/${userId}`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
            })
                .then(async (response) => {
                    const data = await response.json();
                    if (!response.ok) {
                        throw new Error(data.message || 'Unable to update status');
                    }
                    return data;
                })
                .then((data) => {
                    Swal.fire('Updated', data.message, 'success').then(() => window.location.reload());
                })
                .catch((error) => Swal.fire('Error', error.message, 'error'));
        });
    }

    function deleteUser(userId) {
        Swal.fire({
            icon: 'warning',
            title: 'Delete user?',
            text: 'This action cannot be undone.',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'Delete',
        }).then((result) => {
            if (!result.isConfirmed) {
                return;
            }

            fetch(`${userBaseUrl}/${userId}`, {
                method: 'DELETE',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
            })
                .then(async (response) => {
                    const data = await response.json();
                    if (!response.ok) {
                        throw new Error(data.message || 'Unable to delete user');
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
        if ($(event.target).is('#userModal')) {
            closeUserModal();
        }
    });

    document.querySelectorAll('.pagination-links a').forEach((link) => {
        const url = new URL(link.href);
        Object.entries(filtersQuery).forEach(([key, value]) => {
            url.searchParams.set(key, value);
        });
        link.href = url.toString();
    });
</script>
<?= $this->endSection() ?>
