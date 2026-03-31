@extends('Manager.layouts.app')
@section('content')
    <section class="pge">
        <div class="container-fluid">

            <div class="card shadow-sm mb-3">
                <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <h5 class="mb-0">Tax Collected (Output)</h5>
                        <div class="small-help">From taxable income/sales. Shows GST and TDS collected.</div>
                    </div>
                    <div class="topnav">
                        <a class="btn btn-sm btn-outline-primary" href="{{ route('manager.gst') }}">Dashboard</a>
                        <a class="btn btn-sm btn-primary" href="{{ route('manager.gst-collected') }}">GST Collected</a>
                        <a class="btn btn-sm btn-outline-primary" href="{{ route('manager.taxes') }}">Taxes on Expenses</a>
                        <a class="btn btn-sm btn-outline-primary"
                            href="#">Settlements</a>
                        <a class="btn btn-sm btn-outline-primary" href="#">Returns &
                            tasks</a>
                    </div>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="kpi-card">
                        <div class="kpi-label">Tax Period</div>
                        <div class="kpi-value">{{ date('M Y', strtotime($selectedPeriod)) }}</div>
                        <div class="small-help">Currently viewing</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="kpi-card">
                        <div class="kpi-label">GST Collected</div>
                        <div class="kpi-value">₹ {{ number_format($totalGSTCollected, 2) }}</div>
                        <div class="small-help">From {{ $gstTaxes->count() }} records</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="kpi-card">
                        <div class="kpi-label">TDS Collected</div>
                        <div class="kpi-value">₹ {{ number_format($totalTDSCollected, 2) }}</div>
                        <div class="small-help">From {{ $tdsTaxes->count() }} records</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="kpi-card">
                        <div class="kpi-label">Taxable Amount</div>
                        <div class="kpi-value">₹ {{ number_format($totalTaxableAmount, 2) }}</div>
                        <div class="small-help">Base amount before tax</div>
                    </div>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                        <div>
                            <h6 class="mb-1">Filter Income Records</h6>
                            <div class="small-help">View and filter taxable income records.</div>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal"
                                data-bs-target="#attachModal">Attach Receipt</button>
                        </div>
                    </div>

                    <form class="row g-2 align-items-end" id="taxFilterForm">
                        @csrf
                        <div class="col-md-3">
                            <label class="form-label small">Company</label>
                            <select class="form-select form-select-sm" name="company_id" id="company_filter">
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
                            <label class="form-label small">Tax Period</label>
                            <select class="form-select form-select-sm" name="period" id="period_filter">
                                @foreach ($months as $month)
                                    <option value="{{ $month['value'] }}"
                                        {{ $selectedPeriod == $month['value'] ? 'selected' : '' }}>
                                        {{ $month['label'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Tax Type</label>
                            <select class="form-select form-select-sm" name="tax_type" id="tax_type_filter">
                                <option value="all" {{ $selectedTaxType == 'all' ? 'selected' : '' }}>All Taxes</option>
                                <option value="gst" {{ $selectedTaxType == 'gst' ? 'selected' : '' }}>GST Only</option>
                                <option value="tds" {{ $selectedTaxType == 'tds' ? 'selected' : '' }}>TDS Only</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-grid">
                            <button type="button" class="btn btn-sm btn-primary" onclick="applyTaxFilters()">Apply</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Income Records Table -->
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Income Records with Tax</span>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-outline-secondary" onclick="exportTaxData('excel')">
                            Export Excel
                        </button>
                        <button class="btn btn-sm btn-outline-secondary" onclick="exportTaxData('pdf')">
                            Export PDF
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Client</th>
                                    <th>Date</th>
                                    <th class="text-end">Taxable (₹)</th>
                                    <th class="text-end">GST (₹)</th>
                                    <th> Attachment</th>

                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="taxRecordsTable">
                                @if ($incomesWithTax->count() > 0)
                                    @foreach ($incomesWithTax as $income)
                                        @php
                                            // Get tax details for this income
                                            $taxDetails = $income->taxes->first();
                                            $taxType = $taxDetails ? $taxDetails->tax_type : 'N/A';
                                            $taxPercentage = $taxDetails ? $taxDetails->tax_percentage : 0;
                                            $taxAmount = $taxDetails ? $taxDetails->tax_amount : 0;
                                            $status = $taxDetails ? $taxDetails->payment_status : 'N/A';
                                        @endphp
                                        <tr>
                                            <td>{{ date('d-m-Y', strtotime($income->income_date)) }}</td>
                                            <td>{{ $income->company->name ?? 'N/A' }}</td>
                                            <td>{{ $income->description ?: ($income->client_name ?: 'Income') }}</td>
                                            <td class="text-end">{{ number_format($income->amount, 2) }}</td>
                                            <td class="text-end fw-semibold">{{ number_format($taxAmount, 2) }}</td>
                                            <td></td>
                                            <td class="text-end">
                                                <button class="btn btn-sm btn-outline-secondary"
                                                    onclick="viewIncomeDetails({{ $income->id }})">
                                                    View
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="9" class="text-center">No income records with tax found for this
                                            period.</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Tax Summary Section -->
            {{-- <div class="card shadow-sm mt-3">
                <div class="card-header">
                    <h6 class="mb-0">Tax Summary</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>GST Collected</h6>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Company</th>
                                            <th class="text-end">Taxable Amount</th>
                                            <th class="text-end">GST Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $gstByCompany = $gstTaxes->groupBy(function ($tax) {
                                                return $tax->taxable->company_id ?? 0;
                                            });
                                        @endphp
                                        @foreach ($gstByCompany as $companyId => $taxes)
                                            @php
                                                $company = \App\Models\Company::find($companyId);
                                                $totalTaxable = $taxes->sum(function ($tax) {
                                                    return $tax->taxable->amount ?? 0;
                                                });
                                                $totalTax = $taxes->sum('tax_amount');
                                            @endphp
                                            <tr>
                                                <td>{{ $company->name ?? 'Unknown' }}</td>
                                                <td class="text-end">₹ {{ number_format($totalTaxable, 2) }}</td>
                                                <td class="text-end">₹ {{ number_format($totalTax, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr class="table-light">
                                            <td><strong>Total GST</strong></td>
                                            <td class="text-end">₹
                                                {{ number_format(
                                                    $gstTaxes->sum(function ($tax) {
                                                        return $tax->taxable->amount ?? 0;
                                                    }),
                                                    2,
                                                ) }}
                                            </td>
                                            <td class="text-end"><strong>₹
                                                    {{ number_format($totalGSTCollected, 2) }}</strong></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6>TDS Collected</h6>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Company</th>
                                            <th class="text-end">Taxable Amount</th>
                                            <th class="text-end">TDS Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $tdsByCompany = $tdsTaxes->groupBy(function ($tax) {
                                                return $tax->taxable->company_id ?? 0;
                                            });
                                        @endphp
                                        @foreach ($tdsByCompany as $companyId => $taxes)
                                            @php
                                                $company = \App\Models\Company::find($companyId);
                                                $totalTaxable = $taxes->sum(function ($tax) {
                                                    return $tax->taxable->amount ?? 0;
                                                });
                                                $totalTax = $taxes->sum('tax_amount');
                                            @endphp
                                            <tr>
                                                <td>{{ $company->name ?? 'Unknown' }}</td>
                                                <td class="text-end">₹ {{ number_format($totalTaxable, 2) }}</td>
                                                <td class="text-end">₹ {{ number_format($totalTax, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr class="table-light">
                                            <td><strong>Total TDS</strong></td>
                                            <td class="text-end">₹
                                                {{ number_format(
                                                    $tdsTaxes->sum(function ($tax) {
                                                        return $tax->taxable->amount ?? 0;
                                                    }),
                                                    2,
                                                ) }}
                                            </td>
                                            <td class="text-end"><strong>₹
                                                    {{ number_format($totalTDSCollected, 2) }}</strong></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div> --}}

            <!-- Attach Receipt Modal -->
            <div class="modal fade" id="attachModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <div>
                                <h5 class="modal-title">Attach Receipt/Document</h5>
                                <div class="small-help">Upload supporting documents for tax records.</div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <form class="row g-3" id="attachReceiptForm" enctype="multipart/form-data">
                                @csrf
                                <div class="col-md-6">
                                    <label class="form-label small">Select Income Record</label>
                                    <select class="form-select form-select-sm" name="income_id" required>
                                        <option value="">Select Income</option>
                                        @foreach ($incomesWithTax as $income)
                                            <option value="{{ $income->id }}">
                                                {{ $income->company->name ?? 'Unknown' }} -
                                                ₹{{ number_format($income->amount, 2) }} -
                                                {{ date('d-m-Y', strtotime($income->income_date)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small">Document Type</label>
                                    <select class="form-select form-select-sm" name="document_type" required>
                                        <option value="receipt">Receipt</option>
                                        <option value="invoice">Invoice</option>
                                        <option value="tds_certificate">TDS Certificate</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label small">Attach File</label>
                                    <input type="file" class="form-control form-control-sm" name="document_file"
                                        accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small">Notes</label>
                                    <textarea class="form-control form-control-sm" name="notes" rows="2"></textarea>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                            <button class="btn btn-sm btn-primary" onclick="attachReceipt()">Save</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <script>
        function applyTaxFilters() {
            const form = document.getElementById('taxFilterForm');
            const formData = new FormData(form);
            const params = new URLSearchParams(formData).toString();

            window.location.href = `{{ route('manager.gst-collected') }}?${params}`;
        }

        function exportTaxData(type) {
            const company = document.getElementById('company_filter').value;
            const period = document.getElementById('period_filter').value;
            const taxType = document.getElementById('tax_type_filter').value;

            window.location.href =
                `{{ url('manager/gst-collected/export') }}/${type}?company=${company}&period=${period}&tax_type=${taxType}`;
        }

        function attachReceipt() {
            const form = document.getElementById('attachReceiptForm');
            const formData = new FormData(form);

            fetch('{{ route('manager.gst.attach-receipt') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Receipt attached successfully!');
                        form.reset();
                        $('#attachModal').modal('hide');
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while attaching the receipt.');
                });
        }

        function viewIncomeDetails(id) {
            window.location.href = `{{ url('manager/income') }}/${id}`;
        }

        // Initialize with current filters
        document.addEventListener('DOMContentLoaded', function() {
            // Set current period in title
            const periodValue = document.getElementById('period_filter').value;
            const periodDate = new Date(periodValue + '-01');
            document.querySelector('.kpi .value:first-child').textContent =
                periodDate.toLocaleDateString('en-IN', {
                    month: 'short',
                    year: 'numeric'
                });
        });
    </script>
@endsection
