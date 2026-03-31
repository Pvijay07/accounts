@extends('CA.layouts.app')

@section('content')
    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <h6 class="mb-1">Statements Download</h6>
            <div class="small-help">Statement includes: date, type, category, amount, purpose comments, and links to
                attachments (invoice/bill/receipt).</div>
            <hr>
            <form class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small">Company</label>
                    <select class="form-select form-select-sm">
                        <option>Infasta</option>
                        <option>Petsfolio</option>
                        <option>IKC</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">From</label>
                    <input type="date" class="form-control form-control-sm">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">To</label>
                    <input type="date" class="form-control form-control-sm">
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Include</label>
                    <select class="form-select form-select-sm">
                        <option selected>Expenses + Income + Loans/Payables</option>
                        <option>Only Expenses</option>
                        <option>Only Income</option>
                        <option>Only Loans/Payables</option>
                    </select>
                </div>
                <div class="col-md-2 d-grid">
                    <button type="button" class="btn btn-sm btn-primary">Generate</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="card kpi shadow-sm">
                <div class="card-body">
                    <div class="label">Total Expenses</div>
                    <div class="value">₹ 12,40,000</div>
                    <div class="small-help">Selected period</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card kpi shadow-sm">
                <div class="card-body">
                    <div class="label">Total Income</div>
                    <div class="value">₹ 18,10,000</div>
                    <div class="small-help">Selected period</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card kpi shadow-sm">
                <div class="card-body">
                    <div class="label">Missing Attachments</div>
                    <div class="value">9</div>
                    <div class="small-help">Needs follow-up</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Statement Preview</span>
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-outline-secondary">Download Excel</button>
                <button class="btn btn-sm btn-outline-secondary">Download PDF</button>
                <button class="btn btn-sm btn-primary">Download Attachments (ZIP)</button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Category</th>
                            <th>Description</th>
                            <th class="text-end">Amount</th>
                            <th>Purpose / Comments</th>
                            <th>Attachments</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>15-12-2025</td>
                            <td><span class="chip">Expense</span></td>
                            <td>Office Rent</td>
                            <td>Dec rent</td>
                            <td class="text-end">₹ 60,000</td>
                            <td>Office rent payment for Dec</td>
                            <td><a class="link-muted" href="#">Bill.pdf</a></td>
                        </tr>
                        <tr>
                            <td>14-12-2025</td>
                            <td><span class="chip">Income</span></td>
                            <td>Service Revenue</td>
                            <td>Client invoice</td>
                            <td class="text-end">₹ 1,20,000</td>
                            <td>Website development milestone 2</td>
                            <td><a class="link-muted" href="#">Invoice.pdf</a></td>
                        </tr>
                        <tr>
                            <td>13-12-2025</td>
                            <td><span class="chip">Payable</span></td>
                            <td>GST Settlement</td>
                            <td>Output - Input</td>
                            <td class="text-end">₹ 32,450</td>
                            <td>GST payable for Nov</td>
                            <td><span class="text-danger small">Missing</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
