<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

<main class="main-content">

    <div class="topbar">
        <div>
            <h4 class="mb-0">PC Stations</h4>
            <small class="text-muted">View all café computers and their current session status.</small>
        </div>

        <a href="<?= BASE_URL; ?>/index.php?route=dashboard" class="btn btn-outline-dark btn-sm">
            <i class="bi bi-grid me-1"></i>
            Dashboard
        </a>
    </div>

    <div class="section-heading mt-4">
        <h5>Computer Stations</h5>
        <p>Operational status of all PCs in the café.</p>
    </div>

    <div class="row g-3">
        <?php foreach ($pcs as $pc): ?>
            <?php
                $activeSession = $activeSessionsByPc[$pc->id] ?? null;

                $statusClass = match ($pc->status) {
                    'active' => 'success',
                    'locked' => 'secondary',
                    'offline' => 'danger',
                    'maintenance' => 'warning',
                    default => 'dark'
                };
            ?>

            <div class="col-md-6 col-xl-4">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h5 class="fw-bold mb-1"><?= htmlspecialchars($pc->pc_name); ?></h5>
                                <small class="text-muted">
                                    <?= $pc->ip_address ? htmlspecialchars($pc->ip_address) : 'No IP assigned'; ?>
                                </small>
                            </div>

                            <span class="badge bg-<?= $statusClass; ?>">
                                <?= ucfirst($pc->status); ?>
                            </span>
                        </div>

                        <hr>

                        <?php if ($activeSession): ?>
                            <p class="mb-1">
                                <strong>Customer:</strong>
                                <?= htmlspecialchars($activeSession->customer_name); ?>
                            </p>

                            <p class="mb-1">
                                <strong>Minutes:</strong>
                                <?= (int)$activeSession->minutes_purchased + (int)$activeSession->extended_minutes; ?> min
                            </p>

                            <p class="mb-1">
                                <strong>Amount:</strong>
                                R<?= number_format((float)$activeSession->amount_due, 2); ?>
                            </p>

                            <p class="mb-0">
                                <strong>Ends:</strong>
                                <?= date('H:i', strtotime($activeSession->end_time)); ?>
                            </p>
                        <?php else: ?>
                            <p class="text-muted mb-0">No active session on this PC.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        <?php endforeach; ?>
    </div>

</main>

<script>
    window.NTOZONKE = {
        baseUrl: "<?= BASE_URL; ?>",
        csrfToken: "<?= htmlspecialchars($csrfToken); ?>"
    };
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>