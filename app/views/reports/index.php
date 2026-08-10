<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

<main class="main-content">

    <div class="topbar">
        <div>
            <h4 class="mb-0">Reports</h4>
            <small class="text-muted">Daily summary of sales, internet usage, and service revenue.</small>
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
        <p>This is the basic daily report. Profit report will be completed after Expenses module.</p>
    </div>

    <div class="row g-3">
        <div class="col-md-3">
            <div class="stat-card">
                <div>
                    <span>Total Revenue</span>
                    <h3>R<?= number_format((float)($salesStats->total_revenue ?? 0), 2); ?></h3>
                </div>
                <i class="bi bi-wallet2"></i>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div>
                    <span>Internet</span>
                    <h3>R<?= number_format((float)($salesStats->internet_revenue ?? 0), 2); ?></h3>
                </div>
                <i class="bi bi-pc-display"></i>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div>
                    <span>Printing</span>
                    <h3>R<?= number_format((float)($salesStats->printing_revenue ?? 0), 2); ?></h3>
                </div>
                <i class="bi bi-printer"></i>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div>
                    <span>Services</span>
                    <h3>R<?= number_format((float)($salesStats->service_revenue ?? 0), 2); ?></h3>
                </div>
                <i class="bi bi-tools"></i>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-1">
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

</main>

<script>
    window.NTOZONKE = {
        baseUrl: "<?= BASE_URL; ?>",
        csrfToken: "<?= htmlspecialchars($csrfToken); ?>"
    };
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>