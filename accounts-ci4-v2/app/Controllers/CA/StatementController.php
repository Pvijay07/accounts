<?php

namespace App\Controllers\CA;

use App\Controllers\BaseController;
use App\Models\IncomeModel;
use App\Models\ExpenseModel;
use App\Libraries\ManagesCompanies;

class StatementController extends BaseController
{
    protected $companyHelper;

    public function __construct()
    {
        $this->companyHelper = new ManagesCompanies();
    }

    public function index()
    {
        $companyIds = $this->companyHelper->getUserCompanyIds();
        $companies = $this->companyHelper->getUserCompanies();
        return view('CA/statements', [
            'companies' => $companies,
        ]);
    }
}
