<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\InvoiceManagementController;
use App\Http\Controllers\Admin\StandardExpensesController;
use App\Http\Controllers\Admin\SystemSettingsController;
use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ExpenseTypeController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;

use App\Http\Controllers\CA\StatementController;
use App\Http\Controllers\Manager\DashboardController;
use App\Http\Controllers\Manager\ExpensesController;
use App\Http\Controllers\Manager\IncomeController;
use App\Http\Controllers\Manager\LoansController;

use App\Http\Controllers\CA\DashboardController as CADashboardController;
use App\Http\Controllers\CA\InvoiceController as CAInvoiceController;
use App\Http\Controllers\Manager\GstController;
use App\Http\Controllers\Manager\TDSController;
use Illuminate\Support\Facades\Route;

// Authentication Routes
// Separate Logins
Route::get('/admin/login', [LoginController::class, 'showAdminLoginForm'])->name('admin.login');
Route::post('/admin/login', [LoginController::class, 'login']);

Route::get('/manager/login', [LoginController::class, 'showManagerLoginForm'])->name('manager.login');
Route::post('/manager/login', [LoginController::class, 'login']);

Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Admin Routes
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/dashboard', [AdminDashboardController::class, 'dashboard'])->name('dashboard');

        Route::get('/companies', [CompanyController::class, 'index'])->name('companies');
        Route::post('/companies', [CompanyController::class, 'store'])->name('companies.store');

        Route::get('/companies/{id}/edit', [CompanyController::class, 'edit'])->name('companies.edit');
        Route::put('/companies/{company}', [CompanyController::class, 'update'])->name('companies.update');
        Route::delete('/companies/{company}', [CompanyController::class, 'destroy'])->name('companies.destroy');
        Route::post('/companies/settings', [CompanyController::class, 'updateSettings'])->name('companies.settings.update');

        Route::get('/users', [UserController::class, 'index'])->name('users');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');

        Route::get('/expensetypes', [ExpenseTypeController::class, 'index'])->name('expensetypes');
        Route::post('/expensetypes', [ExpenseTypeController::class, 'store'])->name('expensetypes.store');

        Route::get('expensetypes/{id}/edit', [ExpenseTypeController::class, 'edit'])->name('expensetypes.edit');
        Route::put('expensetypes/{id}', [ExpenseTypeController::class, 'update'])->name('expensetypes.update');

        Route::get('/standard-expenses', [StandardExpensesController::class, 'index'])->name('standard-expenses');
        Route::post('/standard-expenses/store', [StandardExpensesController::class, 'store'])->name('standard-expenses.store');
        Route::get('/standard-expenses/{id}', [StandardExpensesController::class, 'show'])->name('standard-expenses.show');
        Route::put('/standard-expenses/{id}', [StandardExpensesController::class, 'update'])->name('standard-expenses.update');
        Route::delete('/standard-expenses/{id}', [StandardExpensesController::class, 'destroy'])->name('standard-expenses.destroy');
        Route::post('/generate-expenses', [StandardExpensesController::class, 'generateExpenses'])->name('generate-expenses');
        Route::post('/standard-expenses/get-categories', [StandardExpensesController::class, 'getCategories'])->name('standard-expenses.get-categories');
        Route::get('/standard-expenses/{id}/taxes', [StandardExpensesController::class, 'getTaxDetails'])->name('standard-expenses.taxes');
        Route::post('/taxes/{id}/pay', [StandardExpensesController::class, 'markTaxAsPaid'])->name('taxes.pay');
        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs');

        Route::prefix('invoices')->group(function () {
            Route::get('/', [InvoiceManagementController::class, 'index'])->name('invoices');
            Route::post('/', [InvoiceManagementController::class, 'store'])->name('invoices.store');
            Route::post('/partial-payment', [InvoiceManagementController::class, 'processPartialPayment'])->name('invoices.partial-payment');
            Route::get('/{id}', [InvoiceManagementController::class, 'getInvoiceDetails'])->name('invoices.details');
        });
        Route::put('/invoices/{id}/update', [InvoiceManagementController::class, 'update'])->name('invoices.update');
        Route::get('/invoices/{id}/view', [InvoiceManagementController::class, 'view'])->name('invoices.view');
        Route::get('/invoices/{id}/download', [InvoiceManagementController::class, 'download'])->name('invoices.download');
        Route::post('/invoices/send-email', [InvoiceManagementController::class, 'sendEmail'])->name('invoices.send-email');
        Route::get('/invoices/{id}/edit', [InvoiceManagementController::class, 'edit'])->name('invoices.edit');


        Route::get('/activity-logs/export', [ActivityLogController::class, 'export'])->name('activity-logs.export');
        Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
        Route::post('/activity-logs/clear', [ActivityLogController::class, 'clear'])->name('activity-logs.clear');
        Route::get('/activity-logs/{id}', [ActivityLogController::class, 'show'])->name('activity-logs.show');

        Route::prefix('users')->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('users');
            Route::post('/', [UserController::class, 'store'])->name('users.store');
            Route::get('/{id}/edit', [UserController::class, 'edit'])->name('users.edit');

            Route::put('/{id}', [UserController::class, 'update'])->name('users.update');
            Route::put('/{id}/status', [UserController::class, 'updateStatus'])->name('users.status');
            Route::delete('/{id}', [UserController::class, 'destroy'])->name('users.destroy');
            Route::post('/role-permissions', [UserController::class, 'saveRolePermissions'])->name('users.role-permissions');
            Route::get('/role-permissions/{role}', [UserController::class, 'getUserPermissions'])->name('users.get-role-permissions');
        });
        Route::get('/system-settings', [SystemSettingsController::class, 'index'])->name('system-settings');

        Route::post('/system-settings/save', [SystemSettingsController::class, 'save'])->name('settings.save');
        Route::post('/system-settings/test-email', [SystemSettingsController::class, 'testEmail'])->name('settings.test-email');
        Route::post('/system-settings/backup/run', [SystemSettingsController::class, 'runBackup'])->name('settings.backup.run');
        Route::get('/system-settings/backup/download', [SystemSettingsController::class, 'downloadBackup'])->name('settings.backup.download');
        Route::post('/system-settings/clear-cache', [SystemSettingsController::class, 'clearCache'])->name('settings.clear-cache');
        Route::post('/system-settings/optimize-db', [SystemSettingsController::class, 'optimizeDatabase'])->name('settings.optimize-db');
        Route::post('/system-settings/clear-logs', [SystemSettingsController::class, 'clearLogs'])->name('settings.clear-logs');

        Route::post('/categories/save', [CategoryController::class, 'store'])->name('categories.store');
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
        Route::post('/categories/assign', [CategoryController::class, 'assign'])
            ->name('categories.assign');
        Route::post('/categories/bulk-update', [CategoryController::class, 'bulkUpdate'])->name('categories.bulk-update');
    });




// Manager Routes
Route::middleware(['auth', 'role:manager'])->prefix('manager')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('manager.dashboard');

    // Expenses
    Route::get('/expenses', [ExpensesController::class, 'index'])->name('manager.expenses');
    Route::post('/expenses', [ExpensesController::class, 'store'])->name('manager.expenses.store');
    Route::get('/expenses/summary', [ExpensesController::class, 'getSummary'])->name('manager.expenses.summary');
    Route::get('/expenses/table', [ExpensesController::class, 'getTable'])->name('manager.expenses.table');
    Route::get('/expenses/export', [ExpensesController::class, 'export'])->name('manager.expenses.export');
    Route::get('/expenses/{id}/edit', [ExpensesController::class, 'edit'])->name('manager.expenses.edit');
    Route::put('/expenses/{id}', [ExpensesController::class, 'update'])->name('manager.expenses.update');
    Route::post('/expenses/{id}', [ExpensesController::class, 'update'])->name('manager.expenses.update.post'); // Fallback for spoofed PUT over AJAX
    Route::post('/expenses/{id}/mark-paid', [ExpensesController::class, 'markPaid'])->name('manager.expenses.mark-paid');
    Route::get('/expenses/{id}/split-history', [ExpensesController::class, 'splitHistory'])->name('manager.expenses.split');
    Route::post('/expenses/{id}/settle', [ExpensesController::class, 'markPaid'])->name('manager.expenses.settle');
    Route::get('/expenses/{id}/receipts', [ExpensesController::class, 'getReceipts'])->name('manager.expenses.receipts');

    // Income
    Route::prefix('income')->group(function () {
        Route::get('/', [IncomeController::class, 'index'])->name('income.index');
        Route::post('/', [IncomeController::class, 'store'])->name('income.store');
        Route::get('/export', [IncomeController::class, 'export'])->name('manager.income.export');
        Route::get('/details/{id}', [IncomeController::class, 'getIncomeDetails'])->name('income.details');
        Route::get('/{id}/edit', [IncomeController::class, 'edit'])->name('income.edit');
        Route::put('/{id}', [IncomeController::class, 'update'])->name('income.update');
        Route::post('/{id}', [IncomeController::class, 'update'])->name('income.update.post'); // Fallback for spoofed PUT over AJAX
        Route::get('/{id}/split-history', [IncomeController::class, 'splitHistory'])->name('income.split');
        Route::post('/{id}/settle', [IncomeController::class, 'settle'])->name('income.settle');
        Route::delete('/{id}', [IncomeController::class, 'destroy'])->name('income.destroy');
        Route::delete('/receipts/{id}', [IncomeController::class, 'deleteReceipt'])->name('income.receipts.delete');
        Route::get('/upcoming', [IncomeController::class, 'upcoming'])->name('income.upcoming');
        Route::get('/balances', [IncomeController::class, 'balance'])->name('income.balance');
        Route::post('/send-email', [IncomeController::class, 'sendEmail'])->name('income.send-email');
        Route::get('/getIncome/{id}', [IncomeController::class, 'getIncomeDetails']); // Helper for modal consistency
    });

    // GST Routes
    Route::prefix('gst')->group(function () {
        Route::get('/', [GstController::class, 'index'])->name('manager.gst');
    });

    // Company specific AJAX routes
    Route::get('/companies/{id}/dues-details', [IncomeController::class, 'companyDuesDetails'])->name('manager.companies.dues-details');
    Route::get('/companies/{id}/balance-summary', [IncomeController::class, 'balanceSummary'])->name('manager.companies.balance-summary');

    // GST Routes (detailed)
    Route::prefix('gst-details')->group(function () {
        Route::get('/collected', [GstController::class, 'gstCollected'])->name('manager.gst-collected');
        Route::get('/settlements', [GstController::class, 'settlement'])->name('manager.gst-settlements');
        Route::get('/returns', [GstController::class, 'returns'])->name('manager.gst-returns');
        Route::get('/taxes', [GstController::class, 'taxes'])->name('manager.taxes');

        Route::post('/filter', [GstController::class, 'filter'])->name('manager.gst.filter');
        Route::post('/invoices/filter', [GstController::class, 'filterInvoices'])->name('manager.gst.invoices.filter');

        Route::post('/settlement/store', [GstController::class, 'storeSettlement'])->name('manager.gst.settlement.store');
        Route::post('/settlement/store/legacy', [GstController::class, 'storeSettlement'])->name('manager.settlements.store');
        Route::get('/settlement/{id}', [GstController::class, 'showSettlement'])->name('manager.gst.settlement.show');
        Route::get('/settlement/export', [GstController::class, 'exportSettlements'])->name('manager.gst.settlement.export');

        Route::post('/task/store', [GstController::class, 'storeTask'])->name('manager.gst.task.store');
        Route::post('/task/{id}/update-status', [GstController::class, 'updateTaskStatus'])->name('manager.gst.task.update-status');
        Route::get('/task/export', [GstController::class, 'exportTasks'])->name('manager.gst.task.export');

        Route::get('/export/{type}', [GstController::class, 'export'])->name('manager.gst.export');
        Route::get('/invoices/export/{type}', [GstController::class, 'exportInvoices'])->name('manager.gst.invoices.export');
        Route::get('/taxes/export/{type}', [GstController::class, 'exportTaxes'])->name('manager.gst.taxes.export');
        Route::get('/collected/export/{type}', [GstController::class, 'exportGstCollected'])->name('manager.gst.collected.export');
    });

    // TDS Routes
    Route::prefix('tds')->group(function () {
        Route::get('/', [TDSController::class, 'index'])->name('manager.tds');
        Route::get('/expense', [TDSController::class, 'tdsExpense'])->name('manager.tdsExpense');
        Route::post('/attach', [TDSController::class, 'attachInvoice'])->name('manager.tds.attach');
        Route::post('/sync', [TDSController::class, 'syncInvoices'])->name('manager.tds.sync');
        Route::get('/export/{type}', [TDSController::class, 'exportData'])->name('manager.tds.export');
        Route::get('/expense/export/{type}', [TDSController::class, 'exportExpenseData'])->name('manager.tds.expense.export');
        Route::post('/mark-paid/{id}', [TDSController::class, 'markTDSPaid'])->name('manager.tds.mark-paid');
        Route::get('/download-proof/{id}', [TDSController::class, 'downloadTdsProof'])->name('manager.tds.download-tds-proof');
        Route::post('/attach-document', [TDSController::class, 'attachTaxProof'])->name('manager.tds.attach-document');
        Route::delete('/delete-attachment/{id}', [TDSController::class, 'deleteAttachment'])->name('manager.tds.delete-attachment');
    });

    // Loans/Advances
    Route::prefix('loans')->group(function () {
        Route::get('/', [LoansController::class, 'index'])->name('manager.loans.index');
        Route::post('/', [LoansController::class, 'store'])->name('manager.loans.store');
        Route::get('/{id}', [LoansController::class, 'show'])->name('manager.loans.show');
        Route::post('/{id}', [LoansController::class, 'update'])->name('manager.loans.update');
        Route::delete('/{id}', [LoansController::class, 'destroy'])->name('manager.loans.destroy');
        Route::post('/{id}/recovery', [LoansController::class, 'storeRecovery'])->name('manager.loans.recovery.store');
        Route::get('/stats', [LoansController::class, 'getStats'])->name('manager.loans.stats');
    });
});

// CA Routes
Route::middleware(['auth', 'role:ca'])->prefix('ca')->group(function () {
    Route::get('/dashboard', [CADashboardController::class, 'index'])->name('ca.dashboard');
    Route::get('/statements', [StatementController::class, 'index'])->name('ca.statements');
    Route::get('/invoices', [CAInvoiceController::class, 'index'])->name('ca.invoices');
});
