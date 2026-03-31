<?php

namespace App\Traits;

use App\Models\Company;

trait ManagesCompanies
{
    /**
     * Get company IDs for the authenticated user
     */
    protected function getUserCompanyIds($specificCompanyId = null)
    {
        $user = auth()->user();

        if ($specificCompanyId && $specificCompanyId !== 'all') {
            // Verify the user has access to this specific company
            $hasAccess = Company::where('id', $specificCompanyId)
                ->where('manager_id', $user->id)
                ->exists();

            if ($hasAccess || $user->isAdmin() || $user->isCA()) {
                return [$specificCompanyId];
            } else {
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
    protected function getUserCompanies()
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

    /**
     * Generate last months for dropdown
     */
    protected function generateMonths($count = 12)
    {
        $months = [];
        for ($i = 0; $i < $count; $i++) {
            $date = date('Y-m', strtotime("-$i months"));
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
    protected function getCommonViewData($selectedMonth = null, $selectedYear = null)
    {
        if (!$selectedMonth) {
            $selectedMonth = date('m');
        }

        if (!$selectedYear) {
            $selectedYear = date('Y');
        }

        return [
            'currentPeriod' => date('F Y', strtotime("$selectedYear-$selectedMonth-01")),
            'companies'     => $this->getUserCompanies(),
            'months'        => $this->generateMonths(),
        ];
    }
}
