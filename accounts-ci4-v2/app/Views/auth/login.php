<?= $this->extend('layouts/auth') ?>
<?= $this->section('title') ?>Login<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="login-container">
    <!-- Left Side - Branding & Features -->
    <div class="login-left">
        <div class="logo">
            <div class="logo-icon">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="logo-text">Finance Manager</div>
        </div>

        <h1 class="welcome-text">
            Welcome to Your Complete Accounting Solution
        </h1>

        <ul class="features">
            <li>
                <i class="fas fa-money-bill-wave"></i>
                <span>Track expenses and income efficiently</span>
            </li>
            <li>
                <i class="fas fa-file-invoice"></i>
                <span>Manage invoices and payments</span>
            </li>
            <li>
                <i class="fas fa-balance-scale"></i>
                <span>Generate CA statements and reports</span>
            </li>
            <li>
                <i class="fas fa-tasks"></i>
                <span>Stay compliant with automated tasks</span>
            </li>
            <li>
                <i class="fas fa-chart-bar"></i>
                <span>Real-time financial insights</span>
            </li>
        </ul>
    </div>

    <!-- Right Side - Login Form -->
    <div class="login-right">
        <div class="login-header">
            <h2 class="login-title">Sign In</h2>
            <p class="login-subtitle">Enter your credentials to access your account</p>
        </div>

        <!-- Error/Success Messages -->
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <span><?= session()->getFlashdata('error') ?></span>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <span><?= session()->getFlashdata('success') ?></span>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('errors')): ?>
            <div class="alert alert-error">
                <div class="d-flex flex-column gap-1">
                    <?php foreach (session()->getFlashdata('errors') as $error): ?>
                        <span><i class="fas fa-exclamation-circle me-1"></i> <?= esc($error) ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <form action="<?= base_url('login') ?>" method="POST" id="login-form">
            <?= csrf_field() ?>
            
            <div class="form-group">
                <label for="email" class="form-label">Email Address</label>
                <div class="password-input">
                    <input type="email" id="email" name="email" class="form-control" 
                           value="<?= old('email') ?>" placeholder="Enter your email" required autofocus>
                    <div style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); color: #94a3b8;">
                        <i class="fas fa-envelope"></i>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <div class="password-input">
                    <input type="password" id="password" name="password" class="form-control" 
                           placeholder="Enter your password" required>
                    <button type="button" class="toggle-password" onclick="togglePassword()">
                        <i class="fas fa-eye" id="toggleIcon"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-primary" id="login-btn">
                <i class="fas fa-sign-in-alt me-2"></i>
                Sign In
            </button>
        </form>

        <div class="footer-links">
            <span>© <?= date('Y') ?> Infasta Soft Solutions</span>
            <div class="mt-2">
                <a href="#">Privacy</a>
                <a href="#">Terms</a>
                <a href="#">Support</a>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword() {
    const input = document.getElementById('password');
    const icon = document.getElementById('toggleIcon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}
</script>
<?= $this->section('styles') ?>
<style>
    /* Small tweaks if needed for content section */
</style>
<?= $this->endSection() ?>
<?= $this->endSection() ?>
