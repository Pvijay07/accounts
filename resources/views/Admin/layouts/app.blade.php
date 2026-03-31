<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finance Admin - Accounting Dashboard</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('public/assets/styles.css') }}">

</head>
<style>


input[type=number]::-webkit-inner-spin-button, 
input[type=number]::-webkit-outer-spin-button { 
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    margin: 0; 
}
</style>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <i class="fas fa-cogs"></i>
                <h2>Finance Admin</h2>
            </div>
            <div class="sidebar-menu">
                <a href="{{ route('admin.dashboard') }}"
                    class="menu-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" data-page="dashboard">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('admin.companies') }}"
                    class="menu-item {{ request()->routeIs('admin.companies') ? 'active' : '' }}"
                    data-page="company-management">
                    <i class="fas fa-building"></i>
                    <span>Company Management</span>
                </a>
                {{-- <a href="{{ route('admin.expensetypes') }}"
                    class="menu-item {{ request()->routeIs('admin.expensetypes') ? 'active' : '' }}"
                    data-page="expense-types">
                    <i class="fas fa-list-alt"></i>
                    <span>Expense Types</span>
                </a> --}}
                <a href="{{ route('admin.standard-expenses') }}"
                    class="menu-item {{ request()->routeIs('admin.standard-expenses') ? 'active' : '' }}"
                    data-page="standard-expenses">
                    <i class="fas fa-money-bill-wave"></i>
                    <span>Standard Expenses</span>
                </a>
                <a href="{{ route('admin.invoices') }}"
                    class="menu-item {{ request()->routeIs('admin.invoices') ? 'active' : '' }}"
                    data-page="standard-expenses">
                    <i class="fas fa-money-bill-wave"></i>
                    <span>Invoice Management</span>
                </a>
                <a href="{{ route('admin.users') }}"
                    class="menu-item {{ request()->routeIs('admin.users') ? 'active' : '' }}"
                    data-page="user-management">
                    <i class="fas fa-users"></i>
                    <span>User Management</span>
                </a>
                <a href="{{ route('admin.system-settings') }}"
                    class="menu-item {{ request()->routeIs('admin.system-settings') ? 'active' : '' }}"
                    data-page="system-settings">
                    <i class="fas fa-cog"></i>
                    <span>System Settings</span>
                </a>
                <a href="{{ route('admin.audit-logs') }}"
                    class="menu-item {{ request()->routeIs('admin.audit-logs') ? 'active' : '' }}"
                    data-page="audit-logs">
                    <i class="fas fa-history"></i>
                    <span>Audit Logs</span>
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Header -->
            <div class="header">
                <h2 id="page-title">Admin Panel</h2>

                <div class="user-info">
                    <div class="user-avatar">
                        {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                    </div>

                    <div>
                        <div>{{ Auth::user()->name }}</div>
                        <div style="font-size: 0.8rem; color: var(--gray);">
                            {{ Auth::user()->role ?? 'User' }}
                        </div>

                        <!-- Logout -->
                        <form action="{{ route('logout') }}" method="POST" style="margin-top: 5px;">
                            @csrf
                            <button type="submit" style="background:none;border:none;color:red;cursor:pointer;">
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>


            @yield('content')
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script src="{{ asset('public/assets/main.js') }}"></script>
</body>

</html>
