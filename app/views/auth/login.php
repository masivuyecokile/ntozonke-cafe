<?php
$csrfToken = $csrfToken ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= APP_NAME; ?> | Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="<?= BASE_URL; ?>/assets/css/app.css" rel="stylesheet">
</head>

<body class="login-body">

<div class="container-fluid min-vh-100">
    <div class="row min-vh-100">

        <div class="col-lg-6 d-none d-lg-flex login-brand-panel">
            <div class="brand-content">
                <img src="<?= BASE_URL; ?>/assets/img/logo.png" class="login-logo-large" alt="Ntozonke Logo">

                <h1 class="mt-4">Internet Cafe Management</h1>

                <p class="mt-3">
                    Manage PC sessions, printing approvals, memberships, sales, and café operations.
                </p>

                <div class="login-feature-list mt-4">
                    <div><i class="bi bi-pc-display-horizontal"></i> PC Lock & Session Control</div>
                    <div><i class="bi bi-printer"></i> Print Job Approval</div>
                    <div><i class="bi bi-graph-up-arrow"></i> Daily Profit Monitoring</div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 d-flex align-items-center justify-content-center login-form-panel">
            <div class="login-card">

                <div class="text-center mb-4">
                    <img src="<?= BASE_URL; ?>/assets/img/logo.png" class="login-logo" alt="Ntozonke Logo">
                    <h2 class="mt-3 mb-1">Internet Cafe Management</h2>
                    <p class="text-muted mb-0">Admin Login</p>
                </div>

                <form id="loginForm" action="<?= BASE_URL; ?>/index.php?route=auth.login" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken); ?>">

                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                            <input type="text" name="username" class="form-control" placeholder="Enter username" required autofocus>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" name="password" class="form-control" placeholder="Enter password" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success w-100 login-btn" id="loginBtn">
                        <i class="bi bi-box-arrow-in-right me-1"></i>
                        Login to Dashboard
                    </button>
                </form>

                <div class="login-footer text-center mt-4">
                    2026 &copy; Ntozonke Internet Cafe
                </div>

            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?= BASE_URL; ?>/assets/js/auth.js"></script>

</body>
</html>