<?php

namespace App\Controllers\Manager;

use App\Controllers\BaseController;
use App\Models\TaxModel;
use App\Models\IncomeModel;
use App\Models\ExpenseModel;
use App\Models\CompanyModel;
use App\Models\GstSettlementModel;
use App\Models\GstTaskModel;
use App\Libraries\ManagesCompanies;
use CodeIgniter\API\ResponseTrait;

class GstController extends BaseController
{
    use ResponseTrait;

    protected $companyHelper;
    protected $taxModel;
    protected $incomeModel;
    protected $expenseModel;
    protected $gstSettlementModel;
    protected $gstTaskModel;

    public function __construct()
    {
        $this->companyHelper = new ManagesCompanies();
        $this->taxModel = new TaxModel();
        $this->incomeModel = new IncomeModel();
        $this->expenseModel = new ExpenseModel();
        $this->gstSettlementModel = new GstSettlementModel();
        $this->gstTaskModel = new GstTaskModel();
    }

    public function index()
    {
        $request = service('request');
        $companyId = $request->getGet('company_id') ?? 'all';
        $period = $request->getGet('period') ?? date('Y-m');

        if (!preg_match('/^\d{4}-\d{2}$/', $period)) {
            $period = date('Y-m');
        }

        $selectedMonth = date('m', strtotime($period));
        $selectedYear = date('Y', strtotime($period));
        $companyIds = $this->companyHelper->getUserCompanyIds($companyId);

        if (empty($companyIds)) {
            return view('Manager/gst/index', array_merge($this->companyHelper->getCommonViewData(), [
                'totalOutputGST' => 0, 'totalInputGST' => 0, 'totalTDS' => 0,
                'netGSTPayable' => 0, 'netGSTReceivable' => 0, 'netPosition' => 0,
                'selectedPeriod' => $period, 'companyId' => $companyId
            ]));
        }

        // Output GST (Income)
        $outputTaxes = $this->taxModel->select('taxes.*, incomes.company_id')
            ->join('incomes', 'incomes.id = taxes.taxable_id AND taxes.taxable_type = "App\\\Models\\\Income"', 'left')
            ->where('taxes.tax_type', 'gst')
            ->where('taxes.direction', 'income')
            ->where('MONTH(taxes.created_at)', $selectedMonth)
            ->where('YEAR(taxes.created_at)', $selectedYear)
            ->whereIn('incomes.company_id', $companyIds)
            ->findAll();

        // Input GST (Expense)
        $inputTaxes = $this->taxModel->select('taxes.*, expenses.company_id')
            ->join('expenses', 'expenses.id = taxes.taxable_id AND taxes.taxable_type = "App\\\Models\\\Expense"', 'left')
            ->where('taxes.tax_type', 'gst')
            ->where('taxes.direction', 'expense')
            ->where('MONTH(taxes.created_at)', $selectedMonth)
            ->where('YEAR(taxes.created_at)', $selectedYear)
            ->whereIn('expenses.company_id', $companyIds)
            ->findAll();

        // TDS
        $tdsTaxes = $this->taxModel->select('taxes.*')
            ->where('tax_type', 'tds')
            ->where('MONTH(created_at)', $selectedMonth)
            ->where('YEAR(created_at)', $selectedYear)
            // Join is a bit more complex for TDS as it can be on both, but usually expense in our system
            ->findAll();

        $totalOutputGST = array_sum(array_column($outputTaxes, 'tax_amount'));
        $totalInputGST = array_sum(array_column($inputTaxes, 'tax_amount'));
        $totalTDS = array_sum(array_column($tdsTaxes, 'tax_amount'));

        $netGSTPayable = $totalOutputGST - $totalInputGST;
        $netPosition = $netGSTPayable - $totalTDS;

        $data = $this->companyHelper->getCommonViewData();
        $data = array_merge($data, [
            'totalOutputGST'   => $totalOutputGST,
            'totalInputGST'    => $totalInputGST,
            'totalTDS'         => $totalTDS,
            'netGSTPayable'    => max(0, $netGSTPayable),
            'netGSTReceivable' => abs(min(0, $netGSTPayable)),
            'netPosition'      => $netPosition,
            'selectedPeriod'   => $period,
            'companyId'        => $companyId,
            'isGSTPayable'     => $netGSTPayable > 0,
            'isOverallPayable' => $netPosition > 0,
            'outputTaxes'      => $outputTaxes,
            'inputTaxes'       => $inputTaxes,
            'tdsTaxes'         => $tdsTaxes
        ]);

        return view('Manager/gst/index', $data);
    }

    public function gstCollected()
    {
        $request = service('request');
        $companyId = $request->getGet('company_id') ?? 'all';
        $period = $request->getGet('period') ?? date('Y-m');
        $companyIds = $this->companyHelper->getUserCompanyIds($companyId);
        
        $selectedMonth = date('m', strtotime($period));
        $selectedYear = date('Y', strtotime($period));

        $query = $this->incomeModel->select('incomes.*, companies.name as company_name')
            ->join('companies', 'companies.id = incomes.company_id')
            ->whereIn('incomes.company_id', $companyIds)
            ->where('MONTH(incomes.income_date)', $selectedMonth)
            ->where('YEAR(incomes.income_date)', $selectedYear);

        $results = $query->findAll() ?? [];
        
        // Manual join for taxes
        foreach($results as &$row) {
            $row['taxes'] = $this->taxModel->where('taxable_id', $row['id'])->where('taxable_type', 'App\Models\Income')->findAll() ?? [];
        }

        $totalGSTCollected = 0;
        $totalTDSCollected = 0;
        $totalTaxableAmount = 0;

        foreach ($results as $row) {
            $totalTaxableAmount += $row['amount'];
            foreach ($row['taxes'] as $tax) {
                if ($tax['tax_type'] === 'gst') $totalGSTCollected += $tax['tax_amount'];
                if ($tax['tax_type'] === 'tds') $totalTDSCollected += $tax['tax_amount'];
            }
        }

        return view('Manager/gst/collected', array_merge($this->companyHelper->getCommonViewData(), [
            'incomes' => $results,
            'companies' => $this->companyHelper->getUserCompanies(),
            'selectedPeriod' => $period,
            'companyId' => $companyId,
            'totalGSTCollected' => $totalGSTCollected,
            'totalTDSCollected' => $totalTDSCollected,
            'totalTaxableAmount' => $totalTaxableAmount,
        ]));
    }

    public function settlement()
    {
        $request = service('request');
        $companyId = $request->getGet('company_id') ?? 'all';
        $period = $request->getGet('period') ?? date('Y-m');
        $companyIds = $this->companyHelper->getUserCompanyIds($companyId);

        $settlements = $this->gstSettlementModel->select('gst_settlements.*, companies.name as company_name')
            ->join('companies', 'companies.id = gst_settlements.company_id')
            ->whereIn('company_id', $companyIds)
            ->orderBy('payment_date', 'DESC')
            ->findAll();

        return view('Manager/gst/settlements', [
            'settlements' => $settlements,
            'companies' => $this->companyHelper->getUserCompanies(),
            'selectedPeriod' => $period,
            'companyId' => $companyId
        ]);
    }

    public function storeSettlement()
    {
        $data = $this->request->getPost();
        $data['created_by'] = session()->get('user_id');
        $data['status'] = 'completed';

        if ($this->gstSettlementModel->insert($data)) {
            return redirect()->back()->with('success', 'GST Settlement recorded successfully!');
        }
        return redirect()->back()->with('error', 'Failed to record settlement.');
    }

    public function returns()
    {
        $request = service('request');
        $companyId = $request->getGet('company_id') ?? 'all';
        $period = $request->getGet('period') ?? date('Y-m');
        $companyIds = $this->companyHelper->getUserCompanyIds($companyId);

        $tasks = $this->gstTaskModel->select('gst_tasks.*, companies.name as company_name, users.name as assigned_to_name')
            ->join('companies', 'companies.id = gst_tasks.company_id')
            ->join('users', 'users.id = gst_tasks.assigned_to', 'left')
            ->whereIn('company_id', $companyIds)
            ->orderBy('due_date', 'ASC')
            ->findAll();

        $stats = [
            'total' => count($tasks),
            'pending' => 0,
            'in_progress' => 0,
            'completed' => 0,
            'overdue' => 0
        ];

        foreach ($tasks as $t) {
            if ($t['status'] === 'completed') {
                $stats['completed']++;
            } else {
                if ($t['due_date'] < date('Y-m-d')) {
                    $stats['overdue']++;
                }
                if ($t['status'] === 'in_progress') {
                    $stats['in_progress']++;
                } else {
                    $stats['pending']++;
                }
            }
        }

        return view('Manager/gst/returns', array_merge($this->companyHelper->getCommonViewData(), [
            'tasks' => $tasks,
            'companies' => $this->companyHelper->getUserCompanies(),
            'selectedPeriod' => $period,
            'companyId' => $companyId,
            'stats' => $stats
        ]));
    }

    public function taxes()
    {
        $request = service('request');
        $companyId = $request->getGet('company_id') ?? 'all';
        $period = $request->getGet('period') ?? date('Y-m');
        $taxType = $request->getGet('tax_type') ?? 'all';
        $companyIds = $this->companyHelper->getUserCompanyIds($companyId);

        $selectedMonth = date('m', strtotime($period));
        $selectedYear = date('Y', strtotime($period));

        $query = $this->taxModel->select('taxes.*, expenses.expense_name, expenses.vendor_name, expenses.party_name, expenses.actual_amount, companies.name as company_name')
            ->join('expenses', 'expenses.id = taxes.taxable_id AND taxes.taxable_type = "App\\\Models\\\Expense"', 'inner')
            ->join('companies', 'companies.id = expenses.company_id')
            ->whereIn('expenses.company_id', $companyIds)
            ->where('MONTH(taxes.created_at)', $selectedMonth)
            ->where('YEAR(taxes.created_at)', $selectedYear);

        if ($taxType !== 'all') {
            $query->where('taxes.tax_type', $taxType);
        }

        $expenseTaxes = $query->findAll();

        $totalTaxPaid = array_sum(array_column($expenseTaxes, 'tax_amount'));
        $billsWithTax = count($expenseTaxes);

        return view('Manager/gst/taxes', array_merge($this->companyHelper->getCommonViewData(), [
            'expenseTaxes' => $expenseTaxes,
            'companies' => $this->companyHelper->getUserCompanies(),
            'selectedPeriod' => $period,
            'companyId' => $companyId,
            'selectedTaxType' => $taxType,
            'totalTaxPaid' => $totalTaxPaid,
            'billsWithTax' => $billsWithTax
        ]));
    }

    public function filter()
    {
        $period = $this->request->getPost('period');
        $companyId = $this->request->getPost('company_id');
        return redirect()->to(base_url("manager/gst?period=$period&company_id=$companyId"));
    }
}
