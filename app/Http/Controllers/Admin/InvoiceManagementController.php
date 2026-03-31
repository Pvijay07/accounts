<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Company, Income, Invoice, UpcomingPayment, User, Writeoff};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Models\Tax;

class InvoiceManagementController extends Controller
{
  public function index(Request $request)
  {
    $invoices = Invoice::with(['company', 'upcomingPayment', 'tdsTax'])
      ->when($request->company, function ($q) use ($request) {
        $q->where('company_id', $request->company);
      })
      ->when($request->type && $request->type !== 'all', function ($q) use ($request) {
        $q->where('type', $request->type);
      })
      ->when($request->status && $request->status !== 'all', function ($q) use ($request) {
        $q->where('status', $request->status);
      })
      ->orderBy('created_at', 'desc')
      ->paginate(10); // Changed from get()

    // Decode JSON fields for current page only
    $invoices->each(function ($invoice) {
      if ($invoice->client_details && is_string($invoice->client_details)) {
        $invoice->client_details = json_decode($invoice->client_details, true);
      }
      if ($invoice->line_items && is_string($invoice->line_items)) {
        $invoice->line_items = json_decode($invoice->line_items, true);
      }
    });

    $companies = Company::all();

    // Paginated pending proformas for the table
    $pendingProformas = Invoice::with(['company', 'upcomingPayment'])
      ->where('type', 'proforma')
      ->where('status', 'pending')
      ->orderBy('created_at', 'desc')
      ->paginate(10); // Changed from get()

    // Decode JSON fields for current page
    $pendingProformas->each(function ($invoice) {
      if ($invoice->client_details && is_string($invoice->client_details)) {
        $invoice->client_details = json_decode($invoice->client_details, true);
      }
      if ($invoice->line_items && is_string($invoice->line_items)) {
        $invoice->line_items = json_decode($invoice->line_items, true);
      }
    });

    // Calculate stats separately to include ALL pending records, not just current page
    $pendingProformasCount = Invoice::where('type', 'proforma')
      ->where('status', 'pending')
      ->count();

    $pendingAmount = Invoice::where('type', 'proforma')
      ->where('status', 'pending')
      ->sum('total_amount');

    $stats = [
      'pending_proformas' => $pendingProformasCount,
      'pending_amount'    => $pendingAmount,
      'paid_this_month'   => Invoice::where('type', 'invoice')
        ->where('status', 'paid')
        ->whereMonth('paid_date', now()->month)
        ->sum('total_amount'),
      'total_invoices'    => Invoice::count(),
    ];

    return view('Admin.invoices', compact(
      'invoices',
      'companies',
      'pendingProformas',
      'pendingProformasCount',
      'stats'
    ));
  }

  public function store(Request $request)
  {
    $request->validate([
      'company_id'                 => 'required|exists:companies,id',
      'client_name'                => 'required|string|max:255',
      'client_email'               => 'required|email',
      'billing_address'            => 'required|string',
      'issue_date'                 => 'required|date',
      'due_date'                   => 'required|date|after_or_equal:issue_date',
      'currency'                   => 'required|in:INR,USD,EUR,GBP',
      // 'conversion_rate'            => 'required|numeric|min:0.0001',
      'conversion_rate_percentage' => 'nullable|numeric|min:0|max:100', // Add this
      'conversion_cost'            => 'nullable|numeric|min:0', // Add this
      'receivable_amount'          => 'nullable|numeric|min:0', // Add this
      'total_amount'               => 'required|numeric|min:0',
      'converted_amount'           => 'required|numeric|min:0',
      'client_gstin'               => 'nullable|string|max:20',
      'mobile_number'              => 'nullable|string|max:50',
      'frequency'                  => 'nullable|in:monthly,quarterly,yearly',
      'status'                     => 'nullable|in:active,inactive',
      'due_day'                    => 'nullable|integer|min:1|max:31',
      'reminder_days'              => 'nullable|integer|min:0',

      // Tax fields
      'apply_gst'                  => 'nullable|in:0,1',
      'apply_tds'                  => 'nullable|in:0,1',
      'gst_percentage'             => 'nullable|required_if:apply_gst,1|numeric|min:0|max:100',
      'tds_percentage'             => 'nullable|required_if:apply_tds,1|numeric|min:0|max:100',
      'gst_amount'                 => 'nullable|numeric|min:0',
      'tds_amount'                 => 'nullable|numeric|min:0',
      'subtotal'                   => 'required|numeric|min:0'
    ]);

    try {
      DB::beginTransaction();

      // Parse line items from JSON
      $lineItems = json_decode($request->line_items, true);
      if (!$lineItems || !is_array($lineItems)) {
        throw new \Exception('Invalid line items format');
      }

      // Calculate subtotal from line items
      $calculatedSubtotal   = 0;
      $lineItemsWithAmounts = [];

      foreach ($lineItems as $item) {
        $lineAmount          = $item['quantity'] * $item['rate'];
        $calculatedSubtotal += $lineAmount;

        $lineItemsWithAmounts[] = [
          'description' => $item['description'],
          'quantity'    => $item['quantity'],
          'rate'        => $item['rate'],
          'amount'      => $lineAmount
        ];
      }

      // Use calculated subtotal
      $subtotal = $calculatedSubtotal;

      // Calculate grand total (including taxes)
      $gstAmount  = $request->apply_gst ? ($request->gst_amount ?? 0) : 0;
      $tdsAmount  = $request->apply_tds ? ($request->tds_amount ?? 0) : 0;
      $grandTotal = $subtotal + $gstAmount - $tdsAmount; // GST is added, TDS is deducted

      // Calculate conversion deduction if percentage provided
      $conversionCost   = $request->conversion_cost ?? 0;
      $receivableAmount = $request->receivable_amount ?? $grandTotal;

      // If conversion percentage is provided but cost is not, calculate it
      if ($request->conversion_rate_percentage && !$conversionCost) {
        $conversionCost   = ($grandTotal * $request->conversion_rate_percentage) / 100;
        $receivableAmount = $grandTotal - $conversionCost;
      }

      $invoiceNumber = $this->generateInvoiceNumber($request->company_id);

      $clientDetails = [
        'name'            => $request->client_name,
        'email'           => $request->client_email,
        'gstin'           => $request->client_gstin,
        'billing_address' => $request->billing_address,
        'mobile_number'   => $request->mobile_number,
      ];

      // Create invoice
      $invoice = Invoice::create([
        'company_id'               => $request->company_id,
        'type'                     => 'proforma',
        'invoice_number'           => $invoiceNumber,
        'client_details'           => json_encode($clientDetails),
        'line_items'               => json_encode($lineItemsWithAmounts),
        'subtotal'                 => $subtotal,

        'grand_total'              => $grandTotal,
        'total_amount'             => $request->converted_amount ?? $grandTotal,
        'converted_amount'         => $request->converted_amount,
        'original_currency_amount' => $request->total_amount,
        'currency'                 => $request->currency,
        'received_amount'          => $receivableAmount,
        // 'conversion_rate'            => $request->conversion_rate,
        'conversion_rate'          => $request->conversion_rate_percentage ?? 0, // Add this
        'conversion_cost'          => $conversionCost, // Add this
        'issue_date'               => $request->issue_date,
        'due_date'                 => $request->due_date,
        'purpose_comment'          => $request->purpose_comment,
        'terms_conditions'         => $request->terms_conditions,
        'created_by'               => auth()->id(),
        'frequency'                => $request->frequency,
        'due_day'                  => $request->due_day,
        'status'                   => 'pending',
        'reminder_days'            => $request->reminder_days,
        'is_active'                => $request->status ?? 'active'
      ]);

      // Create income record based on invoice
      $income = Income::create([
        'company_id'                 => $request->company_id,
        'invoice_id'                 => $invoice->id,
        'party_name'                 => $request->client_name,
        'amount'                     => $receivableAmount, // Use receivable amount after conversion deduction
        'frequency'                  => $request->frequency ?? null,
        'actual_amount'              => 0, // Initially 0, will be updated when payment received
        'balance_amount'             => $receivableAmount,
        'due_day'                    => $request->due_day ?? null,
        'status'                     => 'pending', // Initial status
        'income_date'                => $request->due_date, // Due date as expected income date
        'mail_status'                => $request->mail_status ?? 0,
        'notes'                      => $request->purpose_comment ?? null,
        'is_partial'                 => 0,
        'created_by'                 => auth()->id(),
        'conversion_cost'            => $conversionCost, // Add conversion cost to income
        'conversion_rate_percentage' => $request->conversion_rate_percentage ?? 0, // Add this
        'income_type'    => 'standard',
        'source'         => 'manual',
        'client_details'           => json_encode($clientDetails),
        'line_items'               => json_encode($lineItemsWithAmounts),
        'invoice_number'           => $invoiceNumber,

      ]);

      // Handle GST tax for income
      if ($request->apply_gst) {
        $income->taxes()->create([
          'taxable_type'   => Income::class,
          'taxable_id'     => $income->id,
          'tax_type'       => 'gst',
          'tax_percentage' => $request->gst_percentage ?? 0,
          'tax_amount'     => $gstAmount,
          'status'         => 'not_received', // GST not received yet
          'direction'      => 'income',
        ]);
      }

      // Handle TDS tax for income
      if ($request->apply_tds) {
        $income->taxes()->create([
          'taxable_type'   => Income::class,
          'taxable_id'     => $income->id,
          'tax_type'       => 'tds',
          'tax_percentage' => $request->tds_percentage ?? 0,
          'tax_amount'     => $tdsAmount,
          'status'         => 'not_received', // TDS not received yet
          'direction'      => 'income', // TDS is an expense/deduction
        ]);
      }

      // Handle GST Tax in invoice tax table
      if ($request->apply_gst) {
        $this->saveInvoiceTax($invoice, 'gst', [
          'taxable_type'   => Invoice::class,
          'tax_percentage' => $request->gst_percentage,
          'tax_amount'     => $gstAmount,
          'amount_paid'    => 0,
          'payment_status' => 'not_received',
          'due_date'       => $request->due_date,
          'direction'      => 'income'
        ]);
      }

      // Handle TDS Tax in invoice tax table
      if ($request->apply_tds) {
        $this->saveInvoiceTax($invoice, 'tds', [
          'tax_percentage' => $request->tds_percentage,
          'taxable_type'   => Invoice::class,
          'tax_amount'     => $tdsAmount,
          'amount_paid'    => 0,
          'payment_status' => 'not_received',
          'due_date'       => $request->due_date,
          'direction'      => 'income'
        ]);
      }

      // Create upcoming payment with receivable amount
      $upcomingPayment = UpcomingPayment::create([
        'invoice_id'                 => $invoice->id,
        'company_id'                 => $request->company_id,
        'type'                       => 'credit',
        'payment_number'             => 'UP-' . str_pad(UpcomingPayment::count() + 1, 3, '0', STR_PAD_LEFT),
        'amount'                     => $receivableAmount, // Use receivable amount
        'currency'                   => 'INR',
        'due_date'                   => $request->due_date,
        'status'                     => 'pending',
        'description'                => $request->purpose_comment,
        'client_details'             => json_encode($clientDetails),
        'conversion_cost'            => $conversionCost, // Add conversion cost
        'conversion_rate_percentage' => $request->conversion_rate_percentage ?? 0, // Add this
      ]);

      DB::commit();

      return response()->json([
        'success' => true,
        'message' => 'Proforma invoice created successfully',
        'invoice' => $invoice,
        'income'  => $income,
        'taxes'   => $invoice->taxes
      ]);
    } catch (\Exception $e) {
      DB::rollBack();
      return response()->json([
        'success' => false,
        'message' => 'Failed to create proforma: ' . $e->getMessage()
      ], 500);
    }
  }

  /**
   * Save or update tax record for invoice
   */
  private function saveInvoiceTax($invoice, $taxType, $taxData)
  {
    // Ensure tax_type is set in the data
    $taxData['tax_type'] = $taxType;

    // Set default values
    $defaultTaxData = [
      'tax_amount'     => 0,
      'amount_paid'    => 0,
      'payment_status' => 'pending',
      'direction'      => 'income',
      'taxable_type'   => get_class($invoice),
      'tax_percentage' => 0,
    ];

    // Merge with provided data (tax_type should not be overwritten)
    $taxData = array_merge($defaultTaxData, $taxData);

    // Debug: Check what's being saved
    \Log::info('Saving tax for invoice', [
      'invoice_id' => $invoice->id,
      'tax_type'   => $taxType,
      'tax_data'   => $taxData
    ]);

    // Find existing tax or create new
    $tax = $invoice->taxes()->where('tax_type', $taxType)->first();

    if ($tax) {
      $tax->update($taxData);
      \Log::info('Updated existing tax', ['tax_id' => $tax->id]);
    } else {
      $tax = $invoice->taxes()->create($taxData);
      \Log::info('Created new tax', ['tax_id' => $tax->id]);
    }

    return $tax;
  }
  public function processPartialPayment(Request $request)
  {
    $request->validate([
      'invoice_id'          => 'required|exists:invoices,id',
      'received_amount'     => 'required|numeric|min:0',
      'action_type'         => 'required|in:keep_balance,settle_invoice',
      'payment_date'        => 'required|date',
      'new_due_date'        => 'required_if:action_type,keep_balance|date|after:payment_date',
      'settle_payment_date' => 'required_if:action_type,settle_invoice|date',
      'writeoff_reason'     => 'required_if:action_type,settle_invoice|string|nullable',
      'tds_status'          => 'required|in:received,not_received',
      'tds_amount'          => 'nullable|numeric|min:0',
      'tds_payment_date'    => 'nullable|date',
      'tds_proof'           => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
    ]);

    try {
      DB::beginTransaction();

      $originalInvoice = Invoice::with('tdsTax')->findOrFail($request->invoice_id);
      $receivedAmount  = $request->received_amount;
      $balanceAmount   = $originalInvoice->total_amount - $receivedAmount;
      $actionType      = $request->action_type;

      if ($receivedAmount <= 0 || $receivedAmount > $originalInvoice->total_amount) {
        throw new \Exception('Received amount must be between 0 and total amount');
      }

      // Handle file uploads
      $paymentProofPath = null;
      if ($request->hasFile('payment_proof')) {
        $paymentProofPath = $request->file('payment_proof')->store('payment-proofs', 'public');
      }

      $tdsProofPath = null;
      if ($request->hasFile('tds_proof')) {
        $tdsProofPath = $request->file('tds_proof')->store('tds-proofs', 'public');
      }

      // Update TDS tax record if exists
      if ($originalInvoice->tdsTax) {
        $originalInvoice->tdsTax->update([
          'payment_status'    => $request->tds_status,
          'amount_paid'       => $request->tds_status === 'received' ? ($originalInvoice->tdsTax->tax_amount ?? 0) : 0,
          'paid_date'         => $request->tds_status === 'received' ? ($request->tds_payment_date ?? $request->payment_date) : null,
          'payment_notes'     => $request->note,
          'payment_reference' => $request->payment_reference,
        ]);

        // Store TDS proof if uploaded
        if ($tdsProofPath) {
          $originalInvoice->tdsTax->update(['tds_proof_path' => $tdsProofPath]);
        }
      }

      // Mark original proforma as replaced
      $originalInvoice->update([
        'status'         => 'replaced',
        'balance_amount' => $balanceAmount,
        'updated_by'     => auth()->id(),
      ]);

      // Mark original upcoming payment as replaced
      $originalUpcomingPayment = UpcomingPayment::where('invoice_id', $originalInvoice->id)->first();
      if ($originalUpcomingPayment) {
        $originalUpcomingPayment->update(['status' => 'replaced']);
      }

      // Create taxable invoice
      if ($actionType === 'keep_balance') {
        // Create tax invoice for received amount only
        $result = $this->createTaxInvoice($originalInvoice, $receivedAmount, $request->payment_date, $request->note);

        // Create new proforma for balance
        if ($balanceAmount > 0) {
          $newProforma        = $this->createBalanceProforma($originalInvoice, $balanceAmount, $request->new_due_date, $request->note);
          $newUpcomingPayment = $this->createUpcomingPaymentForBalance($newProforma, $balanceAmount, $request->new_due_date);
        }
      } else {
        // Settle invoice - create tax invoice for full amount but only receive partial
        $result = $this->createSettledInvoice($originalInvoice, $receivedAmount, $balanceAmount, $request->settle_payment_date, $request->writeoff_reason, $request->note);
      }


      DB::commit();

      $response = [
        'success'         => true,
        'message'         => $actionType === 'keep_balance'
          ? 'Partial payment processed successfully. New proforma created for balance.'
          : 'Invoice settled successfully. Balance written off.',
        'tax_invoice'     => $result['invoice'] ?? null,

        'writeoff'        => $result['writeoff'] ?? null,
        'new_proforma'    => $newProforma ?? null,
        'received_amount' => $receivedAmount,
        'balance_amount'  => $balanceAmount,
        'action_type'     => $actionType,
        'tds_updated'     => $originalInvoice->tdsTax ? true : false,
      ];

      return response()->json($response);
    } catch (\Exception $e) {
      DB::rollBack();
      return response()->json([
        'success' => false,
        'message' => 'Failed to process partial payment: ' . $e->getMessage()
      ], 500);
    }
  }

  private function createSettledInvoice($originalInvoice, $receivedAmount, $balanceAmount, $paymentDate, $writeoffReason, $note = null)
  {
    $taxInvoiceNumber = $this->generateInvoiceNumber($originalInvoice->company_id, 'invoice');

    // Decode client details from original invoice
    $clientDetails = json_decode($originalInvoice->client_details, true);
    $lineItems     = json_decode($originalInvoice->line_items, true);

    $subtotal  = $originalInvoice->total_amount / (1 + ($originalInvoice->tax_percentage / 100));
    $taxAmount = $originalInvoice->total_amount - $subtotal;

    $purposeComment = $originalInvoice->purpose_comment . ' - Invoice settled with partial payment';
    if ($note) {
      $purposeComment .= ' (' . $note . ')';
    }

    $taxInvoice = Invoice::create([
      'company_id'           => $originalInvoice->company_id,
      'type'                 => 'invoice',
      'invoice_number'       => $taxInvoiceNumber,
      'status'               => 'paid',
      'client_details'       => json_encode($clientDetails),
      'line_items'           => json_encode($lineItems),
      'subtotal'             => $subtotal,
      'tax_amount'           => $taxAmount,
      'total_amount'         => $originalInvoice->total_amount,
      'is_taxable'           => $originalInvoice->is_taxable,
      'tax_percentage'       => $originalInvoice->tax_percentage,
      'issue_date'           => $paymentDate,
      'due_date'             => $paymentDate,
      'paid_date'            => $paymentDate,
      'purpose_comment'      => $purposeComment,
      'terms_conditions'     => $originalInvoice->terms_conditions,
      'original_proforma_id' => $originalInvoice->id,
      'received_amount'      => $receivedAmount,
      'balance_amount'       => $balanceAmount,
      'is_partial_payment'   => true,
      'is_settled'           => true,
      'writeoff_reason'      => $writeoffReason,
      'created_by'           => auth()->id(),
    ]);

    // Create INCOME entry for received amount
    $income = Income::create([
      'company_id'        => $originalInvoice->company_id,
      'amount'            => $receivedAmount,
      'income_date'       => $paymentDate,
      'income_type'       => $originalInvoice->income_type ?? null,
      'status'            => 'received',
      'invoice_reference' => $taxInvoice->invoice_number,
      'import_method'     => 'manual',
      'description'       => 'Partial payment received - invoice settled',
      'month_year'        => Carbon::parse($paymentDate)->format('Y-m'),
      // 'tax_type'          => $originalInvoice->tax_type ?? null,
      'invoice_id'        => $originalInvoice->id,

    ]);

    // Create write-off record for balance amount
    $writeoff = Writeoff::create([
      'invoice_id'    => $taxInvoice->id,
      'company_id'    => $originalInvoice->company_id,
      'amount'        => $balanceAmount,
      'reason'        => $writeoffReason,
      'writeoff_date' => $paymentDate,
      'description'   => 'Balance written off after partial payment settlement',
      'created_by'    => auth()->id(),
    ]);

    return [
      'invoice'  => $taxInvoice,
      'income'   => $income,
      'writeoff' => $writeoff
    ];
  }


  private function createTaxInvoice($originalInvoice, $receivedAmount, $paymentDate, $note = null)
  {
    $taxInvoiceNumber = $this->generateInvoiceNumber($originalInvoice->company_id, 'invoice');

    // Decode client details from original invoice
    $clientDetails = json_decode($originalInvoice->client_details, true);
    $lineItems     = json_decode($originalInvoice->line_items, true);

    // Calculate proportional line items
    $ratio        = $receivedAmount / $originalInvoice->total_amount;
    $newLineItems = [];

    foreach ($lineItems as $item) {
      $lineAmount         = $item['amount'];
      $proportionalAmount = $lineAmount * $ratio;

      $newLineItems[] = [
        'description' => $item['description'],
        'quantity'    => $item['quantity'],
        'rate'        => $item['rate'],
        'amount'      => $proportionalAmount,
      ];
    }

    $subtotal  = $receivedAmount / (1 + ($originalInvoice->tax_percentage / 100));
    $taxAmount = $receivedAmount - $subtotal;

    $purposeComment = $originalInvoice->purpose_comment . ' - Partial payment received';
    if ($note) {
      $purposeComment .= ' (' . $note . ')';
    }

    // 4️⃣ Create TAX INVOICE
    $taxInvoice = Invoice::create([
      'company_id'           => $originalInvoice->company_id,
      'type'                 => 'invoice',
      'invoice_number'       => $taxInvoiceNumber,
      'status'               => 'paid',
      'client_details'       => json_encode($clientDetails),
      'line_items'           => json_encode($newLineItems),
      'subtotal'             => $subtotal,
      'tax_amount'           => $taxAmount,
      'total_amount'         => $receivedAmount,
      'is_taxable'           => $originalInvoice->is_taxable,
      'tax_percentage'       => $originalInvoice->tax_percentage,
      'issue_date'           => $paymentDate,
      'due_date'             => $paymentDate,
      'paid_date'            => $paymentDate,
      'purpose_comment'      => $purposeComment,
      'terms_conditions'     => $originalInvoice->terms_conditions,
      'original_proforma_id' => $originalInvoice->id,
      'received_amount'      => $receivedAmount,
      'balance_amount'       => 0,
      'is_partial_payment'   => true,
      'created_by'           => auth()->id(),
    ]);

    // 5️⃣ Create INCOME entry
    $income = Income::create([
      'company_id'        => $originalInvoice->company_id,
      'amount'            => $receivedAmount,
      'income_date'       => $paymentDate,
      'income_type'       => $originalInvoice->income_type ?? null,
      'status'            => 'received',
      'invoice_reference' => $taxInvoice->invoice_number,
      'import_method'     => 'manual',
      'description'       => 'Invoice payment received',
      'month_year'        => Carbon::parse($paymentDate)->format('Y-m'),
      'invoice_id'        => $originalInvoice->id,
    ]);

    return [
      'invoice' => $taxInvoice,
      'income'  => $income
    ];
  }

  private function createBalanceProforma($originalInvoice, $balanceAmount, $newDueDate, $note = null)
  {
    $proformaNumber = $this->generateInvoiceNumber($originalInvoice->company_id, 'proforma');

    // Decode client details and line items from original invoice
    $clientDetails = json_decode($originalInvoice->client_details, true);
    $lineItems     = json_decode($originalInvoice->line_items, true);

    // Calculate proportional line items for balance
    $ratio        = $balanceAmount / $originalInvoice->total_amount;
    $newLineItems = [];

    foreach ($lineItems as $item) {
      $lineAmount         = $item['amount'];
      $proportionalAmount = $lineAmount * $ratio;

      $newLineItems[] = [
        'description' => $item['description'],
        'quantity'    => $item['quantity'],
        'rate'        => $item['rate'],
        'amount'      => $proportionalAmount,
      ];
    }

    $subtotal  = $balanceAmount / (1 + ($originalInvoice->tax_percentage / 100));
    $taxAmount = $balanceAmount - $subtotal;

    $purposeComment = $originalInvoice->purpose_comment . ' - Balance amount';
    if ($note) {
      $purposeComment .= ' (' . $note . ')';
    }

    return Invoice::create([
      'company_id'           => $originalInvoice->company_id,
      'type'                 => 'proforma',
      'invoice_number'       => $proformaNumber,
      'status'               => 'pending',
      'client_details'       => json_encode($clientDetails), // Convert to JSON
      'line_items'           => json_encode($newLineItems), // Convert to JSON
      'subtotal'             => $subtotal,
      'tax_amount'           => $taxAmount,
      'total_amount'         => $balanceAmount,
      'is_taxable'           => $originalInvoice->is_taxable,
      'tax_percentage'       => $originalInvoice->tax_percentage,
      'issue_date'           => now()->format('Y-m-d'),
      'due_date'             => $newDueDate,
      'purpose_comment'      => $purposeComment,
      'terms_conditions'     => $originalInvoice->terms_conditions,
      'original_proforma_id' => $originalInvoice->id,
      'received_amount'      => 0,
      'balance_amount'       => $balanceAmount,
      'is_partial_payment'   => true,
      'created_by'           => auth()->id(),
    ]);
  }

  private function createUpcomingPaymentForBalance($proforma, $balanceAmount, $dueDate)
  {
    return UpcomingPayment::create([
      'invoice_id'     => $proforma->id,
      'company_id'     => $proforma->company_id,
      'type'           => 'credit',
      'payment_number' => 'UP-' . str_pad(UpcomingPayment::count() + 1, 3, '0', STR_PAD_LEFT),
      'amount'         => $balanceAmount,
      'due_date'       => $dueDate,
      'status'         => 'pending',
      'description'    => $proforma->purpose_comment,
      'client_details' => $proforma->client_details,
    ]);
  }

  private function generateInvoiceNumber($companyId, $type = 'proforma')
  {
    $company       = Company::find($companyId);
    $companyPrefix = strtoupper(substr($company->name, 0, 3));
    $year          = date('y');
    $nextYear      = $year + 1;

    $prefix = $type === 'invoice' ? 'INV' : 'PRO';

    $lastInvoice = Invoice::where('company_id', $companyId)
      ->where('invoice_number', 'like', "{$companyPrefix}-{$year}-{$nextYear}-{$prefix}-%")
      ->orderBy('id', 'desc')
      ->first();

    $nextNumber = $lastInvoice ?
      (int) substr($lastInvoice->invoice_number, -3) + 1 : 1;

    return sprintf(
      "%s-%s-%s-%s-%03d",
      $companyPrefix,
      $year,
      $nextYear,
      $prefix,
      $nextNumber
    );
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
    $taxes = $invoice->taxes->where('tax_type', 'gst')->where('taxable_id', $invoice->id);
    $invoice->gstTotal =  $taxes->sum('tax_amount');
    return response()->json([
      'success' => true,
      'invoice' => $invoice
    ]);
  }
  public function show($id)
  {
    $invoice = Invoice::with(['company', 'creator', 'taxes'])->findOrFail($id);

    // Decode JSON fields
    if ($invoice->client_details) {
      $invoice->client_details = json_decode($invoice->client_details, true);
    }

    if ($invoice->line_items) {
      $invoice->line_items = json_decode($invoice->line_items, true);
    }

    return response()->json([
      'success' => true,
      'invoice' => $invoice,
      'taxes'   => $invoice->taxes
    ]);
  }
  public function view($id)
  {
    $invoice = Invoice::with(['company', 'creator', 'upcomingPayment'])->findOrFail($id);

    // Get company from the invoice relationship
    $company = $invoice->company;

    // Get client details (decoded from JSON)
    $clientDetails = $invoice->client_details;
    if (is_string($clientDetails)) {
      $clientDetails = json_decode($clientDetails, true);
    }

    // Get line items (decoded from JSON)
    $lineItems = $invoice->line_items;
    if (is_string($lineItems)) {
      $lineItems = json_decode($lineItems, true);
    }

    // Get original proforma if this is a tax invoice from partial payment
    $originalProforma = null;
    if ($invoice->original_proforma_id) {
      $originalProforma = Invoice::find($invoice->original_proforma_id);
    }

    // Get creator
    $taxes = $invoice->taxes->where('taxable_type', Invoice::class)->where('taxable_id', $invoice->id);
    $invoice->gstTotal = $taxes->gst_amount > 0 ? $taxes->gst_amount : ($taxes->tds_amount ?? 0);

    return view('Admin.pdf.invoice-template', compact(
      'invoice',
      'company',
      'clientDetails',
      'lineItems',
      'originalProforma',
      'taxes'
    ));
  }

  // For PDF download
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
    // return view('Admin.pdf.invoice-template', compact(
    //   'invoice',
    //   'clientDetails',
    //   'company',
    //   'lineItems',
    //   'amountInWords',
    //   'logoBase64'

    // ));
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
  public function getClientDetailsAttribute($value)
  {
    if (is_string($value)) {
      return json_decode($value, true);
    }
    return $value;
  }

  // Accessor for line_items
  public function getLineItemsAttribute($value)
  {
    if (is_string($value)) {
      return json_decode($value, true);
    }
    return $value;
  }

  // Accessor for amount in words
  public function getAmountInWordsAttribute()
  {
    return $this->numberToWords($this->total_amount);
  }

  // Helper function to convert number to words
  private function numberToWords($number)
  {
    // You can use a library like "kwn/number-to-words"
    // For simplicity, here's a basic implementation
    $words = [
      0        => 'Zero',
      1        => 'One',
      2        => 'Two',
      3        => 'Three',
      4        => 'Four',
      5        => 'Five',
      6        => 'Six',
      7        => 'Seven',
      8        => 'Eight',
      9        => 'Nine',
      10       => 'Ten',
      11       => 'Eleven',
      12       => 'Twelve',
      13       => 'Thirteen',
      14       => 'Fourteen',
      15       => 'Fifteen',
      16       => 'Sixteen',
      17       => 'Seventeen',
      18       => 'Eighteen',
      19       => 'Nineteen',
      20       => 'Twenty',
      30       => 'Thirty',
      40       => 'Forty',
      50       => 'Fifty',
      60       => 'Sixty',
      70       => 'Seventy',
      80       => 'Eighty',
      90       => 'Ninety',
      100      => 'Hundred',
      1000     => 'Thousand',
      100000   => 'Lakh',
      10000000 => 'Crore'
    ];

    // Convert to integer part only
    $integerPart = floor($number);

    // For production, use a proper library like:
    // composer require kwn/number-to-words
    // or implement a more complete solution

    // For now, return a simple representation
    $formatted     = number_format($number, 2);
    $withoutCommas = str_replace(',', '', $formatted);
    $dollars       = floor($number);
    $cents         = round(($number - $dollars) * 100);

    return ucwords($this->convertNumberToWords($dollars)) . ' Dollars' . ($cents > 0 ? ' and ' . $cents . ' Cents' : '');
  }

  // Simple number to words conversion (basic)
  private function convertNumberToWords($number)
  {
    if ($number < 21) {
      $words = [
        'Zero',
        'One',
        'Two',
        'Three',
        'Four',
        'Five',
        'Six',
        'Seven',
        'Eight',
        'Nine',
        'Ten',
        'Eleven',
        'Twelve',
        'Thirteen',
        'Fourteen',
        'Fifteen',
        'Sixteen',
        'Seventeen',
        'Eighteen',
        'Nineteen',
        'Twenty'
      ];
      return $words[$number] ?? '';
    }

    // For numbers above 20, return simple representation
    return "{$number}";
  }

  public function sendEmail(Request $request)
  {
    try {
      $request->validate([
        'invoice_id' => 'required|exists:invoices,id',
        'to_email'   => 'required|email',
        'subject'    => 'required|string|max:255',
        'message'    => 'required|string',
      ]);

      $invoice = Invoice::with(['company'])->find($request->invoice_id);

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
      $message = str_replace('{invoice_no}', $invoice->invoice_number, $message);
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
      Log::error('Error sending invoice email: ' . $e->getMessage());
      Log::error('Stack trace: ' . $e->getTraceAsString());

      return response()->json([
        'success' => false,
        'message' => 'Failed to send invoice: ' . $e->getMessage()
      ], 500);
    }
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

  public function update(Request $request, $id)
  {
    try {
      DB::beginTransaction();

      $invoice = Invoice::findOrFail($id);

      // Validate request
      $validated = $request->validate([
        'company_id'       => 'required|exists:companies,id',
        'client_name'      => 'required|string|max:255',
        'client_email'     => 'required|email',
        'billing_address'  => 'required|string',
        'issue_date'       => 'required|date',
        'due_date'         => 'required|date|after_or_equal:issue_date',
        'currency'         => 'required|in:INR,USD,EUR,GBP',
        'conversion_rate'  => 'required|numeric|min:0.0001',
        'total_amount'     => 'required|numeric|min:0',
        'converted_amount' => 'required|numeric|min:0',
        'client_gstin'     => 'nullable|string|max:20',
        'mobile_number'    => 'nullable|string|max:50',
        'frequency'        => 'nullable|in:monthly,quarterly,yearly',
        'status'           => 'nullable|in:active,inactive',
        'due_day'          => 'nullable|integer|min:1|max:31',
        'reminder_days'    => 'nullable|integer|min:0',
        'apply_gst'        => 'nullable|in:0,1',
        'apply_tds'        => 'nullable|in:0,1',
        'gst_percentage'   => 'nullable|required_if:apply_gst,1|numeric|min:0|max:100',
        'tds_percentage'   => 'nullable|required_if:apply_tds,1|numeric|min:0|max:100',
        'gst_amount'       => 'nullable|numeric|min:0',
        'tds_amount'       => 'nullable|numeric|min:0',
        'subtotal'         => 'required|numeric|min:0',
        'purpose_comment'  => 'nullable|string',
        'terms_conditions' => 'nullable|string'
      ]);

      // Parse line items
      $lineItems = json_decode($request->line_items, true);
      if (!$lineItems || !is_array($lineItems)) {
        throw new \Exception('Invalid line items format');
      }

      // Update client details
      $clientDetails = [
        'name'            => $request->client_name,
        'email'           => $request->client_email,
        'gstin'           => $request->client_gstin,
        'billing_address' => $request->billing_address,
        'mobile_number'   => $request->mobile_number,
      ];

      // Update invoice
      $invoice->update([
        'company_id'               => $request->company_id,
        'client_details'           => json_encode($clientDetails),
        'line_items'               => json_encode($lineItems),
        'subtotal'                 => $request->subtotal,
        'total_amount'             => $request->total_amount,
        'converted_amount'         => $request->converted_amount,
        'original_currency_amount' => $request->total_amount,
        // 'received_amount'          => $receivableAmount,

        'currency'                 => $request->currency,
        'conversion_rate'          => $request->conversion_rate,
        'issue_date'               => $request->issue_date,
        'due_date'                 => $request->due_date,
        'purpose_comment'          => $request->purpose_comment,
        'terms_conditions'         => $request->terms_conditions,
        'frequency'                => $request->frequency,
        'due_day'                  => $request->due_day,
        'reminder_days'            => $request->reminder_days,
        'is_active'                => $request->status === 'active',
        'status'                   => $request->invoice_status ?? $invoice->status,
      ]);

      // Update or create GST tax
      if ($request->apply_gst) {
        $gstTax = Tax::where('taxable_type', Invoice::class)
          ->where('taxable_id', $invoice->id)
          ->where('tax_type', 'gst')
          ->first();

        if ($gstTax) {
          $gstTax->update([
            'tax_percentage' => $request->gst_percentage,
            'tax_amount'     => $request->gst_amount,
            'due_date'       => $request->due_date,
          ]);
        } else {
          Tax::create([
            'taxable_type'   => Invoice::class,
            'taxable_id'     => $invoice->id,
            'tax_type'       => 'gst',
            'tax_percentage' => $request->gst_percentage,
            'tax_amount'     => $request->gst_amount,
            'amount_paid'    => 0,
            'payment_status' => 'not_received',
            'due_date'       => $request->due_date,
            'direction'      => 'income'
          ]);
        }
      } else {
        // Remove GST tax if unchecked
        Tax::where('taxable_type', Invoice::class)
          ->where('taxable_id', $invoice->id)
          ->where('tax_type', 'gst')
          ->delete();
      }

      // Update or create TDS tax
      if ($request->apply_tds) {
        $tdsTax = Tax::where('taxable_type', Invoice::class)
          ->where('taxable_id', $invoice->id)
          ->where('tax_type', 'tds')
          ->first();

        if ($tdsTax) {
          $tdsTax->update([
            'tax_percentage' => $request->tds_percentage,
            'tax_amount'     => $request->tds_amount,
            'due_date'       => $request->due_date,
            'payment_status' => $request->tds_status ?? 'not_received',
          ]);
        } else {
          Tax::create([
            'taxable_type'   => Invoice::class,
            'taxable_id'     => $invoice->id,
            'tax_type'       => 'tds',
            'tax_percentage' => $request->tds_percentage,
            'tax_amount'     => $request->tds_amount,
            'amount_paid'    => 0,
            'payment_status' => $request->tds_status ?? 'not_received',
            'due_date'       => $request->due_date,
            'direction'      => 'income'
          ]);
        }
      } else {
        // Remove TDS tax if unchecked
        Tax::where('taxable_type', Invoice::class)
          ->where('taxable_id', $invoice->id)
          ->where('tax_type', 'tds')
          ->delete();
      }

      DB::commit();

      return response()->json([
        'success' => true,
        'message' => 'Invoice updated successfully!',
        'invoice' => $invoice
      ]);
    } catch (\Exception $e) {
      DB::rollBack();
      return response()->json([
        'success' => false,
        'message' => 'Failed to update invoice: ' . $e->getMessage()
      ], 500);
    }
  }

  // Helper function for file size formatting (add this to your controller)
  private function formatBytes($bytes, $precision = 2)
  {
    $units  = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes  = max($bytes, 0);
    $pow    = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow    = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);

    return round($bytes, $precision) . ' ' . $units[$pow];
  }
  public function edit($id)
  {
    try {
      $invoice = Invoice::with(['taxes', 'company'])->findOrFail($id);

      // Parse client details and line items
      $clientDetails = json_decode($invoice->client_details, true) ?? [];
      $lineItems     = json_decode($invoice->line_items, true) ?? [];

      return response()->json([
        'success'        => true,
        'invoice'        => $invoice,
        'client_details' => $clientDetails,
        'line_items'     => $lineItems
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Invoice not found'
      ], 404);
    }
  }
}
