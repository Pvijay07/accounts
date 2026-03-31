<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Company;
use App\Models\Expense;
use App\Models\Income;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Tax;
use App\Models\Invoice;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use App\Traits\ManagesCompanies;
use App\Exports\IncomeExport;
use Maatwebsite\Excel\Facades\Excel;

class IncomeController extends Controller
{
    use ManagesCompanies;
  public function index(Request $request)
  {
    $user = auth()->user();

    $companyId = $request->get('company');
    $dateRange = $request->get('date_range', 'month');
    $type      = $request->get('type');
    $status    = $request->get('status', 'all');
    $category  = $request->get('category', 'all');

    $query = Income::with(['company', 'parent', 'children'])
      ->orderBy('created_at', 'desc');

    // Filter by user's managed companies
    $query->whereHas('company', function ($q) use ($user) {
      $q->where('manager_id', $user->id);
    });

    // Apply company filter
    if ($companyId) {
      $query->where('company_id', $companyId);
    }

    $now          = now();
    $currentMonth = $now->format('M');
    $nextMonth    = $now->copy()->addMonth()->format('M');
    $currentYear  = $now->format('Y');

    // Apply date range filter
    switch ($dateRange) {
      case 'today':
        $query->whereDate('created_at', $now->toDateString());
        $statsStartDate = $now->copy()->startOfDay();
        $statsEndDate = $now->copy()->endOfDay();
        $dateRangeTitle = 'Today (' . $now->format('d M Y') . ')';
        break;
      case 'week':
        $query->whereBetween('created_at', [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()]);
        $statsStartDate = $now->copy()->startOfWeek();
        $statsEndDate = $now->copy()->endOfWeek();
        $dateRangeTitle = 'This Week (' . $statsStartDate->format('d M') . ' - ' . $statsEndDate->format('d M Y') . ')';
        break;
      case 'month':
        $query->whereBetween('created_at', [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()]);
        $statsStartDate = $now->copy()->startOfMonth();
        $statsEndDate = $now->copy()->endOfMonth();
        $dateRangeTitle = $now->format('F Y');
        break;
      case 'quarter':
        $query->whereBetween('created_at', [$now->copy()->startOfQuarter(), $now->copy()->endOfQuarter()]);
        $statsStartDate = $now->copy()->startOfQuarter();
        $statsEndDate = $now->copy()->endOfQuarter();
        $quarter = ceil($now->month / 3);
        $dateRangeTitle = 'Q' . $quarter . ' ' . $now->format('Y');
        break;
      case 'year':
        $query->whereBetween('created_at', [$now->copy()->startOfYear(), $now->copy()->endOfYear()]);
        $statsStartDate = $now->copy()->startOfYear();
        $statsEndDate = $now->copy()->endOfYear();
        $dateRangeTitle = $now->format('Y');
        break;
      case 'next7days':
        $query->whereBetween('created_at', [$now->copy(), $now->copy()->addDays(7)]);
        $statsStartDate = $now->copy();
        $statsEndDate = $now->copy()->addDays(7);
        $dateRangeTitle = 'Next 7 Days';
        break;
      case 'custom':
        // Handle custom date range if needed
        $query->whereBetween('created_at', [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()]);
        $statsStartDate = $now->copy()->startOfMonth();
        $statsEndDate = $now->copy()->endOfMonth();
        $dateRangeTitle = $now->format('F Y');
        break;
      default:
        $query->whereBetween('created_at', [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()]);
        $statsStartDate = $now->copy()->startOfMonth();
        $statsEndDate = $now->copy()->endOfMonth();
        $dateRangeTitle = $now->format('F Y');
        break;
    }

    // Apply type filter (category in your case)
    if ($category && $category !== 'all') {
      if ($category === 'standard') {
        $query->where('income_type', 'standard');
      } elseif ($category === 'non-standard') {
        $query->where('income_type', 'non-standard');
      }
    }

    // Apply status filter
    if ($status && $status !== 'all') {
      $query->where('status', $status);
    }

    $incomes   = $query->paginate(20);
    $companies = Company::where('manager_id', $user->id)
      ->where('status', 'active')
      ->get();
    $statuses  = ['pending', 'received', 'overdue', 'upcoming'];

    // Create a helper function for statistics queries
    $statsQuery = function ($conditions = []) use ($user, $statsStartDate, $statsEndDate, $companyId) {
      $query = Income::whereHas('company', function ($q) use ($user) {
        $q->where('manager_id', $user->id);
      });

      // Apply date range
      $query->whereBetween('created_at', [$statsStartDate, $statsEndDate]);

      // Apply company filter if selected
      if ($companyId) {
        $query->where('company_id', $companyId);
      }

      // Apply additional conditions
      foreach ($conditions as $condition) {
        $query->where($condition[0], $condition[1], $condition[2] ?? null);
      }

      return $query;
    };

    // Calculate statistics based on selected date range - FIXED
    $stats = [
      'totalPayments'       => $statsQuery()->sum('amount') ?? 0,
      'paymentItems'        => $statsQuery()->count(),

      'totalReceived'       => $statsQuery([['status', 'received']])->sum('actual_amount') ?? 0,
      'receivedItems'       => $statsQuery([['status', 'received']])->count(),

      'totalPending'        => $statsQuery([['status', 'pending']])->sum('amount') ?? 0,
      'pendingItems'        => $statsQuery([['status', 'pending']])->count(),

      'totalOverdue'        => $statsQuery([['status', 'overdue']])->sum('amount') ?? 0,
      'overdueItems'        => $statsQuery([['status', 'overdue']])->count(),

      // All-time overdue (not filtered by date range) - FIXED
      'allTimeOverdue'      => Income::whereHas('company', function ($q) use ($user) {
        $q->where('manager_id', $user->id);
      })
        ->where('status', 'overdue')
        ->sum('amount') ?? 0,
      'allTimeOverdueItems' => Income::whereHas('company', function ($q) use ($user) {
        $q->where('manager_id', $user->id);
      })
        ->where('status', 'overdue')
        ->count(),
    ];

    return view('Manager.cash-flow.income', compact(
      'incomes',
      'companies',
      'statuses',
      'stats',
      'dateRangeTitle',
      'dateRange',
      'companyId',
      'type',
      'category',
      'status',
      'currentMonth',
      'nextMonth',
      'currentYear'
    ));
  }

  // Add new method to get income details
  public function getIncomeDetails($id)
  {
    try {
      $income = Income::with(['company', 'invoice'])->find($id);

      return response()->json([
        'success' => true,
        'income'  => $income,
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Income not found',
      ]);
    }
  }

  // Add new method to receive partial payment
  public function receivePayment(Request $request, $id)
  {
    $request->validate([
      'received_amount'     => 'required|numeric|min:0.01',
      'payment_date'        => 'required|date',
      'new_due_date'        => 'nullable|date|required_if:create_new_proforma,1',
      'internal_note'       => 'nullable|string',
      'create_new_proforma' => 'required|boolean', // This accepts 0/1, true/false, "0"/"1"
    ]);

    try {
      DB::beginTransaction();

      $income         = Income::findOrFail($id);
      $originalAmount = $income->planned_amount ?? $income->amount;
      $receivedAmount = $request->received_amount;

      if ($receivedAmount > $originalAmount) {
        return response()->json([
          'success' => false,
          'message' => 'Received amount must be less than original amount for partial payment',
        ]);
      }

      // Create new income record for balance if requested
      if ($request->create_new_proforma == 1 || $request->create_new_proforma === true) {
        $newIncome                = $income->replicate();
        $newIncome->amount        = $originalAmount - $receivedAmount;
        $newIncome->actual_amount = 0;
        $newIncome->status        = 'pending';
        $newIncome->due_date      = $request->new_due_date;
        $newIncome->income_date   = $request->payment_date;
        // $newIncome->notes          = $request->internal_note;
        $newIncome->parent_id  = $income->id;
        $newIncome->created_at = now();
        $newIncome->updated_at = now();
        $newIncome->save();
      }

      // Update original income record
      $income->amount      = $receivedAmount;
      $income->status      = 'received';
      $income->income_date = $request->payment_date;
      // $income->notes          = $request->internal_note;
      $income->is_partial     = true;
      $income->balance_amount = $originalAmount - $receivedAmount;
      $income->save();

      DB::commit();

      return response()->json([
        'success' => true,
        'message' => 'Partial payment recorded successfully',
      ]);
    } catch (\Exception $e) {
      DB::rollBack();
      \Log::error('Receive payment error: ' . $e->getMessage());
      return response()->json([
        'success' => false,
        'message' => 'Error recording payment: ' . $e->getMessage(),
      ]);
    }
  }

  public function store(Request $request)
  {
    $data = $request->validate([
      'company_id'       => 'required|exists:companies,id',
      'client_name'      => 'required|string|max:255',
      'amount'           => 'required|numeric|min:0',
      'frequency'        => 'nullable|string|in:Monthly,Weekly,Quarterly,Yearly,One-time',
      'gst_percentage'   => 'nullable|numeric|min:0|max:100',
      'gst_amount'       => 'nullable|numeric|min:0',
      'tds_percentage'   => 'nullable|numeric|min:0|max:100',
      'tds_amount'       => 'nullable|numeric|min:0',
      'amount_after_tds' => 'nullable|numeric|min:0',
      'grand_total'      => 'required|numeric|min:0',
      'received_amount'  => 'nullable|numeric|min:0',
      'balance_amount'   => 'nullable|numeric|min:0',
      'tds_status'       => 'nullable|string|in:received,not_received',
      'due_day'          => 'nullable|integer|min:1|max:31',
      'status'           => 'required|string|in:pending,received,overdue',
      'income_date'      => 'required|date',
      // 'received_date'    => 'nullable|date',
      'mail_status'      => 'nullable|in:1,0',
      'notes'            => 'nullable|string',
      'receipts.*'       => 'nullable|file|max:5120|mimes:jpg,jpeg,png,pdf,doc,docx',
      'tds_receipt'      => 'nullable|file|max:5120|mimes:jpg,jpeg,png,pdf,doc,docx',
    ]);

    DB::beginTransaction();

    try {
      // Calculate split payment logic
      $plannedAmount  = $request->grand_total;
      $receivedAmount = $request->received_amount ?? 0;

      // Check if this should be a split payment
      $isSplitPayment = $request->status === 'received' &&
        $receivedAmount > 0 &&
        $receivedAmount < $plannedAmount;

      // Calculate balance amount
      $balanceAmount = $plannedAmount - $receivedAmount;

      // Calculate base amount proportion
      $actualTotalBase = $data['amount'] ?? 0;
      $proportion = $plannedAmount > 0 ? ($receivedAmount / $plannedAmount) : 0;
      $paidBaseAmount = $actualTotalBase * $proportion;
      $balanceBaseAmount = $actualTotalBase - $paidBaseAmount;

      $income = Income::create([
        'company_id'     => $data['company_id'],
        'party_name'     => $data['client_name'],
        'amount'         => $isSplitPayment ? $receivedAmount : $data['grand_total'],
        'frequency'      => $data['frequency'] ?? null,
        'actual_amount'  => $isSplitPayment ? $paidBaseAmount : $data['amount'],
        'balance_amount' => $isSplitPayment ? 0 : $balanceAmount,
        'due_day'        => $data['due_day'] ?? null,
        'status'         => $isSplitPayment ? 'received' : $data['status'],
        'income_date'    => $data['income_date'],
        'mail_status'    => $data['mail_status'] ?? 0,
        'notes'          => $data['notes'] ?? null,
        'is_partial'     => $isSplitPayment,
        'income_type'    => 'non-standard',
        'source'         => 'manual',
        'created_by'     => auth()->id(),
        'schedule_amount' => $actualTotalBase, // Store original total base for context
      ]);

      // Handle GST tax
      if ($request->apply_gst) {
        // Calculate GST proportion for split payment
        $gstAmount = $data['gst_amount'] ?? 0;
        if ($isSplitPayment && $gstAmount > 0 && $plannedAmount > 0) {
          // Pro-rate GST based on received amount
          $gstAmount = ($receivedAmount / $plannedAmount) * $gstAmount;
        }

        $income->taxes()->create([
          'taxable_type'   => Income::class,
          'taxable_id'     => $income->id,
          'tax_type'       => 'gst',
          'tax_percentage' => $data['gst_percentage'] ?? 0,
          'tax_amount'     => $gstAmount,
          'status'         => 'received', // GST is typically received from client
          'direction'      => 'income',
        ]);
      }

      // Handle TDS tax
      if ($request->apply_tds) {
        // Calculate TDS proportion for split payment
        $tdsAmount = $data['tds_amount'] ?? 0;
        if ($isSplitPayment && $tdsAmount > 0 && $plannedAmount > 0) {
          // Pro-rate TDS based on received amount
          $tdsAmount = ($receivedAmount / $plannedAmount) * $tdsAmount;
        }

        $path = '';
        if ($request->hasFile('tds_receipt')) {
          $tdsReceipt = $request->file('tds_receipt');
          $filename   = 'tds_receipt_' . time() . '_' . uniqid() . '.' . $tdsReceipt->getClientOriginalExtension();
          
          $destinationPath = public_path('uploads/receipts');
          if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0755, true);
          }
          
          $tdsReceipt->move($destinationPath, $filename);
          $path = 'uploads/receipts/' . $filename;
        }

        $income->taxes()->create([
          'taxable_type'     => Income::class,
          'taxable_id'       => $income->id,
          'tax_type'         => 'tds',
          'tax_percentage'   => $data['tds_percentage'] ?? 0,
          'tax_amount'       => $tdsAmount,
          'status'           => $data['tds_status'] ?? 'not_received',
          'amount_after_tds' => $isSplitPayment ? ($receivedAmount - $tdsAmount) : ($data['amount_after_tds'] ?? 0),
          'tds_proof_path'   => $path,
          'direction'        => 'income', // TDS is an expense
        ]);
      }

      // Create new income for balance if this is a split payment
      $newIncomeId = null;
      if ($isSplitPayment && $balanceAmount > 0) {
        $newIncome                = $income->replicate();
        $newIncome->party_name    = $income->party_name . ' - Balance';
        $newIncome->amount        = $balanceAmount;
        $newIncome->actual_amount = $balanceBaseAmount; // Set remaining base amount
        // $newIncome->received_amount = 0;
        $newIncome->balance_amount = $balanceAmount;
        $newIncome->status         = 'pending';
        $newIncome->income_date    = $request->new_due_date ?? now()->addDays(30)->format('Y-m-d');
        // $newIncome->received_date = null;
        $newIncome->is_partial = true;
        $newIncome->parent_id  = $income->id;
        $newIncome->schedule_amount = $actualTotalBase; // Preserve original total base
        $newIncome->notes      = $request->balance_notes ?? 'Balance from partial payment of income #' . $income->id;
        $newIncome->created_at = now();
        $newIncome->updated_at = now();
        $newIncome->save();

        $newIncomeId = $newIncome->id;

        // Copy GST to new income if applicable
        if ($request->apply_gst && isset($data['gst_amount']) && $data['gst_amount'] > 0) {
          $originalGstAmount = $data['gst_amount'];
          $newGstAmount      = $originalGstAmount - ($gstAmount ?? 0); // Remaining GST
          if ($newGstAmount > 0) {
            $newIncome->taxes()->create([
              'taxable_type'   => Income::class,
              'taxable_id'     => $newIncomeId,
              'tax_type'       => 'gst',
              'tax_percentage' => $data['gst_percentage'] ?? 0,
              'tax_amount'     => $newGstAmount,
              'status'         => 'pending',
              'direction'      => 'income',
            ]);
          }
        }

        // Copy TDS to new income if applicable
        if ($request->apply_tds && isset($data['tds_amount']) && $data['tds_amount'] > 0) {
          $originalTdsAmount = $data['tds_amount'];
          $newTdsAmount      = $originalTdsAmount - ($tdsAmount ?? 0); // Remaining TDS
          if ($newTdsAmount > 0) {
            $newIncome->taxes()->create([
              'taxable_type'     => Income::class,
              'taxable_id'       => $newIncomeId,
              'tax_type'         => 'tds',
              'tax_percentage'   => $data['tds_percentage'] ?? 0,
              'tax_amount'       => $newTdsAmount,
              'status'           => 'not_received',
              'amount_after_tds' => $balanceAmount - $newTdsAmount,
              'direction'        => 'income',
            ]);
          }
        }
      }

      // Handle receipt uploads
      if ($request->hasFile('receipts')) {
        foreach ($request->file('receipts') as $file) {
          if ($file->isValid()) {
            $originalName = $file->getClientOriginalName();
            $extension    = $file->getClientOriginalExtension();
            $filename     = 'receipt_' . time() . '_' . uniqid() . '.' . $extension;
            $fileSize     = $this->formatBytes($file->getSize());

            $destinationPath = public_path('uploads/receipts');
            if (!file_exists($destinationPath)) {
              mkdir($destinationPath, 0755, true);
            }

            $file->move($destinationPath, $filename);
            $filePath = 'uploads/receipts/' . $filename;

            // Create receipt record
            $income->receipts()->create([
              'file_name' => $originalName,
              'file_path' => $filePath,
              'file_type' => $extension,
              'file_size' => $fileSize
            ]);
          }
        }
      }

      DB::commit();

      return response()->json([
        'success'          => true,
        'message'          => $isSplitPayment ?
          'Income created with partial payment. New income created for balance.' :
          'Income saved successfully',
        'new_income_id'    => $newIncomeId,
        'is_split_payment' => $isSplitPayment,
        'data'             => [
          'original_income' => [
            'id'            => $income->id,
            'client_name'   => $income->party_name,
            'amount'        => $income->amount,
            'actual_amount' => $income->actual_amount,
            'balance'       => $income->balance,
            'status'        => $income->status
          ]
        ]
      ]);
    } catch (\Exception $e) {
      DB::rollBack();
      return response()->json([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
      ], 500);
    }
  }


  /**
   * Create tax record
   */
  private function createTaxRecord($expense, $type, $data)
  {
    // Check if you have a Tax model, otherwise adjust accordingly
    if (class_exists('App\Models\Tax')) {
      Tax::create([
        'expense_id' => $expense->id,
        'tax_type'   => $type,
        'percentage' => $data['percentage'],
        'amount'     => $data['amount'],
        'status'     => $data['status'],
      ]);
    } else {
      // If you don't have a separate Tax model, store in expense_taxes table
      DB::table('expense_taxes')->insert([
        'expense_id' => $expense->id,
        'tax_type'   => $type,
        'percentage' => $data['percentage'],
        'amount'     => $data['amount'],
        'status'     => $data['status'],
        'created_at' => now(),
        'updated_at' => now(),
      ]);
    }
  }

  /**
   * Upload receipt file
   */
  private function uploadReceipt($file, $expenseId, $type = 'general')
  {
    if ($file->isValid()) {
      $originalName = $file->getClientOriginalName();
      $extension    = $file->getClientOriginalExtension();
      $filename     = 'receipt_' . time() . '_' . uniqid() . '.' . $extension;

      $destinationPath = public_path('uploads/receipts');
      $fileSize        = $file->getSize();

      if (!file_exists($destinationPath)) {
        mkdir($destinationPath, 0755, true);
      }

      $file->move($destinationPath, $filename);
      $filePath = 'uploads/receipts/' . $filename;

      Receipt::create([
        'expense_id' => $expenseId,
        'file_name'  => $originalName,
        'file_path'  => $filePath,
        'file_type'  => $extension,
        'file_size'  => $fileSize,
        'type'       => $type,
      ]);
    }
  }

  public function edit($id)
  {
    try {
      $income = Income::with([
        'company',
        'category',
        'taxes' => function ($query) {
          $query->where('tax_type', 'gst')->orWhere('tax_type', 'tds');
        }
      ])->findOrFail($id);

      // Get GST and TDS tax records
      $gstTax = $income->taxes->where('tax_type', 'gst')->where('direction', 'income')->first();
      $tdsTax = $income->taxes->where('tax_type', 'tds')->where('direction', 'income')->first();

      // Calculate amounts
      $baseAmount = $income->amount;
      $gstAmount  = $gstTax ? $gstTax->tax_amount : 0;
      $tdsAmount  = $tdsTax ? $tdsTax->tax_amount : 0;
      $grandTotal = $baseAmount + $gstAmount - $tdsAmount;
      $balance    = $grandTotal - ($income->received_amount ?? 0);

      return response()->json([
        'success' => true,
        'income'  => [
          'id'               => $income->id,
          'company_id'       => $income->company_id,
          'client_name'      => $income->party_name ?? $income->description,
          'actual_amount'    => $income->actual_amount,
          'planned_amount'   => $income->amount,
          'invoice_id'       => $income->invoice_id,

          'frequency'        => $income->frequency,
          'due_day'          => $income->due_day,
          'status'           => $income->status,
          'income_date'      => $income->income_date,
          'mail_status'      => $income->mail_status ? 1 : 0,
          'notes'            => $income->notes,
          // Tax data
          'gst_percentage'   => $gstTax ? $gstTax->tax_percentage : 18,
          'gst_amount'       => $gstAmount,
          'tds_percentage'   => $tdsTax ? $tdsTax->tax_percentage : 10,
          'tds_amount'       => $tdsAmount,
          'amount_after_tds' => $baseAmount - $tdsAmount,
          'grand_total'      => $grandTotal,
          'tds_status'       => $tdsTax ? $tdsTax->payment_status : 'not_received',
          // Received amounts
          'received_amount'  => $income->received_amount ?? 0,
          // 'received_date'    => $income->received_date,
          'balance_amount'   => $balance,
          'original_total_base' => $income->schedule_amount ?? 0,
          // Other fields
          'source'           => $income->source,
          'category_id'      => $income->category_id,
        ],
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Income not found: ' . $e->getMessage()
      ], 404);
    }
  }
  public function splitHistory($id)
  {
    try {
      $expense = Income::with(['parent', 'children' => function ($query) {
        $query->orderBy('created_at', 'desc');
      }])->findOrFail($id);

      $data = [
        'success' => true,
        'current_expense' => [
          'id' => $expense->id,
          'planned_amount' => $expense->amount,
          'status' => $expense->status,
          'is_split' => $expense->is_split,
          'parent_id' => $expense->parent_id,
        ],
        'parent_expense' => null,
        'children' => []
      ];

      // If this expense has a parent, get parent details
      if ($expense->parent_id) {
        $parent = $expense->parent()->with('children')->first();
        if ($parent) {
          $data['parent_expense'] = [
            'id' => $parent->id,
            'expense_name' => $parent->expense_name,
            'planned_amount' => $parent->amount,
            'created_at' => $parent->created_at->format('Y-m-d H:i:s'),
            'is_split' => $parent->is_split,
          ];
          $data['children'] = $parent->children->map(function ($child) {
            return [
              'id' => $child->id,
              'expense_name' => $child->expense_name,
              'planned_amount' => $child->amount,
              'status' => $child->status,
              'paid_date' => $child->paid_date,
              'created_at' => $child->created_at->format('Y-m-d H:i:s'),
            ];
          });
        }
      } else if ($expense->is_split && $expense->children->count() > 0) {
        // If this is a parent expense with children
        $data['children'] = $expense->children->map(function ($child) {
          return [
            'id' => $child->id,
            'expense_name' => $child->expense_name,
            'planned_amount' => $child->planned_amount,
            'status' => $child->status,
            'paid_date' => $child->paid_date,
            'created_at' => $child->created_at->format('Y-m-d H:i:s'),
          ];
        });
      }

      // Calculate summary
      $originalAmount = $expense->parent_id ?
        ($data['parent_expense']['planned_amount'] ?? $expense->planned_amount) :
        $expense->planned_amount;

      $totalPaid = collect($data['children'])->where('status', 'paid')->sum('planned_amount');
      $totalBalance = collect($data['children'])->where('status', '!=', 'paid')->sum('planned_amount');

      $summaryOriginal = $expense->schedule_amount ?: collect($data['children'])->sum('planned_amount');

      $data['summary'] = [
        'original_amount' => $summaryOriginal,
        'total_paid' => $totalPaid,
        'total_balance' => $totalBalance,
        'split_count' => count($data['children']),
      ];

      return response()->json($data);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Error loading split history: ' . $e->getMessage()
      ], 500);
    }
  }
  public function update(Request $request, $id)
  {
    try {
      DB::beginTransaction();

      $income = Income::findOrFail($id);

      // Validate request
      $validated = $request->validate([
        'company_id'       => 'required|exists:companies,id',
        'client_name'      => 'required|string|max:255',
        'amount'           => 'required|numeric|min:0',
        'status'           => 'required|in:settle,due',
        'notes'            => 'nullable|string',
        // Tax fields
        'apply_gst'        => 'nullable|boolean',
        'gst_percentage'   => 'nullable|numeric|min:0|max:100',
        'gst_amount'       => 'nullable|numeric|min:0',
        'apply_tds'        => 'nullable|boolean',
        'tds_percentage'   => 'nullable|numeric|min:0|max:100',
        'tds_amount'       => 'nullable|numeric|min:0',
        'amount_after_tds' => 'nullable|numeric|min:0',
        'grand_total'      => 'nullable|numeric|min:0',
        'tds_status'       => 'nullable|in:received,not_received',

        // Received amounts
        'received_amount'  => 'nullable|numeric|min:0',
        'received_date'    => 'nullable|date',
        'balance_amount'   => 'nullable|numeric',

        // File uploads
        'tds_receipt'      => 'nullable|file|max:5120|mimes:jpg,jpeg,png,pdf,doc,docx',
        'receipts.*'       => 'nullable|file|max:5120|mimes:jpg,jpeg,png,pdf,doc,docx',
      ]);

      // Get amounts
      $plannedAmount = $validated['amount'] ?? $income->amount;
      $receivedAmount = $validated['received_amount'] ?? $income->received_amount ?? 0;
      $originalGstAmount = $validated['gst_amount'] ?? 0;
      $originalTdsAmount = $validated['tds_amount'] ?? 0;

      // Calculate net payable amount (amount after TDS deduction)
      $netPayableAmount = $plannedAmount - $originalTdsAmount;

      // Debug logging - add this to see what values are being calculated
      \Log::info('Income Update Debug:', [
        'plannedAmount' => $plannedAmount,
        'receivedAmount' => $receivedAmount,
        'originalTdsAmount' => $originalTdsAmount,
        'netPayableAmount' => $netPayableAmount,
        'status' => $validated['status']
      ]);

      // Check if this is a split payment
      // IMPORTANT: We need to check if received amount is positive AND less than net payable
      $isSplitPayment = $validated['status'] === 'due' &&
        $receivedAmount > 0 &&
        $receivedAmount < $netPayableAmount;

      \Log::info('Split Payment Check:', [
        'isSplitPayment' => $isSplitPayment,
        'condition1' => ($validated['status'] === 'due'),
        'condition2' => ($receivedAmount > 0),
        'condition3' => ($receivedAmount < $netPayableAmount),
        'received_vs_net' => "Received: $receivedAmount < Net: $netPayableAmount = " . ($receivedAmount < $netPayableAmount)
      ]);

      // Calculate balance amount (net payable minus received)
      $balanceAmount = $netPayableAmount - $receivedAmount;

      // Calculate proportional taxes for split payment
      $gstAmountForCurrent = $originalGstAmount;
      $tdsAmountForCurrent = $originalTdsAmount;

      if ($isSplitPayment && $netPayableAmount > 0) {
        // Calculate proportion based on net payable amount
        $proportion = $receivedAmount / $netPayableAmount;
        $gstAmountForCurrent = $originalGstAmount * $proportion;
        $tdsAmountForCurrent = $originalTdsAmount * $proportion;

        \Log::info('Split Payment Calculations:', [
          'proportion' => $proportion,
          'gstAmountForCurrent' => $gstAmountForCurrent,
          'tdsAmountForCurrent' => $tdsAmountForCurrent
        ]);
      }

      // Prepare income data
      $incomeData = [
        'company_id'     => $validated['company_id'],
        'party_name'     => $validated['client_name'],
        'amount'         => $isSplitPayment ? ($receivedAmount + $tdsAmountForCurrent) : $plannedAmount,
        'actual_amount'  => $isSplitPayment ? ($proportion * ($validated['amount'] ?? $income->schedule_amount ?? $income->actual_amount)) : ($netPayableAmount > 0 ? $netPayableAmount : $plannedAmount),
        'status'         => $balanceAmount > 0 ? 'received' : ($receivedAmount > 0 ? 'received' : 'pending'),
        'notes'          => $validated['notes'] ?? null,
        'source'         => 'manual',
        'is_partial'     => $isSplitPayment,
        'balance_amount' => $balanceAmount > 0 ? $balanceAmount : 0,
        'schedule_amount' => $validated['amount'] ?? $income->schedule_amount ?? $income->actual_amount, // Preserve total base context
        // 'received_amount' => $receivedAmount,
      ];

      // Add received date if payment was received
      if ($receivedAmount > 0) {
        $incomeData['received_date'] = $validated['received_date'] ?? now()->format('Y-m-d');
      }

      \Log::info('Income Data to Update:', $incomeData);

      // Update income
      $income->update($incomeData);

      // Handle GST tax
      if ($request->has('apply_gst') && $request->apply_gst) {
        Tax::updateOrCreate(
          [
            'taxable_type' => Income::class,
            'taxable_id'   => $income->id,
            'tax_type'     => 'gst',
            'direction'    => 'income'
          ],
          [
            'tax_amount'     => $gstAmountForCurrent,
            'tax_percentage' => $validated['gst_percentage'] ?? 0,
            'payment_status' => 'received',
            'company_id'     => $income->company_id,
          ]
        );
      } else {
        // Remove GST tax if unchecked
        Tax::where('taxable_type', Income::class)
          ->where('taxable_id', $income->id)
          ->where('tax_type', 'gst')
          ->delete();
      }

      // Handle TDS tax
      if ($request->has('apply_tds') && $request->apply_tds) {
        $filePath = null;
        if ($request->hasFile('tds_receipt')) {
          $file = $request->file('tds_receipt');
          if ($file->isValid()) {
            $originalName = $file->getClientOriginalName();
            $extension    = $file->getClientOriginalExtension();
            $filename     = 'tds_receipt_' . time() . '_' . uniqid() . '.' . $extension;
            $fileSize     = $this->formatBytes($file->getSize());

            // Store file in public/uploads
            $destinationPath = public_path('uploads/receipts');
            if (!file_exists($destinationPath)) {
              mkdir($destinationPath, 0755, true);
            }

            $file->move($destinationPath, $filename);
            $filePath = 'uploads/receipts/' . $filename;
          }
        }

        Tax::updateOrCreate(
          [
            'taxable_type' => Income::class,
            'taxable_id'   => $income->id,
            'tax_type'     => 'tds',
            'direction'    => 'income'
          ],
          [
            'tax_amount'     => $tdsAmountForCurrent,
            'tax_percentage' => $validated['tds_percentage'] ?? 0,
            'payment_status' => $validated['tds_status'] ?? 'not_received',
            'company_id'     => $income->company_id,
            'tds_proof_path' => $filePath
          ]
        );
      } else {
        // Remove TDS tax if unchecked
        Tax::where('taxable_type', Income::class)
          ->where('taxable_id', $income->id)
          ->where('tax_type', 'tds')
          ->delete();
      }

      // Create new income for balance if this is a split payment
      $newIncomeId = null;
      if ($isSplitPayment && $balanceAmount > 0) {
        // Calculate remaining TDS for the balance
        $remainingTdsAmount = $originalTdsAmount - $tdsAmountForCurrent;

        \Log::info('Creating New Income for Balance:', [
          'balanceAmount' => $balanceAmount,
          'remainingTdsAmount' => $remainingTdsAmount,
          'newTotalAmount' => $balanceAmount + $remainingTdsAmount
        ]);

        $newIncome = $income->replicate();
        $newIncome->party_name = $income->party_name . ' - Balance';
        $newIncome->amount = $balanceAmount + $remainingTdsAmount; // Include remaining TDS in amount
        $newIncome->actual_amount = ($validated['amount'] ?? $income->schedule_amount ?? $income->amount) - $incomeData['actual_amount']; 
        // $newIncome->received_amount = 0;
        $newIncome->balance_amount = $balanceAmount;
        $newIncome->status = 'pending';
        $newIncome->income_date = $request->new_due_date ?? now()->addDays(30)->format('Y-m-d');
        // $newIncome->received_date = null;
        $newIncome->is_partial = true;
        $newIncome->parent_id = $income->id;
        $newIncome->schedule_amount = $validated['amount'] ?? $income->schedule_amount ?? $income->amount;
        $newIncome->notes = $request->balance_notes ?? 'Balance from partial payment of income #' . $income->id;
        $newIncome->created_at = now();
        $newIncome->updated_at = now();
        $newIncome->save();

        $newIncomeId = $newIncome->id;

        \Log::info('New Income Created:', [
          'id' => $newIncomeId,
          'amount' => $newIncome->amount,
          'actual_amount' => $newIncome->actual_amount
        ]);

        // Copy GST to new income if applicable
        if ($request->apply_gst && $originalGstAmount > 0) {
          $newGstAmount = $originalGstAmount - $gstAmountForCurrent;
          if ($newGstAmount > 0) {
            $newIncome->taxes()->create([
              'taxable_type'   => Income::class,
              'taxable_id'     => $newIncomeId,
              'tax_type'       => 'gst',
              'tax_percentage' => $validated['gst_percentage'] ?? 0,
              'tax_amount'     => $newGstAmount,
              'payment_status' => 'not_received',
              'direction'      => 'income',
              'company_id'     => $income->company_id,
            ]);
          }
        }

        // Copy TDS to new income if applicable
        if ($request->apply_tds && $originalTdsAmount > 0) {
          if ($remainingTdsAmount > 0) {
            $newIncome->taxes()->create([
              'taxable_type'   => Income::class,
              'taxable_id'     => $newIncomeId,
              'tax_type'       => 'tds',
              'tax_percentage' => $validated['tds_percentage'] ?? 0,
              'tax_amount'     => $remainingTdsAmount,
              'payment_status' => 'not_received',
              'direction'      => 'income',
              'company_id'     => $income->company_id,
            ]);
          }
        }
      }

      DB::commit();

      return response()->json([
        'success'          => true,
        'message'          => $isSplitPayment ?
          'Partial payment recorded. Original income updated and new income created for balance.' :
          'Income updated successfully!',
        'new_income_id'    => $newIncomeId,
        'is_split_payment' => $isSplitPayment,
        'data'             => [
          'original_income' => [
            'id'              => $income->id,
            'amount'          => $income->amount,
            'actual_amount'   => $income->actual_amount,
            'received_amount' => $income->received_amount,
            'balance_amount'  => $income->balance_amount,
            'status'          => $income->status,
            'net_payable'     => $netPayableAmount
          ],
          'taxes' => [
            'gst_amount' => $gstAmountForCurrent,
            'tds_amount' => $tdsAmountForCurrent
          ],
          'split_info' => [
            'is_split' => $isSplitPayment,
            'balance' => $balanceAmount,
            'new_income_created' => !is_null($newIncomeId)
          ]
        ]
      ]);
    } catch (\Exception $e) {
      DB::rollBack();
      \Log::error('Income Update Error: ' . $e->getMessage());
      return response()->json([
        'success' => false,
        'message' => 'Error updating income: ' . $e->getMessage()
      ], 500);
    }
  }
  public function show($id)
  {
    try {
      $income = Income::with([
        'company',
        'taxes'
      ])->findOrFail($id);

      // Get client details from party_name if no separate client table
      $clientName = $income->party_name;

      // If you have a separate clients table linked by party_name
      $client = null;
      if ($income->party_name) {
        // Try to find client by name if you have a clients table
      }

      // Calculate tax amounts
      $gstItems = [];
      $tdsItems = [];
      $gstTotal = 0;
      $tdsTotal = 0;

      if ($income->taxes && count($income->taxes) > 0) {
        $gstItems = $income->taxes->filter(function ($tax) {
          return $tax->tax_type === 'gst';
        })->values()->toArray();

        $tdsItems = $income->taxes->filter(function ($tax) {
          return $tax->tax_type === 'tds';
        })->values()->toArray();

        foreach ($gstItems as $gst) {
          $gstTotal += floatval($gst['tax_amount']);
        }

        foreach ($tdsItems as $tds) {
          $tdsTotal += floatval($tds['tax_amount']);
        }
      }

      // Calculate total without TDS
      $totalWithoutTds = floatval($income->amount) + $gstTotal;

      // Create invoice data structure
      $invoice = [
        'id'               => $income->id,
        'invoice_number'   => $income->invoice_id ?? 'INV-' . str_pad($income->id, 6, '0', STR_PAD_LEFT),
        'status'           => $income->status,
        'type'             => $income->income_type ?? 'invoice',
        'issue_date'       => $income->income_date,
        'due_date'         => $income->due_date ?? $income->income_date,
        'subtotal'         => floatval($income->amount),
        'total_amount'     => floatval($income->amount) + $gstTotal - $tdsTotal, // Net amount after GST and TDS
        'purpose_comment'  => $income->notes,
        'terms_conditions' => null, // Add this field if you have it
        'tax_type'         => $income->tax_type ?? 'GST',

        // Company details
        'company'          => $income->company ? [
          'id'      => $income->company->id,
          'name'    => $income->company->name,
          'gstin'   => $income->company->gstin ?? null,
          'address' => $income->company->address ?? null,
          'state'   => $income->company->state ?? null,
          'email'   => $income->company->email ?? null,

        ] : [
          'name'    => 'Unknown Company',
          'gstin'   => null,
          'address' => null,
          'state'   => null,
        ],

        // Client details
        'client_details'   => [
          'name'    => $clientName,
          'email'   => $client ? $client->email : null,
          'phone'   => $client ? $client->phone : null,
          'gstin'   => $client ? $client->gstin : null,
          'address' => $client ? $client->address : null,
        ],

        // Tax details
        'taxes'            => array_merge($gstItems, $tdsItems),

        // Line items - create from income data
        'line_items'       => [
          [
            'description'    => $income->description ?? 'Income Payment',
            'quantity'       => 1,
            'rate'           => floatval($income->amount),
            'amount'         => floatval($income->amount),
            'tax_type'       => $gstItems ? 'gst' : null,
            'tax_percentage' => $gstItems ? ($gstItems[0]['tax_percentage'] ?? 0) : 0,
          ]
        ],
      ];

      return response()->json([
        'success' => true,
        'invoice' => $invoice
      ]);
    } catch (\Exception $e) {
      \Log::error('Error fetching income invoice data: ' . $e->getMessage());
      return response()->json([
        'success' => false,
        'message' => 'Error fetching invoice details: ' . $e->getMessage()
      ], 500);
    }
  }
  public function destroy($id)
  {
    $income = Income::findOrFail($id);
    $income->delete();

    return response()->json([
      'success' => true,
      'message' => 'Income deleted successfully!',
    ]);
  }
  private function formatBytes($bytes, $precision = 2)
  {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];

    $bytes = max($bytes, 0);
    $pow   = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow   = min($pow, count($units) - 1);

    $bytes /= pow(1024, $pow);

    return round($bytes, $precision) . ' ' . $units[$pow];
  }
  public function markAsReceived($id)
  {
    $income = Income::findOrFail($id);
    $income->update(['status' => 'received']);

    return response()->json([
      'success' => true,
      'message' => 'Income marked as received!',
    ]);
  }

    public function export(Request $request)
    {
        $companyIds = $this->getUserCompanyIds($request->company);
        $dateRange = $request->get('date_range', 'month');
        $status = $request->get('status', 'all');
        $type = $request->get('export_type', 'excel');

        $query = Income::with(['company', 'category'])
            ->whereIn('company_id', $companyIds);

        // Apply filters (similar to index)
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $incomes = $query->get();

        if ($type === 'excel') {
            return Excel::download(new IncomeExport($incomes), 'income_report_' . date('Y-m-d') . '.xlsx');
        } else {
            $pdf = Pdf::loadView('Manager.exports.income_pdf', [
                'incomes' => $incomes,
                'period' => $dateRange,
                'title' => 'Income & Balances Report'
            ]);
            return $pdf->download('income_report_' . date('Y-m-d') . '.pdf');
        }
    }
  // ========== UPCOMING PAYMENTS PAGE ==========
  public function upcoming(Request $request)
  {
    // Get filters from request
    $dateRange = $request->get('range', '7days');
    $companyId = $request->get('company');
    $type      = $request->get('type');
    $status    = $request->get('status');
    $tab       = $request->get('tab', 'all');
    $fromDate  = $request->get('from_date', date('Y-m-d'));
    $toDate    = $request->get('to_date', date('Y-m-d', strtotime('+30 days')));

    // Calculate date range based on filter
    switch ($dateRange) {
      case 'today':
        $startDate = date('Y-m-d');
        $endDate = date('Y-m-d');
        break;
      case '7days':
        $startDate = date('Y-m-d');
        $endDate = date('Y-m-d', strtotime('+7 days'));
        break;
      case '30days':
        $startDate = date('Y-m-d');
        $endDate = date('Y-m-d', strtotime('+30 days'));
        break;
      case 'month':
        $startDate = date('Y-m-01');
        $endDate = date('Y-m-t');
        break;
      default:
        $startDate = $fromDate;
        $endDate = $toDate;
    }

    // Get upcoming incomes
    $incomeQuery = Income::with('category')->whereBetween('income_date', [$startDate, $endDate])
      ->with('company')
      ->orderBy('income_date', 'asc');

    // Get upcoming expenses
    $expenseQuery = Expense::with('categoryRelation')
      // ->whereBetween('due_date', [$startDate, $endDate])
      ->where('status', 'upcoming')
      ->with(['company', 'expenseType'])
      ->orderBy('due_date', 'asc');

    // Apply company filter
    if ($companyId) {
      $incomeQuery->where('company_id', $companyId);
      $expenseQuery->where('company_id', $companyId);
    }

    // Apply status filter
    if ($status) {
      if ($status === 'paid') {
        $incomeQuery->where('status', 'received');
        $expenseQuery->where('status', 'paid');
      } else {
        $incomeQuery->where('status', $status);
        $expenseQuery->where('status', $status);
      }
    }

    // Get data
    $upcomingIncomes  = $incomeQuery->get();
    $upcomingExpenses = $expenseQuery->get();

    // Combine and process payments
    $allPayments = collect();
    $today       = Carbon::today();

    foreach ($upcomingIncomes as $income) {
      // echo $income->category->name;
      $daysLeft  = $today->diffInDays(Carbon::parse($income->income_date), false);
      $isOverdue = $daysLeft < 0;

      $allPayments->push([
        'type'         => 'income',
        'id'           => $income->id,
        'date'         => $income->income_date,
        'description'  => $income->source,
        'amount'       => $income->amount,
        'status'       => $income->status,
        'company'      => $income->company->name ?? 'N/A',
        'category'     => ucfirst($income->category->name ?? ''),
        'party_name'   => '-',
        'source'       => 'income',
        'days_left'    => $daysLeft,
        'is_overdue'   => $isOverdue,
        'is_today'     => $daysLeft === 0,
        'is_tomorrow'  => $daysLeft === 1,
        'payment_date' => $income->income_date,
        'payment_type' => 'Credit',
      ]);
    }
    foreach ($upcomingExpenses as $expense) {
      $daysLeft  = $today->diffInDays(Carbon::parse($expense->due_date), false);
      $isOverdue = $daysLeft < 0 || $expense->status === 'overdue';

      $allPayments->push([
        'type'         => 'expense',
        'id'           => $expense->id,
        'date'         => $expense->due_date,
        'description'  => $expense->name,
        'amount'       => -$expense->planned_amount,
        'status'       => $expense->status,
        'company'      => $expense->company->name ?? 'N/A',
        'category'     => $expense->categoryRelation->name ?? 'Other',
        'party_name'   => $expense->party_name ?? '-',
        'source'       => $expense->categoryRelation->category_type === 'standard_fixed' || 'standard_editable' ? 'Standard' : 'Non-Standard',
        'days_left'    => $daysLeft,
        'is_overdue'   => $isOverdue,
        'is_today'     => $daysLeft === 0,
        'is_tomorrow'  => $daysLeft === 1,
        'payment_date' => $expense->due_date,
        'payment_type' => 'Debit',
      ]);
    }

    // Apply type filter
    if ($type === 'credit') {
      $allPayments = $allPayments->where('type', 'income');
    } elseif ($type === 'debit') {
      $allPayments = $allPayments->where('type', 'expense');
    }

    // Apply tab filter
    if ($tab === 'debits') {
      $allPayments = $allPayments->where('type', 'expense');
    } elseif ($tab === 'credits') {
      $allPayments = $allPayments->where('type', 'income');
    } elseif ($tab === 'overdue') {
      $allPayments = $allPayments->where('is_overdue', true);
    }

    // Apply amount filters
    $minAmount = $request->get('min_amount');
    $maxAmount = $request->get('max_amount');

    if ($minAmount) {
      $allPayments = $allPayments->filter(function ($payment) use ($minAmount) {
        return abs($payment['amount']) >= $minAmount;
      });
    }

    if ($maxAmount) {
      $allPayments = $allPayments->filter(function ($payment) use ($maxAmount) {
        return abs($payment['amount']) <= $maxAmount;
      });
    }

    // Sort by date
    $payments = $allPayments->sortBy('date')->values();

    // Group by date for timeline view
    $groupedPayments = $payments->groupBy(function ($item) {
      return Carbon::parse($item['date'])->format('Y-m-d');
    });

    // Calculate statistics
    $stats = [
      'total_upcoming' => $payments->sum(function ($item) {
        return abs($item['amount']);
      }),
      'upcoming_count' => $payments->count(),
      'total_debits'   => $payments->where('type', 'expense')->sum(function ($item) {
        return abs($item['amount']);
      }),
      'debits_count'   => $payments->where('type', 'expense')->count(),
      'total_credits'  => $payments->where('type', 'income')->sum(function ($item) {
        return abs($item['amount']);
      }),
      'credits_count'  => $payments->where('type', 'income')->count(),
      'total_overdue'  => $payments->where('is_overdue', true)->sum(function ($item) {
        return abs($item['amount']);
      }),
      'overdue_count'  => $payments->where('is_overdue', true)->count(),
    ];

    // Get companies for filter
    $companies = Company::where('status', 'active')->get();

    return view('Manager.cash-flow.upcoming_payments', compact(
      'payments',
      'groupedPayments',
      'stats',
      'companies',
      'dateRange',
      'companyId',
      'type',
      'status',
      'tab',
      'fromDate',
      'toDate'
    ));
  }
  // ========== BALANCES & DUES PAGE ==========
  public function balance(Request $request)
  {
    $user = auth()->user();

    $companyId = $request->get('company');
    $sort      = $request->get('sort', 'name');

    $query = Company::where('manager_id', $user->id)
      ->where('status', 'active')
      ->with(['incomes', 'expenses']);

    if ($companyId) {
      $query->where('id', $companyId);
    }

    $companies = $query->get()->map(function ($company) {
      $totalIncome     = $company->incomes->where('status', 'received')->sum('amount');
      $totalExpenses   = $company->expenses->where('status', 'paid')->sum('actual_amount');
      $pendingIncome   = $company->incomes->where('status', 'pending')->sum('amount');
      $pendingExpenses = $company->expenses->whereIn('status', ['pending', 'upcoming'])->sum('planned_amount');

      $netBalance = $totalIncome - $totalExpenses;
      $netDues    = $pendingIncome - $pendingExpenses;

      return [
        'id'                    => $company->id,
        'name'                  => $company->name,
        'code'                  => $company->code,
        'total_income'          => $totalIncome,
        'total_expenses'        => $totalExpenses,
        'net_balance'           => $netBalance,
        'dues'                  => [
          'income'   => $pendingIncome,
          'expenses' => $pendingExpenses,
          'net'      => $netDues,
        ],
        'pending_income_count'  => $company->incomes->where('status', 'pending')->count(),
        'pending_expense_count' => $company->expenses->whereIn('status', ['pending', 'upcoming'])->count(),
      ];
    });

    // Apply sorting
    if ($sort === 'balance_desc') {
      $companies = $companies->sortByDesc('net_balance');
    } elseif ($sort === 'balance_asc') {
      $companies = $companies->sortBy('net_balance');
    } elseif ($sort === 'dues_desc') {
      $companies = $companies->sortByDesc('dues.net');
    } else {
      $companies = $companies->sortBy('name');
    }

    $overallStats = [
      'total_income'        => $companies->sum('total_income'),
      'total_expenses'      => $companies->sum('total_expenses'),
      'net_balance'         => $companies->sum('net_balance'),
      'total_dues_income'   => $companies->sum('dues.income'),
      'total_dues_expenses' => $companies->sum('dues.expenses'),
      'total_net_dues'      => $companies->sum('dues.net'),
    ];

    return view('Manager.cash-flow.balances', compact('companies', 'overallStats'));
  }

  // Add these methods to your controller
  public function companyDuesDetails($id)
  {
    $company = Company::with([
      'incomes'  => function ($q) {
        $q->where('status', 'pending');
      },
      'expenses' => function ($q) {
        $q->whereIn('status', ['pending', 'upcoming']);
      },
    ])->findOrFail($id);

    $pendingIncome   = $company->incomes->sum('amount');
    $pendingExpenses = $company->expenses->sum('planned_amount');
    $netDues         = $pendingIncome - $pendingExpenses;

    return response()->json([
      'success'         => true,
      'company'         => $company->only(['id', 'name', 'code']),
      'pendingIncomes'  => $company->incomes,
      'pendingExpenses' => $company->expenses,
      'netDues'         => $netDues,
    ]);
  }

  public function balanceSummary($id)
  {
    $company = Company::with(['incomes', 'expenses'])->findOrFail($id);

    $pendingIncome   = $company->incomes->where('status', 'pending')->sum('amount');
    $pendingExpenses = $company->expenses->whereIn('status', ['pending', 'upcoming'])->sum('planned_amount');
    $netDues         = $pendingIncome - $pendingExpenses;

    return response()->json([
      'success'         => true,
      'pendingIncome'   => $pendingIncome,
      'pendingExpenses' => $pendingExpenses,
      'netDues'         => $netDues,
    ]);
  }

  public function settle($id)
  {
    try {
      $income = Income::findOrFail($id);
      $income->update([
        'status' => 'paid',
        'received_date' => now()->toDateString()
      ]);

      return response()->json([
        'success' => true,
        'message' => 'Income marked as received'
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Error settling income: ' . $e->getMessage()
      ], 500);
    }
  }

  // ========== IMPORT INCOME FROM EXCEL ==========
  public function import(Request $request)
  {
    $request->validate([
      'file'       => 'required|mimes:xlsx,xls,csv',
      'company_id' => 'required|exists:companies,id',
    ]);

    return response()->json([
      'success' => true,
      'message' => 'Import functionality will be implemented with Laravel Excel package',
    ]);
  }

  public function sendEmail(Request $request)
  {
    try {
      $request->validate([
        'to_email'   => 'required|email',
        'subject'    => 'required|string|max:255',
        'message'    => 'required|string',
      ]);

      $invoice = Income::with(['company'])->find($request->income_id);

      // Properly decode client_details and ensure it's an array
      $clientDetails = $invoice->client_details;

      if (is_string($clientDetails)) {
        $clientDetails = json_decode($clientDetails, true);
      }

      // Ensure clientDetails is always an array
      if (!is_array($clientDetails)) {
        $clientDetails = [];
      }

      // Process variables in message
      $message = $request->message;
      $message = str_replace('{client_name}', $clientDetails['name'] ?? 'Customer', $message);
      $message = str_replace('{invoice_no}', $invoice->invoice_number, subject: $message);
      $message = str_replace('{due_date}', $invoice->due_date ? date('d M, Y', strtotime($invoice->due_date)) : 'N/A', $message);
      $message = str_replace('{amount}', number_format($invoice->total_amount, 2), $message);
      $message = str_replace('{company_name}', $invoice->company->name ?? '', $message);

      // Process subject variables
      $subject = $request->subject;
      $subject = str_replace('{client_name}', $clientDetails['name'] ?? 'Customer', $subject);
      $subject = str_replace('{invoice_no}', $invoice->invoice_number, $subject);

      // CC emails
      $ccEmails = [];
      if ($request->cc_email) {
        $ccEmails = array_map('trim', explode(',', $request->cc_email));
        $ccEmails = array_filter($ccEmails, function ($email) {
          return filter_var($email, FILTER_VALIDATE_EMAIL);
        });
      }

      // Convert client_details back to string for the view if it was an array
      if (is_array($clientDetails)) {
        $invoice->client_details = $clientDetails;
      }

      // Send email
      Mail::send('emails.invoice', [
        'invoice'        => $invoice,
        'client_details' => $clientDetails, // Pass decoded client details separately
        'custom_message' => $message,
        'subject'        => $subject,
      ], function ($mail) use ($invoice, $request, $subject, $ccEmails) {
        $mail->to($request->to_email)
          ->subject($subject)
          ->from('support@petsfolio.in', 'Finance Manager');

        if (!empty($ccEmails)) {
          $mail->cc($ccEmails);
        }

        // Attach PDF if requested
        if ($request->boolean('attach_pdf')) {
          $pdf = $this->generateInvoicePdf($invoice);
          $mail->attachData($pdf->output(), "{$invoice->invoice_number}.pdf", [
            'mime' => 'application/pdf',
          ]);
        }
      });

      return response()->json([
        'success' => true,
        'message' => 'Invoice sent successfully!'
      ]);
    } catch (\Exception $e) {
      \Log::error('Error sending invoice email: ' . $e->getMessage());
      \Log::error('Stack trace: ' . $e->getTraceAsString());

      return response()->json([
        'success' => false,
        'message' => 'Failed to send invoice: ' . $e->getMessage()
      ], 500);
    }
  }
  public function downloadFromIncome($incomeId)
  {
    $income = Income::findOrFail($incomeId);

    if (!$income->invoice_id) {
      abort(404, 'No invoice associated with this income');
    }

    return $this->download($income->invoice_id);
  }
  public function download($id)
  {
    $invoice = Invoice::with(['company', 'creator'])->findOrFail($id);
    $company = $invoice->company;

    $clientDetails = $invoice->client_details;
    if (is_string($clientDetails)) {
      $clientDetails = json_decode($clientDetails, true);
    }

    // Get line items (decoded from JSON)
    $lineItems = $invoice->line_items;
    if (is_string($lineItems)) {
      $lineItems = json_decode($lineItems, true);
    }

    // Generate amount in words
    $amountInWords = $invoice->amount_in_words;

    // Add missing data that might not be in the invoice
    $invoice->currency_symbol = $invoice->currency_symbol ?? '$';
    $invoice->currency        = $invoice->currency ?? 'Dollars';
    $invoice->project_note    = $invoice->project_note ?? 'Digital Display Videos for Restaurant';
    $invoice->delivery_terms  = $invoice->delivery_terms ?? 'Online Delivery';
    $logoBase64               = null;
    $logoPath                 = public_path('uploads/logo.png');

    if (file_exists($logoPath)) {
      $logoData   = file_get_contents($logoPath);
      $logoBase64 = 'data:image/png;base64,' . base64_encode($logoData);
    }
    $pdf = PDF::loadView('Admin.pdf.invoice-template', compact(
      'invoice',
      'clientDetails',
      'company',
      'lineItems',
      'amountInWords',
      'logoBase64'
    ));

    $pdf->setOptions([
      'defaultFont'          => 'DejaVu Sans',
      'isHtml5ParserEnabled' => true,
      'isRemoteEnabled'      => false,
      'isPhpEnabled'         => false,
      'dpi'                  => 150,
      'margin_top'           => 10,
      'margin_right'         => 10,
      'margin_bottom'        => 10,
      'margin_left'          => 10
    ]);

    $pdf->setPaper('A4', 'portrait');
    $filename = strtolower(str_replace(' ', '_', $invoice->invoice_number)) . '.pdf';

    return $pdf->download($filename);
  }
  public function getInvoiceDetails($id)
  {
    $invoice = Invoice::with(['company'])->findOrFail($id);
    if ($invoice->line_items) {
      $invoice->line_items = json_decode($invoice->line_items, true);
    }
    if ($invoice->client_details) {
      $invoice->client_details = json_decode($invoice->client_details, true);
    }

    return response()->json([
      'success' => true,
      'invoice' => $invoice
    ]);
  }
  private function generateInvoicePdf($invoice)
  {
    $logoBase64 = null;
    $logoPath   = public_path('uploads/logo.png');

    if (file_exists($logoPath)) {
      $logoData   = file_get_contents($logoPath);
      $logoBase64 = 'data:image/png;base64,' . base64_encode($logoData);
    }
    $clientDetails = $invoice->client_details;
    if (is_string($clientDetails)) {
      $clientDetails = json_decode($clientDetails, true);
    }
    // Get line items (decoded from JSON)
    $lineItems = $invoice->line_items;
    if (is_string($lineItems)) {
      $lineItems = json_decode($lineItems, true);
    }
    $company = $invoice->company;

    // Generate amount in words
    $amountInWords = $invoice->amount_in_words;

    $pdf = PDF::loadView('Admin.pdf.invoice-template', [
      'invoice'       => $invoice,
      'logoBase64'    => $logoBase64,
      'clientDetails' => $clientDetails,
      'company'       => $company,
      'lineItems'     => $lineItems,
      'amountInWords' => $amountInWords
    ]);
    return $pdf;
  }
}
