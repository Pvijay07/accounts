<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\Expense;
use App\Models\Tax;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class TDSController extends Controller
{
    /**
     * Get company IDs for the authenticated user
     */
    private function getUserCompanyIds($specificCompanyId = null)
    {
        $user = auth()->user();

        if ($specificCompanyId) {
            // Verify the user has access to this specific company
            $hasAccess = Company::where('id', $specificCompanyId)
                ->where('manager_id', $user->id)
                ->exists();

            if ($hasAccess) {
                return [$specificCompanyId];
            } else {
                // If user doesn't have access, return empty array
                return [];
            }
        }

        // For regular users, only return companies they manage
        if ($user->isAdmin() || $user->isCA()) {
            return Company::where('status', 'active')->pluck('id')->toArray();
        }

        return Company::where('manager_id', $user->id)
            ->where('status', 'active')
            ->pluck('id')
            ->toArray();
    }

    /**
     * Get companies for dropdown (only user's companies)
     */
    private function getUserCompanies()
    {
        $user = auth()->user();

        if ($user->isAdmin() || $user->isCA()) {
            return Company::where('status', 'active')->orderBy('name')->get();
        }

        return Company::where('manager_id', $user->id)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
    }

    private function generateMonths($count = 12)
    {
        $months = [];
        for ($i = 0; $i < $count; $i++) {
            $date     = date('Y-m', strtotime("-$i months"));
            $months[] = [
                'value' => $date,
                'label' => date('M Y', strtotime($date)),
            ];
        }
        return $months;
    }

    public function index(Request $request)
    {
        // Get user's company IDs
        $companyIds = $this->getUserCompanyIds($request->company_id);

        // Get filters with validation
        $period = $request->input('period', date('Y-m'));
        $selectedMonth = date('m', strtotime($period));
        $selectedYear = date('Y', strtotime($period));

        // Validate period format
        if (!preg_match('/^\d{4}-\d{2}$/', $period)) {
            $period = date('Y-m');
            $selectedMonth = date('m');
            $selectedYear = date('Y');
        }

        $status = $request->input('status', 'all');

        // Get TDS on Income (Output TDS) - From taxes table where taxable_type is Income/Invoice
        $query = Tax::where('tax_type', 'tds')
            ->where(function ($query) {
                $query->where('taxable_type', 'App\Models\Income')
                    ->orWhere('taxable_type', 'App\Models\Invoice');
            })
            ->whereMonth('created_at', $selectedMonth)
            ->whereYear('created_at', $selectedYear)
            ->whereHas('taxable.company', function ($query) use ($companyIds) {
                $query->whereIn('id', $companyIds);
            });

        // Apply status filter if selected
        if ($status !== 'all') {
            $query->whereHas('taxable', function ($q) use ($status) {
                $q->where('status', $status);
            });
        }

        $tdsIncomes = $query->with(['taxable', 'taxable.company'])->get();

        // Calculate totals
        $totalOutputTDS = $tdsIncomes->sum('tax_amount');
        $totalTaxableAmount = $tdsIncomes->sum(function ($tax) {
            return $tax->taxable ? $tax->taxable->subtotal : 0;
        });

        // Get common view data
        $data = $this->getCommonViewData($selectedMonth, $selectedYear);

        // Merge with TDS Income-specific data
        $data = array_merge($data, [
            'period' => $period,
            'selectedStatus' => $status,
            'companies' => $this->getUserCompanies(),
            'salesInvoices' => $tdsIncomes, // Renamed to match view
            'totalOutputTDS' => $totalOutputTDS,
            'totalTaxableAmount' => $totalTaxableAmount,
            'totalAttachments' => 0,
            'selectedMonth' => $selectedMonth,
            'selectedYear' => $selectedYear,
        ]);

        return view('Manager.tds_income', $data);
    }

    public function getCommonViewData($month = null, $year = null)
    {
        // Generate last 12 months for dropdown
        $months = [];
        for ($i = 0; $i < 12; $i++) {
            $date = date('Y-m', strtotime("-$i months"));
            $months[] = [
                'value' => $date,
                'label' => date('F Y', strtotime($date))
            ];
        }

        return [
            'months' => $months,
            'currentMonth' => $month ? date('F Y', strtotime("$year-$month-01")) : date('F Y'),
            'title' => 'TDS Management',
            'activeMenu' => 'tds',
        ];
    }

    public function tdsExpense(Request $request)
    {
        // Get user's company IDs
        $companyIds = $this->getUserCompanyIds($request->company_id);

        // Get filters
        $companyId = $request->input('company_id', 'all');
        $period    = $request->input('period', date('Y-m'));
        $vendorId  = $request->input('vendor_id', 'all'); // Added vendor filter for expenses
        $status    = $request->input('status', 'all');

        // Parse period
        $selectedMonth = date('m', strtotime($period));
        $selectedYear  = date('Y', strtotime($period));

        // Get TDS on expenses (Input TDS) - direction should be 'expense' for TDS we pay to vendors
        $query = Tax::with(['taxable' => function ($query) {
            $query->with(['company']);
        }])
            ->where('tax_type', 'tds')
            ->where('direction', 'expense') // Changed to expense for TDS we pay
            ->whereMonth('created_at', $selectedMonth)
            ->whereYear('created_at', $selectedYear)
            ->whereHas('taxable.company', function ($query) use ($companyIds) {
                $query->whereIn('id', $companyIds);
            });

        // Apply status filter
        if ($status !== 'all') {
            $query->where('payment_status', $status);
        }

        // Apply specific company filter if selected
        if ($companyId !== 'all') {
            $query->whereHas('taxable', function ($q) use ($companyId) {
                $q->where('company_id', $companyId);
            });
        }

        // Apply vendor filter
        if ($vendorId !== 'all') {
            $query->whereHas('taxable', function ($q) use ($vendorId) {
                $q->where('vendor_id', $vendorId);
            });
        }

        $tdsTaxes = $query->get();

        // Get purchase invoices with TDS
        $purchaseQuery = Expense::whereHas('taxes', function ($q) {
            $q->where('tax_type', 'tds')->where('direction', 'expense');
        })
            ->whereIn('company_id', $companyIds)
            ->whereMonth('created_at', $selectedMonth)
            ->whereYear('created_at', $selectedYear)
            ->with(['company', 'taxes']);

        // Apply specific company filter if selected
        if ($companyId !== 'all') {
            $purchaseQuery->where('company_id', $companyId);
        }

        // Apply vendor filter
        if ($vendorId !== 'all') {
            $purchaseQuery->where('vendor_id', $vendorId);
        }

        // Apply status filter to purchase invoices
        if ($status !== 'all') {
            $purchaseQuery->whereHas('taxes', function ($q) use ($status) {
                $q->where('tax_type', 'tds')->where('direction', 'expense')->where('payment_status', $status);
            });
        }

        $purchaseInvoices = $purchaseQuery->get();

        // Calculate totals
        $totalTDSAmount = $tdsTaxes->sum('tax_amount');
        $totalTDSPaid   = $tdsTaxes->where('payment_status', 'paid')->sum('amount_paid');
        $totalTDSDue    = $totalTDSAmount - $totalTDSPaid;

        // Calculate taxable amount from purchase invoices
        $totalTaxableAmount = $purchaseInvoices->sum('amount');

        // Get common view data
        $data = $this->getCommonViewData($selectedMonth, $selectedYear);

        $data = array_merge($data, [
            // TDS Data
            'tdsTaxes'          => $tdsTaxes,
            'totalTDSAmount'    => $totalTDSAmount,
            'totalTDSPaid'      => $totalTDSPaid,
            'totalTDSDue'       => $totalTDSDue,

            // Purchase Invoices Data
            'purchaseInvoices'  => $purchaseInvoices,
            'totalTaxableAmount' => $totalTaxableAmount,
            'totalAttachments'  => 0,

            // Filter values
            'selectedCompany'   => $companyId,
            'selectedVendor'    => $vendorId,
            'selectedStatus'    => $status,
            'selectedPeriod'    => $period,

            // Dropdowns
            'companies'         => $this->getUserCompanies(),
            'vendors'          => [],
        ]);

        return view('Manager.tds_expense', $data);
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
            'period'     => 'Nov 2025'
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
    // Get attachments for an invoice
    public function getAttachments($invoiceId)
    {
        try {
            $attachments = Attachment::where('invoice_id', $invoiceId)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($attachment) {
                    return [
                        'id' => $attachment->id,
                        'document_type' => $attachment->document_type,
                        'description' => $attachment->description,
                        'file_name' => $attachment->file_name,
                        'file_type' => pathinfo($attachment->file_path, PATHINFO_EXTENSION),
                        'file_size' => $attachment->file_size,
                        'file_url' => asset('storage/' . $attachment->file_path),
                        'created_at' => $attachment->created_at,
                        'uploaded_by' => $attachment->user->name ?? 'Unknown',
                    ];
                });

            return response()->json([
                'success' => true,
                'attachments' => $attachments
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading attachments: ' . $e->getMessage()
            ], 500);
        }
    }

    // Attach document to invoice
    public function attachTaxProof(Request $request)
    {
        $request->validate([
            'tax_id' => 'required|exists:taxes,id',
            'tds_proof' => 'required|file|max:5120|mimes:pdf,jpg,jpeg,png',
            'description' => 'nullable|string|max:500',

        ]);

        try {
            $tax = Tax::findOrFail($request->tax_id);


            $file = $request->file('tds_proof');

            $filename = $tax->tax_type . '_proof_' . $tax->id . '_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

            $path = $file->storeAs($tax->tax_type . '_proofs', $filename, 'public');

            $tax->update([
                'tds_proof_path' => $path,
                'payment_status' => 'received',

            ]);
            return response()->json([
                'success' => true,
                'message' => 'TDS proof attached successfully',
                'data' => [
                    'tax_id' => $tax->id,
                    'tds_proof_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'file_size' => $file->getSize(),
                    'payment_status' => $tax->payment_status,
                    'paid_date' => $tax->paid_date,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error attaching TDS proof: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download TDS proof file
     */

    public function downloadTdsProof($id)
    {
        $tax = Tax::findOrFail($id);

        if (!$tax->tds_proof_path) {
            abort(404, 'No TDS proof found');
        }
        if (!Storage::disk('public')->exists($tax->tds_proof_path)) {
            abort(404, 'File missing in storage');
        }

        return Storage::disk('public')->download($tax->tds_proof_path);
    }


    /**
     * Get TDS taxes for an invoice
     */
    public function getInvoiceTdsTaxes($invoiceId)
    {
        try {
            $invoice = Invoice::with(['taxes' => function ($query) {
                $query->where('tax_type', 'tds');
            }])->findOrFail($invoiceId);

            // Check access
            $userCompanyIds = $this->getUserCompanyIds();
            if (!in_array($invoice->company_id, $userCompanyIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'tds_taxes' => $invoice->taxes
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching TDS taxes'
            ], 500);
        }
    }

    /**
     * View TDS proof (open in browser instead of download)
     */
    public function viewTdsProof($id)
    {
        try {
            $tax = Tax::findOrFail($id);

            if ($tax->tax_type !== 'tds') {
                abort(400, 'This is not a TDS tax record');
            }

            if (!$tax->tds_proof_path) {
                abort(404, 'No TDS proof attached');
            }


            $path = storage_path('app/public/' . $tax->tds_proof_path);

            if (!file_exists($path)) {
                abort(404, 'File not found');
            }

            // For PDF files, display in browser
            if (strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'pdf') {
                return response()->file($path, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="' . basename($path) . '"'
                ]);
            }

            // For images, display in browser
            $imageExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            if (in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), $imageExtensions)) {
                return response()->file($path);
            }

            // For other file types, download
            return response()->download($path);
        } catch (\Exception $e) {
            abort(500, 'Error viewing file');
        }
    }
    private function userHasAccessToTax($tax, $userCompanyIds)
    {

        if ($tax->taxable_type === 'App\Models\Invoice') {
            $invoice = \App\Models\Invoice::find($tax->taxable_id);
            return $invoice && in_array($invoice->company_id, $userCompanyIds);
        }

        return false;
    }

    // Optional logging method
    private function logTdsProofUpload($tax, $description = null)
    {
        \App\Models\ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'tds_proof_uploaded',
            'model_type' => Tax::class,
            'model_id' => $tax->id,
            'description' => $description ?: 'TDS proof uploaded',
            'properties' => [
                'tax_type' => $tax->tax_type,
                'tax_amount' => $tax->tax_amount,
                'tds_proof_path' => $tax->tds_proof_path,
            ],
            'created_at' => now(),
        ]);
    }
    // Delete attachment
    public function deleteAttachment($id)
    {
        try {
            $attachment = Attachment::findOrFail($id);

            // Check if user has access
            $userCompanyIds = $this->getUserCompanyIds();
            if (!in_array($attachment->invoice->company_id, $userCompanyIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have access to this attachment'
                ], 403);
            }

            // Delete file from storage
            Storage::disk('public')->delete($attachment->file_path);

            // Delete from database
            $attachment->delete();

            return response()->json([
                'success' => true,
                'message' => 'Attachment deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting attachment: ' . $e->getMessage()
            ], 500);
        }
    }
    public function markTDSPaid($id)
    {
        try {
            $tax = Tax::findOrFail($id);
            // Mark as paid
            $tax->update([
                'payment_status' => 'received',
                'paid_date'      => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'TDS marked as paid successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error marking TDS as paid: ' . $e->getMessage()
            ], 500);
        }
    }
}
