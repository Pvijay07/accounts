<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->renderSection('title') ?> - Finance Manager</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #3498db;
            --primary-dark: #2980b9;
            --secondary: #2c3e50;
            --success: #27ae60;
            --warning: #f39c12;
            --danger: #e74c3c;
            --light: #ecf0f1;
            --dark: #2c3e50;
            --gray: #95a5a6;
            --border: #bdc3c7;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Outfit', sans-serif; }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            display: flex;
            max-width: 1000px;
            width: 100%;
            background: white;
            border-radius: 20px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            overflow: hidden;
            min-height: 600px;
        }

        .login-left {
            flex: 1;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 60px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .login-left::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 200%;
            background: rgba(255, 255, 255, 0.1);
            transform: rotate(30deg);
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 40px;
            z-index: 1;
        }

        .logo-icon {
            font-size: 2.2rem;
            background: rgba(255, 255, 255, 0.2);
            padding: 12px;
            border-radius: 14px;
            backdrop-filter: blur(10px);
        }

        .logo-text { font-size: 1.8rem; font-weight: 700; }

        .welcome-text {
            font-size: 2.2rem;
            font-weight: 300;
            margin-bottom: 30px;
            line-height: 1.3;
            z-index: 1;
        }

        .features { list-style: none; z-index: 1; }

        .features li {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
            font-size: 1rem;
        }

        .features i {
            background: rgba(255, 255, 255, 0.2);
            padding: 8px;
            border-radius: 50%;
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-right {
            flex: 1;
            padding: 60px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: white;
        }

        .login-header { text-align: center; margin-bottom: 40px; }

        .login-title {
            font-size: 2rem;
            color: var(--secondary);
            margin-bottom: 10px;
            font-weight: 700;
        }

        .login-subtitle { color: var(--gray); font-size: 0.95rem; }

        .form-group { margin-bottom: 25px; }

        .form-label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            color: var(--secondary);
            font-size: 0.9rem;
        }

        .form-control {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #eef2f7;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f8fafc;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            background: white;
            box-shadow: 0 0 0 4px rgba(52, 152, 219, 0.1);
        }

        .password-input { position: relative; }

        .toggle-password {
            position: absolute; right: 15px; top: 50%;
            transform: translateY(-50%);
            background: none; border: none;
            color: #94a3b8; cursor: pointer;
        }

        .btn-primary {
            width: 100%;
            padding: 16px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.2);
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 20px 25px -5px rgba(37, 99, 235, 0.3);
        }

        .footer-links {
            text-align: center;
            margin-top: 40px;
            font-size: 0.85rem;
            color: var(--gray);
        }

        .footer-links a { color: var(--primary); text-decoration: none; margin: 0 10px; }

        .alert {
            padding: 16px; border-radius: 12px; margin-bottom: 25px;
            font-size: 0.9rem; display: flex; align-items: center; gap: 10px;
        }

        .alert-error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

        .alert-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }

        @media (max-width: 900px) {
            .login-container { max-width: 500px; flex-direction: column; }
            .login-left { display: none; }
        }
    </style>
</head>
<body>
    <?= $this->renderSection('content') ?>
</body>
</html>
