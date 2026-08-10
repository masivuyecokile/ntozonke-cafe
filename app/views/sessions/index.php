<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

<main class="main-content">

    <div class="topbar">
        <div>
            <h4 class="mb-0">Sessions</h4>
            <small class="text-muted">Review recent internet sessions and session activity.</small>
        </div>

        <a href="<?= BASE_URL; ?>/index.php?route=dashboard" class="btn btn-outline-dark btn-sm">
            <i class="bi bi-grid me-1"></i>
            Dashboard
        </a>
    </div>

    <div class="row g-3 mt-3">
        <div class="col-md-3">
            <div class="stat-card">
                <div>
                    <span>Today Sessions</span>
                    <h3><?= (int)($stats->total_sessions ?? 0); ?></h3>
                </div>
                <i class="bi bi-clock-history"></i>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div>
                    <span>Active</span>
                    <h3><?= (int)($stats->active_sessions ?? 0); ?></h3>
                </div>
                <i class="bi bi-play-circle"></i>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div>
                    <span>Ended</span>
                    <h3><?= (int)($stats->ended_sessions ?? 0); ?></h3>
                </div>
                <i class="bi bi-check-circle"></i>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div>
                    <span>Minutes Sold</span>
                    <h3><?= (int)($stats->total_minutes ?? 0); ?></h3>
                </div>
                <i class="bi bi-hourglass-split"></i>
            </div>
        </div>
    </div>

    <div class="section-heading mt-4">
        <h5>Recent Sessions</h5>
        <p>Latest 50 sessions recorded by the system.</p>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                    <tr>
                        <th>PC</th>
                        <th>Customer</th>
                        <th>Start</th>
                        <th>End</th>
                        <th>Minutes</th>
                        <th>Status</th>
                        <th>Amount</th>
                    </tr>
                    </thead>

                    <tbody>
                    <?php if (!$sessions): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                No sessions found yet.
                            </td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($sessions as $session): ?>
                        <?php
                            $badge = match ($session->status) {
                                'active' => 'success',
                                'ended' => 'secondary',
                                'cancelled' => 'danger',
                                default => 'dark'
                            };
                        ?>

                        <tr>
                            <td><strong><?= htmlspecialchars($session->pc_name ?? 'Unknown PC'); ?></strong></td>
                            <td><?= htmlspecialchars($session->customer_name); ?></td>
                            <td><?= date('d M H:i', strtotime($session->start_time)); ?></td>
                            <td><?= date('d M H:i', strtotime($session->end_time)); ?></td>
                            <td><?= (int)$session->minutes_purchased + (int)$session->extended_minutes; ?> min</td>
                            <td><span class="badge bg-<?= $badge; ?>"><?= ucfirst($session->status); ?></span></td>
                            <td><strong>R<?= number_format((float)$session->amount_due, 2); ?></strong></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
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