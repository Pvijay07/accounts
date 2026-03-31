@extends('CA.layouts.app')

@section('content')

<div class="card shadow-sm mb-3">
  <div class="card-body">
    <h6 class="mb-1">Invoices Repository</h6>
    <div class="small-help">Includes Proforma + Tax invoices. When payment is marked received, proforma becomes taxable invoice and remains downloadable.</div>
    <hr>
    <form class="row g-2 align-items-end">
      <div class="col-md-3">
        <label class="form-label small">Company</label>
        <select class="form-select form-select-sm">
          <option>All</option>
          <option>Infasta</option>
          <option>Petsfolio</option>
          <option>IKC</option>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label small">Invoice Type</label>
        <select class="form-select form-select-sm">
          <option>All</option>
          <option>Proforma</option>
          <option>Tax Invoice</option>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label small">Payment Status</label>
        <select class="form-select form-select-sm">
          <option>All</option>
          <option>Pending</option>
          <option>Partial</option>
          <option>Received</option>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label small">Search</label>
        <input class="form-control form-control-sm" placeholder="Invoice no / client / amount">
      </div>
      <div class="col-md-1 d-grid">
        <button type="button" class="btn btn-sm btn-primary">Go</button>
      </div>
    </form>
  </div>
</div>

<div class="card shadow-sm">
  <div class="card-header d-flex justify-content-between align-items-center">
    <span>Invoices List</span>
    <div class="d-flex gap-2">
      <button class="btn btn-sm btn-outline-secondary">Download Selected (ZIP)</button>
      <button class="btn btn-sm btn-outline-secondary">Export List (Excel)</button>
    </div>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-sm mb-0 align-middle">
        <thead class="table-light">
          <tr>
            <th><input type="checkbox"></th>
            <th>Invoice No</th>
            <th>Company</th>
            <th>Client</th>
            <th>Invoice Type</th>
            <th class="text-end">Total</th>
            <th class="text-end">Received</th>
            <th class="text-end">Balance</th>
            <th>Status</th>
            <th>Download</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td><input type="checkbox"></td>
            <td>INF-TAX-00021</td>
            <td>Infasta</td>
            <td>ABC Pvt Ltd</td>
            <td><span class="chip">Tax Invoice</span></td>
            <td class="text-end">₹ 1,00,000</td>
            <td class="text-end">₹ 1,00,000</td>
            <td class="text-end">₹ 0</td>
            <td><span class="badge bg-success">Received</span></td>
            <td><a class="link-muted" href="#">Invoice.pdf</a></td>
          </tr>
          <tr>
            <td><input type="checkbox"></td>
            <td>PET-PR-00008</td>
            <td>Petsfolio</td>
            <td>XYZ Client</td>
            <td><span class="chip">Proforma</span></td>
            <td class="text-end">₹ 1,00,000</td>
            <td class="text-end">₹ 50,000</td>
            <td class="text-end">₹ 50,000</td>
            <td><span class="badge bg-warning text-dark">Partial</span></td>
            <td>
              <a class="link-muted" href="#">Proforma.pdf</a>
              <span class="small text-muted">|</span>
              <a class="link-muted" href="#">TaxInvoice(50k).pdf</a>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
  <div class="card-footer small-help">
    Partial receipt flow: received amount creates a Tax Invoice for that received value; remaining balance creates new upcoming credit + new proforma.
  </div>
</div>
@endsection