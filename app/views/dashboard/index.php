<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard | <?= APP_NAME; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container py-5">
    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <h3>Welcome, <?= htmlspecialchars($_SESSION['user_name'] ?? 'Admin'); ?></h3>
            <p class="text-muted mb-4">Dashboard loading successfully.</p>

            <a href="<?= BASE_URL; ?>/index.php?route=logout" class="btn btn-danger">
                Logout
            </a>
        </div>
    </div>
</div>

</body>
</html>