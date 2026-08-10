<?php require_once __DIR__.'/../layouts/header.php';
?>
<?php require_once __DIR__.'/../layouts/sidebar.php';
?>

<main class="main-content">

    <div class="topbar">
        <div>
            <h4 class="mb-0">Admin Dashboard</h4>
            <small class="text-muted">
                Welcome back, <?=htmlspecialchars($_SESSION['user_name']??'Admin');
?>
            </small>
        </div>

        <div class="topbar-actions">
            <span class="badge bg-success-subtle text-success">
                <i class="bi bi-circle-fill me-1"></i>
                Local Server Online
            </span>

            <a href="<?=BASE_URL;
?>/index.php?route=logout" class="btn btn-outline-danger btn-sm">
            <i class="bi bi-box-arrow-right me-1"></i>
            Logout
        </a>
    </div>
</div>

<div class="row g-3 mt-3">
    <div class="col-md-3">
        <div class="stat-card">
            <div>
                <span>Total PCs</span>
                <h3><?=$totalPCs;
?></h3>
            </div>
            <i class="bi bi-pc-display-horizontal"></i>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card">
            <div>
                <span>Active Sessions</span>
                <h3><?=$activePCs;
?></h3>
            </div>
            <i class="bi bi-play-circle"></i>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card">
            <div>
                <span>Locked PCs</span>
                <h3><?=$lockedPCs;
?></h3>
            </div>
            <i class="bi bi-lock"></i>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card">
            <div>
                <span>Offline PCs</span>
                <h3><?=$offlinePCs;
?></h3>
            </div>
            <i class="bi bi-wifi-off"></i>
        </div>
    </div>
</div>
<div class="section-heading mt-4">
    <h5>Todayâ€™s Sales Summary</h5>
    <p>Live revenue from internet sessions, extensions, and printing.</p>
</div>

<div class="row g-3">
    <div class="col-md-3">
        <div class="stat-card sales-card">
            <div>
                <span>Total Revenue</span>
                <h3>R<?=number_format((float)($todayStats->total_revenue??0), 2);
?></h3>
            </div>
            <i class="bi bi-wallet2"></i>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card sales-card">
            <div>
                <span>Internet Revenue</span>
                <h3>R<?=number_format((float)($todayStats->internet_revenue??0), 2);
?></h3>
            </div>
            <i class="bi bi-pc-display"></i>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card sales-card">
            <div>
                <span>Printing Revenue</span>
                <h3>R<?=number_format((float)($todayStats->printing_revenue??0), 2);
?></h3>
            </div>
            <i class="bi bi-printer"></i>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card sales-card">
            <div>
                <span>Total Sales</span>
                <h3><?=(int)($todayStats->total_sales??0);
?></h3>
            </div>
            <i class="bi bi-receipt"></i>
        </div>
    </div>
</div>

<div class="row g-3 mt-1">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h6 class="mb-0 fw-bold">Recent Sales Today</h6>
                <small class="text-muted">Latest internet, extension, and print transactions.</small>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Description</th>
                                <th>Payment</th>
                                <th>Amount</th>
                                <th>Time</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php if(!$recentSales):?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        No sales recorded today yet.
                                    </td>
                                </tr>
                            <?php endif;
?>

                            <?php foreach($recentSales as $sale):?>
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
                            <?php if(!empty($sale->pc_name)):?>
                                <br><small class="text-muted"><?=htmlspecialchars($sale->pc_name);
?></small>
                            <?php endif;
?>
                        </td>

                        <td><?=strtoupper($sale->payment_method);
?></td>

                        <td>
                            <strong>R<?=number_format((float)$sale->amount, 2);
?></strong>
                        </td>

                        <td>
                            <small><?=date('H:i', strtotime($sale->created_at));
?></small>
                        </td>
                    </tr>
                <?php endforeach;
?>
            </tbody>
        </table>
    </div>
</div>
</div>
</div>

<div class="col-lg-4">
    <div class="card border-0 shadow-sm rounded-4 h-100">
        <div class="card-header bg-white border-0 pt-4 px-4">
            <h6 class="mb-0 fw-bold">Payment Breakdown</h6>
            <small class="text-muted">Todayâ€™s payment totals.</small>
        </div>

        <div class="card-body px-4">
            <div class="payment-row">
                <span>Cash</span>
                <strong>R<?=number_format((float)($todayStats->cash_total??0), 2);
?></strong>
            </div>

            <div class="payment-row">
                <span>Card</span>
                <strong>R<?=number_format((float)($todayStats->card_total??0), 2);
?></strong>
            </div>

            <div class="payment-row">
                <span>EFT</span>
                <strong>R<?=number_format((float)($todayStats->eft_total??0), 2);
?></strong>
            </div>

            <hr>

            <div class="payment-row total">
                <span>Total</span>
                <strong>R<?=number_format((float)($todayStats->total_revenue??0), 2);
?></strong>
            </div>
        </div>
    </div>
</div>
</div>
<div class="section-heading mt-4">
    <h5>Computer Stations</h5>
    <p>Manage locked, active, and offline cafÃ© PCs.</p>
</div>

<div class="row g-3">
    <?php foreach($pcs as $pc):?>
        <?php
$activeSession=$activeSessionsByPc[$pc->id]??null;
$remainingSeconds=0;
$remainingMinutes=0;
$remainingText='0 min';
if($activeSession) {
    $remainingSeconds=max(0, strtotime($activeSession->end_time)-time());
    $remainingMinutes=floor($remainingSeconds/60);
    $remainingHours=floor($remainingMinutes/60);
    $remainingMinsOnly=$remainingMinutes%60;
    if($remainingHours>0) {
        $remainingText=$remainingHours.'h '.$remainingMinsOnly.'m';
    }else {
        $remainingText=$remainingMinutes.' min';
    }
}$statusClass=match($pc->status) {
    'active'=>'success', 'locked'=>'dark', 'offline'=>'secondary', 'maintenance'=>'warning', 'ending_soon'=>'danger', default=>'secondary'
};
$statusIcon=match($pc->status) {
    'active'=>'bi-play-circle-fill', 'locked'=>'bi-lock-fill', 'offline'=>'bi-wifi-off', 'maintenance'=>'bi-tools', 'ending_soon'=>'bi-clock-history', default=>'bi-circle'
};
?>

<div class="col-lg-4">
    <div class="pc-card">
        <div class="pc-card-header">
            <div>
                <h5><?=htmlspecialchars($pc->pc_name);
?></h5>
                <span class="text-muted">
                    <?=$pc->ip_address?htmlspecialchars($pc->ip_address):'No IP registered yet';
?>
                </span>
            </div>

            <span class="badge bg-<?=$statusClass;
?>">
            <i class="bi <?=$statusIcon;
?> me-1"></i>
            <?=ucfirst(str_replace('_', ' ', $pc->status));
?>
        </span>
    </div>

    <div class="pc-screen">
        <i class="bi bi-display"></i>

        <?php if($pc->status==='locked'):?>

            <h6>Locked</h6>
            <p>Waiting for admin to start session.</p>

        <?php elseif($pc->status==='active'):?>

        <h6>Active Session</h6>
        <p>
            <?=htmlspecialchars($activeSession->customer_name??'Customer');
?><br>
            Remaining: <strong class="js-session-countdown"
            data-end-timestamp="<?=$activeSession?strtotime($activeSession->end_time):0;
?>">
            <?=$remainingText;
?>
        </strong><br>
        Amount: R<?=number_format((float)($activeSession->amount_due??0), 2);
?>
    </p>

<?php elseif($pc->status==='offline'):?>

<h6>Offline</h6>
<p>Python client is not connected.</p>

<?php elseif($pc->status==='maintenance'):?>

<h6>Maintenance</h6>
<p>This station is currently unavailable.</p>

<?php else:?>

<h6><?=ucfirst(str_replace('_', ' ', $pc->status));
?></h6>
<p>Station status needs attention.</p>

<?php endif;
?>
</div>

<div class="pc-actions">
    <?php if($pc->status==='locked'):?>

        <button
        type="button"
        class="btn btn-success btn-sm w-100 js-start-session"
        data-pc-id="<?=$pc->id;
?>"
        data-pc-name="<?=htmlspecialchars($pc->pc_name);
?>">
        <i class="bi bi-play-fill me-1"></i>
        Start Session
    </button>

<?php elseif($pc->status==='active'):?>
<button type="button"
class="btn btn-outline-success btn-sm w-50 js-extend-session"
data-session-id="<?=$activeSession->id??0;
?>"
data-pc-name="<?=htmlspecialchars($pc->pc_name);
?>">
<i class="bi bi-plus-circle me-1"></i>
Extend
</button>

<button
type="button"
class="btn btn-outline-danger btn-sm w-50 js-end-session"
data-session-id="<?=$activeSession->id??0;
?>"
data-pc-name="<?=htmlspecialchars($pc->pc_name);
?>">
<i class="bi bi-stop-circle me-1"></i>
End
</button>

<?php else:?>

<button class="btn btn-outline-secondary btn-sm w-100" disabled>
    Not Available
</button>

<?php endif;
?>
</div>
</div>


</div>

<?php endforeach;
?>
</div>

</main>


<div class="modal fade" id="startSessionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form
        id="startSessionForm"
        class="modal-content"
        action="<?=BASE_URL;
?>/index.php?route=sessions.start"
        method="POST">

        <input type="hidden" name="csrf_token" value="<?=htmlspecialchars($csrfToken);
?>">
        <input type="hidden" name="pc_id" id="sessionPcId">

        <div class="modal-header">
            <div>
                <h5 class="modal-title">Start Session</h5>
                <small class="text-muted" id="sessionPcName">Selected PC</small>
            </div>

            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">

            <div class="mb-3">
                <label class="form-label">Customer Name</label>
                <input
                type="text"
                name="customer_name"
                class="form-control"
                placeholder="Walk-in Customer">
            </div>

            <div class="mb-3">
                <label class="form-label">Select Internet Package</label>

                <select name="package_id" id="packageSelect" class="form-select" required>
                    <option value="">Choose package</option>

                    <?php foreach($packages as $package):?>
                        <option
                        value="<?=$package->id;
?>"
                        data-minutes="<?=$package->minutes;
?>"
                        data-price="<?=$package->price;
?>">
                        <?=htmlspecialchars($package->package_name);
?> - R<?=number_format((float)$package->price, 2);
?>
                    </option>
                <?php endforeach;
?>
            </select>
        </div>

        <div class="package-summary d-none" id="packageSummary">
            <div>
                <span>Duration</span>
                <strong id="summaryMinutes">0 minutes</strong>
            </div>

            <div>
                <span>Total</span>
                <strong id="summaryPrice">R0.00</strong>
            </div>
        </div>

    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
            Cancel
        </button>

        <button type="submit" class="btn btn-success" id="startSessionBtn">
            <i class="bi bi-play-fill me-1"></i>
            Start Session
        </button>
    </div>

</form>
</div>
</div>

<div class="modal fade" id="extendSessionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form
        id="extendSessionForm"
        class="modal-content"
        action="<?=BASE_URL;
?>/index.php?route=sessions.extend"
        method="POST">

        <input type="hidden" name="csrf_token" value="<?=htmlspecialchars($csrfToken);
?>">
        <input type="hidden" name="session_id" id="extendSessionId">

        <div class="modal-header">
            <div>
                <h5 class="modal-title">Extend Session</h5>
                <small class="text-muted" id="extendPcName">Selected PC</small>
            </div>

            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">

            <div class="mb-3">
                <label class="form-label">Select Extra Time</label>

                <select name="package_id" id="extendPackageSelect" class="form-select" required>
                    <option value="">Choose extension</option>

                    <?php foreach($packages as $package):?>
                        <option
                        value="<?=$package->id;
?>"
                        data-minutes="<?=$package->minutes;
?>"
                        data-price="<?=$package->price;
?>">
                        +<?=htmlspecialchars($package->package_name);
?> - R<?=number_format((float)$package->price, 2);
?>
                    </option>
                <?php endforeach;
?>
            </select>
        </div>

        <div class="package-summary d-none" id="extendPackageSummary">
            <div>
                <span>Extra Time</span>
                <strong id="extendSummaryMinutes">0 minutes</strong>
            </div>

            <div>
                <span>Extra Cost</span>
                <strong id="extendSummaryPrice">R0.00</strong>
            </div>
        </div>

    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
            Cancel
        </button>

        <button type="submit" class="btn btn-success" id="extendSessionBtn">
            <i class="bi bi-plus-circle me-1"></i>
            Extend Session
        </button>
    </div>

</form>
</div>
</div>
<script>
    window.NTOZONKE = {
        baseUrl: "<?=BASE_URL;
?>",
        csrfToken: "<?=htmlspecialchars($csrfToken);
?>"
    };
</script>
<?php require_once __DIR__.'/../layouts/footer.php';
?>
