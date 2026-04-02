<?php

namespace App\Controllers\CA;

use App\Controllers\BaseController;
use App\Models\InvoiceModel;
use App\Libraries\ManagesCompanies;

class InvoiceController extends BaseController
{
    protected $companyHelper;

    public function __construct()
    {
        $this->companyHelper = new ManagesCompanies();
    }

    public function index()
    {
        $companyIds = $this->companyHelper->getUserCompanyIds();
        $invoiceModel = new InvoiceModel();
        $invoices = [];
        if (!empty($companyIds)) {
            $invoices = $invoiceModel->whereIn('company_id', $companyIds)->orderBy('created_at', 'desc')->paginate(20);
        }
        return view('CA/invoices', [
            'invoices'  => $invoices,
            'pager'     => $invoiceModel->pager,
            'companies' => $this->companyHelper->getUserCompanies(),
        ]);
    }
}
