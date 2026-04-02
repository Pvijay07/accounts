<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\CompanyModel;
use App\Models\UserModel;
use App\Models\InvoiceModel;
use App\Models\IncomeModel;
use App\Models\ExpenseModel;
use App\Models\ActivityLogModel;

class DashboardController extends BaseController
{
    public function index()
    {
        $companyModel = new CompanyModel();
        $userModel    = new UserModel();
        $invoiceModel = new InvoiceModel();
        $incomeModel  = new IncomeModel();
        $expenseModel = new ExpenseModel();
        $logModel     = new ActivityLogModel();

        $stats = [
            'total_companies'    => $companyModel->countAllResults(),
            'active_companies'   => $companyModel->where('status', 'active')->countAllResults(),
            'total_users'        => $userModel->countAllResults(),
            'pending_invoices'   => $invoiceModel->where('status', 'pending')->countAllResults(),
            'total_income'       => $incomeModel->selectSum('amount')->get()->getRow()->amount ?? 0,
            'total_expenses'     => $expenseModel->selectSum('actual_amount')->get()->getRow()->actual_amount ?? 0,
            'pending_expenses'   => $expenseModel->whereIn('status', ['pending', 'upcoming'])->countAllResults(),
            'recent_actions'     => $logModel->countAllResults(),
        ];

        // Financial data for chart (last 6 months)
        $financialData = $this->getFinancialData($incomeModel, $expenseModel);

        $data = [
            'stats'            => $stats,
            'recent_companies' => $companyModel->orderBy('created_at', 'desc')->limit(5)->find(), // Using find() instead of findAll() for limit
            'recent_logs'      => $logModel->orderBy('created_at', 'desc')->limit(5)->find(),
            'financialData'    => $financialData,
            'companies'        => $companyModel->where('status', 'active')->findAll(),
        ];

        return view('Admin/dashboard', $data);
    }

    private function getFinancialData($incomeModel, $expenseModel)
    {
        $labels = [];
        $income = [];
        $expenses = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = date('Y-m', strtotime("-$i months"));
            $labels[] = date('M Y', strtotime("-$i months"));
            
            $inc = $incomeModel->where("DATE_FORMAT(income_date, '%Y-%m')", $month)->selectSum('amount')->get()->getRow()->amount ?? 0;
            $exp = $expenseModel->where("DATE_FORMAT(due_date, '%Y-%m')", $month)->selectSum('actual_amount')->get()->getRow()->actual_amount ?? 0;
            
            $income[] = (float)$inc;
            $expenses[] = (float)$exp;
        }

        return [
            'labels'   => $labels,
            'income'   => $income,
            'expenses' => $expenses
        ];
    }
}
