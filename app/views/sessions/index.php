<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

<main class="main-content">

    <div class="topbar">
        <div>
            <h4 class="mb-0">Sessions</h4>
            <small class="text-muted">Review internet usage sessions by date range.</small>
        </div>

        <div class="topbar-actions">
            <form method="GET" action="<?= BASE_URL; ?>/index.php" class="report-filter-form">
                <input type="hidden" name="route" value="sessions">

                <select name="period" class="form-select form-select-sm" id="sessionsPeriod">
                    <option value="today" <?= $period === 'today' ? 'selected' : ''; ?>>Today</option>
                    <option value="yesterday" <?= $period === 'yesterday' ? 'selected' : ''; ?>>Yesterday</option>
                    <option value="this_week" <?= $period === 'this_week' ? 'selected' : ''; ?>>This Week</option>
                    <option value="last_week" <?= $period === 'last_week' ? 'selected' : ''; ?>>Last Week</option>
                    <option value="this_month" <?= $period === 'this_month' ? 'selected' : ''; ?>>This Month</option>
                    <option value="last_month" <?= $period === 'last_month' ? 'selected' : ''; ?>>Last Month</option>
                    <option value="this_quarter" <?= $period === 'this_quarter' ? 'selected' : ''; ?>>This Quarter</option>
                    <option value="last_quarter" <?= $period === 'last_quarter' ? 'selected' : ''; ?>>Last Quarter</option>
                    <option value="this_year" <?= $period === 'this_year' ? 'selected' : ''; ?>>This Year</option>
                    <option value="custom" <?= $period === 'custom' ? 'selected' : ''; ?>>Custom Range</option>
                </select>

                <input 
                    type="date" 
                    name="start_date" 
                    id="sessionsStartDate"
                    class="form-control form-control-sm"
                    value="<?= htmlspecialchars($startDate); ?>">

                <input 
                    type="date" 
                    name="end_date" 
                    id="sessionsEndDate"
                    class="form-control form-control-sm"
                    value="<?= htmlspecialchars($endDate); ?>">

                <button class="btn btn-success btn-sm">
                    <i class="bi bi-search me-1"></i>
                    Apply
                </button>
            </form>

            <a href="<?= BASE_URL; ?>/index.php?route=dashboard" class="btn btn-outline-dark btn-sm">
                <i class="bi bi-grid me-1"></i>
                Dashboard
            </a>
        </div>
    </div>

    <div class="section-heading mt-4">
        <h5><?= htmlspecialchars($periodLabel); ?> Sessions</h5>
        <p>
            Showing sessions from
            <strong><?= date('d M Y', strtotime($startDate)); ?></strong>
            to
            <strong><?= date('d M Y', strtotime($endDate)); ?></strong>.
        </p>
    </div>

    <div class="row g-3">
        <div class="col-xl col-md-4">
            <div class="stat-card">
                <div>
                    <span>Total Sessions</span>
                    <h3><?= (int)($stats->total_sessions ?? 0); ?></h3>
                </div>
                <i class="bi bi-clock-history"></i>
            </div>
        </div>

        <div class="col-xl col-md-4">
            <div class="stat-card">
                <div>
                    <span>Active</span>
                    <h3><?= (int)($stats->active_sessions ?? 0); ?></h3>
                </div>
                <i class="bi bi-play-circle"></i>
            </div>
        </div>

        <div class="col-xl col-md-4">
            <div class="stat-card">
                <div>
                    <span>Ended</span>
                    <h3><?= (int)($stats->ended_sessions ?? 0); ?></h3>
                </div>
                <i class="bi bi-check-circle"></i>
            </div>
        </div>

        <div class="col-xl col-md-6">
            <div class="stat-card">
                <div>
                    <span>Total Minutes</span>
                    <h3><?= (int)($stats->total_minutes ?? 0); ?></h3>
                </div>
                <i class="bi bi-hourglass-split"></i>
            </div>
        </div>

        <div class="col-xl col-md-6">
            <div class="stat-card sales-card">
                <div>
                    <span>Internet Income</span>
                    <h3>R<?= number_format((float)($stats->internet_income ?? 0), 2); ?></h3>
                </div>
                <i class="bi bi-wallet2"></i>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mt-4">
        <div class="card-header bg-white border-0 pt-4 px-4">
            <h6 class="mb-0 fw-bold">Session History</h6>
            <small class="text-muted">
                <?= htmlspecialchars($periodLabel); ?>:
                <?= date('d M Y', strtotime($startDate)); ?>
                to
                <?= date('d M Y', strtotime($endDate)); ?>
            </small>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                    <tr>
                        <th>Date</th>
                        <th>PC</th>
                        <th>Customer</th>
                        <th>Start</th>
                        <th>End</th>
                        <th>Actual End</th>
                        <th>Minutes</th>
                        <th>Status</th>
                        <th class="text-end">Income</th>
                    </tr>
                    </thead>

                    <tbody>
                    <?php if (!$sessions): ?>
                        <tr>
                            <td colspan="9" class="text-center text-muted py-5">
                                No sessions found for this period.
                            </td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($sessions as $session): ?>
                        <?php
                            $statusBadge = 'bg-secondary';

                            if ($session->status === 'active') {
                                $statusBadge = 'bg-success';
                            } elseif ($session->status === 'ended') {
                                $statusBadge = 'bg-dark';
                            } elseif ($session->status === 'cancelled') {
                                $statusBadge = 'bg-danger';
                            }

                            $totalMinutes = (int)$session->minutes_purchased + (int)$session->extended_minutes;
                        ?>

                        <tr>
                            <td>
                                <strong><?= date('d M Y', strtotime($session->created_at)); ?></strong>
                                <br>
                                <small class="text-muted"><?= date('H:i', strtotime($session->created_at)); ?></small>
                            </td>

                            <td>
                                <span class="badge bg-light text-dark">
                                    <?= htmlspecialchars($session->pc_name ?? 'Unknown PC'); ?>
                                </span>
                            </td>

                            <td><?= htmlspecialchars($session->customer_name ?? 'Walk-in Customer'); ?></td>

                            <td><?= date('H:i', strtotime($session->start_time)); ?></td>

                            <td><?= date('H:i', strtotime($session->end_time)); ?></td>

                            <td>
                                <?= $session->actual_end_time ? date('H:i', strtotime($session->actual_end_time)) : '-'; ?>
                            </td>

                            <td>
                                <strong><?= $totalMinutes; ?></strong>
                                <small class="text-muted">min</small>

                                <?php if ((int)$session->extended_minutes > 0): ?>
                                    <br>
                                    <small class="text-success">
                                        +<?= (int)$session->extended_minutes; ?> extended
                                    </small>
                                <?php endif; ?>
                            </td>

                            <td>
                                <span class="badge <?= $statusBadge; ?>">
                                    <?= ucfirst($session->status); ?>
                                </span>
                            </td>

                            <td class="text-end">
                                <strong>R<?= number_format((float)($session->internet_income ?? 0), 2); ?></strong>
                            </td>
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