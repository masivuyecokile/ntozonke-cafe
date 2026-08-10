<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

<main class="main-content">

    <div class="topbar">
        <div>
            <h4 class="mb-0">Settings</h4>
            <small class="text-muted">System settings will be expanded as the café software grows.</small>
        </div>

        <a href="<?= BASE_URL; ?>/index.php?route=dashboard" class="btn btn-outline-dark btn-sm">
            <i class="bi bi-grid me-1"></i>
            Dashboard
        </a>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mt-4">
        <div class="card-body p-5 text-center">
            <i class="bi bi-gear-fill display-4 text-success"></i>
            <h4 class="mt-3">Settings Module Coming Soon</h4>
            <p class="text-muted mb-0">
                This will later manage business details, receipt settings, PC rules, backup settings, and sync settings.
            </p>
        </div>
    </div>

</main>

<script>
    window.NTOZONKE = {
        baseUrl: "<?= BASE_URL; ?>",
        csrfToken: "<?= htmlspecialchars($csrfToken); ?>"
    };
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>