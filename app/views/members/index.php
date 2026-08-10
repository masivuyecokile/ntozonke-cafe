<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

<main class="main-content">

    <div class="topbar">
        <div>
            <h4 class="mb-0">Members</h4>
            <small class="text-muted">Membership management will be built after core sales and expenses.</small>
        </div>

        <a href="<?= BASE_URL; ?>/index.php?route=dashboard" class="btn btn-outline-dark btn-sm">
            <i class="bi bi-grid me-1"></i>
            Dashboard
        </a>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mt-4">
        <div class="card-body p-5 text-center">
            <i class="bi bi-people-fill display-4 text-success"></i>
            <h4 class="mt-3">Membership Module Coming Next</h4>
            <p class="text-muted mb-0">
                This will handle monthly members, internet hour credits, print credits, renewals, and expired memberships.
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