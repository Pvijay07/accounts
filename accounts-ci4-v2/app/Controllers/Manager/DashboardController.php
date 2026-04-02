<?php

namespace App\Controllers\Manager;

use App\Controllers\BaseController;
use App\Models\CompanyModel;
use App\Models\ExpenseModel;
use App\Models\IncomeModel;
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
        $request   = service('request');
        $dateRange = $request->getGet('range') ?? 'month';
        $companyId = $request->getGet('company') ?? 'all';

        $companyIds = $this->companyHelper->getUserCompanyIds($companyId);
        $dateFilters = $this->companyHelper->getDateRange($dateRange);
        $startDate = $dateFilters['start'];
        $endDate   = $dateFilters['end'];

        $db = \Config\Database::connect();

        // Current period stats
        $totalIncome = 0;
        $totalExpenses = 0;

        if (!empty($companyIds)) {
            $incomeModel  = new IncomeModel();
            $expenseModel = new ExpenseModel();

            $totalIncome = $incomeModel
                ->whereIn('company_id', $companyIds)
                ->where('income_date >=', $startDate)
                ->where('income_date <=', $endDate)
                ->selectSum('amount')
                ->get()->getRow()->amount ?? 0;

            $totalExpenses = $expenseModel
                ->whereIn('company_id', $companyIds)
                ->where('created_at >=', $startDate)
                ->where('created_at <=', $endDate)
                ->where('status', 'paid')
                ->selectSum('actual_amount')
                ->get()->getRow()->actual_amount ?? 0;
        }

        $currentStats = [
            'totalIncome'      => $totalIncome ?? 0,
            'totalExpenses'    => $totalExpenses ?? 0,
            'netProfit'        => ($totalIncome ?? 0) - ($totalExpenses ?? 0),
            'upcomingPayments' => 0,
            'periodLabel'      => $this->getPeriodLabel($dateRange),
        ];

        // Previous period stats for comparison
        $prevStart = date('Y-m-d H:i:s', strtotime($startDate . ' -1 month'));
        $prevEnd   = date('Y-m-d H:i:s', strtotime($endDate . ' -1 month'));
        $prevIncome = 0; $prevExpenses = 0;
        if (!empty($companyIds)) {
            $incomeModel2  = new IncomeModel();
            $expenseModel2 = new ExpenseModel();
            $prevIncome = $incomeModel2->whereIn('company_id', $companyIds)
                ->where('income_date >=', $prevStart)->where('income_date <=', $prevEnd)
                ->selectSum('amount')->get()->getRow()->amount ?? 0;
            $prevExpenses = $expenseModel2->whereIn('company_id', $companyIds)
                ->where('created_at >=', $prevStart)->where('created_at <=', $prevEnd)
                ->where('status', 'paid')
                ->selectSum('actual_amount')->get()->getRow()->actual_amount ?? 0;
        }
        $previousStats = [
            'totalIncome'   => $prevIncome ?? 0,
            'totalExpenses' => $prevExpenses ?? 0,
            'netProfit'     => ($prevIncome ?? 0) - ($prevExpenses ?? 0),
        ];

        // Company P&L
        $companyProfitLoss = [];
        if (!empty($companyIds)) {
            $companyModel = new CompanyModel();
            $companies = $companyModel->whereIn('id', $companyIds)->findAll();
            foreach ($companies as $company) {
                $incM = new IncomeModel();
                $expM = new ExpenseModel();
                $cIncome = $incM->where('company_id', $company['id'])
                    ->where('income_date >=', $startDate)->where('income_date <=', $endDate)
                    ->selectSum('amount')->get()->getRow()->amount ?? 0;
                $cExpense = $expM->where('company_id', $company['id'])
                    ->where('created_at >=', $startDate)->where('created_at <=', $endDate)
                    ->where('status', 'paid')
                    ->selectSum('actual_amount')->get()->getRow()->actual_amount ?? 0;
                $companyProfitLoss[] = [
                    'name'     => $company['name'],
                    'income'   => $cIncome ?? 0,
                    'expenses' => $cExpense ?? 0,
                    'profit'   => ($cIncome ?? 0) - ($cExpense ?? 0),
                ];
            }
        }

        // Notifications
        $notifications = [];
        if (!empty($companyIds)) {
            $expM3 = new ExpenseModel();
            $pendingCount = $expM3->whereIn('company_id', $companyIds)->where('status', 'pending')->countAllResults();
            if ($pendingCount > 0) {
                $notifications[] = ['type' => 'info', 'icon' => 'file-invoice-dollar', 'message' => "You have $pendingCount pending expenses"];
            }
        }
        if (empty($notifications)) {
            $notifications[] = ['type' => 'success', 'icon' => 'check-circle', 'message' => 'All payments are up to date'];
        }

        $data = [
            'currentStats'      => $currentStats,
            'previousStats'     => $previousStats,
            'immediatePayments' => [],
            'upcomingStats'     => ['debits' => ['amount' => 0, 'count' => 0], 'credits' => ['amount' => 0, 'count' => 0]],
            'companyProfitLoss' => $companyProfitLoss,
            'notifications'    => $notifications,
            'companies'        => $this->companyHelper->getUserCompanies(),
            'dateRange'        => $dateRange,
            'companyId'        => $companyId,
        ];

        return view('Manager/dashboard', $data);
    }

    private function getPeriodLabel($range)
    {
        $labels = [
            'today' => 'Today', 'week' => 'This Week', 'month' => 'This Month',
            'quarter' => 'This Quarter', 'year' => 'This Year'
        ];
        return $labels[$range] ?? 'This Month';
    }
}
