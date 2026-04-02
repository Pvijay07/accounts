<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Invoice Management<?= $this->endSection() ?>
<?= $this->section('page_title') ?>Admin Panel<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Invoice Stats -->
<div class="dashboard-grid mb-4">
    <div class="stat-card" style="background: linear-gradient(135deg, #6366f1, #4f46e5); color: white;">
        <div class="d-flex justify-content-between mb-3">
            <i class="fas fa-file-invoice fa-2x opacity-50"></i>
            <span class="badge bg-white text-primary rounded-pill px-3">PENDING</span>
        </div>
        <p class="mb-1 small text-uppercase fw-bold opacity-75">Pending Invoices</p>
        <h2 class="fw-bold mb-0"><?= $stats['pending_proformas'] ?? 0 ?></h2>
        <div class="mt-2 small opacity-75">Awaiting payment</div>
    </div>
    <div class="stat-card" style="background: linear-gradient(135deg, #f59e0b, #d97706); color: white;">
        <div class="d-flex justify-content-between mb-3">
            <i class="fas fa-wallet fa-2x opacity-50"></i>
            <span class="badge bg-white text-warning rounded-pill px-3">OUTSTANDING</span>
        </div>
        <p class="mb-1 small text-uppercase fw-bold opacity-75">Outstanding Amount</p>
        <h2 class="fw-bold mb-0">₹<?= number_format($stats['pending_amount'] ?? 0) ?></h2>
        <div class="mt-2 small opacity-75">Total receivable amount</div>
    </div>
    <div class="stat-card" style="background: linear-gradient(135deg, #10b981, #059669); color: white;">
        <div class="d-flex justify-content-between mb-3">
            <i class="fas fa-chart-line fa-2x opacity-50"></i>
            <span class="badge bg-white text-success rounded-pill px-3">MTD</span>
        </div>
        <p class="mb-1 small text-uppercase fw-bold opacity-75">Paid This Month</p>
        <h2 class="fw-bold mb-0">₹<?= number_format($stats['paid_this_month'] ?? 0) ?></h2>
        <div class="mt-2 small opacity-75">Collected this month</div>
    </div>
    <div class="stat-card" style="background: linear-gradient(135deg, #3b82f6, #2563eb); color: white;">
        <div class="d-flex justify-content-between mb-3">
            <i class="fas fa-archive fa-2x opacity-50"></i>
            <span class="badge bg-white text-info rounded-pill px-3">TOTAL</span>
        </div>
        <p class="mb-1 small text-uppercase fw-bold opacity-75">Total Invoices</p>
        <h2 class="fw-bold mb-0"><?= $stats['total_invoices'] ?? 0 ?></h2>
        <div class="mt-2 small opacity-75">All system records</div>
    </div>
</div>

<!-- Tabs -->
<ul class="nav nav-tabs mb-4" id="invoiceTabs" role="tablist" style="border-bottom: none; gap: 12px; background: #ffffff; padding: 12px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.04);">
    <li class="nav-item">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#listTab" style="border: none; border-radius: 12px; font-weight: 600; padding: 12px 24px;">
            <i class="fas fa-list-ul me-2"></i> Invoice List
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#createTab" style="border: none; border-radius: 12px; font-weight: 600; padding: 12px 24px;">
            <i class="fas fa-plus-circle me-2"></i> Create Invoice
        </button>
    </li>
</ul>

<div class="tab-content">
    <!-- List Tab -->
    <div class="tab-pane fade show active" id="listTab">
        <div class="table-container">
            <div class="table-header">
                <div class="table-title">Invoice List</div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Invoice #</th>
                            <th>Company</th>
                            <th>Client</th>
                            <th>Issue Date</th>
                            <th>Amount (₹)</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($invoices)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <i class="fas fa-file-invoice fa-2x text-muted mb-3 d-block"></i>
                                    <p class="text-muted">No invoices found</p>
                                </td>
                            </tr>
                        <?php else: foreach ($invoices as $invoice): ?>
                            <tr>
                                <td><span class="fw-bold text-primary"><?= esc($invoice['invoice_number']) ?></span></td>
                                <td><?= esc($invoice['company_name'] ?? 'N/A') ?></td>
                                <td>
                                    <div class="fw-bold"><?= esc($invoice['client_name'] ?? 'N/A') ?></div>
                                    <div class="text-muted small"><?= esc($invoice['client_email'] ?? '') ?></div>
                                </td>
                                <td><?= date('d M, Y', strtotime($invoice['issue_date'] ?? date('Y-m-d'))) ?></td>
                                <td class="fw-bold">₹<?= number_format($invoice['total_amount'], 2) ?></td>
                                <td>
                                    <?php
                                        $statusClass = match($invoice['status']) {
                                            'paid' => 'active',
                                            'pending' => 'warning',
                                            'overdue' => 'inactive',
                                            default => 'secondary'
                                        };
                                        $statusBg = match($invoice['status']) {
                                            'paid' => 'background: #dcfce7; color: #16a34a;',
                                            'pending' => 'background: #fef9c3; color: #ca8a04;',
                                            'overdue' => 'background: #fee2e2; color: #dc2626;',
                                            default => 'background: #f1f5f9; color: #64748b;'
                                        };
                                    ?>
                                    <span class="status" style="<?= $statusBg ?>">
                                        <?= ucfirst($invoice['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn btn-icon btn-sm" onclick="viewInvoice(<?= $invoice['id'] ?>)" title="View">
                                            <i class="fas fa-eye text-info"></i>
                                        </button>
                                        <button class="btn btn-icon btn-sm" title="Download PDF">
                                            <i class="fas fa-file-pdf text-danger"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
            <?php if(isset($pager)): ?>
            <div class="pagination-container">
                <?= $pager->links() ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Create Tab -->
    <div class="tab-pane fade" id="createTab">
        <div class="table-container">
            <div class="table-header">
                <div class="table-title">Create New Invoice</div>
            </div>
            <form id="invoiceForm">
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label class="form-label">Company *</label>
                        <select name="company_id" class="form-control" required>
                            <option value="">Select Company</option>
                            <?php foreach ($companies as $company): ?>
                                <option value="<?= $company['id'] ?>"><?= esc($company['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Client Name *</label>
                        <input type="text" name="client_name" class="form-control" placeholder="Client name" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Client Email *</label>
                        <input type="email" name="client_email" class="form-control" placeholder="client@email.com" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Issue Date</label>
                        <input type="date" name="issue_date" class="form-control" value="<?= date('Y-m-d') ?>">
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0">Line Items</h6>
                    <button type="button" class="btn btn-outline" id="add-item-btn">
                        <i class="fas fa-plus"></i> Add Item
                    </button>
                </div>

                <div class="table-responsive mb-4">
                    <table class="table" id="line-items-table">
                        <thead>
                            <tr>
                                <th style="width: 50%">Description</th>
                                <th style="width: 12%">Quantity</th>
                                <th style="width: 15%">Rate (₹)</th>
                                <th style="width: 15%">Amount (₹)</th>
                                <th style="width: 8%"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="line-item">
                                <td><input type="text" name="line_items[0][description]" class="form-control" placeholder="Service description..." required></td>
                                <td><input type="number" name="line_items[0][quantity]" class="form-control text-center qty" value="1" min="1" required></td>
                                <td><input type="number" name="line_items[0][rate]" class="form-control text-end rate" placeholder="0.00" step="0.01" required></td>
                                <td><input type="number" class="form-control text-end fw-bold amount" value="0.00" readonly></td>
                                <td><button type="button" class="btn btn-link text-danger p-0 delete-item"><i class="fas fa-times-circle"></i></button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="row border-top pt-4">
                    <div class="col-md-7">
                        <label class="form-label">Terms & Conditions</label>
                        <textarea name="terms" class="form-control" rows="4" placeholder="Payment terms..."></textarea>
                    </div>
                    <div class="col-md-5">
                        <div class="p-4 rounded-4" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                            <div class="d-flex justify-content-between mb-3">
                                <span class="text-muted fw-medium">Subtotal</span>
                                <span class="fw-bold" id="label-subtotal">₹0.00</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-4 pb-4 border-bottom">
                                <div class="form-check form-switch p-0">
                                    <label class="form-check-label small fw-bold text-muted me-2" for="tax-toggle">GST (18%)</label>
                                    <input class="form-check-input ms-0" type="checkbox" id="tax-toggle" checked>
                                </div>
                                <span class="fw-bold text-primary" id="label-tax">₹0.00</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="h6 fw-bold mb-0">Grand Total</span>
                                <span class="h4 fw-bold text-primary mb-0" id="label-total">₹0.00</span>
                                <input type="hidden" name="total_amount" id="input-total">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 text-end">
                    <button type="button" class="btn btn-outline me-2">Preview</button>
                    <button type="submit" class="btn btn-primary px-5">
                        <i class="fas fa-save me-2"></i> Save Invoice
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .dashboard-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 20px;
    }
    .stat-card {
        border-radius: 20px;
        padding: 24px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 25px -5px rgba(0,0,0,0.15);
    }
    #invoiceTabs .nav-link.active {
        background: linear-gradient(135deg, #3b82f6, #2563eb) !important;
        color: #fff !important;
        box-shadow: 0 8px 16px rgba(37, 99, 235, 0.25);
    }
    #invoiceTabs .nav-link:not(.active) {
        color: #64748b;
        background: transparent;
    }
    .form-switch .form-check-input { scale: 1.2; cursor: pointer; }
</style>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    let itemIndex = 1;

    $('#add-item-btn').click(function() {
        const tr = `
            <tr class="line-item">
                <td><input type="text" name="line_items[${itemIndex}][description]" class="form-control" placeholder="Service description..." required></td>
                <td><input type="number" name="line_items[${itemIndex}][quantity]" class="form-control text-center qty" value="1" min="1" required></td>
                <td><input type="number" name="line_items[${itemIndex}][rate]" class="form-control text-end rate" placeholder="0.00" step="0.01" required></td>
                <td><input type="number" class="form-control text-end fw-bold amount" value="0.00" readonly></td>
                <td><button type="button" class="btn btn-link text-danger p-0 delete-item"><i class="fas fa-times-circle"></i></button></td>
            </tr>
        `;
        $('#line-items-table tbody').append(tr);
        itemIndex++;
    });

    $(document).on('click', '.delete-item', function() {
        if($('#line-items-table tbody tr').length > 1) {
            $(this).closest('tr').remove();
            calculateTotals();
        }
    });

    $(document).on('input', '.qty, .rate', function() {
        calculateTotals();
    });

    $('#tax-toggle').on('change', function() {
        calculateTotals();
    });

    function calculateTotals() {
        let subtotal = 0;
        $('#line-items-table tbody tr').each(function() {
            let qty = parseFloat($(this).find('.qty').val()) || 0;
            let rate = parseFloat($(this).find('.rate').val()) || 0;
            let amount = qty * rate;
            $(this).find('.amount').val(amount.toFixed(2));
            subtotal += amount;
        });

        let tax = 0;
        if($('#tax-toggle').is(':checked')) {
            tax = subtotal * 0.18;
        }

        let total = subtotal + tax;
        $('#label-subtotal').text('₹' + subtotal.toLocaleString('en-IN', {minimumFractionDigits: 2}));
        $('#label-tax').text('₹' + tax.toLocaleString('en-IN', {minimumFractionDigits: 2}));
        $('#label-total').text('₹' + total.toLocaleString('en-IN', {minimumFractionDigits: 2}));
        $('#input-total').val(total.toFixed(2));
    }

    $('#invoiceForm').submit(function(e) {
        e.preventDefault();
        const btn = $(this).find('button[type="submit"]');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Saving...');
        
        $.ajax({
            url: '<?= base_url('admin/invoices') ?>',
            method: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                Swal.fire({icon: 'success', title: 'Invoice Created!', text: 'Invoice has been saved successfully.', timer: 1500, showConfirmButton: false}).then(() => location.reload());
            },
            error: function() {
                btn.prop('disabled', false).html('<i class="fas fa-save me-2"></i> Save Invoice');
                Swal.fire('Error', 'Failed to create invoice. Please try again.', 'error');
            }
        });
    });
});

function viewInvoice(id) {
    Swal.fire('Invoice Details', 'Opening invoice #' + id, 'info');
}
</script>
<?= $this->endSection() ?>
