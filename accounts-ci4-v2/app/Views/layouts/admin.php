<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finance Admin - Accounting Dashboard</title>
    <meta name="base-url" content="<?= base_url() ?>">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
    input[type=number]::-webkit-inner-spin-button,
    input[type=number]::-webkit-outer-spin-button {
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        margin: 0;
    }

    /* Premium Layout & Typography */
    @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap');

    body {
        background-color: #f4f7f6;
        font-family: 'Outfit', sans-serif;
        color: #1e293b;
        -webkit-font-smoothing: antialiased;
    }

    /* Glassmorphism Sidebar */
    .sidebar {
        width: 280px;
        height: 100vh;
        position: fixed;
        left: 0;
        top: 0;
        background: linear-gradient(145deg, #1e293b, #0f172a) !important;
        box-shadow: 4px 0 25px rgba(0, 0, 0, 0.1);
        color: white !important;
        border-right: 1px solid rgba(255, 255, 255, 0.05);
        z-index: 1000;
        overflow-y: auto;
    }

    .sidebar-header {
        background: transparent !important;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
        padding: 25px 20px !important;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .sidebar-header h2 {
        font-weight: 700;
        letter-spacing: 0.5px;
        background: linear-gradient(to right, #60a5fa, #c084fc);
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
        font-size: 1.4rem !important;
        margin: 0;
    }

    .sidebar-header i {
        color: #60a5fa;
        font-size: 1.5rem;
    }

    .sidebar-menu {
        padding: 20px 10px !important;
    }

    /* Premium Sidebar Links */
    .menu-item {
        display: flex;
        align-items: center;
        gap: 12px;
        border-radius: 12px;
        margin-bottom: 8px;
        padding: 14px 20px !important;
        color: rgba(255, 255, 255, 0.7) !important;
        text-decoration: none;
        font-weight: 500;
        letter-spacing: 0.3px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
        border-left: none !important;
    }

    .menu-item i {
        color: rgba(255, 255, 255, 0.5);
        font-size: 1.1rem;
        width: 20px;
        text-align: center;
        transition: all 0.3s ease;
    }

    .menu-item:hover {
        background: rgba(255, 255, 255, 0.06) !important;
        color: #ffffff !important;
        transform: translateX(5px);
    }

    .menu-item:hover i {
        color: #60a5fa;
        transform: scale(1.1);
    }

    .menu-item.active {
        background: linear-gradient(90deg, rgba(59, 130, 246, 0.2), transparent) !important;
        color: #ffffff !important;
        position: relative;
    }

    .menu-item.active::before {
        content: '';
        position: absolute;
        left: 0;
        top: 50%;
        transform: translateY(-50%);
        height: 60%;
        width: 4px;
        background: #3b82f6;
        border-radius: 0 4px 4px 0;
        box-shadow: 0 0 10px rgba(59, 130, 246, 0.5);
    }

    .menu-item.active i {
        color: #3b82f6;
    }

    /* Beautiful Header */
    .header {
        background: rgba(255, 255, 255, 0.8) !important;
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border-bottom: 1px solid rgba(0, 0, 0, 0.05) !important;
        padding: 20px 30px !important;
        border-radius: 0 0 20px 20px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
        margin-bottom: 30px !important;
        position: sticky;
        top: 0;
        z-index: 50;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .header #page-title {
        font-weight: 700;
        color: #1e293b;
        font-size: 1.5rem;
        letter-spacing: -0.5px;
        margin: 0;
    }

    /* Modern User Profile area */
    .user-info {
        background: #f8fafc;
        padding: 8px 16px 8px 8px;
        border-radius: 50px;
        border: 1px solid #e2e8f0;
        transition: all 0.3s ease;
    }

    .user-info:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        border-color: #cbd5e1;
    }

    .user-avatar {
        background: linear-gradient(135deg, #3b82f6, #8b5cf6) !important;
        box-shadow: 0 4px 10px rgba(59, 130, 246, 0.3);
        font-weight: 700 !important;
        font-family: 'Outfit', sans-serif;
        letter-spacing: 1px;
        width: 42px !important;
        height: 42px !important;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
    }

    .user-info>div>div:first-child {
        font-weight: 700;
        color: #1e293b;
        font-size: 0.95rem;
    }

    .user-info>div>div:nth-child(2) {
        color: #64748b !important;
        font-weight: 500;
    }

    /* Custom minimal scrollbar */
    ::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }

    ::-webkit-scrollbar-track {
        background: transparent;
    }

    ::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 10px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }

    /* Main Content Area adjustments */
    .main-content {
        margin-left: 280px;
        background-color: transparent !important;
        padding: 0 30px 30px !important;
    }

    /* ========================================================================= */
    /* PREMIUM GLOBAL FORMS & TABLES UI/UX                                       */
    /* ========================================================================= */

    /* General Card Design */
    .card {
        border: none !important;
        border-radius: 20px !important;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.04) !important;
        background: #ffffff !important;
        overflow: hidden;
        margin-bottom: 1.5rem;
    }

    .card-header {
        background: transparent !important;
        border-bottom: 1px solid #f1f5f9 !important;
        padding: 24px 32px !important;
        font-size: 1.2rem;
        font-weight: 700;
        color: #1e293b;
    }

    .card-body {
        padding: 32px !important;
    }

    /* Enhanced Forms Inputs */
    .form-label,
    label {
        font-weight: 600;
        color: #475569;
        margin-bottom: 10px;
        font-size: 0.95rem;
        letter-spacing: 0.3px;
    }

    .form-control,
    .form-select,
    select.form-control,
    input[type="text"],
    input[type="number"],
    input[type="email"],
    input[type="password"] {
        border-radius: 12px !important;
        border: 2px solid #e2e8f0 !important;
        padding: 12px 16px !important;
        transition: all 0.25s ease !important;
        background-color: #f8fafc !important;
        color: #1e293b !important;
        font-weight: 500;
        box-shadow: none !important;
    }

    .form-control:focus,
    .form-select:focus,
    select.form-control:focus,
    input:focus {
        background-color: #ffffff !important;
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15) !important;
        outline: none !important;
    }

    /* Fix for overlapping icons in inputs */
    .input-with-icon .form-control,
    .select-with-icon .form-control,
    .textarea-with-icon .form-control {
        padding-left: 45px !important;
    }

    .input-with-icon,
    .select-with-icon,
    .textarea-with-icon {
        position: relative;
    }

    .input-with-icon .input-icon,
    .select-with-icon .select-icon,
    .textarea-with-icon .textarea-icon {
        color: #64748b !important;
        font-size: 1.1rem;
        z-index: 5;
        position: absolute;
        left: 16px;
        top: 50%;
        transform: translateY(-50%);
        pointer-events: none;
    }

    .textarea-with-icon .textarea-icon {
        top: 16px;
        transform: none;
    }

    /* Global Buttons Overhaul */
    .btn {
        border-radius: 10px !important;
        padding: 10px 20px !important;
        font-weight: 600 !important;
        letter-spacing: 0.4px;
        transition: all 0.3s ease !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .btn-primary {
        background: linear-gradient(135deg, #2563eb, #3b82f6) !important;
        border: none !important;
        color: white !important;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3) !important;
    }

    .btn-primary:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 8px 20px rgba(37, 99, 235, 0.4) !important;
        background: linear-gradient(135deg, #1d4ed8, #2563eb) !important;
    }

    .btn-success {
        background: linear-gradient(135deg, #10b981, #34d399) !important;
        border: none !important;
        color: white !important;
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3) !important;
    }

    .btn-success:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 8px 20px rgba(16, 185, 129, 0.4) !important;
    }

    .btn-danger {
        background: linear-gradient(135deg, #ef4444, #f87171) !important;
        border: none !important;
        color: white !important;
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3) !important;
    }

    .btn-danger:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 8px 20px rgba(239, 68, 68, 0.4) !important;
    }

    .btn-outline {
        background: white !important;
        border: 2px solid #e2e8f0 !important;
        color: #475569 !important;
    }

    .btn-outline:hover {
        background: #f8fafc !important;
        border-color: #cbd5e1 !important;
        transform: translateY(-2px) !important;
    }

    /* Premium Tailwind-inspired Tables */
    .table-responsive {
        border-radius: 16px;
        overflow: hidden;
        border: 1px solid #e2e8f0;
        margin-top: 15px;
    }

    .table {
        margin-bottom: 0 !important;
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .table th,
    .table td {
        border-top: none;
        border-bottom: 1px solid #f1f5f9;
    }

    .table th {
        background-color: #f8fafc !important;
        color: #475569;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 0.5px;
        padding: 16px 20px !important;
        border-bottom: 2px solid #e2e8f0 !important;
        vertical-align: middle;
    }

    .table td {
        padding: 18px 20px !important;
        vertical-align: middle;
        color: #1e293b;
        font-weight: 500;
        transition: background-color 0.2s ease;
    }

    .table tbody tr {
        transition: all 0.2s ease;
    }

    .table tbody tr:hover {
        background-color: #f8fafc !important;
        transform: scale(1.002);
    }

    .table tbody tr:last-child td {
        border-bottom: none;
    }

    /* Custom Checkboxes */
    .form-check-input {
        width: 1.25em !important;
        height: 1.25em !important;
        border: 2px solid #cbd5e1 !important;
        cursor: pointer;
        box-shadow: none !important;
    }

    .form-check-input:checked {
        background-color: #3b82f6 !important;
        border-color: #3b82f6 !important;
    }

    .form-check-label {
        cursor: pointer;
        font-weight: 500;
        color: #334155;
        margin-left: 6px;
    }

    /* Input Groups (e.g., currency symbols prefix) */
    .input-group-text {
        border-radius: 12px !important;
        background-color: #f1f5f9 !important;
        border: 2px solid #e2e8f0 !important;
        border-right: none !important;
        color: #64748b !important;
        font-weight: 600 !important;
        padding: 0 18px !important;
    }

    .input-group>.form-control {
        border-left: none !important;
        padding-left: 0 !important;
    }

    .input-group:focus-within .input-group-text {
        border-color: #3b82f6 !important;
    }

    /* Badges */
    .badge {
        padding: 6px 12px !important;
        border-radius: 50px !important;
        font-weight: 600 !important;
        letter-spacing: 0.3px !important;
    }

    /* Table Container */
    .table-container {
        background: white;
        border-radius: 20px;
        padding: 24px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.04);
        border: 1px solid rgba(226, 232, 240, 0.8);
        margin-bottom: 24px;
    }

    .table-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
    }

    .table-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: #1e293b;
    }

    .table-actions {
        display: flex;
        gap: 12px;
        align-items: center;
    }

    /* Status badges */
    .status {
        padding: 6px 14px;
        border-radius: 50px;
        font-size: 0.8rem;
        font-weight: 600;
        display: inline-block;
    }

    .status.active {
        background-color: #dcfce7;
        color: #16a34a;
    }

    .status.inactive {
        background-color: #fee2e2;
        color: #dc2626;
    }

    /* Search container */
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

    .search-input:focus {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15) !important;
        background: white !important;
    }

    /* Filter container */
    .filter-container {
        margin-bottom: 16px;
    }

    .filter-buttons {
        display: flex;
        gap: 8px;
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

    .filter-btn:hover {
        background: #f1f5f9;
        color: #334155;
    }

    .filter-btn.active {
        background: #3b82f6;
        color: white;
        border-color: #3b82f6;
    }

    .filter-results {
        display: flex;
        align-items: center;
        gap: 12px;
        color: #64748b;
        font-size: 0.9rem;
    }

    .clear-filters-btn {
        color: #ef4444;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.85rem;
    }

    /* Pagination */
    .pagination-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 16px 0;
        margin-top: 16px;
        border-top: 1px solid #f1f5f9;
    }

    .pagination-info {
        color: #64748b;
        font-size: 0.9rem;
        font-weight: 500;
    }

    /* Modal overrides */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(15, 23, 42, 0.5);
        backdrop-filter: blur(4px);
        z-index: 1050;
        align-items: center;
        justify-content: center;
    }

    .modal .modal-content {
        background: white;
        border-radius: 20px;
        width: 90%;
        max-width: 600px;
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

    /* Form Groups */
    .form-group {
        margin-bottom: 20px;
    }

    .form-label-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .char-count {
        font-size: 0.8rem;
        color: #94a3b8;
        font-weight: 500;
    }

    .hint-text {
        font-size: 0.8rem;
        color: #94a3b8;
        margin-top: 6px;
    }

    .error-message {
        color: #ef4444;
        font-size: 0.8rem;
        margin-top: 6px;
        display: none;
    }

    .error-message.show {
        display: block;
    }

    .error-border {
        border-color: #ef4444 !important;
    }

    .required {
        color: #ef4444;
    }

    .form-row {
        display: flex;
        gap: 20px;
    }

    .half-width {
        flex: 1;
    }

    /* Select arrows */
    .select-with-icon {
        position: relative;
    }

    .select-arrow {
        position: absolute;
        right: 16px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        pointer-events: none;
        font-size: 0.8rem;
    }

    /* Notification */
    .notification {
        position: fixed;
        top: 30px;
        right: 30px;
        padding: 16px 24px;
        border-radius: 12px;
        color: white;
        font-weight: 600;
        z-index: 2000;
        display: flex;
        align-items: center;
        gap: 12px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        animation: slideInRight 0.3s ease;
    }

    .notification.success {
        background: linear-gradient(135deg, #10b981, #34d399);
    }

    .notification.error {
        background: linear-gradient(135deg, #ef4444, #f87171);
    }

    .notification-close {
        background: none;
        border: none;
        color: white;
        cursor: pointer;
        opacity: 0.7;
        padding: 0;
    }

    @keyframes slideInRight {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }

    /* Action buttons */
    .action-buttons {
        display: flex;
        gap: 5px;
    }

    .btn-icon {
        width: 32px;
        height: 32px;
        padding: 0 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px !important;
    }

    /* Sortable headers */
    .sortable-header {
        color: inherit;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .sortable-header:hover {
        color: #3b82f6;
    }

    /* Spinner */
    .spinner {
        display: inline-block;
        width: 16px;
        height: 16px;
        border: 2px solid rgba(255,255,255,0.3);
        border-radius: 50%;
        border-top-color: white;
        animation: spin 0.6s linear infinite;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    /* Responsive */
    @media (max-width: 768px) {
        .sidebar {
            width: 70px;
        }
        .sidebar-header h2,
        .sidebar-menu span {
            display: none;
        }
        .main-content {
            margin-left: 70px;
        }
    }
</style>
</head>

<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <i class="fas fa-cogs"></i>
                <h2>Finance Admin</h2>
            </div>
            <div class="sidebar-menu">
                <a href="<?= base_url('admin/dashboard') ?>" class="menu-item <?= (uri_string() == 'admin/dashboard' || uri_string() == 'admin') ? 'active' : '' ?>" data-page="dashboard">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                <a href="<?= base_url('admin/companies') ?>" class="menu-item <?= (strpos(uri_string(), 'admin/companies') !== false) ? 'active' : '' ?>" data-page="company-management">
                    <i class="fas fa-building"></i>
                    <span>Company Management</span>
                </a>
                <a href="<?= base_url('admin/standard-expenses') ?>" class="menu-item <?= (strpos(uri_string(), 'admin/standard-expenses') !== false) ? 'active' : '' ?>" data-page="standard-expenses">
                    <i class="fas fa-money-bill-wave"></i>
                    <span>Standard Expenses</span>
                </a>
                <a href="<?= base_url('admin/invoices') ?>" class="menu-item <?= (strpos(uri_string(), 'admin/invoices') !== false) ? 'active' : '' ?>" data-page="invoices">
                    <i class="fas fa-file-invoice"></i>
                    <span>Invoice Management</span>
                </a>
                <a href="<?= base_url('admin/users') ?>" class="menu-item <?= (strpos(uri_string(), 'admin/users') !== false) ? 'active' : '' ?>" data-page="user-management">
                    <i class="fas fa-users"></i>
                    <span>User Management</span>
                </a>
                <a href="<?= base_url('admin/system-settings') ?>" class="menu-item <?= (strpos(uri_string(), 'admin/system-settings') !== false) ? 'active' : '' ?>" data-page="system-settings">
                    <i class="fas fa-cog"></i>
                    <span>System Settings</span>
                </a>
                <a href="<?= base_url('admin/activity-logs') ?>" class="menu-item <?= (strpos(uri_string(), 'admin/activity-logs') !== false) ? 'active' : '' ?>" data-page="audit-logs">
                    <i class="fas fa-history"></i>
                    <span>Audit Logs</span>
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Header -->
            <div class="header">
                <h2 id="page-title"><?= $this->renderSection('page_title') ?></h2>

                <div class="dropdown d-inline-block">
                    <div class="user-info" style="padding: 6px 16px 6px 6px; cursor: pointer; transition: background 0.2s;" data-bs-toggle="dropdown" aria-expanded="false" onmouseover="this.style.background='#e2e8f0';" onmouseout="this.style.background='#f8fafc';">
                        <div class="d-flex align-items-center">
                            <div class="user-avatar me-2" style="width: 40px; height: 40px; font-size: 1rem; border-radius: 50%; background: linear-gradient(135deg, #3b82f6, #8b5cf6); color: white; display:flex; align-items:center; justify-content:center; box-shadow: 0 4px 10px rgba(59, 130, 246, 0.3);">
                                <?= strtoupper(substr(session()->get('user_name') ?? 'AD', 0, 2)) ?>
                            </div>
                            <div class="text-start pe-2 d-none d-md-block">
                                <div style="font-weight: 700; color: #1e293b; font-size: 0.95rem; line-height: 1.2;"><?= session()->get('user_name') ?? 'Administrator' ?></div>
                                <div style="font-size: 0.8rem; color: #64748b; font-weight: 500;">
                                    <?= ucfirst(session()->get('user_role') ?? 'Admin') ?>
                                </div>
                            </div>
                            <i class="fas fa-chevron-down ms-1" style="color: #94a3b8; font-size: 0.8rem;"></i>
                        </div>
                    </div>

                    <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0" style="border-radius: 16px; min-width: 240px; padding: 12px 0; margin-top: 10px; animation: fadeIn 0.2s ease;">
                        <li>
                            <div class="dropdown-item-text px-4 py-3 bg-light rounded-top text-center mx-2 mb-2">
                                <strong class="d-block text-dark fw-bold" style="font-size:1.1rem;"><?= session()->get('user_name') ?? 'Admin User' ?></strong>
                                <span class="badge bg-primary rounded-pill mt-1 px-3"><?= session()->get('user_email') ?? ucfirst(session()->get('user_role') ?? 'Admin') ?></span>
                            </div>
                        </li>
                        <li>
                            <a class="dropdown-item py-2 px-4 text-secondary d-flex align-items-center" href="<?= base_url('admin/system-settings') ?>" onmouseover="this.style.backgroundColor='#f1f5f9'; this.style.color='#3b82f6'" onmouseout="this.style.backgroundColor='transparent'; this.style.color='#6c757d'">
                                <div style="width:28px;"><i class="fas fa-cog"></i></div> Settings
                            </a>
                        </li>
                        <li><hr class="dropdown-divider mx-3 my-2" style="border-color: #f1f5f9;"></li>
                        <li>
                            <a class="dropdown-item py-2 px-4 text-danger d-flex align-items-center" href="<?= base_url('logout') ?>" onmouseover="this.style.backgroundColor='#fef2f2';" onmouseout="this.style.backgroundColor='transparent';">
                                <div style="width:28px;"><i class="fas fa-sign-out-alt"></i></div> Logout
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
            confirmButtonColor: '#3b82f6',
            timer: 3000,
            showConfirmButton: false
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
