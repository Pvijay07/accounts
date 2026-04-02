<?php

namespace App\Libraries;

use App\Models\CompanyModel;

class ManagesCompanies
{
    protected $companyModel;

    public function __construct()
    {
        $this->companyModel = new CompanyModel();
    }

    /**
     * Get company IDs for the authenticated user
     */
    public function getUserCompanyIds($specificCompanyId = null)
    {
        $session = session();
        $userId  = $session->get('user_id');
        $role    = $session->get('role');

        if ($specificCompanyId && $specificCompanyId !== 'all') {
            if (in_array($role, ['admin', 'ca'])) {
                return [$specificCompanyId];
            }
            $hasAccess = $this->companyModel
                ->where('id', $specificCompanyId)
                ->where('manager_id', $userId)
                ->countAllResults() > 0;
            return $hasAccess ? [$specificCompanyId] : [];
        }

        if (in_array($role, ['admin', 'ca'])) {
            return array_column(
                $this->companyModel->where('status', 'active')->findAll(),
                'id'
            );
        }

        return array_column(
            $this->companyModel
                ->where('manager_id', $userId)
                ->where('status', 'active')
                ->findAll(),
            'id'
        );
    }

    /**
     * Get companies for dropdown (only user's companies)
     */
    public function getUserCompanies()
    {
        $session = session();
        $userId  = $session->get('user_id');
        $role    = $session->get('role');

        if (in_array($role, ['admin', 'ca'])) {
            return $this->companyModel->where('status', 'active')->orderBy('name')->findAll();
        }

        return $this->companyModel
            ->where('manager_id', $userId)
            ->where('status', 'active')
            ->orderBy('name')
            ->findAll();
    }

    /**
     * Generate last months for dropdown
     */
    public function generateMonths($count = 12)
    {
        $months = [];
        for ($i = 0; $i < $count; $i++) {
            $date     = date('Y-m', strtotime("-$i months"));
            $months[] = [
                'value' => $date,
                'label' => date('F Y', strtotime($date))
            ];
        }
        return $months;
    }

    /**
     * Get common view data with user's companies
     */
    public function getCommonViewData($selectedMonth = null, $selectedYear = null)
    {
        if (!$selectedMonth) $selectedMonth = date('m');
        if (!$selectedYear) $selectedYear = date('Y');

        return [
            'currentPeriod' => date('F Y', strtotime("$selectedYear-$selectedMonth-01")),
            'companies'     => $this->getUserCompanies(),
            'months'        => $this->generateMonths(),
        ];
    }

    /**
     * Get date range based on period string
     */
    public function getDateRange($range)
    {
        $now = date('Y-m-d H:i:s');
        switch ($range) {
            case 'today':
                return ['start' => date('Y-m-d 00:00:00'), 'end' => date('Y-m-d 23:59:59')];
            case 'week':
                return [
                    'start' => date('Y-m-d 00:00:00', strtotime('monday this week')),
                    'end'   => date('Y-m-d 23:59:59', strtotime('sunday this week'))
                ];
            case 'month':
                return [
                    'start' => date('Y-m-01 00:00:00'),
                    'end'   => date('Y-m-t 23:59:59')
                ];
            case 'quarter':
                $quarter = ceil(date('n') / 3);
                $startMonth = ($quarter - 1) * 3 + 1;
                return [
                    'start' => date('Y-' . str_pad($startMonth, 2, '0', STR_PAD_LEFT) . '-01 00:00:00'),
                    'end'   => date('Y-m-t 23:59:59', strtotime(date('Y-' . str_pad($startMonth + 2, 2, '0', STR_PAD_LEFT) . '-01')))
                ];
            case 'year':
                return ['start' => date('Y-01-01 00:00:00'), 'end' => date('Y-12-31 23:59:59')];
            default:
                return ['start' => date('Y-m-01 00:00:00'), 'end' => date('Y-m-t 23:59:59')];
        }
    }

    /**
     * Format bytes to human readable
     */
    public function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow   = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow   = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
