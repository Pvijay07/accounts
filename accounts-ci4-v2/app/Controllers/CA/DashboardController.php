<?php

namespace App\Controllers\CA;

use App\Controllers\BaseController;
use App\Models\CompanyModel;
use App\Models\IncomeModel;
use App\Models\ExpenseModel;
use App\Libraries\ManagesCompanies;

class DashboardController extends BaseController
{
    protected $companyHelper;

    public function __construct()
    {
        $this->companyHelper = new ManagesCompanies();
    }

    public function index()
    {
        $companyIds = $this->companyHelper->getUserCompanyIds();

        $totalIncome = 0; $totalExpenses = 0; $companyCount = 0;
        if (!empty($companyIds)) {
            $incModel = new IncomeModel();
            $expModel = new ExpenseModel();
            $comModel = new CompanyModel();
            $totalIncome = $incModel->whereIn('company_id', $companyIds)
                ->where('MONTH(created_at)', date('m'))->where('YEAR(created_at)', date('Y'))
                ->selectSum('amount')->get()->getRow()->amount ?? 0;
            $totalExpenses = $expModel->whereIn('company_id', $companyIds)
                ->where('MONTH(created_at)', date('m'))->where('YEAR(created_at)', date('Y'))
                ->where('status', 'paid')->selectSum('actual_amount')->get()->getRow()->actual_amount ?? 0;
            $companyCount = count($companyIds);
        }

        return view('CA/dashboard', [
            'totalIncome'   => $totalIncome ?? 0,
            'totalExpenses' => $totalExpenses ?? 0,
            'companyCount'  => $companyCount,
            'companies'     => $this->companyHelper->getUserCompanies(),
        ]);
    }
}
