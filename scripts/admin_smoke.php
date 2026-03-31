<?php

require __DIR__ . '/../vendor/autoload.php';

use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$kernel = $app->make(HttpKernel::class);

$cookies = [];
$failures = [];

function request_once(HttpKernel $kernel, array &$cookies, string $method, string $uri, array $data = [])
{
    $cookieHeader = implode('; ', array_map(
        fn ($name, $value) => $name . '=' . $value,
        array_keys($cookies),
        $cookies
    ));

    $server = $cookieHeader ? ['HTTP_COOKIE' => $cookieHeader] : [];
    $request = Request::create($uri, $method, $data, [], [], $server);
    $response = $kernel->handle($request);

    foreach ($response->headers->getCookies() as $cookie) {
        $cookies[$cookie->getName()] = $cookie->getValue();
    }

    $kernel->terminate($request, $response);

    return $response;
}

function csrf_token_from(string $html): ?string
{
    if (preg_match('/meta name="csrf-token" content="([^"]+)"/', $html, $matches)) {
        return $matches[1];
    }

    if (preg_match('/name="_token" value="([^"]+)"/', $html, $matches)) {
        return $matches[1];
    }

    return null;
}

function record_result(string $name, bool $passed, string $details = ''): void
{
    $status = $passed ? 'PASS' : 'FAIL';
    echo sprintf("[%s] %s%s\n", $status, $name, $details !== '' ? " - {$details}" : '');
}

function expect_status(array &$failures, string $name, $response, int $status, string $details = ''): bool
{
    $passed = $response->getStatusCode() === $status;
    record_result($name, $passed, $passed ? $details : 'expected ' . $status . ', got ' . $response->getStatusCode());

    if (!$passed) {
        $failures[] = $name;
    }

    return $passed;
}

function expect_truthy(array &$failures, string $name, bool $passed, string $details = ''): bool
{
    record_result($name, $passed, $details);

    if (!$passed) {
        $failures[] = $name;
    }

    return $passed;
}

$loginPage = request_once($kernel, $cookies, 'GET', '/login');
$loginToken = csrf_token_from($loginPage->getContent() ?? '');

expect_status($failures, 'login page', $loginPage, 200);
expect_truthy($failures, 'login page content', str_contains($loginPage->getContent(), 'Sign In'));

$login = request_once($kernel, $cookies, 'POST', '/login', [
    '_token' => $loginToken,
    'email' => 'shiva.acedezines@gmail.com',
    'password' => '12345678',
]);

$loginLocation = $login->headers->get('Location') ?? '';
expect_status($failures, 'login submit', $login, 302);
expect_truthy($failures, 'login redirect', str_contains($loginLocation, '/admin/dashboard'), $loginLocation);

$pages = [
    '/admin/dashboard' => false,
    '/admin/companies' => true,
    '/admin/expensetypes' => false,
    '/admin/standard-expenses' => true,
    '/admin/invoices' => true,
    '/admin/users' => true,
    '/admin/system-settings' => true,
    '/admin/audit-logs' => false,
    '/admin/activity-logs' => false,
];

foreach ($pages as $uri => $checkProdUrl) {
    $response = request_once($kernel, $cookies, 'GET', $uri);
    if (!expect_status($failures, "GET {$uri}", $response, 200)) {
        continue;
    }

    if ($checkProdUrl) {
        expect_truthy(
            $failures,
            "no production url in {$uri}",
            !str_contains($response->getContent(), 'https://xhtmlreviews.in/finance-manager')
        );
    }
}

$companyId = App\Models\Company::query()->value('id');
$managerId = App\Models\User::query()->where('role', 'manager')->value('id');
$categoryId = App\Models\Category::query()->where('main_type', 'expense')->value('id');
$expenseId = App\Models\Expense::query()->where('source', 'standard')->value('id');
$invoiceId = App\Models\Invoice::query()->value('id');

$ajaxChecks = [
    '/admin/companies/' . $companyId . '/edit',
    '/admin/standard-expenses/' . $expenseId,
    '/admin/standard-expenses/' . $expenseId . '/taxes',
    '/admin/invoices/' . $invoiceId,
    '/admin/invoices/' . $invoiceId . '/edit',
    '/admin/users/' . $managerId . '/edit',
    '/admin/users/role-permissions/manager',
];

foreach ($ajaxChecks as $uri) {
    expect_status($failures, "GET {$uri}", request_once($kernel, $cookies, 'GET', $uri), 200);
}

$formPage = request_once($kernel, $cookies, 'GET', '/admin/companies');
$csrfToken = csrf_token_from($formPage->getContent() ?? '');

DB::beginTransaction();

try {
    $companyCreate = request_once($kernel, $cookies, 'POST', '/admin/companies', [
        '_token' => $csrfToken,
        'name' => 'Smoke Test Company',
        'email' => 'smoke-company@example.com',
        'manager_id' => $managerId,
        'currency' => 'INR',
        'website' => 'https://example.com',
        'address' => 'Smoke Address',
        'status' => 'active',
    ]);
    $companyCreateData = json_decode($companyCreate->getContent(), true) ?? [];
    $newCompanyId = $companyCreateData['company']['id'] ?? null;

    expect_status($failures, 'company create status', $companyCreate, 201);
    expect_truthy($failures, 'company create payload', ($companyCreateData['success'] ?? false) === true);

    if ($newCompanyId) {
        $companyUpdate = request_once($kernel, $cookies, 'POST', '/admin/companies/' . $newCompanyId, [
            '_token' => $csrfToken,
            '_method' => 'PUT',
            'name' => 'Smoke Test Company Updated',
            'email' => 'smoke-company@example.com',
            'manager_id' => $managerId,
            'currency' => 'INR',
            'website' => 'https://example.com',
            'address' => 'Smoke Address Updated',
            'status' => 'active',
        ]);
        $companyUpdateData = json_decode($companyUpdate->getContent(), true) ?? [];

        expect_status($failures, 'company update status', $companyUpdate, 200);
        expect_truthy($failures, 'company update payload', ($companyUpdateData['success'] ?? false) === true);
    }

    $userCreate = request_once($kernel, $cookies, 'POST', '/admin/users', [
        '_token' => $csrfToken,
        'name' => 'Smoke Test User',
        'email' => 'smoke-user@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'manager',
        'company_id' => $companyId,
        'status' => 'active',
        'permissions' => json_encode([]),
    ]);
    $userCreateData = json_decode($userCreate->getContent(), true) ?? [];
    $newUserId = $userCreateData['user']['id'] ?? null;

    expect_status($failures, 'user create status', $userCreate, 200);
    expect_truthy($failures, 'user create payload', ($userCreateData['success'] ?? false) === true);

    if ($newUserId) {
        $userUpdate = request_once($kernel, $cookies, 'POST', '/admin/users/' . $newUserId, [
            '_token' => $csrfToken,
            '_method' => 'PUT',
            'name' => 'Smoke Test User Updated',
            'email' => 'smoke-user@example.com',
            'role' => 'manager',
            'company_id' => $companyId,
            'status' => 'active',
            'permissions' => json_encode([]),
        ]);
        $userUpdateData = json_decode($userUpdate->getContent(), true) ?? [];
        expect_status($failures, 'user update status', $userUpdate, 200);
        expect_truthy($failures, 'user update payload', ($userUpdateData['success'] ?? false) === true);

        $userStatus = request_once($kernel, $cookies, 'POST', '/admin/users/' . $newUserId . '/status', [
            '_token' => $csrfToken,
            '_method' => 'PUT',
            'status' => 'inactive',
        ]);
        $userStatusData = json_decode($userStatus->getContent(), true) ?? [];
        expect_status($failures, 'user status update', $userStatus, 200);
        expect_truthy($failures, 'user status payload', ($userStatusData['success'] ?? false) === true);

        $userDelete = request_once($kernel, $cookies, 'POST', '/admin/users/' . $newUserId, [
            '_token' => $csrfToken,
            '_method' => 'DELETE',
        ]);
        $userDeleteData = json_decode($userDelete->getContent(), true) ?? [];
        expect_status($failures, 'user delete status', $userDelete, 200);
        expect_truthy($failures, 'user delete payload', ($userDeleteData['success'] ?? false) === true);
    }

    $categoryCreate = request_once($kernel, $cookies, 'POST', '/admin/categories/save', [
        '_token' => $csrfToken,
        'name' => 'Smoke Test Category',
        'main_type' => 'expense',
        'category_type' => 'not_standard',
    ]);
    $categoryCreateData = json_decode($categoryCreate->getContent(), true) ?? [];
    $newCategoryId = $categoryCreateData['category']['id'] ?? null;

    expect_status($failures, 'category create status', $categoryCreate, 200);
    expect_truthy($failures, 'category create payload', ($categoryCreateData['success'] ?? false) === true);

    if ($newCategoryId) {
        $categoryDelete = request_once($kernel, $cookies, 'POST', '/admin/categories/' . $newCategoryId, [
            '_token' => $csrfToken,
            '_method' => 'DELETE',
        ]);
        $categoryDeleteData = json_decode($categoryDelete->getContent(), true) ?? [];
        expect_status($failures, 'category delete status', $categoryDelete, 200);
        expect_truthy($failures, 'category delete payload', ($categoryDeleteData['success'] ?? false) === true);
    }

    $expenseCreate = request_once($kernel, $cookies, 'POST', '/admin/standard-expenses/store', [
        '_token' => $csrfToken,
        'expense_name' => 'Smoke Test Expense',
        'company_id' => $companyId,
        'category_id' => $categoryId,
        'actual_amount' => '1000',
        'planned_amount' => '1000',
        'frequency' => 'monthly',
        'due_day' => '15',
        'reminder_days' => '3',
        'party_name' => 'Smoke Vendor',
        'mobile_number' => '9876543210',
        'is_active' => '1',
    ]);
    $createdExpense = App\Models\Expense::query()->where('expense_name', 'Smoke Test Expense')->first();

    expect_status($failures, 'standard expense create redirect', $expenseCreate, 302);
    expect_truthy($failures, 'standard expense create persisted', $createdExpense !== null);

    if ($createdExpense) {
        $expenseUpdate = request_once($kernel, $cookies, 'POST', '/admin/standard-expenses/' . $createdExpense->id, [
            '_token' => $csrfToken,
            '_method' => 'PUT',
            'expense_name' => 'Smoke Test Expense Updated',
            'company_id' => $companyId,
            'category_id' => $categoryId,
            'actual_amount' => '1200',
            'planned_amount' => '1200',
            'frequency' => 'monthly',
            'due_day' => '15',
            'reminder_days' => '5',
            'party_name' => 'Smoke Vendor',
            'mobile_number' => '9876543210',
            'is_active' => '1',
        ]);
        $expenseUpdateData = json_decode($expenseUpdate->getContent(), true) ?? [];
        expect_status($failures, 'standard expense update status', $expenseUpdate, 200);
        expect_truthy($failures, 'standard expense update payload', ($expenseUpdateData['success'] ?? false) === true);
    }

    $settingsSave = request_once($kernel, $cookies, 'POST', '/admin/system-settings/save', [
        '_token' => $csrfToken,
        'group' => 'general',
        'app_name' => 'Finance Manager',
    ]);
    $settingsSaveData = json_decode($settingsSave->getContent(), true) ?? [];
    expect_status($failures, 'settings save status', $settingsSave, 200);
    expect_truthy($failures, 'settings save payload', ($settingsSaveData['success'] ?? false) === true);

    $invoiceCreate = request_once($kernel, $cookies, 'POST', '/admin/invoices', [
        '_token' => $csrfToken,
        'company_id' => $companyId,
        'client_name' => 'Smoke Client',
        'client_email' => 'smoke-client@example.com',
        'billing_address' => 'Smoke Address',
        'issue_date' => '2026-03-31',
        'due_date' => '2026-04-15',
        'currency' => 'INR',
        'total_amount' => '1180',
        'converted_amount' => '1180',
        'subtotal' => '1000',
        'line_items' => json_encode([
            ['description' => 'Service', 'quantity' => 1, 'rate' => 1000],
        ]),
        'apply_gst' => '1',
        'gst_percentage' => '18',
        'gst_amount' => '180',
    ]);
    $invoiceCreateData = json_decode($invoiceCreate->getContent(), true) ?? [];
    $newInvoiceId = $invoiceCreateData['invoice']['id'] ?? null;

    expect_status($failures, 'invoice create status', $invoiceCreate, 200);
    expect_truthy($failures, 'invoice create payload', ($invoiceCreateData['success'] ?? false) === true, $invoiceCreateData['message'] ?? '');

    if ($newInvoiceId) {
        expect_status($failures, 'invoice details status', request_once($kernel, $cookies, 'GET', '/admin/invoices/' . $newInvoiceId), 200);
        expect_status($failures, 'invoice edit status', request_once($kernel, $cookies, 'GET', '/admin/invoices/' . $newInvoiceId . '/edit'), 200);

        $invoiceUpdate = request_once($kernel, $cookies, 'POST', '/admin/invoices/' . $newInvoiceId . '/update', [
            '_token' => $csrfToken,
            '_method' => 'PUT',
            'company_id' => $companyId,
            'client_name' => 'Smoke Client Updated',
            'client_email' => 'smoke-client@example.com',
            'billing_address' => 'Smoke Address',
            'issue_date' => '2026-03-31',
            'due_date' => '2026-04-20',
            'currency' => 'INR',
            'conversion_rate' => '1',
            'total_amount' => '1180',
            'converted_amount' => '1180',
            'subtotal' => '1000',
            'line_items' => json_encode([
                ['description' => 'Service', 'quantity' => 1, 'rate' => 1000],
            ]),
            'gst_percentage' => '18',
            'gst_amount' => '180',
            'purpose_comment' => 'Smoke update',
            'terms_conditions' => 'Smoke terms',
        ]);
        $invoiceUpdateData = json_decode($invoiceUpdate->getContent(), true) ?? [];
        expect_status($failures, 'invoice update status', $invoiceUpdate, 200);
        expect_truthy($failures, 'invoice update payload', ($invoiceUpdateData['success'] ?? false) === true, $invoiceUpdateData['message'] ?? '');
    }
} finally {
    DB::rollBack();
}

if ($failures !== []) {
    echo "\nFailures:\n";
    foreach ($failures as $failure) {
        echo " - {$failure}\n";
    }

    exit(1);
}

echo "\nAdmin smoke test completed successfully.\n";
