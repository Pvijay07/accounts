<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;

class InvoiceApiController extends Controller
{
    public function index(Request $request)
    {
        $invoices = Invoice::query()
            ->with('company')
            ->when($request->filled('company_id'), fn ($query) => $query->where('company_id', $request->company_id))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->when($request->filled('type'), fn ($query) => $query->where('type', $request->type))
            ->latest()
            ->paginate(15);

        return response()->json($invoices);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'type' => 'required|in:proforma,invoice',
            'client_details' => 'required|array',
            'line_items' => 'required|array|min:1',
            'subtotal' => 'required|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'issue_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:issue_date',
            'status' => 'nullable|in:draft,sent,paid,overdue,pending,replaced',
        ]);

        $invoice = Invoice::create([
            'company_id' => $validated['company_id'],
            'type' => $validated['type'],
            'invoice_number' => $request->input('invoice_number') ?: 'API-' . str_pad((Invoice::max('id') ?? 0) + 1, 4, '0', STR_PAD_LEFT),
            'client_details' => $validated['client_details'],
            'line_items' => $validated['line_items'],
            'subtotal' => $validated['subtotal'],
            'total_amount' => $validated['total_amount'],
            'issue_date' => $validated['issue_date'],
            'due_date' => $validated['due_date'],
            'status' => $validated['status'] ?? 'pending',
            'created_by' => $request->user()->id,
        ]);

        return response()->json([
            'success' => true,
            'invoice' => $invoice->fresh('company'),
        ], 201);
    }

    public function markPaid(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'paid_date' => 'nullable|date',
        ]);

        $invoice->update([
            'status' => 'paid',
            'paid_date' => $validated['paid_date'] ?? now()->toDateString(),
        ]);

        return response()->json([
            'success' => true,
            'invoice' => $invoice->fresh('company'),
        ]);
    }
}
