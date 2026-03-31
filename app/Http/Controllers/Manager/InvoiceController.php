<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Admin\InvoiceManagementController;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Invoice;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $companies = Company::query()
            ->where('manager_id', auth()->id())
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('Manager.invoices', compact('companies'));
    }

    public function store(Request $request, InvoiceManagementController $invoiceManagementController)
    {
        return $invoiceManagementController->store($request);
    }

    public function markPaid(Request $request, Invoice $invoice)
    {
        $invoice->update([
            'status' => 'paid',
            'paid_date' => $request->input('paid_date', now()->toDateString()),
        ]);

        return response()->json([
            'success' => true,
            'invoice' => $invoice->fresh(),
        ]);
    }
}
