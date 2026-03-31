<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Http\Request;

class ExpenseApiController extends Controller
{
    public function index(Request $request)
    {
        $expenses = Expense::query()
            ->with(['company', 'categoryRelation'])
            ->when($request->filled('company_id'), fn ($query) => $query->where('company_id', $request->company_id))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->latest()
            ->paginate(15);

        return response()->json($expenses);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'category_id' => 'required|exists:categories,id',
            'expense_name' => 'required|string|max:255',
            'actual_amount' => 'required|numeric|min:0',
            'planned_amount' => 'nullable|numeric|min:0',
            'due_date' => 'nullable|date',
            'status' => 'nullable|in:upcoming,pending,paid,overdue',
            'party_name' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $expense = Expense::create([
            'company_id' => $validated['company_id'],
            'category_id' => $validated['category_id'],
            'expense_name' => $validated['expense_name'],
            'actual_amount' => $validated['actual_amount'],
            'planned_amount' => $validated['planned_amount'] ?? $validated['actual_amount'],
            'due_date' => $validated['due_date'] ?? now()->toDateString(),
            'status' => $validated['status'] ?? 'pending',
            'party_name' => $validated['party_name'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'source' => 'manual',
            'created_by' => $request->user()->id,
        ]);

        return response()->json([
            'success' => true,
            'expense' => $expense->fresh(['company', 'categoryRelation']),
        ], 201);
    }

    public function markPaid(Request $request, Expense $expense)
    {
        $validated = $request->validate([
            'actual_amount' => 'nullable|numeric|min:0',
            'paid_date' => 'nullable|date',
        ]);

        $expense->update([
            'status' => 'paid',
            'actual_amount' => $validated['actual_amount'] ?? $expense->actual_amount ?? $expense->planned_amount,
            'paid_date' => $validated['paid_date'] ?? now()->toDateString(),
            'balance_amount' => 0,
        ]);

        return response()->json([
            'success' => true,
            'expense' => $expense->fresh(),
        ]);
    }
}
