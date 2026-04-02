<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->renderSection('title') ?> - Auditor Portal</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #0891b2;
            --primary-hover: #0e7490;
            --secondary: #64748b;
            --bg-color: #f8fafc;
            --card-bg: rgba(255, 255, 255, 0.9);
            --border-radius: 20px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        body {
            background-color: var(--bg-color);
            background-image: radial-gradient(at 0% 0%, hsla(190,100%,90%,0.15) 0, transparent 50%);
            background-attachment: fixed;
            color: #1e293b;
            font-family: 'Outfit', sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        .auditor-frame {
            padding: 30px;
            min-height: 100vh;
        }

        .governance-header {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.4);
            border-radius: 24px;
            padding: 24px 32px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.03);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .governance-header h5 {
            font-weight: 800;
            margin-bottom: 4px;
            color: #0f172a;
            letter-spacing: -0.5px;
        }

        .nav-links {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .nav-btn {
            padding: 10px 20px;
            border-radius: 14px;
            font-weight: 600;
            font-size: 0.9rem;
            text-decoration: none;
            transition: var(--transition);
            border: 1px solid transparent;
        }

        .nav-btn-primary { background: #0891b2; color: white; box-shadow: 0 4px 12px rgba(8, 145, 178, 0.2); }
        .nav-btn-primary:hover { background: #0e7490; transform: translateY(-2px); }

        .nav-btn-outline { background: white; color: #64748b; border-color: #e2e8f0; }
        .nav-btn-outline:hover { background: #f8fafc; color: #0891b2; border-color: #0891b2; }

        .nav-btn.active { background: #0891b2; color: white; border-color: #0891b2; }

        /* Card System */
        .card {
            border: none;
            border-radius: var(--border-radius);
            box-shadow: 0 10px 25px rgba(0,0,0,0.03);
            background: white;
            overflow: hidden;
            transition: var(--transition);
        }

        /* Forms */
        .form-control, .form-select {
            border-radius: 12px;
            border: 2px solid #f1f5f9;
            padding: 12px 16px;
            background: #f8fafc;
            font-weight: 500;
        }

        .form-control:focus {
            border-color: #0891b2;
            background: white;
            box-shadow: 0 0 0 4px rgba(8, 145, 178, 0.1);
        }
    </style>
</head>
<body>
    <div class="auditor-frame">
        <div class="container-fluid">
            <div class="governance-header">
                <div>
                    <h5>Auditor Compliance Hub</h5>
                    <div class="text-muted small fw-medium">Secured Read-Only Forensic Access • Statutory Reporting Interface</div>
                </div>
                <div class="nav-links">
                    <a href="<?= base_url('ca/dashboard') ?>" class="nav-btn <?= current_url() == base_url('ca/dashboard') ? 'active' : 'nav-btn-outline' ?>">Overview</a>
                    <a href="<?= base_url('ca/statements') ?>" class="nav-btn <?= str_contains(current_url(), 'ca/statements') ? 'nav-btn-primary' : 'nav-btn-outline' ?>">Statements</a>
                    <a href="<?= base_url('ca/invoices') ?>" class="nav-btn <?= str_contains(current_url(), 'ca/invoices') ? 'nav-btn-primary' : 'nav-btn-outline' ?>">Invoices Registry</a>
                    <a href="<?= base_url('logout') ?>" class="nav-btn nav-btn-outline text-danger border-danger"><i class="fas fa-sign-out-alt me-1"></i> Exit</a>
                </div>
            </div>
            
            <?= $this->renderSection('content') ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?= $this->renderSection('scripts') ?>
</body>
</html>
