<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\InvoiceModel;
use App\Models\CompanyModel;
use App\Models\IncomeModel;
use CodeIgniter\API\ResponseTrait;

class InvoiceController extends BaseController
{
    use ResponseTrait;

    protected $invoiceModel;
    protected $companyModel;

    public function __construct()
    {
        $this->invoiceModel = new InvoiceModel();
        $this->companyModel = new CompanyModel();
    }

    public function index()
    {
        $company = $this->request->getGet('company');
        $type    = $this->request->getGet('type');
        $status  = $this->request->getGet('status');

        $query = $this->invoiceModel->select('invoices.*, companies.name as company_name')
            ->join('companies', 'companies.id = invoices.company_id', 'left');

        if ($company) {
            $query = $query->where('invoices.company_id', $company);
        }
        if ($type && $type !== 'all') {
            $query = $query->where('invoices.type', $type);
        }
        if ($status && $status !== 'all') {
            $query = $query->where('invoices.status', $status);
        }

        $invoices = $query->orderBy('created_at', 'desc')->paginate(10);
        
        // Decode client_details to set client_name for the view
        foreach ($invoices as &$invoice) {
            if ($invoice['client_details']) {
                $details = json_decode($invoice['client_details'], true);
                $invoice['client_name'] = $details['name'] ?? 'N/A';
            } else {
                $invoice['client_name'] = 'N/A';
            }
        }

        $data = [
            'invoices'  => $invoices,
            'pager'     => $this->invoiceModel->pager,
            'companies' => $this->companyModel->findAll(),
            'stats'     => $this->getStats()
        ];

        return view('Admin/invoices', $data);
    }

    public function store()
    {
        $rules = [
            'company_id'   => 'required',
            'client_name'  => 'required',
            'client_email' => 'required|valid_email',
            'issue_date'   => 'required|valid_date',
            'due_date'     => 'required|valid_date',
            'total_amount' => 'required|numeric'
        ];

        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $data = $this->request->getPost();
        
        // Process line items
        $lineItems = json_decode($this->request->getPost('line_items'), true);
        if (!$lineItems) {
            return $this->fail('Invalid line items');
        }

        $data['invoice_number'] = $this->generateInvoiceNumber($data['company_id']);
        $data['status'] = 'pending';
        $data['type'] = 'proforma';

        $this->invoiceModel->insert($data);
        $invoiceId = $this->invoiceModel->getInsertID();

        // Create Income Entry
        $incomeModel = new IncomeModel();
        $incomeModel->insert([
            'company_id' => $data['company_id'],
            'invoice_id' => $invoiceId,
            'party_name' => $data['client_name'],
            'amount'     => $data['total_amount'],
            'status'     => 'pending',
            'income_date' => $data['due_date']
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->fail('Failed to create invoice');
        }

        return $this->respondCreated([
            'success' => true,
            'message' => 'Invoice created successfully',
            'id'      => $invoiceId
        ]);
    }

    private function generateInvoiceNumber($companyId)
    {
        $company = $this->companyModel->find($companyId);
        $prefix = $company ? strtoupper(substr((string)$company['name'], 0, 3)) : 'INV';
        $year = date('y');
        $next = $year + 1;
        
        $lastInvoice = $this->invoiceModel->where('company_id', $companyId)
            ->orderBy('id', 'desc')
            ->first();
            
        $num = $lastInvoice ? (int)substr($lastInvoice['invoice_number'], -3) + 1 : 1;
        
        return sprintf("%s-%s-%s-PRO-%03d", $prefix, $year, $next, $num);
    }

    private function getStats()
    {
        return [
            'pending_proformas' => $this->invoiceModel->where('type', 'proforma')->where('status', 'pending')->countAllResults(),
            'pending_amount'    => $this->invoiceModel->where('type', 'proforma')->where('status', 'pending')->selectSum('total_amount')->get()->getRow()->total_amount ?? 0,
            'paid_this_month'   => $this->invoiceModel->where('type', 'invoice')->where('status', 'paid')->where('MONTH(paid_date)', date('m'))->where('YEAR(paid_date)', date('Y'))->selectSum('total_amount')->get()->getRow()->total_amount ?? 0,
            'total_invoices'    => $this->invoiceModel->countAllResults()
        ];
    }
}
