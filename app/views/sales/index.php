<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

<main class="main-content">

    <div class="topbar">
        <div>
            <h4 class="mb-0">Sales</h4>
            <small class="text-muted">Review internet, printing, and service income.</small>
        </div>

        <div class="topbar-actions">
            <form method="GET" action="<?= BASE_URL; ?>/index.php" class="report-filter-form">
                <input type="hidden" name="route" value="sales">

                <select name="period" class="form-select form-select-sm" id="salesPeriod">
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
                    id="salesStartDate"
                    class="form-control form-control-sm"
                    value="<?= htmlspecialchars($startDate); ?>">

                <input 
                    type="date" 
                    name="end_date" 
                    id="salesEndDate"
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
        <h5><?= htmlspecialchars($periodLabel); ?> Sales</h5>
        <p>
            Showing sales from
            <strong><?= date('d M Y', strtotime($startDate)); ?></strong>
            to
            <strong><?= date('d M Y', strtotime($endDate)); ?></strong>.
        </p>
    </div>

    <div class="row g-3">
        <div class="col-md-3">
            <div class="stat-card sales-card">
                <div>
                    <span>Total Revenue</span>
                    <h3>R<?= number_format((float)($stats->total_revenue ?? 0), 2); ?></h3>
                </div>
                <i class="bi bi-wallet2"></i>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div>
                    <span>Total Sales</span>
                    <h3><?= (int)($stats->total_sales ?? 0); ?></h3>
                </div>
                <i class="bi bi-receipt"></i>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div>
                    <span>Cash</span>
                    <h3>R<?= number_format((float)($stats->cash_total ?? 0), 2); ?></h3>
                </div>
                <i class="bi bi-cash"></i>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div>
                    <span>Card / EFT</span>
                    <h3>
                        R<?= number_format(
                            (float)($stats->card_total ?? 0) + (float)($stats->eft_total ?? 0),
                            2
                        ); ?>
                    </h3>
                </div>
                <i class="bi bi-credit-card"></i>
            </div>
        </div>
    </div>

    <div class="section-heading mt-4">
        <h5>Income Breakdown</h5>
        <p>Breakdown by sale type.</p>
    </div>

    <div class="row g-3">
        <div class="col-md-3">
            <div class="mini-summary-card">
                <span>Internet</span>
                <strong>R<?= number_format((float)($stats->internet_revenue ?? 0), 2); ?></strong>
            </div>
        </div>

        <div class="col-md-3">
            <div class="mini-summary-card">
                <span>Printing</span>
                <strong>R<?= number_format((float)($stats->printing_revenue ?? 0), 2); ?></strong>
            </div>
        </div>

        <div class="col-md-3">
            <div class="mini-summary-card">
                <span>Services</span>
                <strong>R<?= number_format((float)($stats->service_revenue ?? 0), 2); ?></strong>
            </div>
        </div>

        <div class="col-md-3">
            <div class="mini-summary-card">
                <span>Other</span>
                <strong>R<?= number_format((float)($stats->other_revenue ?? 0), 2); ?></strong>
            </div>
        </div>
    </div>

    <div class="section-heading mt-4">
        <h5>Payment Breakdown</h5>
        <p>Payment totals for the selected period.</p>
    </div>

    <div class="row g-3">
        <div class="col-md-3">
            <div class="mini-summary-card">
                <span>Cash</span>
                <strong>R<?= number_format((float)($stats->cash_total ?? 0), 2); ?></strong>
            </div>
        </div>

        <div class="col-md-3">
            <div class="mini-summary-card">
                <span>Card</span>
                <strong>R<?= number_format((float)($stats->card_total ?? 0), 2); ?></strong>
            </div>
        </div>

        <div class="col-md-3">
            <div class="mini-summary-card">
                <span>EFT</span>
                <strong>R<?= number_format((float)($stats->eft_total ?? 0), 2); ?></strong>
            </div>
        </div>

        <div class="col-md-3">
            <div class="mini-summary-card">
                <span>Free</span>
                <strong>R<?= number_format((float)($stats->free_total ?? 0), 2); ?></strong>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mt-4">
        <div class="card-header bg-white border-0 pt-4 px-4">
            <h6 class="mb-0 fw-bold">Sales Transactions</h6>
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
                        <th>Date / Time</th>
                        <th>Type</th>
                        <th>Description</th>
                        <th>PC / Document</th>
                        <th>Payment</th>
                        <th>Created By</th>
                        <th class="text-end">Amount</th>
                    </tr>
                    </thead>

                    <tbody>
                    <?php if (!$sales): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                No sales found for this period.
                            </td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($sales as $sale): ?>
                        <tr>
                            <td>
                                <strong><?= date('d M Y', strtotime($sale->created_at)); ?></strong>
                                <br>
                                <small class="text-muted"><?= date('H:i', strtotime($sale->created_at)); ?></small>
                            </td>

                            <td>
                                <?php
                                    $badgeClass = 'bg-secondary';

                                    if ($sale->sale_type === 'internet') {
                                        $badgeClass = 'bg-success';
                                    } elseif ($sale->sale_type === 'printing') {
                                        $badgeClass = 'bg-primary';
                                    } elseif ($sale->sale_type === 'service') {
                                        $badgeClass = 'bg-warning text-dark';
                                    }
                                ?>

                                <span class="badge <?= $badgeClass; ?>">
                                    <?= ucfirst($sale->sale_type); ?>
                                </span>
                            </td>

                            <td>
                                <strong><?= htmlspecialchars($sale->description ?? 'Sale'); ?></strong>
                            </td>

                            <td>
                                <?php if (!empty($sale->pc_name)): ?>
                                    <span class="badge bg-light text-dark">
                                        <?= htmlspecialchars($sale->pc_name); ?>
                                    </span>
                                <?php endif; ?>

                                <?php if (!empty($sale->document_name)): ?>
                                    <br>
                                    <small class="text-muted">
                                        <?= htmlspecialchars($sale->document_name); ?>
                                    </small>
                                <?php endif; ?>

                                <?php if (empty($sale->pc_name) && empty($sale->document_name)): ?>
                                    <small class="text-muted">N/A</small>
                                <?php endif; ?>
                            </td>

                            <td><?= strtoupper($sale->payment_method); ?></td>

                            <td><?= htmlspecialchars($sale->created_by_name ?? 'System'); ?></td>

                            <td class="text-end">
                                <strong>R<?= number_format((float)$sale->amount, 2); ?></strong>
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