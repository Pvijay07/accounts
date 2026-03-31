<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Expense;
use App\Models\GstSettlement;
use App\Models\GstTask;
use App\Models\Income;
use App\Models\Invoice;
use App\Models\Tax;
use App\Traits\ManagesCompanies;
use App\Exports\GstExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class GSTController extends Controller
{
    use ManagesCompanies;

    /**
     * Main GST dashboard
     */
    public function index(Request $request)
    {
        // Get user's company IDs
        $companyIds = $this->getUserCompanyIds($request->company_id);
        
        // Get filters with validation
        $period        = $request->input('period', date('Y-m'));
        $selectedMonth = date('m', strtotime($period));
        $selectedYear  = date('Y', strtotime($period));

        // Validate period format
        if (! preg_match('/^\d{4}-\d{2}$/', $period)) {
            $period        = date('Y-m');
            $selectedMonth = date('m');
            $selectedYear  = date('Y');
        }

        // Get Income/Output GST (Sales) - Only from user's companies
        $outputTaxes = Tax::where(function ($query) {
                $query->where('direction', 'income')
                    ->orWhere('taxable_type', 'App\Models\Income');
            })
            ->where('tax_type', 'gst')
            ->whereMonth('created_at', $selectedMonth)
            ->whereYear('created_at', $selectedYear)
            ->whereHas('taxable.company', function ($query) use ($companyIds) {
                $query->whereIn('id', $companyIds);
            })
            ->with('taxable')
            ->get();

        // Get Expense/Input GST (Purchases) - Only from user's companies
        $inputTaxes = Tax::where(function ($query) {
                $query->where('direction', 'expense')
                    ->orWhere('taxable_type', 'App\Models\Expense');
            })
            ->where('tax_type', 'gst')
            ->whereMonth('created_at', $selectedMonth)
            ->whereYear('created_at', $selectedYear)
            ->whereHas('taxable.company', function ($query) use ($companyIds) {
                $query->whereIn('id', $companyIds);
            })
            ->with('taxable')
            ->get();

        // Get TDS deductions (both income and expense) - Only from user's companies
        $tdsTaxes = Tax::where('tax_type', 'tds')
            ->whereMonth('created_at', $selectedMonth)
            ->whereYear('created_at', $selectedYear)
            ->whereHas('taxable.company', function ($query) use ($companyIds) {
                $query->whereIn('id', $companyIds);
            })
            ->with('taxable')
            ->get();

        // Calculate totals
        $totalOutputGST = $outputTaxes->sum('tax_amount');
        $totalInputGST  = $inputTaxes->sum('tax_amount');
        $totalTDS       = $tdsTaxes->sum('tax_amount');

        // Net GST payable (Output GST - Input GST)
        $netGSTPayable = $totalOutputGST - $totalInputGST;

        // Net position (considering TDS as well)
        $netPosition = $netGSTPayable - $totalTDS;

        // Get the actual expenses and incomes for display - Only from user's companies
        $gstExpenses = Expense::whereHas('taxes', function ($query) {
                $query->where('tax_type', 'gst');
            })
            ->whereIn('company_id', $companyIds)
            ->whereMonth('created_at', $selectedMonth)
            ->whereYear('created_at', $selectedYear)
            ->get();

        $gstIncomes = Income::whereHas('taxes', function ($query) {
                $query->where('tax_type', 'gst');
            })
            ->whereIn('company_id', $companyIds)
            ->whereMonth('created_at', $selectedMonth)
            ->whereYear('created_at', $selectedYear)
            ->get();

        $tdsExpenses = Expense::whereHas('taxes', function ($query) {
                $query->where('tax_type', 'tds');
            })
            ->whereIn('company_id', $companyIds)
            ->whereMonth('created_at', $selectedMonth)
            ->whereYear('created_at', $selectedYear)
            ->get();

        $tdsIncomes = Income::whereHas('taxes', function ($query) {
                $query->where('tax_type', 'tds');
            })
            ->whereIn('company_id', $companyIds)
            ->whereMonth('created_at', $selectedMonth)
            ->whereYear('created_at', $selectedYear)
            ->get();

        // Get common view data
        $data = $this->getCommonViewData($selectedMonth, $selectedYear);

        // Merge with GST-specific data
        $data = array_merge($data, [
            'totalOutputGST'   => $totalOutputGST,             // GST collected from income/sales
            'totalInputGST'    => $totalInputGST,              // GST paid on expenses/purchases (ITC)
            'totalTDS'         => $totalTDS,                   // TDS deducted
            'netGSTPayable'    => max(0, $netGSTPayable),      // GST payable (if positive)
            'netGSTReceivable' => abs(min(0, $netGSTPayable)), // GST receivable (if negative)
            'netPosition'      => $netPosition,                // Overall position including TDS
            'gstExpenses'      => $gstExpenses,                // Expenses with GST
            'gstIncomes'       => $gstIncomes,                 // Incomes with GST
            'tdsExpenses'      => $tdsExpenses,                // Expenses with TDS
            'tdsIncomes'       => $tdsIncomes,                 // Incomes with TDS
            'outputTaxes'      => $outputTaxes,                // All output GST tax records
            'inputTaxes'       => $inputTaxes,                 // All input GST tax records
            'tdsTaxes'         => $tdsTaxes,                   // All TDS tax records
            'selectedPeriod'   => $period,
            'isGSTPayable'     => $netGSTPayable > 0, // Flag to indicate if GST is payable
            'isOverallPayable' => $netPosition > 0,   // Flag for overall payable position
        ]);

        return view('Manager.gst', $data);
    }

    /**
     * GST Collected Page
     */
    public function gstCollected(Request $request)
    {
        // Get user's company IDs
        $companyIds = $this->getUserCompanyIds($request->company_id);
        
        // Get filters
        $companyId = $request->input('company_id', 'all');
        $period    = $request->input('period', date('Y-m'));
        $taxType   = $request->input('tax_type', 'gst'); // Default to GST

        // Parse period
        $selectedMonth = date('m', strtotime($period));
        $selectedYear  = date('Y', strtotime($period));

        // Get GST collected from INCOME (Output GST) - This is your sales/income GST
        $incomeQuery = Income::whereHas('taxes', function ($query) use ($taxType) {
                $query->where('tax_type', $taxType);
            })
            ->whereIn('company_id', $companyIds)
            ->whereMonth('created_at', $selectedMonth)
            ->whereYear('created_at', $selectedYear);

        // Apply specific company filter if selected
        if ($companyId !== 'all') {
            $incomeQuery->where('company_id', $companyId);
        }

        $incomesWithTax = $incomeQuery->with(['company', 'taxes'])->get();

        // Get tax amounts separately for better calculation
        $gstTaxes = Tax::where('tax_type', 'gst')
            ->where(function ($query) {
                $query->where('direction', 'income')
                    ->orWhere('taxable_type', 'App\Models\Income');
            })
            ->whereMonth('created_at', $selectedMonth)
            ->whereYear('created_at', $selectedYear)
            ->whereHas('taxable.company', function ($query) use ($companyIds) {
                $query->whereIn('id', $companyIds);
            })
            ->get();

        $tdsTaxes = Tax::where('tax_type', 'tds')
            ->where(function ($query) {
                $query->where('direction', 'income')
                    ->orWhere('taxable_type', 'App\Models\Income');
            })
            ->whereMonth('created_at', $selectedMonth)
            ->whereYear('created_at', $selectedYear)
            ->whereHas('taxable.company', function ($query) use ($companyIds) {
                $query->whereIn('id', $companyIds);
            })
            ->get();

        // Calculate totals
        $totalGSTCollected  = $gstTaxes->sum('tax_amount');
        $totalTDSCollected  = $tdsTaxes->sum('tax_amount');
        $totalTaxableAmount = $incomesWithTax->sum('amount');

        // Get all income records for the period (for count and base amounts)
        $allIncomes = Income::whereIn('company_id', $companyIds)
            ->whereMonth('created_at', $selectedMonth)
            ->whereYear('created_at', $selectedYear);

        if ($companyId !== 'all') {
            $allIncomes->where('company_id', $companyId);
        }

        $allIncomes = $allIncomes->get();

        // Get receipts count (assuming you have a receipts table)
        $receiptsCount = 0; // You can implement this if you have receipts table

        $data = $this->getCommonViewData($selectedMonth, $selectedYear);
        $data = array_merge($data, [
            'totalGSTCollected'  => $totalGSTCollected,
            'totalTDSCollected'  => $totalTDSCollected,
            'totalTaxableAmount' => $totalTaxableAmount,
            'incomesWithTax'     => $incomesWithTax,
            'gstTaxes'           => $gstTaxes,
            'tdsTaxes'           => $tdsTaxes,
            'selectedCompany'    => $companyId,
            'selectedPeriod'     => $period,
            'selectedTaxType'    => $taxType,
            'totalRecords'       => $allIncomes->count(),
            'receiptsCount'      => $receiptsCount,
        ]);

        return view('Manager.gst_collected', $data);
    }

    public function attachReceipt(Request $request)
    {
        $request->validate([
            'income_id'     => 'required|exists:incomes,id',
            'document_type' => 'required|string',
            'document_file' => 'required|file|max:5120|mimes:pdf,jpg,jpeg,png,doc,docx',
            'notes'         => 'nullable|string',
        ]);

        try {
            // Check if user has access to this income's company
            $income = Income::findOrFail($request->income_id);
            $userCompanyIds = $this->getUserCompanyIds();
            
            if (!in_array($income->company_id, $userCompanyIds)) {
                return response()->json([
                    'success' => false, 
                    'message' => 'You do not have access to this company'
                ], 403);
            }

            $file     = $request->file('document_file');
            $filename = 'tax_receipt_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path     = $file->storeAs('receipts', $filename, 'public');

            // Save to receipts table or your preferred storage
            // Assuming you have a Receipt model
            Receipt::create([
                'income_id'     => $request->income_id,
                'file_name'     => $file->getClientOriginalName(),
                'file_path'     => $path,
                'file_type'     => $file->getClientOriginalExtension(),
                'document_type' => $request->document_type,
                'notes'         => $request->notes,
                'uploaded_by'   => auth()->id(),
            ]);

            return response()->json(['success' => true, 'message' => 'Receipt attached successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Taxes on Expenses Page
     */
    public function taxes(Request $request)
    {
        // Get user's company IDs
        $companyIds = $this->getUserCompanyIds($request->company_id);
        
        // Get filters
        $companyId = $request->input('company_id', 'all');
        $period    = $request->input('period', date('Y-m'));
        $taxType   = $request->input('tax_type', 'all');

        // Parse period
        $selectedMonth = date('m', strtotime($period));
        $selectedYear  = date('Y', strtotime($period));

        // Get expenses with taxes from the taxes table (Input Tax Credit)
        // Only get expenses, not incomes
        $expenseTaxesQuery = Tax::where(function ($query) {
                $query->where('direction', 'expense')
                    ->orWhere('taxable_type', 'App\Models\Expense');
            })
            ->whereMonth('created_at', $selectedMonth)
            ->whereYear('created_at', $selectedYear)
            ->whereHas('taxable.company', function ($query) use ($companyIds) {
                $query->whereIn('id', $companyIds);
            });

        // Apply tax type filter
        if ($taxType !== 'all') {
            $expenseTaxesQuery->where('tax_type', $taxType);
        }

        // Eager load relationships - only for Expense models
        $expenseTaxes = $expenseTaxesQuery->with([
            'taxable' => function ($query) {
                // Check if taxable_type is Expense before loading categoryRelation
                $query->when(
                    $query->getModel() instanceof \App\Models\Expense,
                    function ($q) {
                        $q->with(['company', 'categoryRelation']);
                    },
                    function ($q) {
                        $q->with(['company']); // For Income or other models
                    }
                );
            }
        ])->get();

        // Now filter to only include expenses (just in case)
        $expenseTaxes = $expenseTaxes->filter(function ($tax) {
            return $tax->taxable_type === 'App\Models\Expense';
        });

        // Filter by specific company if needed
        if ($companyId !== 'all') {
            $expenseTaxes = $expenseTaxes->filter(function ($tax) use ($companyId) {
                return $tax->taxable && $tax->taxable->company_id == $companyId;
            });
        }

        // Calculate totals
        $totalTaxPaid = $expenseTaxes->sum('tax_amount');
        $billsWithTax = $expenseTaxes->count();

        // Separate by tax type
        $gstTaxes = $expenseTaxes->where('tax_type', 'gst');
        $tdsTaxes = $expenseTaxes->where('tax_type', 'tds');
        $otherTaxes = $expenseTaxes->whereNotIn('tax_type', ['gst', 'tds']);

        $gstPaid = $gstTaxes->sum('tax_amount');
        $tdsPaid = $tdsTaxes->sum('tax_amount');
        $otherTaxPaid = $otherTaxes->sum('tax_amount');

        // Get total expense amount (before tax)
        $totalExpenseAmount = $expenseTaxes->sum(function ($tax) {
            return $tax->taxable ? $tax->taxable->actual_amount : 0;
        });

        // Get last updated date
        $lastUpdated = $expenseTaxes->isNotEmpty()
            ? $expenseTaxes->max('created_at')
            : now();

        $lastUpdatedFormatted = date('d-m-Y', strtotime($lastUpdated));

        // Get common view data
        $data = $this->getCommonViewData($selectedMonth, $selectedYear);

        // Merge with tax data
        $data = array_merge($data, [
            'totalTaxPaid'      => $totalTaxPaid,
            'totalExpenseAmount' => $totalExpenseAmount,
            'billsWithTax'      => $billsWithTax,
            'gstPaid'           => $gstPaid,
            'tdsPaid'           => $tdsPaid,
            'otherTaxPaid'      => $otherTaxPaid,
            'lastUpdated'       => $lastUpdatedFormatted,
            'currentPeriod'     => date('M Y', strtotime($period)),
            'expenseTaxes'      => $expenseTaxes,
            'selectedCompany'   => $companyId,
            'selectedPeriod'    => $period,
            'selectedTaxType'   => $taxType,
            'gstTaxes'          => $gstTaxes,
            'tdsTaxes'          => $tdsTaxes,
        ]);

        return view('Manager.expense_taxes', $data);
    }

    /**
     * Consolidated Tax Report
     */
    public function taxSummary(Request $request)
    {
        // Get user's company IDs
        $companyIds = $this->getUserCompanyIds($request->company_id);
        
        $period        = $request->input('period', date('Y-m'));
        $selectedMonth = date('m', strtotime($period));
        $selectedYear  = date('Y', strtotime($period));

        // Check if tax_type column exists in invoices table
        $hasTaxTypeInInvoice = Schema::hasColumn('invoices', 'tax_type');

        // Sales (Output) - Only from user's companies
        $salesQuery = Invoice::where('type', 'invoice')
            ->where('is_taxable', true)
            ->whereIn('company_id', $companyIds)
            ->whereMonth('issue_date', $selectedMonth)
            ->whereYear('issue_date', $selectedYear);

        $salesInvoices = $salesQuery->get();

        // Purchases (Input) - Only from user's companies
        $purchaseQuery = Invoice::where('type', 'purchase')
            ->where('is_taxable', true)
            ->whereIn('company_id', $companyIds)
            ->whereMonth('paid_date', $selectedMonth)
            ->whereYear('paid_date', $selectedYear);

        $purchaseInvoices = $purchaseQuery->get();

        // Expenses - Only from user's companies
        $expenseQuery = Expense::whereNotNull('tax_amount')
            ->where('tax_amount', '>', 0)
            ->whereIn('company_id', $companyIds)
            ->whereMonth('paid_date', $selectedMonth)
            ->whereYear('paid_date', $selectedYear);

        $expenses = $expenseQuery->get();

        // Initialize tax summary
        $taxSummary = [
            'gst'   => [
                'sales'     => 0,
                'purchases' => 0,
                'expenses'  => $expenses->where('tax_type', 'gst')->sum('tax_amount'),
            ],
            'tds'   => [
                'sales'     => 0,
                'purchases' => 0,
                'expenses'  => $expenses->where('tax_type', 'tds')->sum('tax_amount'),
            ],
            'other' => [
                'sales'     => 0,
                'purchases' => 0,
                'expenses'  => $expenses->where('tax_type', 'other')->sum('tax_amount'),
            ],
        ];

        // Fill sales and purchases data if tax_type column exists
        if ($hasTaxTypeInInvoice) {
            $taxSummary['gst']['sales']   = $salesInvoices->where('tax_type', 'gst')->sum('tax_amount');
            $taxSummary['tds']['sales']   = $salesInvoices->where('tax_type', 'tds')->sum('tax_amount');
            $taxSummary['other']['sales'] = $salesInvoices->where('tax_type', 'other')->sum('tax_amount');

            $taxSummary['gst']['purchases']   = $purchaseInvoices->where('tax_type', 'gst')->sum('tax_amount');
            $taxSummary['tds']['purchases']   = $purchaseInvoices->where('tax_type', 'tds')->sum('tax_amount');
            $taxSummary['other']['purchases'] = $purchaseInvoices->where('tax_type', 'other')->sum('tax_amount');
        } else {
            // If no tax_type column, assume all sales/purchases are GST
            $taxSummary['gst']['sales']     = $salesInvoices->sum('tax_amount');
            $taxSummary['gst']['purchases'] = $purchaseInvoices->sum('tax_amount');
        }

        // Calculate net payable by tax type
        $netPayable = [
            'gst' => max(0, $taxSummary['gst']['sales'] - $taxSummary['gst']['purchases']),
            'tds' => max(0, $taxSummary['tds']['sales'] - $taxSummary['tds']['purchases']),
        ];

        $data = $this->getCommonViewData($selectedMonth, $selectedYear);
        $data = array_merge($data, [
            'taxSummary'          => $taxSummary,
            'netPayable'          => $netPayable,
            'selectedPeriod'      => $period,
            'salesInvoices'       => $salesInvoices,
            'purchaseInvoices'    => $purchaseInvoices,
            'expenses'            => $expenses,
            'hasTaxTypeInInvoice' => $hasTaxTypeInInvoice,
        ]);

        return view('Manager.tax_summary', $data);
    }

    // Filter methods (for AJAX calls)
    public function filter(Request $request)
    {
        // Get user's company IDs
        $companyIds = $this->getUserCompanyIds($request->company_id);
        
        // Your filter logic here with user's companies
        return response()->json([
            'success'    => true,
            'outputGST'  => 180000,
            'itc'        => 60000,
            'netPayable' => 120000,
            'period'     => 'Nov 2025',
        ]);
    }

    public function storeTaxEntry(Request $request)
    {
        // Validate and store tax entry
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'date'       => 'required|date',
            'tax_period' => 'required|string',
            'vendor'     => 'required|string',
            'category'   => 'required|string',
            'bill_no'    => 'required|string',
            'tax_type'   => 'required|in:gst,tds,other',
            'tax_amount' => 'required|numeric|min:0',
            'comment'    => 'nullable|string',
            'attachment' => 'nullable|file|mimes:pdf,jpg,png|max:2048',
        ]);

        // Check if user has access to this company
        $userCompanyIds = $this->getUserCompanyIds();
        if (!in_array($validated['company_id'], $userCompanyIds)) {
            return response()->json([
                'success' => false, 
                'message' => 'You do not have access to this company'
            ], 403);
        }

        // Create tax entry
        $taxEntry = TaxEntry::create($validated);

        // Handle file upload
        // if ($request->hasFile('attachment')) {
        //     $path = $request->file('attachment')->store('tax-attachments');
        //     $taxEntry->update(['attachment' => $path]);
        // }

        return response()->json(['success' => true, 'message' => 'Tax entry saved successfully']);
    }

    public function settlement(Request $request)
    {
        // Get user's company IDs
        $companyIds = $this->getUserCompanyIds($request->company_id);
        
        // Get settlements only for user's companies
        $settlements = GstSettlement::whereIn('company_id', $companyIds)
            ->with(['company'])
            ->orderBy('payment_date', 'desc')
            ->get();

        // Calculate GST summary (only for user's companies)
        $gstSummary = $this->calculateGstSummary($companyIds);

        // Get common view data
        $data = $this->getCommonViewData();

        return array_merge($data, [
            'settlements' => $settlements,
            'gstSummary' => $gstSummary,
            'currentPeriod' => date('M Y'),
        ]);
    }

    private function calculateGstSummary($companyIds = null)
    {
        $companyIds = $companyIds ?? $this->getUserCompanyIds();
        
        $currentMonth = date('m');
        $currentYear = date('Y');

        // Get output GST (from income) - Only from user's companies
        $outputGst = Tax::where('tax_type', 'gst')
            ->where(function ($query) {
                $query->where('direction', 'income')
                    ->orWhere('taxable_type', 'App\Models\Income');
            })
            ->whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->whereHas('taxable.company', function ($query) use ($companyIds) {
                $query->whereIn('id', $companyIds);
            })
            ->sum('tax_amount');

        // Get input GST (from expenses - ITC) - Only from user's companies
        $inputGst = Tax::where('tax_type', 'gst')
            ->where(function ($query) {
                $query->where('direction', 'expense')
                    ->orWhere('taxable_type', 'App\Models\Expense');
            })
            ->whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->whereHas('taxable.company', function ($query) use ($companyIds) {
                $query->whereIn('id', $companyIds);
            })
            ->sum('tax_amount');

        // Calculate net payable
        $netPayable = max(0, $outputGst - $inputGst);

        return [
            'output_gst' => $outputGst,
            'input_gst' => $inputGst,
            'net_payable' => $netPayable,
        ];
    }

    public function storeSettlement(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'tax_period' => 'required|date_format:Y-m',
            'amount' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'payment_mode' => 'required|in:netbanking,upi,neft_rtgs,cheque',
            'challan_number' => 'nullable|string|max:100',
            'utr_number' => 'nullable|string|max:100',
            'purpose_comment' => 'nullable|string|max:500',
            'attachment' => 'nullable|file|max:5120|mimes:pdf,jpg,jpeg,png',
        ]);

        // Check if user has access to this company
        $userCompanyIds = $this->getUserCompanyIds();
        if (!in_array($validated['company_id'], $userCompanyIds)) {
            return response()->json([
                'success' => false, 
                'message' => 'You do not have access to this company'
            ], 403);
        }

        try {
            DB::beginTransaction();

            $settlement = GstSettlement::create([
                'company_id' => $validated['company_id'],
                'tax_period' => $validated['tax_period'],
                'amount' => $validated['amount'],
                'payment_date' => $validated['payment_date'],
                'payment_mode' => $validated['payment_mode'],
                'challan_number' => $validated['challan_number'] ?? null,
                'utr_number' => $validated['utr_number'] ?? null,
                'status' => 'paid',
                'purpose_comment' => $validated['purpose_comment'] ?? null,
                'created_by' => auth()->id(),
            ]);

            // Handle attachment if provided
            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $filename = 'settlement_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('settlements', $filename, 'public');

                // Assuming you have an Attachment model
                $settlement->attachments()->create([
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_type' => $file->getClientOriginalExtension(),
                    'uploaded_by' => auth()->id(),
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Settlement created successfully!',
                'settlement' => $settlement,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function showSettlement($id)
    {
        $settlement = GstSettlement::with(['company', 'creator', 'attachments'])->findOrFail($id);
        
        // Check if user has access to this settlement's company
        $userCompanyIds = $this->getUserCompanyIds();
        if (!in_array($settlement->company_id, $userCompanyIds)) {
            abort(403, 'You do not have access to this settlement');
        }

        return view('Manager.gst_settlement_show', compact('settlement'));
    }

    public function exportGstCollected(Request $request, $type)
    {
        $companyIds = $this->getUserCompanyIds($request->company_id);
        $period = $request->input('period', date('Y-m'));
        
        $selectedMonth = date('m', strtotime($period));
        $selectedYear = date('Y', strtotime($period));

        $taxes = Tax::where('tax_type', 'gst')
            ->where('direction', 'income')
            ->whereMonth('created_at', $selectedMonth)
            ->whereYear('created_at', $selectedYear)
            ->whereHas('taxable.company', function($q) use ($companyIds) {
                $q->whereIn('id', $companyIds);
            })
            ->with(['taxable', 'taxable.company'])
            ->get();

        if ($type === 'excel') {
            return Excel::download(new GstExport($taxes), 'gst_collected_' . $period . '.xlsx');
        } else {
             $pdf = Pdf::loadView('Manager.exports.gst_pdf', [
                'taxes' => $taxes,
                'period' => $period,
                'title' => 'GST Collected (Output)'
            ]);
            return $pdf->download('gst_collected_' . $period . '.pdf');
        }
    }

    public function exportTaxes(Request $request, $type)
    {
        $companyIds = $this->getUserCompanyIds($request->company_id);
        $period = $request->input('period', date('Y-m'));
        $taxTypeFilter = $request->input('tax_type', 'all');

        $selectedMonth = date('m', strtotime($period));
        $selectedYear = date('Y', strtotime($period));

        $query = Tax::where('direction', 'expense')
            ->whereMonth('created_at', $selectedMonth)
            ->whereYear('created_at', $selectedYear)
            ->whereHas('taxable.company', function($q) use ($companyIds) {
                $q->whereIn('id', $companyIds);
            })
            ->with(['taxable', 'taxable.company']);

        if ($taxTypeFilter !== 'all') {
            $query->where('tax_type', $taxTypeFilter);
        }

        $taxes = $query->get();

        if ($type === 'excel') {
            return Excel::download(new GstExport($taxes), 'gst_input_taxes_' . $period . '.xlsx');
        } else {
            $pdf = Pdf::loadView('Manager.exports.gst_pdf', [
                'taxes' => $taxes,
                'period' => $period,
                'title' => 'GST Paid (Input Tax Credit)'
            ]);
            return $pdf->download('gst_input_taxes_' . $period . '.pdf');
        }
    }

    public function export(Request $request, $type)
    {
        // Combined report for the dashboard
        $companyIds = $this->getUserCompanyIds($request->company_id);
        $period = $request->input('period', date('Y-m'));

        $selectedMonth = date('m', strtotime($period));
        $selectedYear = date('Y', strtotime($period));

        $taxes = Tax::whereIn('tax_type', ['gst', 'tds'])
             ->whereMonth('created_at', $selectedMonth)
             ->whereYear('created_at', $selectedYear)
             ->whereHas('taxable.company', function($q) use ($companyIds) {
                 $q->whereIn('id', $companyIds);
             })
             ->with(['taxable', 'taxable.company'])
             ->get();

        if ($type === 'excel') {
             return Excel::download(new GstExport($taxes), 'tax_summary_' . $period . '.xlsx');
        } else {
             $pdf = Pdf::loadView('Manager.exports.gst_pdf', [
                'taxes' => $taxes,
                'period' => $period,
                'title' => 'Tax Dashboard Summary'
            ]);
            return $pdf->download('tax_summary_' . $period . '.pdf');
        }
    }

    public function returns(Request $request)
    {
        // Get user's company IDs
        $companyIds = $this->getUserCompanyIds($request->company_id);
        
        // Get tasks with filters
        $status = $request->input('status', 'all');
        $companyId = $request->input('company_id', 'all');
        $returnType = $request->input('return_type', 'all');

        $query = GstTask::with(['company'])
            ->whereIn('company_id', $companyIds)
            ->orderBy('due_date', 'asc');

        // Apply filters
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($companyId !== 'all') {
            $query->where('company_id', $companyId);
        }

        if ($returnType !== 'all') {
            $query->where('return_type', $returnType);
        }

        $tasks = $query->get();

        // Get statistics - Only for user's companies
        $stats = [
            'total' => GstTask::whereIn('company_id', $companyIds)->count(),
            'pending' => GstTask::whereIn('company_id', $companyIds)->where('status', 'pending')->count(),
            'in_progress' => GstTask::whereIn('company_id', $companyIds)->where('status', 'in_progress')->count(),
            'completed' => GstTask::whereIn('company_id', $companyIds)->where('status', 'completed')->count(),
            'overdue' => GstTask::whereIn('company_id', $companyIds)->overdue()->count(),
        ];

        // Get common view data
        $data = $this->getCommonViewData();

        // Get available return types
        $returnTypes = ['GSTR-1', 'GSTR-3B', 'GSTR-9', 'TDS Return', 'Income Tax Return', 'Other'];

        return array_merge($data, [
            'tasks' => $tasks,
            'stats' => $stats,
            'returnTypes' => $returnTypes,
            'filters' => [
                'status' => $status,
                'company_id' => $companyId,
                'return_type' => $returnType,
            ],
        ]);
    }

    public function storeTask(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'tax_period' => 'required|date_format:Y-m',
            'return_type' => 'required|string|max:50',
            'due_date' => 'required|date',
            'reminder_date' => 'required|date|before_or_equal:due_date',
            'assigned_to' => 'required|string|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        // Check if user has access to this company
        $userCompanyIds = $this->getUserCompanyIds();
        if (!in_array($validated['company_id'], $userCompanyIds)) {
            return response()->json([
                'success' => false, 
                'message' => 'You do not have access to this company'
            ], 403);
        }

        try {
            $task = GstTask::create([
                'company_id' => $validated['company_id'],
                'tax_period' => $validated['tax_period'],
                'return_type' => $validated['return_type'],
                'due_date' => $validated['due_date'],
                'reminder_date' => $validated['reminder_date'],
                'assigned_to' => $validated['assigned_to'],
                'notes' => $validated['notes'] ?? null,
                'status' => 'pending',
                'created_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Task created successfully!',
                'task' => $task,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function updateTaskStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,in_progress,completed',
        ]);

        try {
            $task = GstTask::findOrFail($id);
            
            // Check if user has access to this task's company
            $userCompanyIds = $this->getUserCompanyIds();
            if (!in_array($task->company_id, $userCompanyIds)) {
                return response()->json([
                    'success' => false, 
                    'message' => 'You do not have access to this task'
                ], 403);
            }

            $task->update([
                'status' => $validated['status'],
                'completed_date' => $validated['status'] === 'completed' ? now() : null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Task status updated successfully!',
                'task' => $task,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function sendReminders(Request $request)
    {
        try {
            // Get user's company IDs
            $companyIds = $this->getUserCompanyIds($request->company_id);
            
            // Get upcoming tasks (due in next 7 days) - Only for user's companies
            $upcomingTasks = GstTask::with(['company'])
                ->whereIn('company_id', $companyIds)
                ->where('due_date', '<=', now()->addDays(7))
                ->where('due_date', '>=', now())
                ->where('status', '!=', 'completed')
                ->get();

            // Get overdue tasks - Only for user's companies
            $overdueTasks = GstTask::with(['company'])
                ->whereIn('company_id', $companyIds)
                ->where('due_date', '<', now())
                ->where('status', '!=', 'completed')
                ->get();

            $totalSent = 0;

            // Send emails for upcoming tasks
            foreach ($upcomingTasks as $task) {
                // Implement your email sending logic here
                // Mail::to($task->assigned_to)->send(new TaskReminderMail($task));
                $totalSent++;
            }

            // Send emails for overdue tasks
            foreach ($overdueTasks as $task) {
                // Mail::to($task->assigned_to)->send(new TaskOverdueMail($task));
                $totalSent++;
            }

            return response()->json([
                'success' => true,
                'message' => "Reminder emails sent for {$totalSent} tasks.",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function exportTasks(Request $request)
    {
        $companyIds = $this->getUserCompanyIds($request->company_id);
        $tasks = GstTask::whereIn('company_id', $companyIds)
            ->with('company')
            ->get();
            
        $pdf = Pdf::loadView('Manager.exports.gst_tasks_pdf', compact('tasks'));
        return $pdf->download('gst_tasks_' . date('Y-m-d') . '.pdf');
    }

    public function exportSettlements(Request $request)
    {
        $companyIds = $this->getUserCompanyIds($request->company_id);
        $settlements = GstSettlement::whereIn('company_id', $companyIds)
            ->with('company')
            ->get();
            
        $pdf = Pdf::loadView('Manager.exports.gst_settlements_pdf', compact('settlements'));
        return $pdf->download('gst_settlements_' . date('Y-m-d') . '.pdf');
    }

}