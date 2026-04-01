@extends( 'Manager.layouts.app' )
@section( 'content' )
<div id="income" class="manager-panel">
    <!-- Date Range & Filter Section -->
    <div class="filter-section mb-4">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label small fw-bold">Date Range</label>
                <select class="form-select form-select-sm" id="dateRangeFilter" onchange="applyFilters()">
                    <option value="today" {{ $dateRange == 'today' ? 'selected' : '' }}>Today</option>
                    <option value="week" {{ $dateRange == 'week' ? 'selected' : '' }}>This Week</option>
                    <option value="month" {{ $dateRange == 'month' ? 'selected' : '' }}>This Month</option>
                    <option value="quarter" {{ $dateRange == 'quarter' ? 'selected' : '' }}>This Quarter</option>
                    <option value="year" {{ $dateRange == 'year' ? 'selected' : '' }}>This Year</option>
                    <option value="next7days" {{ $dateRange == 'next7days' ? 'selected' : '' }}>Next 7 Days</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold">Company</label>
                <select class="form-select form-select-sm" id="companyFilter" onchange="applyFilters()">
                    <option value="">All Companies</option>
                    @foreach ( $companies as $company )
                    <option value="{{ $company->id }}" {{ $companyId == $company->id ? 'selected' : '' }}>
                        {{ $company->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold">Category</label>
                <select class="form-select form-select-sm" id="categoryFilter" onchange="applyFilters()">
                    <option value="all" {{ $category == 'all' ? 'selected' : '' }}>All Types</option>
                    <option value="standard" {{ $category == 'standard' ? 'selected' : '' }}>Standard</option>
                    <option value="non-standard" {{ $category == 'non-standard' ? 'selected' : '' }}>Non Standard
                    </option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold">Status</label>
                <select class="form-select form-select-sm" id="statusFilter" onchange="applyFilters()">
                    <option value="all" {{ $status == 'all' ? 'selected' : '' }}>All Status</option>
                    <option value="pending" {{ $status == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="received" {{ $status == 'received' ? 'selected' : '' }}>Received</option>
                    <option value="overdue" {{ $status == 'overdue' ? 'selected' : '' }}>Overdue</option>
                    <option value="upcoming" {{ $status == 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                </select>
            </div>
        </div>

        <!-- Second row for Reset button -->
        <div class="row g-3 mt-2">
            <div class="col-md-12 d-flex justify-content-end">
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="resetFilters()">
                    <i class="fas fa-redo me-1"></i> Reset All Filters
                </button>
            </div>
        </div>
    </div>

    <!-- Summary Cards Row -->
    <div class="row mb-4">
        <div class="col-4 mb-3">
            <div class="summary-card">
                <div class="summary-header">
                    <h6 class="mb-1">{{ $currentMonth }}-{{ $nextMonth }} {{ $currentYear }} Payments</h6>
                </div>
                <div class="summary-body">
                    <div class="d-flex justify-content-between align-items-end">
                        <div>
                            <h3 class="mb-0" id="totalPayments">
                                ₹{{ number_format ( $stats['totalPayments'] ?? 0, 2 ) }}
                            </h3>
                            <small class="text-muted">{{ $stats['paymentItems'] ?? 0 }} Items</small>
                        </div>
                        <div class="summary-icon">
                            <i class="fas fa-money-bill-wave text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-4 mb-3">
            <div class="summary-card">
                <div class="summary-header">
                    <h6 class="mb-1">{{ $currentMonth }}-{{ $nextMonth }} {{ $currentYear }} Received</h6>
                </div>
                <div class="summary-body">
                    <div class="d-flex justify-content-between align-items-end">
                        <div>
                            <h3 class="mb-0" id="receivedAmount">
                                ₹{{ number_format ( $stats['totalReceived'] ?? 0, 2 ) }}</h3>
                            <small class="text-muted">{{ $stats['receivedItems'] ?? 0 }} items</small>
                        </div>
                        <div class="summary-icon">
                            <i class="fas fa-check-circle text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-4 mb-3">
            <div class="summary-card">
                <div class="summary-header">
                    <h6 class="mb-1">{{ $currentMonth }}-{{ $nextMonth }} {{ $currentYear }} Pending</h6>
                </div>
                <div class="summary-body">
                    <div class="d-flex justify-content-between align-items-end">
                        <div>
                            <h3 class="mb-0" id="pendingAmount">
                                ₹{{ number_format ( $stats['totalPending'] ?? 0, 2 ) }}
                            </h3>
                            <small class="text-muted">{{ $stats['pendingItems'] ?? 0 }} items</small>
                        </div>
                        <div class="summary-icon">
                            <i class="fas fa-clock text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-4 mb-3">
            <div class="summary-card">
                <div class="summary-header">
                    <h6 class="mb-1">{{ $currentMonth }}-{{ $nextMonth }} {{ $currentYear }} Over Due</h6>
                </div>
                <div class="summary-body">
                    <div class="d-flex justify-content-between align-items-end">
                        <div>
                            <h3 class="mb-0" id="overdueAmount">₹{{ number_format ( $stats['overdue'] ?? 0, 2 ) }}
                            </h3>
                            <small class="text-muted">{{ $stats['overdueItems'] ?? 0 }} items</small>
                        </div>
                        <div class="summary-icon">
                            <i class="fas fa-exclamation-triangle text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-4 mb-3">
            <div class="summary-card">
                <div class="summary-header">
                    <h6 class="mb-1">Total Over Due</h6>
                </div>
                <div class="summary-body">
                    <div class="d-flex justify-content-between align-items-end">
                        <div>
                            <h3 class="mb-0" id="totalOverdueAmount">
                                ₹{{ number_format ( $stats['totalOverdue'] ?? 0, 2 ) }}</h3>
                            <small class="text-muted">{{ $stats['totalOverdueItems'] ?? 0 }} items</small>
                        </div>
                        <div class="summary-icon">
                            <i class="fas fa-exclamation-circle text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Buttons Row -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="summary-card h-100">
                <div class="summary-header">
                    <h6 class="mb-1">All Payments</h6>
                </div>
                <div class="summary-body">
                    <div class="btn-group w-100">
                        <button
                            class="btn btn-outline-primary {{ request ( 'status' ) == 'all' || !request ( 'status' ) ? 'active' : '' }}"
                            onclick="filterPayments('all')" id="btnAll">
                            All Payments
                        </button>
                        <button class="btn btn-outline-warning {{ request ( 'status' ) == 'pending' ? 'active' : '' }}"
                            onclick="filterPayments('pending')" id="btnPending">
                            Only Pending
                        </button>
                        <button class="btn btn-outline-info {{ request ( 'status' ) == 'upcoming' ? 'active' : '' }}"
                            onclick="filterPayments('upcoming')" id="btnUpcoming">
                            Only Upcoming
                        </button>
                        <button class="btn btn-outline-success {{ request ( 'status' ) == 'received' ? 'active' : '' }}"
                            onclick="filterPayments('received')" id="btnReceived">
                            Only Received
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Table Section -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">All Payments</h5>
            <div class="card-tools">
                <button class="btn btn-sm btn-primary" onclick="openAddIncomeModal()">
                    <i class="fas fa-plus"></i> Add Non-standard Income
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Company</th>
                            <th>Client Name</th>
                            <th>Actual Amount</th>
                            <th>Receivable Amount</th>
                            <th>Income Type</th>
                            <th>Status</th>
                            <th>Created Date</th>
                            <th>Mail Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="incomeTableBody">
                        @foreach ( $incomes as $income )
                        <tr>
                            <td>
                                <strong>{{ $income->company->name ?? 'N/A' }}</strong>
                            </td>
                            <td>{{ $income->client_name }}</td>

                            <td>
                                <strong>₹{{ number_format ( $income->planned_amount ?? $income->amount, 2 ) }}</strong>
                            </td>
                            <td>
                                <strong class="{{ $income->actual_amount > 0 ? 'text-success' : 'text-muted' }}">
                                    ₹{{ number_format ( $income->actual_amount ?? 0, 2 ) }}
                                </strong>
                            </td>

                            <td>
                                <span class="badge {{ $income->invoice_id ? 'bg-info' : 'bg-secondary' }}">
                                    {{ $income->invoice_id ? 'Standard' : 'Non-Standard' }}
                                </span>
                            </td>
                            <td>
                                @php
                                $statusColors = [
                                'received' => 'success',
                                'pending' => 'warning',
                                'upcoming' => 'info',
                                'overdue' => 'danger',
                                ];
                                $statusText = [
                                'received' => 'Paid',
                                'pending' => 'Pending',
                                'upcoming' => 'Upcoming',
                                'overdue' => 'Overdue',
                                ];
                                @endphp
                                <span class="badge bg-{{ $statusColors[$income->status] ?? 'secondary' }}">
                                    {{ $statusText[$income->status] ?? ucfirst ( $income->status ) }}
                                </span>
                            </td>
                            <td>{{ \Carbon\Carbon::parse ( $income->created_at )->format ( 'd M Y' ) }}</td>
                            <td>
                                <span class="badge {{ $income->mail_status ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $income->mail_status ? 'Yes' : 'No' }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    @if ( $income->status != 'received' )
                                    {{-- <button class="btn btn-sm btn-outline-success"
                                                    onclick="openReceivePaymentModal({{ $income->id }})">
                                    <i class="fas fa-check me-1"></i> Receive Payment
                                    </button> --}}
                                    <button class="btn btn-sm btn-outline-primary"
                                        onclick="openEditIncomeModal({{ $income->id }})">
                                        <i class="fas fa-edit me-1"></i>
                                    </button>
                                    @endif
                                    <div class="btn-group btn-group-sm">

                                        <button class="btn btn-outline-secondary"
                                            onclick="viewProforma({{ $income->id }})">
                                            <i class="fas fa-eye me-1"></i>

                                        </button>
                                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                            data-bs-target="#sendInvoiceModal" data-income-id="{{ $income->id }}">
                                            <i class="fas fa-envelope"></i>
                                        </button>
                                        {{-- <button class="btn btn-outline-info"
                                                    onclick="downloadProforma({{ $income->id }})">
                                        <i class="fas fa-download"></i>
                                        </button> --}}
                                        @if ( $income->is_split || $income->parent_id )
                                        <button class="btn btn-outline-info btn-sm ms-1"
                                            onclick="viewSplitHistory({{ $income->id }})" title="View Split History">
                                            <i class="fas fa-code-branch"></i>
                                        </button>
                                        @endif
                                        <button class="btn btn-outline-danger btn-sm ms-1"
                                            onclick="deleteIncome({{ $income->id }})" title="Delete Income">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ( $incomes->hasPages () )
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted">
                    Showing {{ $incomes->firstItem () }} to {{ $incomes->lastItem () }} of
                    {{ $incomes->total () }}
                    entries
                </div>
                <div>
                    {{ $incomes->links () }}
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Receive Payment Modal (Partial Payment) -->
<div class="modal fade" id="receivePaymentModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Record Payment – Partial Amount Received</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="receivePaymentForm">
                @csrf
                <input type="hidden" id="receiveIncomeId" name="income_id">
                <input type="hidden" id="originalAmount" name="original_amount">

                <div class="modal-body">
                    <div class="alert alert-info mb-4">
                        <i class="fas fa-info-circle"></i>
                        <strong>Client has paid less than the scheduled amount.</strong>
                        Confirm how to handle this payment.
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Invoice No</label>
                            <p class="form-control-plaintext fw-bold" id="invoiceNoDisplay">N/A</p>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Company</label>
                            <p class="form-control-plaintext fw-bold" id="companyDisplay"></p>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Client / Party</label>
                            <p class="form-control-plaintext fw-bold" id="clientDisplay"></p>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label class="form-label">Original Scheduled Amount</label>
                            <p class="form-control-plaintext fw-bold" id="originalAmountDisplay"></p>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Amount Received Now *</label>
                            <input type="number" class="form-control" id="receivedAmount" name="received_amount"
                                step="0.01" required>
                            <small class="text-muted">Must be less than original amount</small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Balance Amount (auto)</label>
                            <p class="form-control-plaintext fw-bold text-danger" id="balanceAmountDisplay">0.00</p>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="createNewProforma"
                                    name="create_new_proforma" checked>
                                <label class="form-check-label fw-bold" for="createNewProforma">
                                    Keep Balance & Create New Proforma
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4" id="newProformaSection">
                        <div class="col-md-6">
                            <label class="form-label">New Due Date for Balance *</label>
                            <input type="date" class="form-control" id="newDueDate" name="new_due_date" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Payment Date *</label>
                            <input type="date" class="form-control" id="paymentDate" name="payment_date" required>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-12">
                            <label class="form-label">Internal Note (optional)</label>
                            <textarea class="form-control" id="internalNote" name="internal_note" rows="3"
                                placeholder="e.g., Client paid 50,000 now, remaining 50,000 to be paid next month."></textarea>
                        </div>
                    </div>

                    <div class="alert alert-warning">
                        <h6><i class="fas fa-exclamation-triangle"></i> What will happen now?</h6>
                        <ul class="mb-0">
                            <li>A taxable invoice will be generated for the amount received now.</li>
                            <li>A new proforma will be created for the balance with the new due date.</li>
                            <li>The original proforma will be marked as Replaced.</li>
                            <li><strong>Once confirmed, this action cannot be undone.</strong></li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Confirm Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add/Edit Income Modal -->
<div class="modal fade" id="incomeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Add Non-standard Income</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="incomeForm" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" id="incomeId" name="id">

                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="companyId" class="form-label">Company *</label>
                            <select class="form-select" id="companyId" name="company_id" required>
                                <option value="">Select Company</option>
                                @foreach ( $companies as $company )
                                <option value="{{ $company->id }}">{{ $company->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="clientName" class="form-label">Client Name / Description *</label>
                            <input type="text" class="form-control" id="clientName" name="client_name" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="actualAmount" class="form-label">Actual Amount *</label>
                            <input type="number" step="0.01" class="form-control" id="actualAmount" name="amount"
                                value="" required placeholder="0.00">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="originalTotalBase" class="form-label">Original Total (Base)</label>
                            <input type="number" readonly class="form-control bg-light" id="originalTotalBase">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="frequency" class="form-label">Frequency</label>
                            <select class="form-select" id="frequency" name="frequency">
                                <option value="">Select Frequency</option>
                                <option value="Monthly">Monthly</option>
                                <option value="Weekly">Weekly</option>
                                <option value="Quarterly">Quarterly</option>
                                <option value="Yearly">Yearly</option>
                                <option value="One-time">One-time</option>
                            </select>
                        </div>

                        <!-- Tax Section -->
                        <div class="mb-3">
                            <!-- GST Section -->
                            <div class="d-flex justify-content-between mb-2">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" name="apply_gst" id="applyGst"
                                        value="1" checked>
                                    <label class="form-check-label" for="applyGst">Apply GST</label>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">GST %</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="gst_percentage"
                                            name="gst_percentage" value="18" min="0" max="100" step="0.01">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">GST Amount</label>
                                    <input type="number" class="form-control" id="gst_amount" name="gst_amount"
                                        readonly>
                                </div>
                            </div>

                            <!-- TDS Section -->
                            <div class="d-flex justify-content-between mb-2">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" name="apply_tds" id="applyTds"
                                        value="1" checked>
                                    <label class="form-check-label" for="applyTds">Apply TDS</label>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">TDS %</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="tds_percentage"
                                            name="tds_percentage" value="10" min="0" max="100" step="0.01">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">TDS Amount</label>
                                    <input type="number" class="form-control" id="tds_amount" name="tds_amount"
                                        readonly>
                                </div>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label class="form-label">Amount After TDS</label>
                                <input type="number" class="form-control" id="amount_after_tds" name="amount_after_tds"
                                    readonly>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-5">
                                    <label class="form-label">TDS Status</label>
                                    <select class="form-select" id="addTdsStatus" name="tds_status">
                                        <option value="">Select Status</option>
                                        <option value="received">Received</option>
                                        <option value="not_received">Not Received</option>
                                    </select>
                                </div>
                                <div class="col-md-7">
                                    <label class="form-label">Receipt</label>
                                    <input type="file" id="addTdsReceipt" name="tds_receipt" class="form-control"
                                        accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Grand Total</label>
                                <input type="number" class="form-control" id="grand_total" name="grand_total" readonly>
                            </div>
                        </div>

                        <!-- Amount Received Section -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Amount Received</label>
                                <input type="number" class="form-control" id="received_amount" name="received_amount"
                                    step="0.01" value="0.00">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Received Payment Date</label>
                                <input type="date" class="form-control" id="received_date" name="received_date">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Balance</label>
                                <input type="number" class="form-control" id="balance_amount" name="balance_amount"
                                    step="0.01" readonly>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="dueDay" class="form-label">Due Day</label>
                            <input type="number" min="1" max="31" class="form-control" id="dueDay" name="due_day"
                                placeholder="Day of month">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">Status *</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="pending" selected>Pending</option>
                                <option value="received">Received</option>
                                <option value="overdue">Overdue</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="incomeDate" class="form-label">Payment Date *</label>
                            <input type="date" class="form-control" id="incomeDate" name="income_date" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="mailStatus" class="form-label">Mail Status</label>
                            <select class="form-select" id="mailStatus" name="mail_status">
                                <option value="1">Yes</option>
                                <option value="0" selected>No</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="fas fa-save"></i> Save Income
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- Edit Income Modal (Separate) -->
<div class="modal fade" id="editIncomeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-edit me-2"></i>Edit Income
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form id="editIncomeForm" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="_method" value="PUT">
                <input type="hidden" id="editIncomeId" name="id">

                <div class="modal-body">
                    <!-- Basic Information -->
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Company</label>
                            <select class="form-select" id="editCompanyId" name="company_id" required>
                                <option value="">Select Company</option>
                                @foreach ( $companies as $company )
                                <option value="{{ $company->id }}">{{ $company->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Client Name</label>
                            <input type="text" class="form-control" id="editClientName" name="client_name" required>
                        </div>
                    </div>

                    <!-- Amount Information -->
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Planned Amount (₹)</label>
                            <div class="input-group">
                                <span class="input-group-text">₹</span>
                                <input type="number" class="form-control" id="editPlannedAmount" name="amount"
                                    step="0.01" min="0" required>
                                <input type="hidden" class="form-control" id="editOriginalAmount"
                                    name="editOriginalAmount" step="0.01" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Paid Amount (₹)</label>
                            <div class="input-group">
                                <span class="input-group-text">₹</span>
                                <input type="number" class="form-control" id="editPaidAmount" name="received_amount"
                                    step="0.01" min="0">
                            </div>
                        </div>
                        <div class="row">

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Paid Date</label>
                                <input type="date" class="form-control" id="editPaidDate" name="received_date">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Payment Mode</label>
                                <select class="form-select" id="editPaymentMode" name="payment_mode">
                                    <option value="">Select Mode</option>
                                    <option value="cash">Cash</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="cheque">Cheque</option>
                                    <option value="online">Online</option>
                                    <option value="card">Card</option>
                                </select>
                            </div>
                            <!-- Receipts Upload -->
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Upload Receipts</label>
                                <div class="input-group">
                                    <input type="file" class="form-control" id="editReceipts" name="receipts[]" multiple
                                        accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                                    <label class="input-group-text" for="editReceipts">
                                        <i class="fas fa-paperclip"></i>
                                    </label>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- Status & Dates -->
                    <div class="row mb-4">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Balance Amount</label>
                            <div class="input-group">
                                <span class="input-group-text">₹</span>
                                <input type="number" class="form-control" id="editBalanceAmount" name="balance_amount"
                                    step="0.01" min="0" readonly>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label required">Status</label>
                            <select class="form-select" id="editStatus" name="status" required>
                                <option value="pending">Pending</option>
                                <option value="received">Received</option>
                                <option value="overdue">Overdue</option>
                                <option value="settle">Settle</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Due Date</label>
                            <input type="date" class="form-control" id="editDueDate" name="due_date">
                        </div>

                    </div>

                    <!-- Tax Information -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <h6 class="border-bottom pb-2 mb-3">Tax Information</h6>
                        </div>
                        <div id="gstSection">
                            <!-- GST Section -->
                            <div class="d-flex justify-content-between mb-2">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" name="apply_gst" id="editApplyGst"
                                        value="1" checked>
                                    <label class="form-check-label" for="editApplyGst">Apply GST</label>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">GST %</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="editGstPercentage"
                                            name="gst_percentage" value="18" min="0" max="100" step="0.01">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">GST Amount</label>
                                    <input type="number" class="form-control" id="editGstAmount" name="gst_amount"
                                        readonly>
                                </div>
                            </div>

                        </div>
                        <div id="tdsSection">

                            <!-- TDS Section -->
                            <div class="d-flex justify-content-between mb-2">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" name="apply_tds" id="editApplyTds"
                                        value="1" checked>
                                    <label class="form-check-label" for="editApplyTds">Apply TDS</label>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">TDS %</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="editTdsPercentage"
                                            name="tds_percentage" value="10" min="0" max="100" step="0.01">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">TDS Amount</label>
                                    <input type="number" class="form-control" id="editTdsAmount" name="tds_amount"
                                        readonly>
                                </div>
                            </div>
                            <div class="row mb-3">

                                <div class="col-md-6">
                                    <label class="form-label">TDS Status</label>
                                    <select class="form-select" id="editTdsStatus" name="tds_status">
                                        <option value="">Select Status</option>
                                        <option value="received">Received</option>
                                        <option value="not_received">Not Received</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Receipt</label>
                                    <input type="file" id="editTdsReceipt" name="tds_receipt" class="form-control"
                                        accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                                </div>
                            </div>
                        </div>

                    </div>
                    <!-- Vendor Information -->
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Vendor/Party Name</label>
                            <input type="text" class="form-control" id="editPartyName" name="party_name">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Mobile Number</label>
                            <input type="tel" class="form-control" id="editMobileNumber" name="mobile_number"
                                pattern="[0-9]{10}" maxlength="10">
                        </div>
                    </div>


                    <!-- Notes -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" id="editNotes" name="notes" rows="3"
                                placeholder="Add any additional notes..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-primary" id="editSubmitBtn">
                        <i class="fas fa-save me-1"></i>Update Income
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Add this modal to your HTML if not present -->
<div class="modal fade" id="viewInvoiceModal" tabindex="-1" aria-labelledby="viewInvoiceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewInvoiceModalLabel">Invoice Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="invoiceDetailsContent">
                    <!-- Content will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="printInvoice()">
                    <i class="fas fa-print"></i> Print
                </button>
            </div>
        </div>
    </div>
</div>
<!-- Correct Bootstrap modal structure -->
<div class="modal fade" id="sendInvoiceModal" tabindex="-1" aria-labelledby="sendInvoiceLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="sendInvoiceLabel">Send Invoice via Email</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="sendInvoiceForm" method="POST" action="{{ route ( 'income.send-email' ) }}">
                    @csrf
                    <input type="hidden" name="invoice_id" id="send_invoice_id">
                    <input type="hidden" name="income_id" id="send_income_id">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Invoice Number</label>
                            <input type="text" class="form-control" id="send_invoice_no" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Invoice Type</label>
                            <input type="text" class="form-control" id="send_invoice_type_display" readonly>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">To Email *</label>
                            <input type="email" class="form-control" id="send_to_email" name="to_email" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">CC Email (optional)</label>
                            <input type="text" class="form-control" id="send_cc_email" name="cc_email"
                                placeholder="comma-separated emails">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Subject *</label>
                            <input type="text" class="form-control" id="send_subject" name="subject" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Message Body *</label>
                            <textarea class="form-control" id="send_message" name="message" rows="6"
                                required></textarea>
                            {{-- <small class="text-muted">
                                    Available variables: {client_name}, {invoice_no}, {due_date}, {amount}, {company_name}
                                </small> --}}
                        </div>

                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="attach_pdf" name="attach_pdf"
                                    checked>
                                <label class="form-check-label" for="attach_pdf">
                                    Attach PDF copy of invoice
                                </label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="send_confirm_btn">
                    <i class="fas fa-paper-plane"></i> Send Invoice
                </button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="splitHistoryModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Split Payment History</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="splitHistoryContent">
                    <!-- Split history will be loaded here -->
                </div>
                <div id="noSplitHistory" class="text-center py-4" style="display: none;">
                    <i class="fas fa-code-branch fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No split payment history found</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Apply filters
    function applyFilters() {
        const companyId = document.getElementById('companyFilter').value;
        const category = document.getElementById('categoryFilter').value;
        const dateRange = document.getElementById('dateRangeFilter').value;
        const status = document.getElementById('statusFilter').value;

        const url = new URL(window.location.href);

        // Set or remove company filter
        if (companyId) {
            url.searchParams.set('company', companyId);
        } else {
            url.searchParams.delete('company');
        }

        // Set or remove category filter
        if (category && category !== 'all') {
            url.searchParams.set('category', category);
        } else {
            url.searchParams.delete('category');
        }

        // Set date range
        if (dateRange) {
            url.searchParams.set('date_range', dateRange);
        } else {
            url.searchParams.set('date_range', 'month');
        }

        // Set or remove status filter
        if (status && status !== 'all') {
            url.searchParams.set('status', status);
        } else {
            url.searchParams.delete('status');
        }

        // Reset to page 1 when filtering
        url.searchParams.delete('page');

        window.location.href = url.toString();
    }

    // Update date range (kept for backward compatibility)
    function updateDateRange() {
        applyFilters();
    }

    // Reset all filters
    function resetFilters() {
        const url = new URL(window.location.href);

        // Remove all query parameters except the base route
        url.search = '';

        window.location.href = url.toString();
    }

    // Filter payments by status
    function filterPayments(status) {
        const url = new URL(window.location.href);

        if (status === 'all') {
            url.searchParams.delete('status');
        } else {
            url.searchParams.set('status', status);
        }

        window.location.href = url.toString();
    }

    // Open add income modal
    function openAddIncomeModal() {
        document.getElementById('modalTitle').textContent = 'Add Non-standard Income';
        document.getElementById('incomeForm').reset();
        document.getElementById('incomeId').value = '';
        document.getElementById('incomeDate').value = '{{ date ( "Y-m-d" ) }}';
        document.getElementById('status').value = 'pending';
        document.getElementById('mailStatus').value = '0';

        const modal = new bootstrap.Modal(document.getElementById('incomeModal'));
        modal.show();
    }

    // Edit income
    // Initialize tax calculation when modal is shown
    const incomeModal = document.getElementById('incomeModal');
    if (incomeModal) {
        // Add event listeners for tax calculation
        const actualAmountInput = document.getElementById('actualAmount');
        const gstCheckbox = document.getElementById('applyGst');
        const tdsCheckbox = document.getElementById('applyTds');
        const gstPercentageInput = document.getElementById('gst_percentage');
        const tdsPercentageInput = document.getElementById('tds_percentage');
        const receivedAmountInput = document.getElementById('received_amount');

        // Function to calculate taxes
        function calculateIncomeTax() {
            const actualAmount = parseFloat(actualAmountInput.value) || 0;
            const applyGst = gstCheckbox.checked;
            const applyTds = tdsCheckbox.checked;
            const gstPercentage = parseFloat(gstPercentageInput.value) || 0;
            const tdsPercentage = parseFloat(tdsPercentageInput.value) || 0;
            const receivedAmount = parseFloat(receivedAmountInput.value) || 0;

            let gstAmount = 0;
            let tdsAmount = 0;
            let amountAfterGst = actualAmount;
            let amountAfterTds = actualAmount;
            let grandTotal = actualAmount;

            // Calculate GST if checked
            if (applyGst && gstPercentage > 0) {
                gstAmount = (actualAmount * gstPercentage) / 100;
                amountAfterGst = actualAmount + gstAmount;
                grandTotal = amountAfterGst; // Grand total includes GST
            }

            // Calculate TDS if checked (TDS is deducted separately, not part of grand total)
            if (applyTds && tdsPercentage > 0) {
                // TDS is calculated on GST-inclusive amount if GST is applied
                const baseForTds = applyGst ? amountAfterGst : actualAmount;
                tdsAmount = (baseForTds * tdsPercentage) / 100;
                amountAfterTds = baseForTds - tdsAmount; // This is after TDS deduction
                // Note: grandTotal remains unchanged as TDS is not included in grand total
            }

            // Update display fields
            document.getElementById('gst_amount').value = gstAmount.toFixed(2);
            document.getElementById('tds_amount').value = tdsAmount.toFixed(2);
            document.getElementById('amount_after_tds').value = amountAfterTds.toFixed(2);
            document.getElementById('grand_total').value = grandTotal.toFixed(2); // TDS not included

            // Calculate balance (based on grand total minus received amount)
            const balance = Math.max(0, grandTotal - receivedAmount);
            document.getElementById('balance_amount').value = balance.toFixed(2);

            // Enable/disable percentage inputs based on checkbox state
            gstPercentageInput.disabled = !applyGst;
            tdsPercentageInput.disabled = !applyTds;
        }
        // Add event listeners
        actualAmountInput.addEventListener('input', calculateIncomeTax);
        gstCheckbox.addEventListener('change', calculateIncomeTax);
        tdsCheckbox.addEventListener('change', calculateIncomeTax);
        gstPercentageInput.addEventListener('input', calculateIncomeTax);
        tdsPercentageInput.addEventListener('input', calculateIncomeTax);
        receivedAmountInput.addEventListener('input', calculateIncomeTax);

        // Initialize calculation on modal show
        incomeModal.addEventListener('show.bs.modal', function() {
            // Set default date for income date
            const incomeDateInput = document.getElementById('incomeDate');
            if (incomeDateInput && !incomeDateInput.value) {
                incomeDateInput.value = new Date().toISOString().split('T')[0];
            }

            // Set default date for received date
            const receivedDateInput = document.getElementById('received_date');
            if (receivedDateInput && !receivedDateInput.value) {
                receivedDateInput.value = new Date().toISOString().split('T')[0];
            }

            // Calculate initial tax
            setTimeout(() => {
                calculateIncomeTax();
                if (typeof handleTdsStatusBehavior === 'function') {
                    handleTdsStatusBehavior('addTdsStatus', 'addTdsReceipt');
                }
            }, 100);
        });

        // Also handle the edit function
        window.editIncome = async function(incomeId) {
            try {
                console.log('Editing income ID:', incomeId);

                const response = await fetch(
                    `/manager/income/${incomeId}/edit`);
                const data = await response.json();

                console.log('API Response:', data);

                if (data.success) {
                    const income = data.income;
                    console.log('Income data received:', income);

                    // Populate form fields
                    document.getElementById('modalTitle').textContent = 'Edit Income';
                    document.getElementById('incomeId').value = income.id;
                    document.getElementById('companyId').value = income.company_id;
                    document.getElementById('clientName').value = income.client_name;
                    document.getElementById('actualAmount').value = income.actual_amount_base || income.actual_amount || income.planned_amount;
                    document.getElementById('originalTotalBase').value = income.original_total_base || income.actual_amount;
                    document.getElementById('frequency').value = income.frequency || '';
                    document.getElementById('dueDay').value = income.due_day || '';
                    document.getElementById('status').value = income.status;
                    document.getElementById('incomeDate').value = income.income_date;
                    document.getElementById('mailStatus').value = income.mail_status ? '1' : '0';

                    // Debug log for tax values
                    console.log('Tax values from API:', {
                        gst_amount: income.gst_amount,
                        tds_amount: income.tds_amount,
                        gst_percentage: income.gst_percentage,
                        tds_percentage: income.tds_percentage
                    });

                    // Determine if GST/TDS should be checked
                    const hasGst = parseFloat(income.gst_amount) > 0 || parseFloat(income.gst_percentage) > 0;
                    const hasTds = parseFloat(income.tds_amount) > 0 || parseFloat(income.tds_percentage) > 0;

                    console.log('Checkbox states:', {
                        hasGst,
                        hasTds
                    });

                    // Set checkbox states
                    document.getElementById('applyGst').checked = hasGst;
                    document.getElementById('applyTds').checked = hasTds;

                    // Set percentage values
                    document.getElementById('gst_percentage').value = income.gst_percentage || 18;
                    document.getElementById('tds_percentage').value = income.tds_percentage || 10;

                    // Set amount values
                    document.getElementById('gst_amount').value = income.gst_amount || 0;
                    document.getElementById('tds_amount').value = income.tds_amount || 0;
                    document.getElementById('amount_after_tds').value = income.amount_after_tds || income
                        .actual_amount;
                    document.getElementById('grand_total').value = income.grand_total || income.actual_amount;
                    document.getElementById('addTdsStatus').value = income.tds_status || 'not_received';

                    // Received amounts
                    document.getElementById('received_amount').value = income.received_amount || 0;
                    document.getElementById('received_date').value = income.received_date || '';
                    document.getElementById('balance_amount').value = income.balance_amount || 0;

                    // Enable/disable percentage inputs based on checkbox state
                    document.getElementById('gst_percentage').disabled = !hasGst;
                    document.getElementById('tds_percentage').disabled = !hasTds;

                    // Show the modal
                    const modal = new bootstrap.Modal(document.getElementById('incomeModal'));
                    modal.show();

                    // Recalculate to ensure all fields are updated
                    setTimeout(() => {
                        console.log('Recalculating taxes after form population...');
                        calculateIncomeTax();
                    }, 500);
                } else {
                    alert(data.message || 'Error loading income data');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error loading income data');
            }
        }

    }
    // Update the form submission handler
    document.getElementById('incomeForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const incomeId = document.getElementById('incomeId').value;
        const url = incomeId ?
            `/manager/income/${incomeId}` :
            '/manager/income';

        // Add method spoofing for PUT
        if (incomeId) {
            formData.append('_method', 'PUT');
        }

        // Ensure checkbox values are properly set
        const checkboxes = ['apply_gst', 'apply_tds', 'mail_status'];
        checkboxes.forEach(name => {
            const checkbox = document.querySelector(`[name="${name}"]`);
            if (checkbox) {
                formData.set(name, checkbox.checked ? '1' : '0');
            }
        });

        // Show loading state
        const submitBtn = document.getElementById('submitBtn');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
        submitBtn.disabled = true;

        try {
            const response = await fetch(url, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                        .getAttribute(
                            'content')
                }
            });

            const data = await response.json();

            if (data.success) {
                // Show success message
                showNotification('success', data.message);

                // Hide modal
                $('#incomeModal').modal('hide');

                // Reload page after a short delay
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                // Show error message
                showNotification('error', data.message || 'Error saving income');

                // Reset button
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;

                // Show validation errors if any
                if (data.errors) {
                    console.error('Validation errors:', data.errors);
                    // Clear previous errors
                    document.querySelectorAll('.is-invalid').forEach(el => {
                        el.classList.remove('is-invalid');
                    });
                    document.querySelectorAll('.invalid-feedback').forEach(el => {
                        el.remove();
                    });

                    // Add new errors
                    Object.keys(data.errors).forEach(fieldName => {
                        const field = document.querySelector(`[name="${fieldName}"]`);
                        if (field) {
                            field.classList.add('is-invalid');
                            const errorElement = document.createElement('div');
                            errorElement.className = 'invalid-feedback';
                            errorElement.textContent = data.errors[fieldName][0];
                            field.parentNode.appendChild(errorElement);
                        }
                    });
                }
            }
        } catch (error) {
            console.error('Error:', error);
            showNotification('error', 'Error saving income: ' + error.message);

            // Reset button
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    });
    // Open receive payment modal
    async function openReceivePaymentModal(incomeId) {
        try {
            const response = await fetch(
                `/manager/income/details/${incomeId}`);
            const data = await response.json();

            if (data.success) {
                const income = data.income;

                // Populate modal fields
                document.getElementById('receiveIncomeId').value = income.id;
                document.getElementById('originalAmount').value = income.planned_amount || income
                    .amount;
                document.getElementById('invoiceNoDisplay').textContent = income.invoice_no || 'N/A';
                document.getElementById('companyDisplay').textContent = income.company?.name || 'N/A';
                document.getElementById('clientDisplay').textContent = income.client_name || 'N/A';
                document.getElementById('originalAmountDisplay').textContent = '₹' + (income
                    .planned_amount ||
                    income.amount);

                // Set payment date to today
                const today = new Date().toISOString().split('T')[0];
                document.getElementById('paymentDate').value = today;

                // Set new due date to 30 days from today
                const futureDate = new Date();
                futureDate.setDate(futureDate.getDate() + 30);
                document.getElementById('newDueDate').value = futureDate.toISOString().split('T')[0];

                // Reset received amount
                document.getElementById('receivedAmount').value = '';
                document.getElementById('balanceAmountDisplay').textContent = '0.00';

                // Show modal
                const modal = new bootstrap.Modal(document.getElementById('receivePaymentModal'));
                modal.show();

                // Initialize event listeners after modal is shown
                initializeReceivePaymentListeners();
            } else {
                alert(data.message || 'Error loading income details');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error loading income details');
        }
    }

    // Initialize event listeners for receive payment modal
    function initializeReceivePaymentListeners() {
        const receivedAmountInput = document.getElementById('receivedAmount');
        const originalAmount = parseFloat(document.getElementById('originalAmount').value) || 0;

        // Remove any existing listeners first
        const newReceivedAmountInput = receivedAmountInput.cloneNode(true);
        receivedAmountInput.parentNode.replaceChild(newReceivedAmountInput, receivedAmountInput);

        // Add new event listener
        document.getElementById('receivedAmount').addEventListener('input', function(e) {
            const originalAmount = parseFloat(document.getElementById('originalAmount').value) || 0;
            const receivedAmount = parseFloat(this.value) || 0;
            const balance = originalAmount - receivedAmount;

            console.log('Original Amount:', originalAmount);
            console.log('Received Amount:', receivedAmount);
            console.log('Balance:', balance);

            document.getElementById('balanceAmountDisplay').textContent = balance.toFixed(2);

            // Validate that received amount is less than original amount
            if (receivedAmount >= originalAmount) {
                this.classList.add('is-invalid');
                document.getElementById('newProformaSection').style.display = 'none';
            } else {
                this.classList.remove('is-invalid');
                document.getElementById('newProformaSection').style.display = 'block';
            }
        });

        // Toggle new proforma section
        const createNewProforma = document.getElementById('createNewProforma');
        if (createNewProforma) {
            const newCreateNewProforma = createNewProforma.cloneNode(true);
            createNewProforma.parentNode.replaceChild(newCreateNewProforma, createNewProforma);

            document.getElementById('createNewProforma').addEventListener('change', function() {
                document.getElementById('newProformaSection').style.display = this.checked ?
                    'block' : 'none';
                if (!this.checked) {
                    document.getElementById('newDueDate').removeAttribute('required');
                } else {
                    document.getElementById('newDueDate').setAttribute('required', 'required');
                }
            });
        }
    }

    // Handle receive payment form submission
    document.getElementById('receivePaymentForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const originalAmount = parseFloat(document.getElementById('originalAmount').value) || 0;
        const receivedAmount = parseFloat(document.getElementById('receivedAmount').value);

        console.log('Form Submit - Original:', originalAmount);
        console.log('Form Submit - Received:', receivedAmount);

        // Validate received amount
        if (receivedAmount <= 0) {
            alert('Please enter a valid amount received');
            return;
        }

        if (receivedAmount >= originalAmount) {
            alert('Received amount must be less than the original scheduled amount');
            return;
        }

        if (!confirm(
                'Are you sure you want to record this partial payment? This action cannot be undone.'
            )) {
            return;
        }

        const formData = new FormData(this);
        const incomeId = document.getElementById('receiveIncomeId').value;

        // Convert checkbox value to boolean (1/0)
        const createNewProforma = document.getElementById('createNewProforma').checked ? 1 : 0;
        formData.set('create_new_proforma', createNewProforma); // Override the string value

        fetch(`/manager/income/${incomeId}/receive-payment`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                        .getAttribute(
                            'content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    $('#receivePaymentModal').modal('hide');
                    location.reload();
                } else {
                    alert(data.message || 'Error recording payment');
                    // Show validation errors if any
                    if (data.errors) {
                        console.error('Validation errors:', data.errors);
                        Object.keys(data.errors).forEach(fieldName => {
                            const field = document.querySelector(
                                `[name="${fieldName}"]`);
                            if (field) {
                                field.classList.add('is-invalid');
                                const errorElement = document.createElement('div');
                                errorElement.className = 'invalid-feedback';
                                errorElement.textContent = data.errors[fieldName][0];
                                field.parentNode.appendChild(errorElement);
                            }
                        });
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error recording payment');
            });
    });
    // Handle income form submission

    // Delete Income
    function deleteIncome(incomeId) {
        if (confirm('Are you sure you want to delete this income? This action cannot be undone.')) {
            fetch(`/manager/income/${incomeId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token () }}',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification('success', data.message);
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showNotification('error', data.message || 'Error deleting income');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('error', 'Failed to delete income');
                });
        }
    }

    // Helper function to show notifications
    function showNotification(type, message) {
        // Remove existing notifications
        const existingNotifications = document.querySelectorAll('.custom-notification');
        existingNotifications.forEach(notification => notification.remove());

        // Create notification element
        const notification = document.createElement('div');
        notification.className =
            `custom-notification alert alert-${type === 'success' ? 'success' : 'danger'}`;
        notification.style.cssText = `
                                                                    position: fixed;
                                                                    top: 20px;
                                                                    right: 20px;
                                                                    z-index: 9999;
                                                                    padding: 15px 20px;
                                                                    border-radius: 5px;
                                                                    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                                                                    min-width: 300px;
                                                                    max-width: 400px;
                                                                `;
        notification.innerHTML = `
                                                                    <div class="d-flex justify-content-between align-items-center">
                                                                        <span>${message}</span>
                                                                        <button type="button" class="btn-close" onclick="this.parentElement.parentElement.remove()"></button>
                                                                    </div>
                                                                `;

        document.body.appendChild(notification);

        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 5000);
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Ensure checkboxes send proper values
        const checkboxes = document.querySelectorAll('input[type="checkbox"]');
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                if (this.checked) {
                    this.value = '1';
                } else {
                    this.value = '0';
                }
            });
        });
    });
    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Page loaded, income module initialized');
    });
</script>

<script>
    function calculateTax() {
        try {
            // Get base amount
            const baseAmount = parseFloat(document.getElementById('actualAmount').value) || 0;

            // Check if taxes should be applied
            const applyGst = document.getElementById('applyGst')?.checked || false;
            const applyTds = document.getElementById('applyTds')?.checked || false;

            // Get tax percentages
            const gstPercentage = applyGst ? (parseFloat(document.getElementById('gst_percentage').value) || 0) : 0;
            const tdsPercentage = applyTds ? (parseFloat(document.getElementById('tds_percentage').value) || 0) : 0;

            // Calculate amounts
            const gstAmount = (baseAmount * gstPercentage) / 100;
            const tdsAmount = (baseAmount * tdsPercentage) / 100;
            const amountAfterTDS = baseAmount - tdsAmount;
            const grandTotal = baseAmount + gstAmount;

            // Update fields
            const gstAmountField = document.getElementById('gst_amount');
            const tdsAmountField = document.getElementById('tds_amount');
            const amountAfterTDSField = document.getElementById('amount_after_tds');
            const grandTotalField = document.getElementById('grand_total');

            if (gstAmountField) gstAmountField.value = gstAmount.toFixed(2);
            if (tdsAmountField) tdsAmountField.value = tdsAmount.toFixed(2);
            if (amountAfterTDSField) amountAfterTDSField.value = amountAfterTDS.toFixed(2);
            if (grandTotalField) grandTotalField.value = grandTotal.toFixed(2);

            // Recalculate balance
            calculateBalance();

        } catch (error) {
            console.error('Error in calculateTax:', error);
        }
    }

    function calculateBalance() {
        try {
            console.log('Calculating balance...');

            const grandTotalField = document.getElementById('grand_total');
            const receivedAmountField = document.getElementById('received_amount');
            const balanceField = document.getElementById('balance_amount');

            if (!grandTotalField || !receivedAmountField || !balanceField) {
                console.error('Required fields not found');
                return;
            }

            const grandTotal = parseFloat(grandTotalField.value) || 0;
            const receivedAmount = parseFloat(receivedAmountField.value) || 0;
            const balance = grandTotal - receivedAmount;

            console.log('Grand Total:', grandTotal, 'Received:', receivedAmount, 'Balance:', balance);

            balanceField.value = balance.toFixed(2);

        } catch (error) {
            console.error('Error in calculateBalance:', error);
        }
    }

    // Initialize when page loads
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM loaded, initializing tax calculator...');

        // Add event listeners to all calculation inputs
        const calcInputs = ['actualAmount', 'gst_percentage', 'tds_percentage', 'received_amount'];

        calcInputs.forEach(id => {
            const input = document.getElementById(id);
            if (input) {
                console.log('Adding listener to:', id);
                input.addEventListener('input', function() {
                    if (id === 'received_amount') {
                        calculateBalance();
                    } else {
                        calculateTax();
                    }
                });
            } else {
                console.warn('Input not found:', id);
            }
        });

        // Add event listeners for checkboxes
        const gstCheckbox = document.getElementById('applyGst');
        const tdsCheckbox = document.getElementById('applyTds');

        if (gstCheckbox) {
            console.log('Adding GST checkbox listener');
            gstCheckbox.addEventListener('change', function() {
                const gstPercentageInput = document.getElementById('gst_percentage');
                if (gstPercentageInput) {
                    gstPercentageInput.disabled = !this.checked;
                    if (!this.checked) {
                        gstPercentageInput.value = '0';
                    }
                }
                calculateTax();
            });
        }

        if (tdsCheckbox) {
            console.log('Adding TDS checkbox listener');
            tdsCheckbox.addEventListener('change', function() {
                const tdsPercentageInput = document.getElementById('tds_percentage');
                if (tdsPercentageInput) {
                    tdsPercentageInput.disabled = !this.checked;
                    if (!this.checked) {
                        tdsPercentageInput.value = '0';
                    }
                }
                calculateTax();
                handleTdsStatusBehavior('addTdsStatus', 'addTdsReceipt');
            });
        }

        // Enable/disable tax percentage inputs based on initial checkbox state
        if (gstCheckbox && document.getElementById('gst_percentage')) {
            document.getElementById('gst_percentage').disabled = !gstCheckbox.checked;
        }

        if (tdsCheckbox && document.getElementById('tds_percentage')) {
            document.getElementById('tds_percentage').disabled = !tdsCheckbox.checked;
        }

        // Initial calculation
        console.log('Running initial calculation...');
        calculateTax();

        // Add listener for TDS Status in Add Modal
        const addTdsStatus = document.getElementById('addTdsStatus');
        if (addTdsStatus) {
            addTdsStatus.addEventListener('change', function() {
                handleTdsStatusBehavior('addTdsStatus', 'addTdsReceipt');
            });
        }
    });

    function handleStatusBehavior(modalType) {
        let statusId, balanceId, dueDateId;

        if (modalType === 'income-edit') {
            statusId = 'editStatus';
            balanceId = 'editBalanceAmount';
            dueDateId = 'editDueDate';
        }

        const statusEl = document.getElementById(statusId);
        const balanceEl = document.getElementById(balanceId);
        const dueDateEl = document.getElementById(dueDateId);

        if (!statusEl) return;

        const status = statusEl.value;
        let balance = 0;
        if (balanceEl) {
            balance = parseFloat(balanceEl.value) || 0;
        }

        if (status === 'settle') {
            if (balanceEl) {
                if (balanceEl.tagName === 'INPUT') {
                    balanceEl.value = '0.00';
                } else {
                    balanceEl.textContent = '0.00';
                }
            }
            if (dueDateEl) {
                dueDateEl.disabled = true;
                dueDateEl.required = false;
                dueDateEl.value = '';
            }
        } else if (status === 'due' || status === 'pending' || status === 'overdue') {
            if (dueDateEl) {
                dueDateEl.disabled = false;
                if (balance > 0) {
                    dueDateEl.required = true;
                } else {
                    dueDateEl.required = false;
                }
            }
        } else {
            if (dueDateEl) {
                dueDateEl.disabled = false;
                dueDateEl.required = false;
            }
        }
    }

    function handleTdsStatusBehavior(statusId, fileId) {
        const statusEl = document.getElementById(statusId);
        const fileEl = document.getElementById(fileId);

        if (!statusEl || !fileEl) return;

        // Find if this is within a hidden section (e.g. TDS section)
        const section = fileEl.closest('.row')?.parentElement?.closest('[id$="Section"]');
        const isHidden = section && section.style.display === 'none';

        const status = statusEl.value;
        // For income, 'received' or 'paid' are the mandatory statuses
        // But only if the section itself is not hidden
        if (!isHidden && (status === 'received' || status === 'paid')) {
            fileEl.required = true;
            fileEl.setAttribute('required', 'required');

            // Add asterisk to label
            const label = fileEl.closest('div').querySelector('label');
            if (label && !label.querySelector('.text-danger')) {
                label.innerHTML += ' <span class="text-danger">*</span>';
            }
        } else {
            fileEl.required = false;
            fileEl.removeAttribute('required');

            // Remove asterisk from label
            const label = fileEl.closest('div').querySelector('label');
            if (label) {
                const asterisk = label.querySelector('.text-danger');
                if (asterisk) asterisk.remove();
            }
        }
    }
</script>




<script>
    async function openEditIncomeModal(incomeId) {
        try {
            console.log('Opening edit modal for income ID:', incomeId);

            const response = await fetch(
                `/manager/income/${incomeId}/edit`
            );
            const data = await response.json();

            if (data.success && data.income) {
                const income = data.income;
                console.log('Income data loaded:', income);
                console.log('Has invoice_id?', income.invoice_id);

                // Populate basic form fields
                document.getElementById('editIncomeId').value = income.id;
                document.getElementById('editCompanyId').value = income.company_id || '';
                document.getElementById('editClientName').value = income.client_name || '';
                document.getElementById('editPlannedAmount').value = income.planned_amount || income
                    .actual_amount || 0;

                // FIXED: Use actual paid_amount from database
                document.getElementById('editPaidAmount').value = income.received_amount || income.paid_amount || 0;
                document.getElementById('editOriginalAmount').value = income.planned_amount || 0;

                // Format dates
                if (income.paid_date) {
                    document.getElementById('editPaidDate').value = formatDateForInput(income.paid_date);
                }
                if (income.due_date) {
                    document.getElementById('editDueDate').value = formatDateForInput(income.due_date);
                }

                // Determine if it's standard income (has invoice_id)
                const isStandardIncome = income.invoice_id && income.invoice_id > 0;
                console.log('Is standard income?', isStandardIncome, 'invoice_id:', income.invoice_id);

                // Calculate and set balance for ALL incomes (including standard)
                const planned = parseFloat(income.planned_amount || income.actual_amount || 0);
                const paid = parseFloat(income.received_amount || income.paid_amount ||
                    0); // Use actual paid amount
                const gstAmount = parseFloat(income.gst_amount || 0);
                const tdsAmount = parseFloat(income.tds_amount || 0);

                // For standard income, balance should be: (Planned + GST - TDS) - Paid
                // For non-standard income, balance should be: Planned - TDS - Paid
                let totalAmount = planned - tdsAmount;
                if (isStandardIncome) {
                    totalAmount = planned + gstAmount - tdsAmount;
                }

                const balance = totalAmount - paid;

                console.log('Balance calculation:', {
                    planned,
                    paid,
                    gstAmount,
                    tdsAmount,
                    totalAmount,
                    balance,
                    isStandardIncome
                });

                document.getElementById('editBalanceAmount').value = Math.max(0, balance).toFixed(2);
                document.getElementById('editStatus').value = income.status || 'pending';
                document.getElementById('editPaymentMode').value = income.payment_mode || '';
                document.getElementById('editPartyName').value = income.party_name || '';
                document.getElementById('editMobileNumber').value = income.mobile_number || '';
                document.getElementById('editNotes').value = income.notes || '';

                // Get tax section elements
                const gstSection = document.querySelector('#gstSection');
                const tdsSection = document.querySelector('#tdsSection');
                const gstCheckbox = document.getElementById('editApplyGst');
                const tdsCheckbox = document.getElementById('editApplyTds');
                const gstPercentageInput = document.getElementById('editGstPercentage');
                const tdsPercentageInput = document.getElementById('editTdsPercentage');
                const gstAmountInput = document.getElementById('editGstAmount');
                const tdsAmountInput = document.getElementById('editTdsAmount');

                console.log('Checkbox states:', {
                    hasGst: parseFloat(income.gst_amount) > 0 || parseFloat(income.gst_percentage) > 0,
                    hasTds: parseFloat(income.tds_amount) > 0 || parseFloat(income.tds_percentage) > 0
                });

                // ALWAYS show both sections first
                if (gstSection) gstSection.style.display = 'block';
                if (tdsSection) tdsSection.style.display = 'block';

                // GST handling
                const hasGst = parseFloat(income.gst_amount) > 0 || parseFloat(income.gst_percentage) > 0;
                if (gstCheckbox) {
                    gstCheckbox.checked = hasGst;
                    gstCheckbox.disabled = isStandardIncome; // Disable for standard income
                }
                if (gstPercentageInput) {
                    gstPercentageInput.value = income.gst_percentage || (hasGst ? 18 : 0);
                    gstPercentageInput.disabled = isStandardIncome || !hasGst;
                }
                if (gstAmountInput) {
                    gstAmountInput.value = income.gst_amount || 0;
                }

                // TDS handling
                const hasTds = parseFloat(income.tds_amount) > 0 || parseFloat(income.tds_percentage) > 0;
                if (tdsCheckbox) {
                    tdsCheckbox.checked = hasTds;
                    tdsCheckbox.disabled = isStandardIncome; // Disable for standard income
                }
                if (tdsPercentageInput) {
                    tdsPercentageInput.value = income.tds_percentage || (hasTds ? 10 : 0);
                    tdsPercentageInput.disabled = isStandardIncome || !hasTds;
                }
                if (tdsAmountInput) {
                    tdsAmountInput.value = income.tds_amount || 0;
                }

                // TDS status field
                const tdsStatusSelect = document.getElementById('editTdsStatus');
                if (tdsStatusSelect) {
                    tdsStatusSelect.value = income.tds_status || 'not_received';
                    tdsStatusSelect.disabled = isStandardIncome; // Disable for standard income
                }

                // Show/hide entire tax sections based on checkbox state
                if (isStandardIncome) {
                    // For standard income, always show GST/TDS sections if they have values
                    if (gstSection) {
                        gstSection.style.display = hasGst ? 'block' : 'none';
                    }
                    if (tdsSection) {
                        tdsSection.style.display = hasTds ? 'block' : 'none';
                    }

                    // Add labels to indicate they're read-only
                    if (hasGst && gstSection) {
                        const readOnlyLabel = document.createElement('small');
                        readOnlyLabel.className = 'text-muted ms-2';
                        readOnlyLabel.innerHTML = '<i class="fas fa-lock"></i>';
                        const gstLabel = gstSection.querySelector('.form-check-label');
                        if (gstLabel) {
                            gstLabel.appendChild(readOnlyLabel);
                        }
                    }

                    if (hasTds && tdsSection) {
                        const readOnlyLabel = document.createElement('small');
                        readOnlyLabel.className = 'text-muted ms-2';
                        readOnlyLabel.innerHTML = '<i class="fas fa-lock"></i>';
                        const tdsLabel = tdsSection.querySelector('.form-check-label');
                        if (tdsLabel) {
                            tdsLabel.appendChild(readOnlyLabel);
                        }
                    }
                }

                // Initialize tax calculation for ALL incomes
                // But for standard income, disable the automatic calculations
                console.log('Initializing tax calculation');
                initializeEditTaxAndBalance();

                // For standard income, we need to set up a manual balance calculation for paid amount changes
                if (isStandardIncome) {
                    setupStandardIncomeBalanceCalculation(income);
                }

                // Show modal
                const modal = new bootstrap.Modal(document.getElementById('editIncomeModal'));
                modal.show();

                // Initial behavior call
                setTimeout(() => {
                    handleStatusBehavior('income-edit');
                    handleTdsStatusBehavior('editTdsStatus', 'editTdsReceipt');
                }, 500);

                // Add listeners for status changes in edit modal
                const editStatus = document.getElementById('editStatus');
                if (editStatus) {
                    editStatus.addEventListener('change', function() {
                        handleStatusBehavior('income-edit');
                        // If switching to 'due', recalculate to restore balance
                        if (this.value !== 'settle') {
                            if (typeof window.recalculateEditIncome === 'function') {
                                window.recalculateEditIncome();
                            }
                        }
                    });
                }

                const editTdsStatus = document.getElementById('editTdsStatus');
                if (editTdsStatus) {
                    editTdsStatus.addEventListener('change', function() {
                        handleTdsStatusBehavior('editTdsStatus', 'editTdsReceipt');
                    });
                }

                const editBalanceAmount = document.getElementById('editBalanceAmount');
                if (editBalanceAmount) {
                    // We might need to watch for changes if it's updated via calculation
                    const observer = new MutationObserver(() => handleStatusBehavior('income-edit'));
                    observer.observe(editBalanceAmount, {
                        attributes: true,
                        attributeFilter: ['value']
                    });
                    // Also hook into the calculation function
                }

            } else {
                alert(data.message || 'Error loading income data');
            }
        } catch (error) {
            console.error('Error loading income:', error);
            alert('Error loading income data');
        }
    }

    // Add this new function for standard income balance calculation
    function setupStandardIncomeBalanceCalculation(income) {
        const plannedAmountInput = document.getElementById('editPlannedAmount');
        const paidAmountInput = document.getElementById('editPaidAmount');
        const balanceAmountInput = document.getElementById('editBalanceAmount');
        const gstAmountInput = document.getElementById('editGstAmount');
        const tdsAmountInput = document.getElementById('editTdsAmount');

        if (!paidAmountInput || !balanceAmountInput) return;

        // Function to calculate balance for standard income
        function calculateStandardIncomeBalance() {
            const planned = parseFloat(plannedAmountInput.value) || 0;
            const paid = parseFloat(paidAmountInput.value) || 0;
            const gstAmount = parseFloat(gstAmountInput ? gstAmountInput.value : 0) || 0;
            const tdsAmount = parseFloat(tdsAmountInput ? tdsAmountInput.value : 0) || 0;
            // Standard income balance formula: (Planned + GST - TDS) - Paid
            const totalAmount = planned - tdsAmount;
            const balance = totalAmount - paid;

            console.log('Standard income balance calculation:', {
                planned,
                paid,
                gstAmount,
                tdsAmount,
                totalAmount,
                balance
            });

            if (balanceAmountInput) {
                balanceAmountInput.value = Math.max(0, balance).toFixed(2);
            }
        }

        // Add event listener for paid amount changes
        paidAmountInput.addEventListener('input', calculateStandardIncomeBalance);

        // Initial calculation
        calculateStandardIncomeBalance();
    }
    async function viewSplitHistory(expenseId) {
        try {
            const response = await fetch(
                `/manager/income/${expenseId}/split-history`);
            const data = await response.json();

            const splitHistoryContent = document.getElementById('splitHistoryContent');
            splitHistoryContent.innerHTML = '';

            if (data.success && (data.parent_expense || data.children.length > 0)) {
                document.getElementById('noSplitHistory').style.display = 'none';

                let historyHTML = '';

                // Show parent expense if this is a child
                if (data.parent_expense) {
                    historyHTML += `
                        <div class="card mb-3 border-primary">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0">Original Expense (Parent)</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <strong>Expense ID:</strong><br>
                                        <span class="badge bg-primary">#${data.parent_expense.id}</span>
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Original Amount:</strong><br>
                                        ₹${parseFloat(data.parent_expense.planned_amount).toFixed(2)}
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Created Date:</strong><br>
                                        ${new Date(data.parent_expense.created_at).toLocaleDateString()}
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Split Status:</strong><br>
                                        <span class="badge bg-warning">Split Initiated</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                }

                // Show all children (split transactions)
                if (data.children.length > 0) {
                    historyHTML += `
                        <h6 class="mt-4 mb-3">Split Transactions:</h6>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Split #</th>
                                        <th>Expense ID</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Created Date</th>
                                        <th>Paid Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                    `;

                    data.children.forEach((child, index) => {
                        const statusClass = {
                            'paid': 'success',
                            'pending': 'warning',
                            'overdue': 'danger',
                            'due': 'info'
                        } [child.status] || 'secondary';

                        historyHTML += `
                            <tr ${child.id == expenseId ? 'class="table-info"' : ''}>
                                <td>${index + 1}</td>
                                <td>
                                    <span class="badge bg-${child.id == expenseId ? 'primary' : 'secondary'}">
                                        #${child.id}
                                    </span>
                                </td>
                                <td>₹${parseFloat(child.planned_amount).toFixed(2)}</td>
                                <td>
                                    <span class="badge bg-${statusClass}">
                                        ${child.status}
                                    </span>
                                </td>
                                <td>${new Date(child.created_at).toLocaleDateString()}</td>
                                <td>${child.paid_date ? new Date(child.paid_date).toLocaleDateString() : '-'}</td>
                            </tr>
                        `;
                    });

                    historyHTML += `
                                </tbody>
                            </table>
                        </div>
                    `;
                }

                // Show summary
                if (data.summary) {
                    historyHTML += `
                        <div class="card mt-4 border-success">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0">Split Summary</h6>
                            </div>
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col-md-3">
                                        <div class="text-muted">Original Amount</div>
                                        <div class="h5">₹${parseFloat(data.summary.original_amount).toFixed(2)}</div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="text-muted">Total Paid</div>
                                        <div class="h5 text-success">₹${parseFloat(data.summary.total_paid).toFixed(2)}</div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="text-muted">Total Balance</div>
                                        <div class="h5 text-warning">₹${parseFloat(data.summary.total_balance).toFixed(2)}</div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="text-muted">Split Count</div>
                                        <div class="h5">${data.summary.split_count}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                }

                splitHistoryContent.innerHTML = historyHTML;
            } else {
                document.getElementById('noSplitHistory').style.display = 'block';
            }

            const modal = new bootstrap.Modal(document.getElementById('splitHistoryModal'));
            modal.show();
        } catch (error) {
            console.error('Error loading split history:', error);
            alert('Error loading split history');
        }
    }

    // Also update the initializeEditTaxAndBalance function to handle standard income
    function initializeEditTaxAndBalance() {
        const plannedAmountInput = document.getElementById('editPlannedAmount');
        const paidAmountInput = document.getElementById('editPaidAmount');
        const gstCheckbox = document.getElementById('editApplyGst');
        const tdsCheckbox = document.getElementById('editApplyTds');
        const gstPercentageInput = document.getElementById('editGstPercentage');
        const tdsPercentageInput = document.getElementById('editTdsPercentage');
        const gstAmountInput = document.getElementById('editGstAmount');
        const tdsAmountInput = document.getElementById('editTdsAmount');
        const balanceAmountInput = document.getElementById('editBalanceAmount');

        // Expose a way to trigger recalculation from outside
        window.recalculateEditIncome = function() {
            if (paidAmountInput) {
                paidAmountInput.dispatchEvent(new Event('input', {
                    bubbles: true
                }));
            }
        };

        // Check if this is standard income (checkboxes disabled)
        const isStandardIncome = (gstCheckbox && gstCheckbox.disabled) || (tdsCheckbox && tdsCheckbox.disabled);

        // Function to calculate taxes and balance
        function calculateEditTaxAndBalance() {
            const plannedAmount = parseFloat(plannedAmountInput.value) || 0;
            const paidAmount = parseFloat(paidAmountInput.value) || 0;

            // For standard income, GST and TDS are fixed
            if (isStandardIncome) {
                const gstAmount = parseFloat(gstAmountInput ? gstAmountInput.value : 0) || 0;
                const tdsAmount = parseFloat(tdsAmountInput ? tdsAmountInput.value : 0) || 0;

                // Standard income: (Planned + GST - TDS) - Paid
                const totalAmount = plannedAmount + gstAmount - tdsAmount;
                const balance = totalAmount - paidAmount;

                console.log('Standard income calculation:', {
                    plannedAmount,
                    paidAmount,
                    gstAmount,
                    tdsAmount,
                    totalAmount,
                    balance
                });

                if (balanceAmountInput) {
                    balanceAmountInput.value = Math.max(0, balance).toFixed(2);
                    // Trigger status behavior after balance update
                    handleStatusBehavior('income-edit');
                }
            } else {
                // For non-standard income, calculate GST/TDS dynamically
                const applyGst = gstCheckbox ? gstCheckbox.checked : false;
                const applyTds = tdsCheckbox ? tdsCheckbox.checked : false;
                const gstPercentage = parseFloat(gstPercentageInput ? gstPercentageInput.value : 0) || 0;
                const tdsPercentage = parseFloat(tdsPercentageInput ? tdsPercentageInput.value : 0) || 0;

                console.log('Non-standard income calculation:', {
                    plannedAmount,
                    paidAmount,
                    applyGst,
                    applyTds,
                    gstPercentage,
                    tdsPercentage
                });

                let gstAmount = 0;
                if (applyGst && gstPercentage > 0) {
                    // Formula: GST = Gross - (Gross / (1 + Rate/100))
                    gstAmount = plannedAmount - (plannedAmount / (1 + (gstPercentage / 100)));
                }

                // Calculate TDS (on Base Amount = Planned - GST)
                let tdsAmount = 0;
                if (applyTds && tdsPercentage > 0) {
                    const baseAmount = plannedAmount - gstAmount;
                    tdsAmount = (baseAmount * tdsPercentage) / 100;
                }

                // Update GST/TDS amount fields
                if (gstAmountInput && !gstAmountInput.disabled) {
                    gstAmountInput.value = gstAmount.toFixed(2);
                }
                if (tdsAmountInput && !tdsAmountInput.disabled) {
                    tdsAmountInput.value = tdsAmount.toFixed(2);
                }

                // Calculate balance
                const totalAmount = plannedAmount - tdsAmount;
                const balance = totalAmount - paidAmount;

                console.log('Non-standard calculated values:', {
                    gstAmount,
                    tdsAmount,
                    totalAmount,
                    balance
                });

                if (balanceAmountInput) {
                    balanceAmountInput.value = Math.max(0, balance).toFixed(2);
                    // Trigger status behavior after balance update
                    handleStatusBehavior('income-edit');
                }
            }
        }

        // Add event listeners for amount inputs
        if (plannedAmountInput) {
            plannedAmountInput.addEventListener('input', calculateEditTaxAndBalance);
        }

        if (paidAmountInput) {
            paidAmountInput.addEventListener('input', calculateEditTaxAndBalance);
        }

        // Add checkbox listeners only if not disabled (non-standard income)
        if (gstCheckbox && !gstCheckbox.disabled) {
            gstCheckbox.addEventListener('change', function() {
                if (gstPercentageInput) {
                    gstPercentageInput.disabled = !this.checked;
                    if (!this.checked) {
                        gstPercentageInput.value = 0;
                    } else {
                        gstPercentageInput.value = 18;
                    }
                }
                calculateEditTaxAndBalance();
            });
        }

        if (tdsCheckbox && !tdsCheckbox.disabled) {
            tdsCheckbox.addEventListener('change', function() {
                if (tdsPercentageInput) {
                    tdsPercentageInput.disabled = !this.checked;
                    if (!this.checked) {
                        tdsPercentageInput.value = 0;
                    } else {
                        tdsPercentageInput.value = 10;
                    }
                }
                calculateEditTaxAndBalance();
                handleTdsStatusBehavior('editTdsStatus', 'editTdsReceipt');
            });
        }

        // Add percentage listeners only if not disabled
        if (gstPercentageInput && !gstPercentageInput.disabled) {
            gstPercentageInput.addEventListener('input', calculateEditTaxAndBalance);
        }

        if (tdsPercentageInput && !tdsPercentageInput.disabled) {
            tdsPercentageInput.addEventListener('input', calculateEditTaxAndBalance);
        }

        // Initial calculation - REMOVED to prevent overwriting saved values from DB
        // calculateEditTaxAndBalance();
    }
    // Format date for input field (YYYY-MM-DD)
    function formatDateForInput(dateString) {
        if (!dateString) return '';
        const date = new Date(dateString);
        return date.toISOString().split('T')[0];
    }

    // Handle edit form submission
    document.getElementById('editIncomeForm')?.addEventListener('submit', async function(e) {
        e.preventDefault();

        const incomeId = document.getElementById('editIncomeId').value;
        if (!incomeId) {
            alert('Invalid income ID');
            return;
        }

        const formData = new FormData(this);
        const url = `/manager/income/${incomeId}`;

        // Show loading state
        const submitBtn = document.getElementById('editSubmitBtn');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Updating...';
        submitBtn.disabled = true;

        try {
            const response = await fetch(url, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute(
                        'content') || ''
                }
            });

            const data = await response.json();

            if (data.success) {
                // Show success message
                showNotification('success', data.message || 'Income updated successfully');

                // Hide modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('editIncomeModal'));
                if (modal) modal.hide();

                // Reload page after delay
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                // Show error
                showNotification('error', data.message || 'Error updating income');

                // Reset button
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;

                // Show validation errors
                if (data.errors) {
                    Object.keys(data.errors).forEach(fieldName => {
                        const field = document.querySelector(`[name="${fieldName}"]`);
                        if (field) {
                            field.classList.add('is-invalid');
                            const errorDiv = document.createElement('div');
                            errorDiv.className = 'invalid-feedback';
                            errorDiv.textContent = data.errors[fieldName][0];
                            field.parentNode.appendChild(errorDiv);
                        }
                    });
                }
            }
        } catch (error) {
            console.error('Update error:', error);
            showNotification('error', 'Error updating income: ' + error.message);

            // Reset button
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    });

    // Delete receipt function
    async function deleteReceipt(receiptId, incomeId) {
        if (!confirm('Are you sure you want to delete this receipt?')) {
            return;
        }

        try {
            const response = await fetch(
                `/manager/receipts/${receiptId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute(
                            'content') || '',
                        'Accept': 'application/json'
                    }
                }
            );

            const data = await response.json();

            if (data.success) {
                showNotification('success', 'Receipt deleted successfully');
                // Reload the edit modal
                openEditIncomeModal(incomeId);
            } else {
                showNotification('error', data.message || 'Error deleting receipt');
            }
        } catch (error) {
            console.error('Delete error:', error);
            showNotification('error', 'Error deleting receipt');
        }
    }

    // Notification function (reuse from main page)
    function showNotification(type, message) {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show`;
        notification.style.cssText = `
                                                                position: fixed;
                                                                top: 20px;
                                                                right: 20px;
                                                                z-index: 9999;
                                                                min-width: 300px;
                                                                max-width: 400px;
                                                            `;
        notification.innerHTML = `
                                                                <div class="d-flex justify-content-between align-items-center">
                                                                    <span>${message}</span>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                                                </div>
                                                            `;

        document.body.appendChild(notification);

        setTimeout(() => {
            notification.remove();
        }, 5000);
    }

    function viewProforma(id) {
        console.log('Fetching invoice data for ID:', id);

        fetch(`/manager/income/getIncome/${id}`)
            .then(response => {
                console.log('Response status:', response.status);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('API Response:', data);

                if (data.success) {
                    const invoice = data.invoice;
                    const content = document.getElementById('invoiceDetailsContent');

                    if (!content) {
                        console.error('Element #invoiceDetailsContent not found!');
                        alert('Error: Invoice details container not found');
                        return;
                    }

                    if (!invoice) {
                        content.innerHTML = '<div class="alert alert-warning">No invoice associated with this income.</div>';
                        const modal = new bootstrap.Modal(document.getElementById('viewInvoiceModal'));
                        modal.show();
                        return;
                    }

                    // Calculate amounts excluding TDS
                    let gstTotal = 0;
                    let tdsTotal = 0;
                    let gstItems = [];
                    let tdsItems = [];

                    if (invoice && invoice.taxes && invoice.taxes.length > 0) {
                        console.log('Taxes found:', invoice.taxes);

                        // Separate GST and TDS
                        gstItems = invoice.taxes.filter(tax => tax.tax_type === 'gst');
                        tdsItems = invoice.taxes.filter(tax => tax.tax_type === 'tds');

                        console.log('GST Items:', gstItems);
                        console.log('TDS Items:', tdsItems);

                        // Calculate totals
                        gstItems.forEach(tax => gstTotal += parseFloat(tax.tax_amount) || 0);
                        tdsItems.forEach(tax => tdsTotal += parseFloat(tax.tax_amount) || 0);
                    }

                    console.log('GST Total:', gstTotal);
                    console.log('TDS Total:', tdsTotal);

                    // Calculate total without TDS
                    const subtotal = parseFloat(invoice.subtotal) || 0;
                    const totalWithoutTds = subtotal;

                    console.log('Subtotal:', subtotal);
                    console.log('Total without TDS:', totalWithoutTds);
                    console.log('Invoice total amount:', invoice.total_amount);

                    // Format line items HTML
                    let lineItemsHtml = '';
                    if (invoice.line_items && invoice.line_items.length > 0) {
                        invoice.line_items.forEach(item => {
                            lineItemsHtml += `
                                                                            <tr>
                                                                                <td>${item.description || 'Item'}</td>
                                                                                <td class="text-end">${item.quantity || 1}</td>
                                                                                <td class="text-end">₹${parseFloat(item.rate || 0).toFixed(2)}</td>
                                                                                <td class="text-end">₹${parseFloat(item.amount || 0).toFixed(2)}</td>
                                                                            </tr>
                                                                        `;
                        });
                    }

                    // Format GST details HTML (only GST, no TDS)
                    let gstHtml = '';

                    if (gstItems.length > 0) {
                        // Display GST breakdown
                        gstHtml = `
                                                                        <h6 class="mt-4">GST Details</h6>
                                                                        <table class="table table-sm">
                                                                            <thead>
                                                                                <tr>
                                                                                    <th>GST Type</th>
                                                                                    <th class="text-end">Percentage</th>
                                                                                    <th class="text-end">Amount</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                    `;

                        gstItems.forEach(tax => {
                            gstHtml += `
                                                                            <tr>
                                                                                <td>${tax.tax_type.toUpperCase()}</td>
                                                                                <td class="text-end">${parseFloat(tax.tax_percentage || 0).toFixed(2)}%</td>
                                                                                <td class="text-end">₹${parseFloat(tax.tax_amount || 0).toFixed(2)}</td>
                                                                            </tr>
                                                                        `;
                        });

                        gstHtml += `
                                                                            </tbody>
                                                                            <tfoot>
                                                                                <tr>
                                                                                    <th colspan="2" class="text-end">Total GST:</th>
                                                                                    <td class="text-end"><strong>₹${gstTotal.toFixed(2)}</strong></td>
                                                                                </tr>
                                                                            </tfoot>
                                                                        </table>
                                                                    `;
                    }

                    // Format dates properly
                    const formatDate = (dateString) => {
                        if (!dateString) return 'N/A';
                        try {
                            const date = new Date(dateString);
                            return date.toLocaleDateString('en-IN', {
                                year: 'numeric',
                                month: 'short',
                                day: 'numeric'
                            });
                        } catch (e) {
                            console.error('Date parsing error:', e);
                            return dateString;
                        }
                    };

                    const html = `
                                                                    <div class="container-fluid">
                                                                        <div class="row mb-4">
                                                                            <div class="col-md-8">
                                                                                <h5>Invoice Details</h5>
                                                                                <table class="table table-sm">
                                                                                    <tr>
                                                                                        <th width="150">Invoice Number:</th>
                                                                                        <td>${invoice.invoice_number || 'N/A'}</td>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <th>Company:</th>
                                                                                        <td>${invoice.company?.name || 'N/A'}</td>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <th>Client:</th>
                                                                                        <td>${invoice.client_details?.name || 'N/A'}</td>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <th>Email:</th>
                                                                                        <td>${invoice.client_details?.email || 'N/A'}</td>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <th>GSTIN:</th>
                                                                                        <td>${invoice.client_details?.gstin || 'N/A'}</td>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <th>Tax Type:</th>
                                                                                        <td>${invoice.tax_type || 'N/A'}</td>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <th>Issue Date:</th>
                                                                                        <td>${formatDate(invoice.issue_date)}</td>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <th>Due Date:</th>
                                                                                        <td>${formatDate(invoice.due_date)}</td>
                                                                                    </tr>
                                                                                </table>
                                                                            </div>
                                                                            <div class="col-md-4 text-end">
                                                                                <div class="alert ${invoice.status === 'pending' ? 'alert-warning' : invoice.status === 'received' ? 'alert-success' : invoice.status === 'overdue' ? 'alert-danger' : 'alert-secondary'}">
                                                                                    <strong>Status:</strong> ${(invoice.status || '').toUpperCase()}<br>
                                                                                    <strong>Type:</strong> ${(invoice.type || 'invoice').toUpperCase()}
                                                                                </div>
                                                                            </div>
                                                                        </div>

                                                                        <h6>Line Items</h6>
                                                                        <table class="table table-sm">
                                                                            <thead>
                                                                                <tr>
                                                                                    <th>Description</th>
                                                                                    <th class="text-end">Qty</th>
                                                                                    <th class="text-end">Rate</th>
                                                                                    <th class="text-end">Amount</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                                ${lineItemsHtml || '<tr><td colspan="4" class="text-center">No line items found</td></tr>'}
                                                                            </tbody>
                                                                            <tfoot>
                                                                                <tr>
                                                                                    <th colspan="3" class="text-end">Subtotal:</th>
                                                                                    <td class="text-end">₹${subtotal.toFixed(2)}</td>
                                                                                </tr>
                                                                                ${gstTotal > 0 ? `
                                                                                                                                                                    <tr>
                                                                                                                                                                        <th colspan="3" class="text-end">GST (${gstItems[0]?.tax_percentage || 0}%):</th>
                                                                                                                                                                        <td class="text-end">₹${gstTotal.toFixed(2)}</td>
                                                                                                                                                                    </tr>
                                                                                                                                                                ` : ''}
                                                                                <tr>
                                                                                    <th colspan="3" class="text-end">Total (excluding TDS):</th>
                                                                                    <td class="text-end"><strong>₹${totalWithoutTds.toFixed(2)}</strong></td>
                                                                                </tr>

                                                                                <tr class="border-top">
                                                                                    <th colspan="3" class="text-end">Net Amount Payable:</th>
                                                                                    <td class="text-end"><strong>₹${parseFloat(invoice.total_amount || totalWithoutTds - tdsTotal).toFixed(2)}</strong></td>
                                                                                </tr>
                                                                            </tfoot>
                                                                        </table>

                                                                        ${gstHtml}

                                                                        ${invoice.purpose_comment ? `
                                                                                                                                                            <div class="mt-3">
                                                                                                                                                                <h6>Purpose Comment</h6>
                                                                                                                                                                <p class="text-muted">${invoice.purpose_comment}</p>
                                                                                                                                                            </div>
                                                                                                                                                        ` : ''}

                                                                        ${invoice.terms_conditions ? `
                                                                                                                                                            <div class="mt-3">
                                                                                                                                                                <h6>Terms & Conditions</h6>
                                                                                                                                                                <div class="text-muted" style="white-space: pre-line;">${invoice.terms_conditions}</div>
                                                                                                                                                            </div>
                                                                                                                                                        ` : ''}
                                                                    </div>
                                                                `;

                    console.log('Setting HTML content');
                    content.innerHTML = html;

                    // Show the modal
                    const modalElement = document.getElementById('viewInvoiceModal');
                    if (modalElement) {
                        console.log('Modal element found, showing modal');
                        const modal = new bootstrap.Modal(modalElement);
                        modal.show();
                    } else {
                        console.error('Modal element #viewInvoiceModal not found!');
                        alert('Error: Invoice modal not found');
                    }
                } else {
                    console.error('API Error:', data.message);
                    alert('Error loading invoice details: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Fetch Error:', error);
                alert('Error loading invoice details. Please check console for details.');
            });
    }

    // Add print function
    function printInvoice() {
        const content = document.getElementById('invoiceDetailsContent');
        if (content) {
            const printWindow = window.open('', '_blank');
            printWindow.document.write(`
                                                                <!DOCTYPE html>
                                                                <html>
                                                                <head>
                                                                    <title>Invoice Print</title>
                                                                    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
                                                                    <style>
                                                                        @media print {
                                                                            body { margin: 0; padding: 20px; }
                                                                            .no-print { display: none !important; }
                                                                        }
                                                                        .invoice-header { border-bottom: 2px solid #000; margin-bottom: 20px; padding-bottom: 10px; }
                                                                        .total-row { font-weight: bold; }
                                                                    </style>
                                                                </head>
                                                                <body>
                                                                    ${content.innerHTML}
                                                                    <div class="text-center mt-4 no-print">
                                                                        <button class="btn btn-primary" onclick="window.print()">Print</button>
                                                                        <button class="btn btn-secondary" onclick="window.close()">Close</button>
                                                                    </div>
                                                                </body>
                                                                </html>
                                                            `);
            printWindow.document.close();
        }
    }

    function closeSendModal() {
        const modal = document.getElementById('sendInvoiceModal');
        if (modal) {
            const bootstrapModal = bootstrap.Modal.getInstance(modal);
            if (bootstrapModal) {
                bootstrapModal.hide();
            }
        }
    }

    function downloadProforma(id) {
        // First get the invoice_id from income
        fetch(`/manager/income/getIncome/${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.income && data.income.invoice_id) {
                    // Use the invoice_id to download
                    window.open(
                        `/admin/income/${data.income.invoice_id}/download`,
                        '_blank');
                } else {
                    alert('No invoice found for this income');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error fetching income details');
            });
    }

    // Updated modal handling with income ID
    document.addEventListener('DOMContentLoaded', function() {
        const sendInvoiceModal = document.getElementById('sendInvoiceModal');

        if (sendInvoiceModal) {
            const sendConfirmBtn = document.getElementById('send_confirm_btn');

            // Modal show event - now using income ID
            sendInvoiceModal.addEventListener('show.bs.modal', async function(event) {
                const button = event.relatedTarget;
                const incomeId = button.dataset.incomeId;

                // Fetch income and invoice details
                try {
                    const response = await fetch(
                        `/manager/income/getIncome/${incomeId}`);
                    const data = await response.json();

                    if (data.success && (data.invoice || data.income)) {
                        const invoice = data.invoice || {};
                        const income = data.income;
                        
                        // Client details are already decoded by the controller
                        const clientDetails = invoice.client_details || {};
                        const clientName = clientDetails?.name || income?.party_name || 'Customer';
                        const clientEmail = clientDetails?.email || '';

                        // Set form values
                        document.getElementById('send_invoice_id').value = invoice.id || '';
                        document.getElementById('send_income_id').value = incomeId;
                        document.getElementById('send_invoice_no').value = invoice.invoice_number ||
                            '';

                        // Check if type exists, otherwise use a default
                        const invoiceType = invoice.type || 'invoice';
                        document.getElementById('send_invoice_type_display').value =
                            invoiceType === 'proforma' ? 'Proforma Invoice' : 'Tax Invoice';

                        document.getElementById('send_to_email').value = clientEmail;

                        // Format due date
                        let formattedDueDate = 'N/A';
                        if (invoice.due_date) {
                            try {
                                formattedDueDate = new Date(invoice.due_date).toLocaleDateString(
                                    'en-IN', {
                                        day: 'numeric',
                                        month: 'short',
                                        year: 'numeric'
                                    });
                            } catch (e) {
                                console.error('Error formatting due date:', e);
                            }
                        }

                        // Set default subject
                        const companyName = invoice.company?.name || income.company?.name || '';
                        const defaultSubject = invoiceType === 'proforma' ?
                            `Proforma Invoice ${invoice.invoice_number} from ${companyName}` :
                            `Invoice ${invoice.invoice_number} from ${companyName}`;
                        document.getElementById('send_subject').value = defaultSubject;

                        // Set default message - use income amount or invoice amount
                        const amount = income?.amount || income?.actual_amount ||
                            invoice?.total_amount || invoice?.amount || 0;
                        const defaultMessage = `Dear ${clientName},

                                    ${invoiceType === 'proforma' ? 'Please find attached the proforma invoice' : 'Please find attached your invoice'} for ₹${parseFloat(amount).toFixed(2)}.

                                    Invoice Details:
                                    - Invoice Number: ${invoice.invoice_number || ''}
                                    - Amount: ₹${parseFloat(amount).toFixed(2)}
                                    ${invoice.due_date ? `- Due Date: ${formattedDueDate}` : ''}

                                    Please let us know if you have any questions.

                                    Best regards,
                                    ${companyName}`;

                        document.getElementById('send_message').value = defaultMessage;

                    } else {
                        alert('Error loading income details: ' + (data.message || 'Unknown error'));
                        bootstrap.Modal.getInstance(sendInvoiceModal).hide();
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Error loading details: ' + error.message);
                    bootstrap.Modal.getInstance(sendInvoiceModal).hide();
                }
            });

            // Confirm send invoice
            sendConfirmBtn.addEventListener('click', function() {
                const form = document.getElementById('sendInvoiceForm');
                if (!form) {
                    alert('Form not found');
                    return;
                }

                const formData = new FormData(form);
                const submitBtn = this;
                const originalText = submitBtn.innerHTML;

                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
                submitBtn.disabled = true;

                fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .content
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            alert(data.message || 'Invoice sent successfully!');
                            const modal = bootstrap.Modal.getInstance(sendInvoiceModal);
                            modal.hide();

                            // Optional: Reload the page or update UI
                            window.location.reload();
                        } else {
                            alert(data.message || 'Error sending invoice');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while sending the invoice: ' + error.message);
                    })
                    .finally(() => {
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    });
            });
        }
    });
</script>

<style>
    /* Edit Modal Specific Styles */
    .required::after {
        content: " *";
        color: #dc3545;
    }

    .modal-header.bg-primary {
        background: linear-gradient(135deg, #4e73df 0%, #224abe 100%) !important;
    }

    .btn-close-white {
        filter: invert(1) grayscale(100%) brightness(200%);
    }

    .form-control:read-only {
        background-color: #f8f9fa;
        cursor: not-allowed;
    }

    .list-group-item {
        border-left: none;
        border-right: none;
        border-radius: 0 !important;
    }

    .list-group-item:first-child {
        border-top: none;
    }

    .list-group-item:last-child {
        border-bottom: none;
    }

    .input-group-text {
        background-color: #f8f9fa;
        border-color: #dee2e6;
    }

    .invalid-feedback {
        display: block;
        font-size: 0.875rem;
    }

    .is-invalid {
        border-color: #dc3545 !important;
    }
</style>
<style>
    .manager-panel {
        padding: 20px;
    }

    .filter-section {
        background: white;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .summary-card {
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 10px;
        padding: 15px;
        height: 100%;
        transition: all 0.3s ease;
    }

    .summary-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        transform: translateY(-2px);
    }

    .summary-header {
        border-bottom: 1px solid #f0f0f0;
        padding-bottom: 10px;
        margin-bottom: 15px;
    }

    .summary-header h6 {
        font-size: 14px;
        font-weight: 600;
        color: #333;
        margin: 0;
    }

    .summary-body h3 {
        font-size: 24px;
        font-weight: 700;
        color: #2c3e50;
        margin: 0;
    }

    .summary-icon {
        font-size: 32px;
        opacity: 0.8;
    }

    .btn-group .btn.active {
        background-color: #4e73df;
        color: white;
        border-color: #4e73df;
    }

    .badge {
        padding: 5px 10px;
        font-size: 12px;
        font-weight: 500;
    }

    .card-tools {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .btn-group-sm .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }

    table.table-hover tbody tr:hover {
        background-color: #f8f9fa;
    }

    .table th {
        font-weight: 600;
        color: #495057;
        background-color: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
    }

    .form-label.small {
        font-size: 12px;
        font-weight: 600;
        margin-bottom: 5px;
    }

    .form-select-sm {
        font-size: 14px;
        padding: 5px 10px;
    }
</style>
<style>
    .modal-content {
        border: none;
        border-radius: 12px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
    }

    .modal-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 12px 12px 0 0;
        padding: 20px 24px;
        border: none;
    }

    .modal-title {
        font-weight: 600;
        font-size: 1.25rem;
    }

    .btn-close {
        filter: brightness(0) invert(1);
    }

    .modal-body {
        padding: 24px;
        max-height: 70vh;
        overflow-y: auto;
    }

    .form-label {
        color: #4a5568;
        font-weight: 500;
        margin-bottom: 8px;
        font-size: 0.875rem;
    }

    .form-control,
    .form-select {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 10px 12px;
        font-size: 0.9375rem;
        transition: all 0.2s;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .form-control-sm,
    .form-select-sm {
        padding: 8px 12px;
        font-size: 0.875rem;
    }

    .tax-section {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 16px;
        margin-bottom: 16px;
    }

    .form-check-input:checked {
        background-color: #667eea;
        border-color: #667eea;
    }

    .input-group-text {
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        border-left: none;
        color: #64748b;
    }

    .grand-total-box {
        background: linear-gradient(135deg, #667eea15 0%, #764ba215 100%);
        border: 2px solid #667eea;
        border-radius: 10px;
        padding: 16px;
        margin-bottom: 20px;
    }

    .grand-total-box .form-control {
        font-size: 1.5rem;
        font-weight: 700;
        color: #667eea;
        border: none;
        background: transparent;
        text-align: center;
    }

    .receipt-item {
        position: relative;
    }

    .btn-outline-danger {
        border-color: #ef4444;
        color: #ef4444;
    }

    .btn-outline-danger:hover {
        background: #ef4444;
        color: white;
    }

    .btn-outline-secondary {
        border-color: #cbd5e1;
        color: #64748b;
    }

    .btn-outline-secondary:hover {
        background: #f1f5f9;
        border-color: #94a3b8;
    }

    .modal-footer {
        border-top: 1px solid #e2e8f0;
        padding: 16px 24px;
        background: #f8fafc;
        border-radius: 0 0 12px 12px;
    }

    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        padding: 10px 24px;
        font-weight: 500;
        border-radius: 8px;
        transition: transform 0.2s;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    }

    .btn-secondary {
        background: #e2e8f0;
        border: none;
        color: #64748b;
        padding: 10px 24px;
        font-weight: 500;
        border-radius: 8px;
    }

    .section-divider {
        border-top: 2px solid #e2e8f0;
        margin: 24px 0;
    }

    .bg-light {
        background-color: #f8fafc !important;
    }

    textarea.form-control {
        resize: vertical;
    }

    .text-muted {
        color: #94a3b8 !important;
        font-size: 0.8125rem;
    }

    ::-webkit-scrollbar {
        width: 8px;
    }

    ::-webkit-scrollbar-track {
        background: #f1f5f9;
    }

    ::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }


    input[type=number]::-webkit-inner-spin-button,
    input[type=number]::-webkit-outer-spin-button {
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        margin: 0;
    }
</style>
@endsection