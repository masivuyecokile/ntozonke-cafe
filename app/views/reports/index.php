<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

<main class="main-content">

    <div class="topbar">
        <div>
            <h4 class="mb-0">Reports</h4>
            <small class="text-muted">Daily summary of sales, expenses, and net profit.</small>
        </div>

        <form method="GET" action="<?= BASE_URL; ?>/index.php" class="d-flex gap-2">
            <input type="hidden" name="route" value="reports">

            <input 
                type="date" 
                name="date" 
                class="form-control form-control-sm"
                value="<?= htmlspecialchars($selectedDate); ?>">

            <button class="btn btn-success btn-sm">
                <i class="bi bi-search me-1"></i>
                Filter
            </button>
        </form>
    </div>

    <div class="section-heading mt-4">
        <h5>Report for <?= date('d M Y', strtotime($selectedDate)); ?></h5>
        <p>Revenue, expenses, payment breakdown, and profit summary.</p>
    </div>

    <div class="row g-3">
        <div class="col-md-4">
            <div class="stat-card">
                <div>
                    <span>Total Revenue</span>
                    <h3>R<?= number_format($totalRevenue, 2); ?></h3>
                </div>
                <i class="bi bi-wallet2"></i>
            </div>
        </div>

        <div class="col-md-4">
            <div class="stat-card expense-stat-card">
                <div>
                    <span>Total Expenses</span>
                    <h3>R<?= number_format($totalExpenses, 2); ?></h3>
                </div>
                <i class="bi bi-journal-minus"></i>
            </div>
        </div>

        <div class="col-md-4">
            <div class="stat-card profit-stat-card <?= $netProfit < 0 ? 'loss' : ''; ?>">
                <div>
                    <span>Net Profit</span>
                    <h3>R<?= number_format($netProfit, 2); ?></h3>
                </div>
                <i class="bi <?= $netProfit < 0 ? 'bi-arrow-down-circle' : 'bi-arrow-up-circle'; ?>"></i>
            </div>
        </div>
    </div>

    <div class="section-heading mt-4">
        <h5>Revenue Breakdown</h5>
        <p>Where today’s income came from.</p>
    </div>

    <div class="row g-3">
        <div class="col-md-3">
            <div class="mini-summary-card">
                <span>Internet</span>
                <strong>R<?= number_format((float)($salesStats->internet_revenue ?? 0), 2); ?></strong>
            </div>
        </div>

        <div class="col-md-3">
            <div class="mini-summary-card">
                <span>Printing</span>
                <strong>R<?= number_format((float)($salesStats->printing_revenue ?? 0), 2); ?></strong>
            </div>
        </div>

        <div class="col-md-3">
            <div class="mini-summary-card">
                <span>Services</span>
                <strong>R<?= number_format((float)($salesStats->service_revenue ?? 0), 2); ?></strong>
            </div>
        </div>

        <div class="col-md-3">
            <div class="mini-summary-card">
                <span>Other</span>
                <strong>R<?= number_format((float)($salesStats->other_revenue ?? 0), 2); ?></strong>
            </div>
        </div>
    </div>

    <div class="section-heading mt-4">
        <h5>Payment Breakdown</h5>
        <p>Payment method totals for the selected date.</p>
    </div>

    <div class="row g-3">
        <div class="col-md-3">
            <div class="mini-summary-card">
                <span>Cash</span>
                <strong>R<?= number_format((float)($salesStats->cash_total ?? 0), 2); ?></strong>
            </div>
        </div>

        <div class="col-md-3">
            <div class="mini-summary-card">
                <span>Card</span>
                <strong>R<?= number_format((float)($salesStats->card_total ?? 0), 2); ?></strong>
            </div>
        </div>

        <div class="col-md-3">
            <div class="mini-summary-card">
                <span>EFT</span>
                <strong>R<?= number_format((float)($salesStats->eft_total ?? 0), 2); ?></strong>
            </div>
        </div>

        <div class="col-md-3">
            <div class="mini-summary-card">
                <span>Transactions</span>
                <strong><?= (int)($salesStats->total_sales ?? 0); ?></strong>
            </div>
        </div>
    </div>

    <div class="section-heading mt-4">
        <h5>Quick Actions</h5>
        <p>Jump to the detailed records behind this report.</p>
    </div>

    <div class="row g-3">
        <div class="col-md-4">
            <a href="<?= BASE_URL; ?>/index.php?route=sales&date=<?= htmlspecialchars($selectedDate); ?>" class="report-action-card">
                <i class="bi bi-cash-stack"></i>
                <div>
                    <strong>View Sales</strong>
                    <span>Open all sales for this date</span>
                </div>
            </a>
        </div>

        <div class="col-md-4">
            <a href="<?= BASE_URL; ?>/index.php?route=expenses&date=<?= htmlspecialchars($selectedDate); ?>" class="report-action-card">
                <i class="bi bi-journal-minus"></i>
                <div>
                    <strong>View Expenses</strong>
                    <span>Open all expenses for this date</span>
                </div>
            </a>
        </div>

        <div class="col-md-4">
            <a href="<?= BASE_URL; ?>/index.php?route=dashboard" class="report-action-card">
                <i class="bi bi-grid"></i>
                <div>
                    <strong>Back to Dashboard</strong>
                    <span>Return to live café overview</span>
                </div>
            </a>
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