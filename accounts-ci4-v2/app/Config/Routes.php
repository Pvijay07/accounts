<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Public Routes
$routes->get('/', 'AuthController::login');
$routes->get('login', 'AuthController::login');
$routes->post('login', 'AuthController::attemptLogin');
$routes->get('logout', 'AuthController::logout');

// ============================================================
// ADMIN ROUTES
// ============================================================
$routes->group('admin', ['namespace' => 'App\Controllers\Admin', 'filter' => 'auth:admin'], function($routes) {
    $routes->get('dashboard', 'DashboardController::index');

    // Company Management
    $routes->get('companies', 'CompanyController::index');
    $routes->post('companies', 'CompanyController::store');
    $routes->get('companies/(:num)', 'CompanyController::edit/$1');
    $routes->post('companies/update/(:num)', 'CompanyController::update/$1');
    $routes->delete('companies/(:num)', 'CompanyController::delete/$1');
    $routes->post('companies/delete/(:num)', 'CompanyController::delete/$1');

    // User Management
    $routes->get('users', 'UserController::index');
    $routes->post('users', 'UserController::store');
    $routes->get('users/(:num)/edit', 'UserController::edit/$1');
    $routes->post('users/update/(:num)', 'UserController::update/$1');
    $routes->post('users/status/(:num)', 'UserController::updateStatus/$1');
    $routes->delete('users/(:num)', 'UserController::delete/$1');
    $routes->post('users/delete/(:num)', 'UserController::delete/$1');

    // Invoice Management
    $routes->get('invoices', 'InvoiceController::index');
    $routes->post('invoices', 'InvoiceController::store');
    $routes->get('invoices/(:num)', 'InvoiceController::details/$1');
    $routes->get('invoices/(:num)/edit', 'InvoiceController::edit/$1');
    $routes->post('invoices/update/(:num)', 'InvoiceController::update/$1');
    $routes->post('invoices/partial-payment', 'InvoiceController::processPartialPayment');

    // Standard Expenses
    $routes->get('standard-expenses', 'StandardExpenseController::index');
    $routes->post('standard-expenses', 'StandardExpenseController::store');
    $routes->get('standard-expenses/(:num)', 'StandardExpenseController::show/$1');
    $routes->post('standard-expenses/update/(:num)', 'StandardExpenseController::update/$1');
    $routes->delete('standard-expenses/(:num)', 'StandardExpenseController::delete/$1');
    $routes->post('standard-expenses/delete/(:num)', 'StandardExpenseController::delete/$1');
    $routes->post('generate-expenses', 'StandardExpenseController::generateExpenses');
    $routes->post('standard-expenses/get-categories', 'StandardExpenseController::getCategories');
    $routes->get('standard-expenses/(:num)/taxes', 'StandardExpenseController::getTaxDetails/$1');
    $routes->post('taxes/(:num)/pay', 'StandardExpenseController::markTaxAsPaid/$1');

    // Expense Types
    $routes->get('expense-types', 'ExpenseTypeController::index');
    $routes->post('expense-types', 'ExpenseTypeController::store');
    $routes->get('expense-types/(:num)/edit', 'ExpenseTypeController::edit/$1');
    $routes->post('expense-types/update/(:num)', 'ExpenseTypeController::update/$1');

    // Category Management
    $routes->get('categories', 'CategoryController::index');
    $routes->post('categories', 'CategoryController::store');
    $routes->post('categories/update/(:num)', 'CategoryController::update/$1');
    $routes->delete('categories/(:num)', 'CategoryController::delete/$1');
    $routes->post('categories/assign', 'CategoryController::assign');
    $routes->post('categories/bulk-update', 'CategoryController::bulkUpdate');

    // System Settings
    $routes->get('system-settings', 'SystemSettingsController::index');
    $routes->post('system-settings/save', 'SystemSettingsController::save');
    $routes->post('system-settings/test-email', 'SystemSettingsController::testEmail');
    $routes->post('system-settings/backup/run', 'SystemSettingsController::runBackup');
    $routes->get('system-settings/backup/download', 'SystemSettingsController::downloadBackup');
    $routes->post('system-settings/clear-cache', 'SystemSettingsController::clearCache');
    $routes->post('system-settings/optimize-db', 'SystemSettingsController::optimizeDatabase');
    $routes->post('system-settings/clear-logs', 'SystemSettingsController::clearLogs');

    // Activity Logs
    $routes->get('activity-logs', 'ActivityLogController::index');
    $routes->get('activity-logs/export', 'ActivityLogController::export');
    $routes->get('activity-logs/(:num)', 'ActivityLogController::show/$1');
    $routes->post('activity-logs/clear', 'ActivityLogController::clear');
});

// ============================================================
// MANAGER ROUTES
// ============================================================
$routes->group('manager', ['namespace' => 'App\Controllers\Manager', 'filter' => 'auth:manager'], function($routes) {
    $routes->get('dashboard', 'DashboardController::index');

    // Expenses
    $routes->get('expenses', 'ExpensesController::index');
    $routes->post('expenses', 'ExpensesController::store');
    $routes->get('expenses/summary', 'ExpensesController::getSummary');
    $routes->get('expenses/table', 'ExpensesController::getTable');
    $routes->get('expenses/export', 'ExpensesController::export');
    $routes->get('expenses/(:num)/edit', 'ExpensesController::edit/$1');
    $routes->post('expenses/update/(:num)', 'ExpensesController::update/$1');
    $routes->post('expenses/(:num)/mark-paid', 'ExpensesController::markPaid/$1');
    $routes->get('expenses/(:num)/split-history', 'ExpensesController::splitHistory/$1');
    $routes->delete('expenses/(:num)', 'ExpensesController::delete/$1');
    $routes->post('expenses/delete/(:num)', 'ExpensesController::delete/$1');

    // Income
    $routes->get('income', 'IncomeController::index');
    $routes->post('income', 'IncomeController::store');
    $routes->get('income/export', 'IncomeController::export');
    $routes->get('income/details/(:num)', 'IncomeController::getIncomeDetails/$1');
    $routes->get('income/(:num)/edit', 'IncomeController::edit/$1');
    $routes->post('income/update/(:num)', 'IncomeController::update/$1');
    $routes->get('income/(:num)/split-history', 'IncomeController::splitHistory/$1');
    $routes->post('income/(:num)/settle', 'IncomeController::settle/$1');
    $routes->delete('income/(:num)', 'IncomeController::delete/$1');
    $routes->post('income/delete/(:num)', 'IncomeController::delete/$1');

    // GST
    $routes->get('gst', 'GstController::index');
    $routes->get('gst-collected', 'GstController::gstCollected');
    $routes->get('settlement', 'GstController::settlement');
    $routes->get('returns', 'GstController::returns');
    $routes->get('taxes', 'GstController::taxes');
    $routes->post('gst-filter', 'GstController::filter');
    $routes->post('settlement/store', 'GstController::storeSettlement');
    $routes->get('settlement/(:num)', 'GstController::showSettlement/$1');
    $routes->post('task/store', 'GstController::storeTask');
    $routes->get('gst/export/(:segment)', 'GstController::export/$1');
    $routes->get('gst/taxes/export/(:segment)', 'GstController::exportTaxes/$1');
    $routes->get('gst/collected/export/(:segment)', 'GstController::exportGstCollected/$1');

    // TDS
    $routes->get('tds', 'TdsController::index');
    $routes->get('tds/expense', 'TdsController::tdsExpense');
    $routes->post('tds/attach-document', 'TdsController::attachTaxProof');
    $routes->get('tds/download-proof/(:num)', 'TdsController::downloadTdsProof/$1');
    $routes->post('tds/mark-paid/(:num)', 'TdsController::markTDSPaid/$1');
    $routes->get('tds/export/(:segment)', 'TdsController::exportData/$1');
    $routes->get('tds/expense/export/(:segment)', 'TdsController::exportExpenseData/$1');

    // Loans/Advances
    $routes->get('loans', 'LoansController::index');
    $routes->post('loans', 'LoansController::store');
    $routes->get('loans/(:num)', 'LoansController::show/$1');
    $routes->post('loans/update/(:num)', 'LoansController::update/$1');
    $routes->delete('loans/(:num)', 'LoansController::destroy/$1');
    $routes->post('loans/delete/(:num)', 'LoansController::destroy/$1');
    $routes->post('loans/(:num)/recovery', 'LoansController::storeRecovery/$1');
    $routes->get('loans/stats', 'LoansController::getStats');

    // Company AJAX
    $routes->get('companies/(:num)/dues-details', 'IncomeController::companyDuesDetails/$1');
    $routes->get('companies/(:num)/balance-summary', 'IncomeController::balanceSummary/$1');
});

// ============================================================
// CA ROUTES
// ============================================================
$routes->group('ca', ['namespace' => 'App\Controllers\CA', 'filter' => 'auth:ca'], function($routes) {
    $routes->get('dashboard', 'DashboardController::index');
    $routes->get('statements', 'StatementController::index');
    $routes->get('invoices', 'InvoiceController::index');
});
