<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $this->renderSection('title') ?> - Finance Manager</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="base-url" content="<?= base_url() ?>">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --secondary: #10b981;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --light: #f8fafc;
            --dark: #0f172a;
            --gray: #64748b;
            --gray-light: #e2e8f0;
            --bg-color: #f1f5f9;
            --card-bg: rgba(255, 255, 255, 0.9);
            --sidebar-bg: #1e1b4b;
            --border-radius: 16px;
            --box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.01);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --glass-border: 1px solid rgba(255, 255, 255, 0.5);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Outfit', sans-serif;
        }

        body {
            background-color: var(--bg-color);
            background-image:
                radial-gradient(at 0% 0%, hsla(253,16%,7%,0.05) 0, transparent 50%),
                radial-gradient(at 50% 0%, hsla(225,39%,30%,0.05) 0, transparent 50%),
                radial-gradient(at 100% 0%, hsla(339,49%,30%,0.05) 0, transparent 50%);
            background-attachment: fixed;
            color: var(--dark);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }

        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, var(--sidebar-bg) 0%, #0f172a 100%);
            color: white;
            transition: var(--transition);
            box-shadow: 4px 0 24px rgba(0,0,0,0.1);
            z-index: 100;
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }

        .sidebar::-webkit-scrollbar { width: 4px; }
        .sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 4px; }

        .sidebar-header {
            padding: 30px 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            background: rgba(255,255,255,0.03);
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }

        .sidebar-header i {
            font-size: 28px;
            color: #818cf8;
            filter: drop-shadow(0 0 8px rgba(129, 140, 248, 0.5));
        }

        .sidebar-header h2 {
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0;
            background: linear-gradient(120deg, #e0e7ff, #818cf8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .sidebar-menu {
            padding: 24px 16px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .menu-item {
            padding: 14px 20px;
            display: flex;
            align-items: center;
            gap: 16px;
            cursor: pointer;
            transition: var(--transition);
            border-radius: 12px;
            text-decoration: none;
            color: #cbd5e1;
            font-weight: 500;
            font-size: 1.05rem;
            border: 1px solid transparent;
        }

        .menu-item:hover {
            background-color: rgba(255, 255, 255, 0.06);
            color: #fff;
            transform: translateX(4px);
        }

        .menu-item.active {
            background: linear-gradient(90deg, rgba(79, 70, 229, 0.2) 0%, rgba(79, 70, 229, 0) 100%);
            color: white;
            border-left: 4px solid #818cf8;
            border-radius: 0 12px 12px 0;
            box-shadow: inset 2px 0 10px rgba(129, 140, 248, 0.1);
        }

        .menu-item i {
            width: 24px;
            font-size: 1.2rem;
            text-align: center;
            transition: var(--transition);
        }

        .menu-item:hover i {
            color: #818cf8;
            transform: scale(1.1);
        }

        .main-content {
            flex: 1;
            padding: 30px 40px;
            margin-left: 280px;
            max-width: calc(100% - 280px);
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding: 20px 30px;
            background: rgba(255, 255, 255, 0.8) !important;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(0,0,0,0.05) !important;
            border-radius: 0 0 20px 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.02);
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .header h2 {
            color: var(--dark);
            font-weight: 700;
            margin: 0;
            font-size: 1.5rem;
            letter-spacing: -0.5px;
        }

        .user-info {
            background: #f8fafc;
            padding: 8px 16px 8px 8px;
            border-radius: 50px;
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
        }

        .user-info:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            border-color: #cbd5e1;
        }

        .user-avatar {
            background: linear-gradient(135deg, #3b82f6, #8b5cf6) !important;
            box-shadow: 0 4px 10px rgba(59, 130, 246, 0.3);
            font-weight: 700 !important;
            letter-spacing: 1px;
            width: 42px !important;
            height: 42px !important;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }

        .card {
            background: var(--card-bg);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            border: var(--glass-border);
            padding: 24px;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.1);
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.95rem;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            letter-spacing: 0.3px;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), #6366f1);
            color: white;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
        }

        .btn-success { background: linear-gradient(135deg, #10b981, #059669); color: white; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3); }
        .btn-danger { background: linear-gradient(135deg, #ef4444, #dc2626); color: white; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3); }
        .btn-warning { background: linear-gradient(135deg, #f59e0b, #d97706); color: white; box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3); }
        .btn-outline {
            background: rgba(255, 255, 255, 0.5);
            border: 1px solid var(--gray-light);
            color: var(--dark);
            backdrop-filter: blur(4px);
        }

        .btn:hover {
            transform: translateY(-2px);
            filter: brightness(1.05);
        }

        .form-control,
        .form-select,
        select,
        input,
        textarea {
            padding: 12px 16px;
            border: 1px solid var(--gray-light);
            border-radius: 10px;
            background-color: rgba(255, 255, 255, 0.85);
            font-size: 0.95rem;
            font-weight: 500;
            color: var(--dark);
            transition: var(--transition);
            outline: none;
        }

        .form-control:focus,
        .form-select:focus,
        select:focus,
        input:focus,
        textarea:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2);
            background-color: #fff;
        }

        .table-responsive {
            border-radius: 16px;
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        th,
        td {
            padding: 16px 24px;
            text-align: left;
            border-bottom: 1px solid var(--gray-light);
            vertical-align: middle;
        }

        th {
            background-color: rgba(248, 250, 252, 0.6);
            font-weight: 600;
            color: var(--gray);
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        tr:last-child td { border-bottom: none; }
        tr:hover td { background-color: rgba(241, 245, 249, 0.55); }

        .badge,
        .status {
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-block;
            backdrop-filter: blur(4px);
        }

        .status.upcoming { background-color: rgba(245, 158, 11, 0.15); color: #d97706; border: 1px solid rgba(245, 158, 11, 0.2); }
        .status.pending { background-color: rgba(59, 130, 246, 0.15); color: #2563eb; border: 1px solid rgba(59, 130, 246, 0.2); }
        .status.overdue { background-color: rgba(239, 68, 68, 0.15); color: #dc2626; border: 1px solid rgba(239, 68, 68, 0.2); }
        .status.outstanding { background-color: rgba(59, 130, 246, 0.15); color: #2563eb; border: 1px solid rgba(59, 130, 246, 0.2); }
        .status.partially_recovered { background-color: rgba(245, 158, 11, 0.15); color: #d97706; border: 1px solid rgba(245, 158, 11, 0.2); }
        .status.paid,
        .status.recovered,
        .status.received,
        .status.active { background-color: rgba(16, 185, 129, 0.15); color: #059669; border: 1px solid rgba(16, 185, 129, 0.2); }
        .status.inactive { background-color: rgba(239, 68, 68, 0.15); color: #dc2626; border: 1px solid rgba(239, 68, 68, 0.2); }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(15, 23, 42, 0.55);
            backdrop-filter: blur(4px);
            z-index: 1050;
            align-items: center;
            justify-content: center;
        }

        .modal .modal-content {
            background: white;
            border-radius: 20px;
            width: 90%;
            max-width: 720px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.15);
        }

        .modal .modal-header {
            padding: 24px 32px;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal .modal-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1e293b;
        }

        .modal .close-modal {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: #94a3b8;
            cursor: pointer;
            padding: 4px 8px;
            border-radius: 8px;
            transition: all 0.2s ease;
        }

        .modal .close-modal:hover {
            background: #f1f5f9;
            color: #475569;
        }

        .table-container,
        .filter-bar,
        .filter-section {
            background: var(--card-bg);
            backdrop-filter: blur(16px);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            border: var(--glass-border);
            padding: 24px;
            margin-bottom: 24px;
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin-bottom: 20px;
        }

        .table-title {
            color: var(--dark);
            font-size: 1.35rem;
            font-weight: 700;
        }

        .table-actions,
        .action-buttons {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .search-container {
            position: relative;
            display: flex;
            align-items: center;
        }

        .search-icon {
            position: absolute;
            left: 14px;
            color: #94a3b8;
            font-size: 0.9rem;
            z-index: 2;
        }

        .search-input {
            padding: 10px 16px 10px 40px !important;
            border: 2px solid #e2e8f0 !important;
            border-radius: 12px !important;
            background: #f8fafc !important;
            font-weight: 500;
            width: 280px;
            transition: all 0.25s ease;
        }

        .filter-buttons {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-bottom: 12px;
        }

        .filter-btn {
            padding: 8px 16px;
            border-radius: 8px;
            border: 2px solid #e2e8f0;
            background: white;
            color: #64748b;
            font-weight: 600;
            font-size: 0.85rem;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .filter-btn:hover { background: #f1f5f9; color: #334155; }
        .filter-btn.active { background: #3b82f6; color: white; border-color: #3b82f6; }

        .pagination-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 0 0;
            margin-top: 16px;
            border-top: 1px solid #f1f5f9;
        }

        .pagination-info {
            color: #64748b;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .hint-text {
            font-size: 0.8rem;
            color: #94a3b8;
            margin-top: 6px;
        }

        .required { color: #ef4444; }

        .spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255,255,255,0.35);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 0.6s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        @media (max-width: 992px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .main-content {
                margin-left: 0;
                max-width: 100%;
                padding: 20px;
            }
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
                <a href="<?= base_url('manager/dashboard') ?>" class="menu-item <?= uri_string() === 'manager/dashboard' ? 'active' : '' ?>">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                <a href="<?= base_url('manager/expenses') ?>" class="menu-item <?= str_contains(uri_string(), 'manager/expenses') ? 'active' : '' ?>">
                    <i class="fas fa-money-bill-wave"></i>
                    <span>Expenses</span>
                </a>
                <a href="<?= base_url('manager/income') ?>" class="menu-item <?= str_contains(uri_string(), 'manager/income') ? 'active' : '' ?>">
                    <i class="fas fa-hand-holding-usd"></i>
                    <span>Income</span>
                </a>
                <a href="<?= base_url('manager/gst') ?>" class="menu-item <?= str_contains(uri_string(), 'manager/gst') ? 'active' : '' ?>">
                    <i class="fas fa-balance-scale"></i>
                    <span>GST & Taxes</span>
                </a>
                <a href="<?= base_url('manager/tds') ?>" class="menu-item <?= str_contains(uri_string(), 'manager/tds') ? 'active' : '' ?>">
                    <i class="fas fa-file-invoice-dollar"></i>
                    <span>TDS</span>
                </a>
                <a href="<?= base_url('manager/loans') ?>" class="menu-item <?= str_contains(uri_string(), 'manager/loans') ? 'active' : '' ?>">
                    <i class="fas fa-coins"></i>
                    <span>Advances/Loans</span>
                </a>
            </div>
        </div>

        <div class="main-content">
            <div class="header">
                <h2><?= $this->renderSection('page_title') ?></h2>

                <div class="dropdown">
                    <div class="user-info" data-bs-toggle="dropdown" aria-expanded="false" style="cursor:pointer;">
                        <div class="d-flex align-items-center">
                            <div class="user-avatar me-2">
                                <?= strtoupper(substr(session()->get('user_name') ?? 'MN', 0, 2)) ?>
                            </div>
                            <div class="text-start pe-2">
                                <div style="font-weight:700; color:#1e293b; font-size:0.95rem; line-height:1.2;">
                                    <?= session()->get('user_name') ?? 'Manager' ?>
                                </div>
                                <div style="font-size:0.8rem; color:#64748b; font-weight:500;">
                                    <?= ucfirst(session()->get('user_role') ?? 'manager') ?>
                                </div>
                            </div>
                            <i class="fas fa-chevron-down ms-1" style="color:#94a3b8; font-size:0.8rem;"></i>
                        </div>
                    </div>

                    <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="border-radius:12px; border:1px solid #e2e8f0; min-width:220px; padding:8px 0; margin-top:10px !important;">
                        <li>
                            <div class="dropdown-item-text px-3 py-2">
                                <strong class="d-block text-dark"><?= session()->get('user_name') ?? 'Manager' ?></strong>
                                <small class="text-muted"><?= session()->get('user_email') ?? ucfirst(session()->get('user_role') ?? 'manager') ?></small>
                            </div>
                        </li>
                        <li><hr class="dropdown-divider" style="border-color:#f1f5f9;"></li>
                        <li>
                            <a class="dropdown-item py-2 px-3 text-danger d-flex align-items-center bg-transparent" href="<?= base_url('logout') ?>" style="cursor:pointer;" onmouseover="this.style.backgroundColor='#fef2f2';" onmouseout="this.style.backgroundColor='transparent';">
                                <i class="fas fa-sign-out-alt me-2"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <?= $this->renderSection('content') ?>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <?php if (session()->getFlashdata('success')): ?>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: '<?= session()->getFlashdata('success') ?>',
                timer: 3000,
                showConfirmButton: false,
            });
        </script>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: '<?= session()->getFlashdata('error') ?>',
                confirmButtonColor: '#3b82f6',
            });
        </script>
    <?php endif; ?>

    <?= $this->renderSection('scripts') ?>
</body>
</html>
