@extends('Manager.layouts.app')
@section('content')
    <section class="pge">
        <div class="container-fluid">

            <!-- Header with navigation -->
            <div class="card shadow-sm mb-3">
                <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <h5 class="mb-0">TDS on Expenses (Input TDS)</h5>
                        <div class="small-help">TDS deducted on purchases from vendors. Attach bills for CA export.</div>
                    </div>
                    <div class="topnav">
                        <a class="btn btn-sm btn-outline-primary" href="{{ route('manager.tds') }}">TDS on Income</a>
                        <a class="btn btn-sm btn-primary" href="{{ route('manager.tdsExpense') }}">TDS on Expense</a>
                    </div>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="row g-3 mb-3">
                <div class="col-md-3">
                    <div class="card kpi shadow-sm">
                        <div class="card-body">
                            <div class="label">Tax Period</div>
                            <div class="value">{{ date('F Y', strtotime($selectedPeriod . '-01')) }}</div>
                            <div class="small-help">Currently viewing</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card kpi shadow-sm">
                        <div class="card-body">
                            <div class="label">Total Input TDS</div>
                            <div class="value">₹ {{ number_format($totalTDSAmount, 2) }}</div>
                            <div class="small-help">From purchase bills</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card kpi shadow-sm">
                        <div class="card-body">
                            <div class="label">Taxable Amount</div>
                            <div class="value">₹ {{ number_format($totalTaxableAmount, 2) }}</div>
                            <div class="small-help">Base amount before TDS</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card kpi shadow-sm">
                        <div class="card-body">
                            <div class="label">Attachments</div>
                            <div class="value"></div>
                            <div class="small-help">Bills for CA</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TDS Summary Cards -->
            <div class="row mb-3">
                <div class="col-md-4">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-1">TDS Paid</h6>
                                    <h4 class="text-success">₹{{ number_format($totalTDSPaid, 2) }}</h4>
                                </div>
                                <div class="icon-circle bg-success">
                                    <i class="fas fa-check text-white"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-1">TDS Due</h6>
                                    <h4 class="text-warning">₹{{ number_format($totalTDSDue, 2) }}</h4>
                                </div>
                                <div class="icon-circle bg-warning">
                                    <i class="fas fa-clock text-white"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-1">Total Bills</h6>
                                    <h4 class="text-primary">{{ $purchaseInvoices->count() }}</h4>
                                </div>
                                <div class="icon-circle bg-primary">
                                    <i class="fas fa-file-invoice text-white"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                        <div>
                            <h6 class="mb-1">Filter Purchase Bills</h6>
                            <div class="small-help">View and filter TDS deducted on purchases.</div>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal"
                                data-bs-target="#attachModal">Attach Bill PDF</button>
                            <button class="btn btn-sm btn-primary" onclick="syncFromExpenses()">Sync from Expenses</button>
                        </div>
                    </div>

                    <form class="row g-2 align-items-end" id="expenseFilterForm" method="GET"
                        action="{{ route('manager.tdsExpense') }}">
                        @csrf
                        <div class="col-md-3">
                            <label class="form-label small">Company</label>
                            <select class="form-select form-select-sm" name="company_id" id="expense_company_filter">
                                <option value="all">All</option>
                                @foreach ($companies as $company)
                                    <option value="{{ $company->id }}"
                                        {{ $selectedCompany == $company->id ? 'selected' : '' }}>
                                        {{ $company->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Vendor</label>
                            <select class="form-select form-select-sm" name="vendor_id" id="expense_vendor_filter">
                                <option value="all">All</option>
                                @foreach ($vendors as $vendor)
                                    <option value="{{ $vendor->id }}"
                                        {{ $selectedVendor == $vendor->id ? 'selected' : '' }}>
                                        {{ $vendor->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">Tax Period</label>
                            <select class="form-select form-select-sm" name="period" id="expense_period_filter">
                                @foreach ($months as $month)
                                    <option value="{{ $month['value'] }}"
                                        {{ $selectedPeriod == $month['value'] ? 'selected' : '' }}>
                                        {{ $month['label'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">Status</label>
                            <select class="form-select form-select-sm" name="status" id="expense_status_filter">
                                <option value="all">All Status</option>
                                <option value="paid" {{ ($selectedStatus ?? 'all') == 'paid' ? 'selected' : '' }}>Paid</option>
                                <option value="pending" {{ ($selectedStatus ?? 'all') == 'pending' ? 'selected' : '' }}>Pending</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-grid">
                            <button type="submit" class="btn btn-sm btn-primary">Apply</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Purchase Bills Table -->
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Purchase Bills (Input TDS)</span>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-outline-secondary" onclick="exportExpenseData('excel')">
                            Export Excel
                        </button>
                        <button class="btn btn-sm btn-outline-secondary" onclick="exportExpenseData('pdf')">
                            Export PDF
                        </button>
                        <button class="btn btn-sm btn-outline-secondary" onclick="exportExpenseData('zip')">
                            Export Attachments
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Bill No</th>
                                    <th>Company</th>
                                    <th>Vendor</th>
                                    <th>Date</th>
                                    <th class="text-end">Taxable (₹)</th>
                                    <th class="text-end">TDS (₹)</th>
                                    <th>Payment Status</th>

                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($purchaseInvoices as $expense)
                                    @php
                                        $tdsTax = $expense->taxes
                                            ->where('tax_type', 'tds')
                                            ->where('direction', 'expense')
                                            ->first();
                                        $attachmentCount = 0;
                                        $paymentStatus = $tdsTax ? $tdsTax->payment_status : 'pending';
                                        $statusClass = match ($paymentStatus) {
                                            'paid' => 'text-success',
                                            'pending' => 'text-warning',
                                            'overdue' => 'text-danger',
                                            default => 'text-secondary',
                                        };
                                    @endphp
                                    <tr>
                                        <td>{{ $expense->bill_number ?? 'N/A' }}</td>
                                        <td>{{ $expense->company->name ?? 'N/A' }}</td>
                                        <td>{{ $expense->vendor->name ?? 'N/A' }}</td>
                                        <td>{{ date('d M Y', strtotime($expense->created_at)) }}</td>
                                        <td class="text-end">{{ number_format($expense->amount, 2) }}</td>
                                        <td class="text-end fw-semibold">
                                            {{ number_format($tdsTax ? $tdsTax->tax_amount : 0, 2) }}
                                        </td>
                                        <td>
                                            <span class="fw-medium {{ $statusClass }}">
                                                {{ ucfirst($paymentStatus) }}
                                            </span>
                                        </td>

                                        <td class="text-end">
                                            <div class="btn-group btn-group-sm">
                                                @if ($tdsTax && $tdsTax->payment_status !== 'received')
                                                    <button class="btn btn-sm btn-outline-success"
                                                        onclick="markTDSPaid({{ $tdsTax->id }})">
                                                        Mark Paid
                                                    </button>
                                                @endif

                                                <button class="btn btn-outline-secondary"
                                                    onclick="window.location.href='{{ route('manager.tds.download-tds-proof', $tdsTax->id) }}'">
                                                    Download
                                                </button>

                                                <button class="btn btn-outline-primary"
                                                    onclick="openTdsAttachmentModal({{ $tdsTax->id }}, '{{ $tdsTax->tax_type }} TDS')">
                                                    <i class="fas fa-paperclip"></i> Attach
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center">No purchase bills with TDS found for this
                                            period.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Attach Bill Modal -->
            <div class="modal fade" id="attachModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <div>
                                <h5 class="modal-title">Attach Bill PDF</h5>
                                <div class="small-help">Upload purchase bill PDF for CA export.</div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <form class="row g-3" id="attachBillForm" method="POST"
                                action="{{ route('manager.tds.attach') }}" enctype="multipart/form-data">
                                @csrf
                                <div class="col-md-6">
                                    <label class="form-label small">Select Bill</label>
                                    <select class="form-select form-select-sm" name="expense_id" required>
                                        <option value="">Select Bill</option>
                                        @foreach ($purchaseInvoices as $expense)
                                            <option value="{{ $expense->id }}">
                                                {{ $expense->bill_number ?? 'Bill #' . $expense->id }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small">Attach PDF</label>
                                    <input type="file" class="form-control form-control-sm" name="pdf_file"
                                        accept=".pdf" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small">Notes</label>
                                    <textarea class="form-control form-control-sm" name="notes" rows="2"></textarea>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                            <button class="btn btn-sm btn-primary" type="submit" form="attachBillForm">Save</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="attachmentModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <div>
                                <h5 class="modal-title" id="attachmentModalTitle">Attach Document</h5>
                                <div class="small-help">Invoice: <span id="attachmentInvoiceNumber"></span></div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <!-- Upload Form -->
                            <form id="uploadAttachmentForm" method="POST" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="tax_id" id="attachment_invoice_id">

                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label class="form-label small">Document Type *</label>
                                        TDS Certificate

                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small">Upload File *</label>
                                        <input type="file" class="form-control form-control-sm" name="tds_proof"
                                            accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" required>
                                        <small class="text-muted">Max: 5MB. Allowed: PDF, Images, DOC</small>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small">Description</label>
                                        <textarea class="form-control form-control-sm" name="description" rows="2"
                                            placeholder="Add notes about this attachment..."></textarea>
                                    </div>
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-sm btn-primary" id="uploadBtn">
                                        <i class="fas fa-upload"></i> Upload Attachment
                                    </button>
                                </div>
                            </form>


                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function exportExpenseData(type) {
            const company = document.getElementById('expense_company_filter').value;
            const vendor = document.getElementById('expense_vendor_filter').value;
            const period = document.getElementById('expense_period_filter').value;
            const status = document.getElementById('expense_status_filter').value;

            window.location.href =
                `{{ url('manager/tds/expense/export') }}/${type}?company=${company}&vendor=${vendor}&period=${period}&status=${status}`;
        }

        function downloadBill(id) {
            window.location.href = `{{ url('manager/tds/download-bill') }}/${id}`;
        }

        function viewAttachments(id) {
            window.location.href = `{{ url('manager/tds/bill-attachments') }}/${id}`;
        }

        function attachToBill(id) {
            // Open modal and set the bill ID
            $('#attachModal select[name="expense_id"]').val(id);
            $('#attachModal').modal('show');
        }

        function syncFromExpenses() {
            const company = document.getElementById('expense_company_filter').value;
            const vendor = document.getElementById('expense_vendor_filter').value;
            const period = document.getElementById('expense_period_filter').value;

            fetch('{{ route('manager.tds.sync.expenses') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        company: company,
                        vendor: vendor,
                        period: period
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Expenses synced successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while syncing expenses.');
                });
        }

        function markTDSPaid(taxId) {
            const markPaidUrl = "{{ route('manager.tds.mark-paid', ['id' => 'ID_PLACEHOLDER']) }}";
            const url = markPaidUrl.replace('ID_PLACEHOLDER', taxId);

            if (confirm('Mark this TDS as paid?')) {
                fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('TDS marked as paid successfully!');
                            location.reload();
                        } else {
                            alert('Error: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while updating status.');
                    });
            }
        }
    </script>
    <script>
        // Open attachment modal and load existing attachments
        function openTdsAttachmentModal(taxId, tax_type = '') {
            // Set invoice ID in form
            document.getElementById('attachment_invoice_id').value = taxId;

            // Reset form
            document.getElementById('uploadAttachmentForm').reset();

            // Set form action
            document.getElementById('uploadAttachmentForm').action = `{{ route('manager.tds.attach-document') }}`;


            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('attachmentModal'));
            modal.show();
        }

        // Handle attachment form submission
        document.getElementById('uploadAttachmentForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const form = this;
            const formData = new FormData(form);
            const uploadBtn = document.getElementById('uploadBtn');
            const originalBtnText = uploadBtn.innerHTML;

            // Show loading state
            uploadBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';
            uploadBtn.disabled = true;

            fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Attachment uploaded successfully!');
                        form.reset();

                        // Reload attachments list
                        const invoiceId = document.getElementById('attachment_invoice_id').value;
                        // loadAttachments(invoiceId);
                        location.reload();
                    } else {
                        alert('Error: ' + (data.message || 'Upload failed'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while uploading');
                })
                .finally(() => {
                    uploadBtn.innerHTML = originalBtnText;
                    uploadBtn.disabled = false;
                });
        });

        // View invoice details
        function viewInvoiceDetails(invoiceId) {
            window.open(`{{ url('manager/invoices') }}/${invoiceId}`, '_blank');
        }

        function downloadTdsProofById(id) {
            window.location.href = `https://xhtmlreviews.in/finance-manager/manager/taxes/${id}/download-tds-proof`;
        }
    </script>
@endsection
