@extends('CA.layouts.app')

@section('content')
    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="card kpi shadow-sm">
                <div class="card-body">
                    <div class="label">Companies</div>
                    <div class="value">3</div>
                    <div class="small-help">Infasta, Petsfolio, IKC</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card kpi shadow-sm">
                <div class="card-body">
                    <div class="label">Pending Docs</div>
                    <div class="value">14</div>
                    <div class="small-help">Missing invoice/bill attachments</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card kpi shadow-sm">
                <div class="card-body">
                    <div class="label">Open Tasks</div>
                    <div class="value">6</div>
                    <div class="small-help">Due this week</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card kpi shadow-sm">
                <div class="card-body">
                    <div class="label">Last Updated</div>
                    <div class="value">16-12-2025</div>
                    <div class="small-help">Auto from latest change</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card shadow-sm mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Quick Downloads</span>
                    <span class="small-help">Most used exports</span>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-md-6 d-grid">
                            <a class="btn btn-sm btn-primary" href="ca_statements.html">Download Company Statement
                                (PDF/Excel)</a>
                        </div>
                        <div class="col-md-6 d-grid">
                            <a class="btn btn-sm btn-outline-primary" href="ca_invoices_repository.html">Download Invoices
                                (ZIP)</a>
                        </div>
                        <div class="col-md-6 d-grid">
                            <a class="btn btn-sm btn-outline-primary" href="ca_records.html">View Expense/Income Records</a>
                        </div>
                        <div class="col-md-6 d-grid">
                            <a class="btn btn-sm btn-outline-secondary" href="ca_salary_packs.html">Download Salary Pack</a>
                        </div>
                    </div>
                    <div class="small-help mt-3">
                        CA role: view/download + update task status only. No editing
                        amounts.
                    </div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header">Data Quality Alerts</div>
                <div class="card-body">
                    <ul class="small mb-0">
                        <li class="mb-2">
                            3 expenses missing <strong>purpose comments</strong>.
                        </li>
                        <li class="mb-2">
                            9 expenses missing
                            <strong>bills/invoices</strong> attachments.
                        </li>
                        <li>
                            2 income entries missing
                            <strong>receipt / invoice</strong> attachment.
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card shadow-sm mb-3">
                <div class="card-header">Recent Activity</div>
                <div class="card-body">
                    <div class="small">
                        <div class="d-flex justify-content-between">
                            <span>Infasta — Salary Sheet locked (Dec)</span><span class="text-muted">Today</span>
                        </div>
                        <hr class="my-2" />
                        <div class="d-flex justify-content-between">
                            <span>Petsfolio — Expense added (Rent)</span><span class="text-muted">Yesterday</span>
                        </div>
                        <hr class="my-2" />
                        <div class="d-flex justify-content-between">
                            <span>IKC — Invoice generated (Proforma)</span><span class="text-muted">2d</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header">Task Reminders</div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a class="btn btn-sm btn-outline-primary" href="ca_tasks.html">Open CA Tasks & mark status</a>
                        <button class="btn btn-sm btn-outline-secondary">
                            Email reminders (preview)
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
