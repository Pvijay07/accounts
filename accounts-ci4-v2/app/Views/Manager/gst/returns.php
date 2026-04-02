<?= $this->extend('layouts/manager') ?>
<?= $this->section('title') ?>GST Returns<?= $this->endSection() ?>
<?= $this->section('page_title') ?>GST Returns<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Header -->
<div class="card shadow-sm mb-4 border-0" style="border-radius: 16px; background: rgba(255,255,255,0.7); backdrop-filter: blur(10px);">
    <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h5 class="mb-0 fw-bold">GST Returns & Tasks</h5>
            <div class="text-muted small">Track due dates and update task statuses.</div>
        </div>
        <div class="d-flex gap-2">
            <a class="btn btn-sm btn-outline-primary rounded-pill px-3" href="<?= base_url('manager/gst') ?>">Dashboard</a>
            <a class="btn btn-sm btn-outline-primary rounded-pill px-3" href="<?= base_url('manager/gst-collected') ?>">GST Collected</a>
            <a class="btn btn-sm btn-outline-primary rounded-pill px-3" href="<?= base_url('manager/taxes') ?>">Taxes on Expenses</a>
            <a class="btn btn-sm btn-outline-primary rounded-pill px-3" href="<?= base_url('manager/settlement') ?>">Settlements</a>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <p class="text-muted mb-1 small">Total Tasks</p>
            <h4 class="fw-bold"><?= $stats['total']??0 ?></h4>
            <small class="text-muted">All return tasks</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <p class="text-muted mb-1 small">Pending</p>
            <h4 class="fw-bold text-warning"><?= $stats['pending']??0 ?></h4>
            <small class="text-muted">Awaiting Action</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <p class="text-muted mb-1 small">In Progress</p>
            <h4 class="fw-bold text-primary"><?= $stats['in_progress']??0 ?></h4>
            <small class="text-muted">Processing</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card border-<?= ($stats['overdue']??0) > 0 ? 'danger' : 'success' ?>">
            <p class="text-muted mb-1 small">Overdue</p>
            <h4 class="fw-bold <?= ($stats['overdue']??0) > 0 ? 'text-danger' : 'text-success' ?>"><?= $stats['overdue']??0 ?></h4>
            <small class="text-muted">Past Due Date</small>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card shadow-sm mb-4 border-0" style="border-radius: 16px;">
    <div class="card-body">
        <div class="d-flex justify-content-between mb-3">
            <h6 class="fw-bold mb-0">Filter Tasks</h6>
            <button class="btn btn-primary btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#addTaskModal"><i class="fas fa-plus me-1"></i>Add Task</button>
        </div>
        <form class="row g-3 align-items-end" method="GET">
            <div class="col-md-4">
                <label class="form-label small fw-bold">Company</label>
                <select name="company_id" class="form-select form-select-sm border-0 bg-light rounded-3 shadow-none py-2">
                    <option value="all">All Companies</option>
                    <?php if(!empty($companies)): foreach($companies as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= ($companyId??'') == $c['id'] ? 'selected' : '' ?>><?= esc($c['name']) ?></option>
                    <?php endforeach; endif; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold">Period</label>
                <input type="month" name="period" class="form-control form-control-sm border-0 bg-light rounded-3 shadow-none py-2" value="<?= $selectedPeriod??date('Y-m') ?>">
            </div>
            <div class="col-md-4 d-grid">
                <button type="submit" class="btn btn-primary rounded-3 py-2 fw-bold">Apply Filters</button>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm border-0" style="border-radius: 16px;">
    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold">Task List</h6>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary btn-sm rounded-pill"><i class="fas fa-file-excel me-1"></i>Export</button>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Company</th>
                        <th>Period</th>
                        <th>Return Type</th>
                        <th>Due Date</th>
                        <th>Assigned To</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if(empty($tasks)): ?>
                    <tr><td colspan="7" class="text-center py-5 text-muted">No tasks found for this period.</td></tr>
                <?php else: foreach($tasks as $t): 
                    $isOverdue = ($t['due_date'] < date('Y-m-d') && $t['status'] !== 'completed');
                ?>
                    <tr class="<?= $isOverdue ? 'table-danger' : '' ?>">
                        <td class="ps-4 fw-medium"><?= esc($t['company_name']) ?></td>
                        <td><?= date('M Y', strtotime($t['tax_period'] . '-01')) ?></td>
                        <td><?= esc($t['task_name']) ?></td>
                        <td>
                            <div class="<?= $isOverdue ? 'text-danger fw-bold' : '' ?>">
                                <?= date('d-m-Y', strtotime($t['due_date'])) ?>
                                <?php if($isOverdue): ?><br><small>Overdue</small><?php endif; ?>
                            </div>
                        </td>
                        <td><?= esc($t['assigned_to_name'] ?: 'N/A') ?></td>
                        <td>
                            <?php 
                                $badge = 'bg-secondary';
                                if($t['status'] == 'completed') $badge = 'bg-success';
                                elseif($t['status'] == 'in_progress') $badge = 'bg-primary';
                                elseif($isOverdue) $badge = 'bg-danger';
                            ?>
                            <span class="badge <?= $badge ?> rounded-pill px-3 py-1"><?= ucfirst(str_replace('_',' ', $t['status'])) ?></span>
                        </td>
                        <td class="text-end pe-4">
                            <button class="btn btn-sm btn-outline-secondary rounded-circle" onclick="editTask(<?= $t['id'] ?>)"><i class="fas fa-edit"></i></button>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="addTaskModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 24px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Add Return Task</h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <form id="taskForm">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small fw-bold">Company *</label>
                            <select name="company_id" class="form-select border-0 bg-light rounded-3 py-2 shadow-none" required>
                                <option value="">Select Company</option>
                                <?php if(!empty($companies)): foreach($companies as $c): ?>
                                    <option value="<?= $c['id'] ?>"><?= esc($c['name']) ?></option>
                                <?php endforeach; endif; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Return Type *</label>
                            <select name="task_name" class="form-select border-0 bg-light rounded-3 py-2 shadow-none" required>
                                <option value="GSTR-1">GSTR-1</option>
                                <option value="GSTR-3B">GSTR-3B</option>
                                <option value="GSTR-9">GSTR-9</option>
                                <option value="TDS Return">TDS Return</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Period *</label>
                            <input type="month" name="tax_period" class="form-control border-0 bg-light rounded-3 py-2 shadow-none" value="<?= date('Y-m') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Due Date *</label>
                            <input type="date" name="due_date" class="form-control border-0 bg-light rounded-3 py-2 shadow-none" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Assigned To *</label>
                            <select name="assigned_to" class="form-select border-0 bg-light rounded-3 py-2 shadow-none">
                                <option value="<?= session()->get('user_id') ?>">Self</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">Notes</label>
                            <textarea name="notes" class="form-control border-0 bg-light rounded-3 py-2 shadow-none" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 p-4">
                    <button type="button" class="btn btn-light rounded-3 px-4 fw-bold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-3 px-4 py-2 fw-bold">Save Task</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .stat-card { background: white; padding: 1.25rem; border-radius: 1rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); border: 1px solid rgba(0,0,0,0.05); }
</style>
<?= $this->endSection() ?>
