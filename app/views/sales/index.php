<?php require_once __DIR__.'/../layouts/header.php';
?>
<?php require_once __DIR__.'/../layouts/sidebar.php';
?>

<main class="main-content">

    <div class="topbar">
        <div>
            <h4 class="mb-0">Sales</h4>
            <small class="text-muted">Review daily internet, printing, and service transactions.</small>
        </div>

        <div class="topbar-actions">
            <form method="GET" action="<?=BASE_URL;
?>/index.php" class="d-flex gap-2">
            <input type="hidden" name="route" value="sales">
            <input
            type="date"
            name="date"
            class="form-control form-control-sm"
            value="<?=htmlspecialchars($selectedDate);
?>">
            <button class="btn btn-success btn-sm">
                <i class="bi bi-search me-1"></i>
                Filter
            </button>
        </form>

        <a href="<?=BASE_URL;
?>/index.php?route=dashboard" class="btn btn-outline-dark btn-sm">
        <i class="bi bi-grid me-1"></i>
        Dashboard
    </a>
</div>
</div>

<div class="row g-3 mt-3">
    <div class="col-md-3">
        <div class="stat-card">
            <div>
                <span>Total Revenue</span>
                <h3>R<?=number_format((float)($stats->total_revenue??0), 2);
?></h3>
            </div>
            <i class="bi bi-wallet2"></i>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card">
            <div>
                <span>Internet Revenue</span>
                <h3>R<?=number_format((float)($stats->internet_revenue??0), 2);
?></h3>
            </div>
            <i class="bi bi-pc-display"></i>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card">
            <div>
                <span>Printing Revenue</span>
                <h3>R<?=number_format((float)($stats->printing_revenue??0), 2);
?></h3>
            </div>
            <i class="bi bi-printer"></i>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card">
            <div>
                <span>Total Transactions</span>
                <h3><?=(int)($stats->total_sales??0);
?></h3>
            </div>
            <i class="bi bi-receipt"></i>
        </div>
    </div>
</div>

<div class="row g-3 mt-1">
    <div class="col-md-3">
        <div class="mini-summary-card">
            <span>Cash</span>
            <strong>R<?=number_format((float)($stats->cash_total??0), 2);
?></strong>
        </div>
    </div>

    <div class="col-md-3">
        <div class="mini-summary-card">
            <span>Card</span>
            <strong>R<?=number_format((float)($stats->card_total??0), 2);
?></strong>
        </div>
    </div>

    <div class="col-md-3">
        <div class="mini-summary-card">
            <span>EFT</span>
            <strong>R<?=number_format((float)($stats->eft_total??0), 2);
?></strong>
        </div>
    </div>

    <div class="col-md-3">
        <div class="mini-summary-card">
            <span>Free / Comp</span>
            <strong>R<?=number_format((float)($stats->free_total??0), 2);
?></strong>
        </div>
    </div>
</div>

<div class="section-heading mt-4">
    <h5>Sales Transactions</h5>
    <p>Showing all transactions for <?=date('d M Y', strtotime($selectedDate));
?>.</p>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Description</th>
                        <th>PC / Document</th>
                        <th>Payment</th>
                        <th>Created By</th>
                        <th>Time</th>
                        <th class="text-end">Amount</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if(!$sales):?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                No sales found for this date.
                            </td>
                        </tr>
                    <?php endif;
?>

                    <?php foreach($sales as $sale):?>
                        <?php
$saleIcon=match($sale->sale_type) {
    'internet'=>'bi-pc-display', 'printing'=>'bi-printer', 'service'=>'bi-tools', default=>'bi-receipt'
};
$saleBadge=match($sale->sale_type) {
    'internet'=>'success', 'printing'=>'primary', 'service'=>'warning', default=>'secondary'
};
?>

                <tr>
                    <td>
                        <span class="badge bg-<?=$saleBadge;
?>">
                        <i class="bi <?=$saleIcon;
?> me-1"></i>
                        <?=ucfirst($sale->sale_type);
?>
                    </span>
                </td>

                <td>
                    <strong><?=htmlspecialchars($sale->description??'Sale');
?></strong>
                </td>

                <td>
                    <?php if(!empty($sale->pc_name)):?>
                        <?=htmlspecialchars($sale->pc_name);
?>
                    <?php elseif(!empty($sale->document_name)):?>
                    <?=htmlspecialchars($sale->document_name);
?>
                <?php else:?>
                <span class="text-muted">N/A</span>
            <?php endif;
?>
        </td>

        <td>
            <span class="badge bg-light text-dark">
                <?=strtoupper($sale->payment_method);
?>
            </span>
        </td>

        <td>
            <?=htmlspecialchars($sale->created_by_name??'System');
?>
        </td>

        <td>
            <?=date('H:i', strtotime($sale->created_at));
?>
        </td>

        <td class="text-end">
            <strong>R<?=number_format((float)$sale->amount, 2);
?></strong>
        </td>
    </tr>

<?php endforeach;
?>
</tbody>
</table>
</div>
</div>
</div>

</main>

<?php require_once __DIR__.'/../layouts/footer.php';
?>
