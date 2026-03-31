<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Finance Manager</title>
    <!-- Bootstrap CDN -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #3498db;
            --success: #27ae60;
            --warning: #f39c12;
            --danger: #e74c3c;
            --light: #ecf0f1;
            --dark: #2c3e50;
            --gray: #95a5a6;
            --border-radius: 8px;
            --box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f5f7fa;
            color: #333;
            line-height: 1.6;
        }

        .container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 250px;
            background-color: var(--primary);
            color: white;
            transition: var(--transition);
            box-shadow: var(--box-shadow);
            z-index: 100;
        }

        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sidebar-header h2 {
            font-size: 1.5rem;
            font-weight: 600;
        }

        .sidebar-menu {
            padding: 15px 0;
        }

        .menu-item {
            padding: 12px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            transition: var(--transition);
            border-left: 4px solid transparent;
            text-decoration: none;
            color: white;
        }

        .menu-item:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .menu-item.active {
            background-color: rgba(255, 255, 255, 0.15);
            border-left: 4px solid var(--secondary);
        }

        .menu-item i {
            width: 20px;
            text-align: center;
        }

        /* Main Content Styles */
        .main-content {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid #ddd;
        }

        .header h1 {
            color: var(--primary);
            font-weight: 600;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--secondary);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }

        /* Dashboard Grid */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }

        .card {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 20px;
            transition: var(--transition);
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        .card-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--primary);
        }

        .card-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }

        .card-icon.income {
            background-color: var(--success);
        }

        .card-icon.expense {
            background-color: var(--danger);
        }

        .card-icon.profit {
            background-color: var(--secondary);
        }

        .card-icon.upcoming {
            background-color: var(--warning);
        }

        .card-value {
            font-size: 1.8rem;
            font-weight: 700;
            margin: 10px 0;
        }

        .card-footer {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
            color: var(--gray);
            font-size: 0.9rem;
        }

        /* Table Styles */
        .table-container {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
            margin-bottom: 25px;
        }

        .table-header {
            padding: 15px 20px;
            background-color: var(--primary);
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .table-title {
            color: #fff !important;
            font-size: 1.2rem;
            font-weight: 600;
        }

        .table-actions {
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 5px;
            text-decoration: none;
        }

        .btn-primary {
            background-color: var(--secondary);
            color: white;
        }

        .btn-success {
            background-color: var(--success);
            color: white;
        }

        .btn-danger {
            background-color: var(--danger);
            color: white;
        }

        .btn-warning {
            background-color: var(--warning);
            color: white;
        }

        .btn-outline {
            background-color: transparent;
            border: 1px solid var(--gray);
            color: var(--dark);
        }

        .btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: var(--primary);
        }

        tr:hover {
            background-color: #f8f9fa;
        }

        .status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .status.upcoming {
            background-color: #fff3cd;
            color: #856404;
        }

        .status.pending {
            background-color: #cce7ff;
            color: #004085;
        }

        .status.overdue {
            background-color: #f8d7da;
            color: #721c24;
        }

        .status.paid {
            background-color: #d4edda;
            color: #155724;
        }

        /* Filter Bar */
        .filter-bar {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
            padding: 15px;
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .filter-label {
            font-size: 0.9rem;
            color: var(--gray);
        }

        select,
        input {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background-color: white;
        }

        /* Tabs */
        .tabs {
            display: flex;
            border-bottom: 1px solid #ddd;
            margin-bottom: 20px;
        }

        .tab {
            padding: 10px 20px;
            cursor: pointer;
            border-bottom: 3px solid transparent;
            transition: var(--transition);
        }

        .tab.active {
            border-bottom: 3px solid var(--secondary);
            color: var(--secondary);
            font-weight: 600;
        }

        /* Charts */
        .chart-container {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 20px;
            margin-bottom: 25px;
        }

        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .chart-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--primary);
        }

        .chart {
            height: 300px;
            background-color: #f8f9fa;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--gray);
        }

        /* Form Styles */
        .form-container {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 20px;
            margin-bottom: 25px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: var(--primary);
        }

        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }

        .form-row {
            display: flex;
            gap: 15px;
        }

        .form-row .form-group {
            flex: 1;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1100;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background-color: white;
            border-radius: var(--border-radius);
            
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        .modal-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--primary);
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--gray);
        }

        /* Page Styles */
        .page {
            display: none;
        }

        .page.active {
            display: block;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .container {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                height: auto;
            }

            .dashboard-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 768px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }

            .filter-bar {
                flex-direction: column;
            }

            .table-container {
                overflow-x: auto;
            }

            .form-row {
                flex-direction: column;
                gap: 0;
            }
        }

        .kpi .value {
            font-size: 1.35rem;
            font-weight: 700;
        }
    </style>
</head>

<body>
    <div class="d-flex">
        <div class="sidebar">
            <div class="sidebar-header">
                <i class="fas fa-chart-line"></i>
                <h2>Finance Manager</h2>
            </div>
            <div class="sidebar-menu">
                <a href="{{ route('manager.dashboard') }}"
                    class="menu-item {{ request()->routeIs('manager.dashboard') ? 'active' : '' }}"
                    data-page="dashboard">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('manager.expenses') }}"
                    class="menu-item {{ request()->routeIs('manager.expenses') ? 'active' : '' }}" data-page="expenses">
                    <i class="fas fa-money-bill-wave"></i>
                    <span>Expenses</span>
                </a>
                {{-- <a href="{{ route('income.upcoming') }}"
                    class="menu-item {{ request()->routeIs('income.upcoming') ? 'active' : '' }}"
                    data-page="upcoming-payments">
                    <i class="fas fa-calendar-day"></i>
                    <span>Upcoming Payments</span>
                </a> --}}
                <a href="{{ route('income.index') }}"
                    class="menu-item {{ request()->routeIs('income.index') ? 'active' : '' }}" data-page="income">
                    <i class="fas fa-hand-holding-usd"></i>
                    <span>Income</span>
                </a>
                <a href="{{ route('income.balance') }}"
                    class="menu-item {{ request()->routeIs('income.balance') ? 'active' : '' }}" data-page="balances">
                    <i class="fas fa-balance-scale"></i>
                    <span>Balances & Dues</span>
                </a>
                <a href="{{ route('manager.gst') }}"
                    class="menu-item {{ request()->routeIs('manager.gst') || request()->routeIs('manager.gst-collected') || request()->routeIs('manager.taxes') ? 'active' : '' }}" data-page="balances">
                    <i class="fas fa-balance-scale"></i>
                    <span>GST & Taxes</span>
                </a>
                 <a href="{{ route('manager.tds') }}"
                    class="menu-item {{ request()->routeIs('manager.tds') ? 'active' : '' }}"
                    data-page="upcoming-payments">
                    <i class="fas fa-calendar-day"></i>
                    <span>TDS</span>
                </a>
                  <a href="{{ route('manager.loans.index') }}"
                    class="menu-item {{ request()->routeIs('manager.loans.index') ? 'active' : '' }}"
                    data-page="upcoming-payments">
                    <i class="fas fa-calendar-day"></i>
                    <span>Advances/Loans</span>
                </a>
                <a href="{{ route('manager.reports') }}"
                    class="menu-item {{ request()->routeIs('manager.reports') ? 'active' : '' }}" data-page="reports">
                    <i class="fas fa-chart-bar"></i>
                    <span>Reports & Export</span>
                </a>
                <a href="#import" class="menu-item" data-page="import">
                    <i class="fas fa-file-import"></i>
                    <span>Import Data</span>
                </a>
                <a href="#notifications" class="menu-item" data-page="notifications">
                    <i class="fas fa-bell"></i>
                    <span>Notifications</span>
                </a>
            </div>
        </div>
        <!-- MAIN CONTENT -->
        <div class="main-content">

            <!-- Top Bar -->
            <div class="header">
                <h2 id="page-title">Manager Panel</h2>
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
    <script>
        // Page Navigation
        document.addEventListener('DOMContentLoaded', function() {
            // Menu item click handler
            const menuItems = document.querySelectorAll('.menu-item');
            const pages = document.querySelectorAll('.page');
            const pageTitle = document.getElementById('page-title');

            const tabs = document.querySelectorAll('.tab');
            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    const tabName = this.getAttribute('data-tab');

                    // Update active tab
                    tabs.forEach(t => t.classList.remove('active'));
                    this.classList.add('active');

                    // Show corresponding content
                    if (tabName === 'standard') {
                        document.getElementById('standard-expenses').style.display = 'block';
                        document.getElementById('non-standard-expenses').style.display = 'none';
                    } else if (tabName === 'non-standard') {
                        document.getElementById('standard-expenses').style.display = 'none';
                        document.getElementById('non-standard-expenses').style.display = 'block';
                    }

                    // For upcoming payments tabs
                    if (tabName === 'all' || tabName === 'debits' || tabName === 'credits' ||
                        tabName === 'overdue') {
                        // In a real app, you would filter the table here
                        console.log(`Switched to ${tabName} tab`);
                    }
                });
            });

            // Modal functionality
            const modal = document.getElementById('expense-modal');
            const addExpenseBtn = document.getElementById('add-expense-btn');
            const closeModalBtns = document.querySelectorAll('.close-modal');

            addExpenseBtn.addEventListener('click', function() {
                modal.style.display = 'flex';
            });

            closeModalBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    modal.style.display = 'none';
                });
            });

            window.addEventListener('click', function(e) {
                if (e.target === modal) {
                    modal.style.display = 'none';
                }
            });

            // Mark as Paid button functionality
            const markPaidButtons = document.querySelectorAll('.mark-paid-btn');
            markPaidButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const row = this.closest('tr');
                    const statusCell = row.querySelector('.status');
                    statusCell.textContent = 'Paid';
                    statusCell.className = 'status paid';
                    this.innerHTML = '<i class="fas fa-check"></i> Paid';
                    this.classList.remove('btn-success');
                    this.classList.add('btn-outline');
                    this.disabled = true;
                });
            });

            // Initialize Charts
            const profitLossCtx = document.getElementById('profitLossChart').getContext('2d');
            const profitLossChart = new Chart(profitLossCtx, {
                type: 'bar',
                data: {
                    labels: ['Company A', 'Company B', 'Company C'],
                    datasets: [{
                            label: 'Income',
                            data: [125000, 95000, 75000],
                            backgroundColor: '#27ae60'
                        },
                        {
                            label: 'Expenses',
                            data: [85000, 72000, 68000],
                            backgroundColor: '#e74c3c'
                        },
                        {
                            label: 'Net Profit',
                            data: [40000, 23000, 7000],
                            backgroundColor: '#3498db'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '₹' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });

            const detailedProfitLossCtx = document.getElementById('detailedProfitLossChart').getContext('2d');
            const detailedProfitLossChart = new Chart(detailedProfitLossCtx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [{
                            label: 'Income',
                            data: [220000, 240000, 245000, 260000, 255000, 270000],
                            borderColor: '#27ae60',
                            backgroundColor: 'rgba(39, 174, 96, 0.1)',
                            fill: true
                        },
                        {
                            label: 'Expenses',
                            data: [180000, 190000, 187000, 195000, 200000, 205000],
                            borderColor: '#e74c3c',
                            backgroundColor: 'rgba(231, 76, 60, 0.1)',
                            fill: true
                        },
                        {
                            label: 'Net Profit',
                            data: [40000, 50000, 58000, 65000, 55000, 65000],
                            borderColor: '#3498db',
                            backgroundColor: 'rgba(52, 152, 219, 0.1)',
                            fill: true
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '₹' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
</body>

</html>
