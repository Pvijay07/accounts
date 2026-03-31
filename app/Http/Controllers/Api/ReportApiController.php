<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportApiController extends Controller
{
    public function profitLoss(Request $request)
    {
        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to = $request->input('to', now()->endOfMonth()->toDateString());

        $income = Invoice::query()
            ->whereBetween('issue_date', [$from, $to])
            ->sum('total_amount');

        $expenses = Expense::query()
            ->whereBetween('due_date', [$from, $to])
            ->sum(DB::raw('COALESCE(actual_amount, planned_amount, 0)'));

        return response()->json([
            'from' => $from,
            'to' => $to,
            'income' => (float) $income,
            'expenses' => (float) $expenses,
            'profit' => (float) $income - (float) $expenses,
        ]);
    }

    public function expenseBreakdown(Request $request)
    {
        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to = $request->input('to', now()->endOfMonth()->toDateString());

        $breakdown = Expense::query()
            ->selectRaw('categories.name as category, SUM(COALESCE(expenses.actual_amount, expenses.planned_amount, 0)) as total')
            ->leftJoin('categories', 'categories.id', '=', 'expenses.category_id')
            ->whereBetween('expenses.due_date', [$from, $to])
            ->groupBy('categories.name')
            ->orderByDesc('total')
            ->get();

        return response()->json([
            'from' => $from,
            'to' => $to,
            'data' => $breakdown,
        ]);
    }
}
