<?php

use App\Models\Company;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    config()->set('app.url', 'http://localhost');
    config()->set('database.default', 'mysql');
    config()->set('database.connections.mysql', [
        'driver' => 'mysql',
        'host' => '127.0.0.1',
        'port' => '3306',
        'database' => 'finance_manage',
        'username' => 'root',
        'password' => '',
        'unix_socket' => '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
        'prefix_indexes' => true,
        'strict' => true,
        'engine' => null,
    ]);

    DB::purge('mysql');
    DB::setDefaultConnection('mysql');
    DB::reconnect('mysql');
});

function adminUser(): User
{
    return User::query()
        ->where('email', 'shiva.acedezines@gmail.com')
        ->where('role', 'admin')
        ->firstOrFail();
}

function firstManagerUserId(): int
{
    return User::query()
        ->where('role', 'manager')
        ->value('id');
}

it('renders the login page', function () {
    $response = $this->get('/login');

    $response->assertOk();
    $response->assertSee('Sign In');
});

it('loads the core admin pages', function (string $uri) {
    $response = $this
        ->actingAs(adminUser())
        ->get($uri);

    $response->assertOk();
})->with([
    '/admin/dashboard',
    '/admin/companies',
    '/admin/standard-expenses',
    '/admin/invoices',
    '/admin/users',
    '/admin/system-settings',
    '/admin/audit-logs',
    '/admin/activity-logs',
]);

it('loads the core admin ajax endpoints', function (callable $uriFactory) {
    $response = $this
        ->actingAs(adminUser())
        ->get($uriFactory());

    $response->assertOk();
})->with([
    'company edit' => fn () => '/admin/companies/' . Company::query()->value('id') . '/edit',
    'standard expense details' => fn () => '/admin/standard-expenses/' . Expense::query()->where('source', 'standard')->value('id'),
    'standard expense taxes' => fn () => '/admin/standard-expenses/' . Expense::query()->where('source', 'standard')->value('id') . '/taxes',
    'invoice details' => fn () => '/admin/invoices/' . Invoice::query()->value('id'),
    'invoice edit' => fn () => '/admin/invoices/' . Invoice::query()->value('id') . '/edit',
    'user edit' => fn () => '/admin/users/' . firstManagerUserId() . '/edit',
    'role permissions' => fn () => '/admin/users/role-permissions/manager',
]);

it('does not leak the production domain into admin pages', function (string $uri) {
    $response = $this
        ->actingAs(adminUser())
        ->get($uri);

    $response->assertOk();
    expect($response->getContent())->not->toContain('https://xhtmlreviews.in/finance-manager');
})->with([
    '/admin/companies',
    '/admin/standard-expenses',
    '/admin/invoices',
    '/admin/users',
    '/admin/system-settings',
]);
