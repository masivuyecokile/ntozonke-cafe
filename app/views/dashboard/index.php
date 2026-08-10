<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

<main class="main-content">

    <div class="topbar">
        <div>
            <h4 class="mb-0">Admin Dashboard</h4>
            <small class="text-muted">Live cafe operations, sales, expenses, and computer sessions.</small>
        </div>

        <div class="topbar-actions">
            <span class="local-server-badge">Local Server Online</span>

            <a href="<?= BASE_URL; ?>/index.php?route=reports&period=today" class="btn btn-outline-success btn-sm">
                <i class="bi bi-graph-up-arrow me-1"></i>
                Today Report
            </a>
        </div>
    </div>

    <!-- Today Business Summary -->
    <div class="section-heading mt-4">
        <h5>Today's Business Summary</h5>
        <p>Live income, expense, and profit overview for today.</p>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-card sales-card">
                <div>
                    <span>Today Revenue</span>
                    <h3>R<?= number_format((float)($todayRevenue ?? 0), 2); ?></h3>
                </div>
                <i class="bi bi-wallet2"></i>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card expense-stat-card">
                <div>
                    <span>Today Expenses</span>
                    <h3>R<?= number_format((float)($todayExpenses ?? 0), 2); ?></h3>
                </div>
                <i class="bi bi-journal-minus"></i>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card profit-stat-card <?= ((float)($todayNetProfit ?? 0) < 0) ? 'loss' : ''; ?>">
                <div>
                    <span>Today Net Profit</span>
                    <h3>R<?= number_format((float)($todayNetProfit ?? 0), 2); ?></h3>
                </div>
                <i class="bi <?= ((float)($todayNetProfit ?? 0) < 0) ? 'bi-arrow-down-circle' : 'bi-arrow-up-circle'; ?>"></i>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div>
                    <span>Pending Print Jobs</span>
                    <h3><?= (int)($pendingPrintJobs ?? 0); ?></h3>
                </div>
                <i class="bi bi-printer-fill"></i>
            </div>
        </div>
    </div>

    <!-- PC Status Summary -->
    <div class="section-heading mt-4">
        <h5>Computer Status</h5>
        <p>Quick overview of all registered cafe PCs.</p>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div>
                    <span>Total PCs</span>
                    <h3><?= (int)($totalPCs ?? count($pcs ?? [])); ?></h3>
                </div>
                <i class="bi bi-pc-display-horizontal"></i>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div>
                    <span>Active Sessions</span>
                    <h3><?= (int)($activePCs ?? 0); ?></h3>
                </div>
                <i class="bi bi-play-circle"></i>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div>
                    <span>Locked PCs</span>
                    <h3><?= (int)($lockedPCs ?? 0); ?></h3>
                </div>
                <i class="bi bi-lock-fill"></i>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div>
                    <span>Offline PCs</span>
                    <h3><?= (int)($offlinePCs ?? 0); ?></h3>
                </div>
                <i class="bi bi-wifi-off"></i>
            </div>
        </div>
    </div>

    <!-- Today's Sales Breakdown -->
    <div class="section-heading mt-4">
        <h5>Today's Sales Breakdown</h5>
        <p>Internet, printing, services, and payment totals for today.</p>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-8">
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="mini-summary-card">
                        <span>Internet</span>
                        <strong>R<?= number_format((float)($todayStats->internet_revenue ?? 0), 2); ?></strong>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="mini-summary-card">
                        <span>Printing</span>
                        <strong>R<?= number_format((float)($todayStats->printing_revenue ?? 0), 2); ?></strong>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="mini-summary-card">
                        <span>Services</span>
                        <strong>R<?= number_format((float)($todayStats->service_revenue ?? 0), 2); ?></strong>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="mini-summary-card">
                        <span>Other</span>
                        <strong>R<?= number_format((float)($todayStats->other_revenue ?? 0), 2); ?></strong>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="mini-summary-card">
                        <span>Cash</span>
                        <strong>R<?= number_format((float)($todayStats->cash_total ?? 0), 2); ?></strong>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="mini-summary-card">
                        <span>Card</span>
                        <strong>R<?= number_format((float)($todayStats->card_total ?? 0), 2); ?></strong>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="mini-summary-card">
                        <span>EFT</span>
                        <strong>R<?= number_format((float)($todayStats->eft_total ?? 0), 2); ?></strong>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="mini-summary-card">
                        <span>Sales</span>
                        <strong><?= (int)($todayStats->total_sales ?? 0); ?></strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h6 class="mb-0 fw-bold">Recent Sales</h6>
                    <small class="text-muted">Latest transactions recorded today.</small>
                </div>

                <div class="card-body px-4 pt-2">
                    <?php if (empty($recentSales)): ?>
                        <div class="text-muted small py-3">
                            No sales recorded yet today.
                        </div>
                    <?php else: ?>
                        <?php foreach ($recentSales as $sale): ?>
                            <div class="payment-row">
                                <span>
                                    <?= ucfirst(htmlspecialchars($sale->sale_type ?? 'sale')); ?>
                                    <br>
                                    <small><?= date('H:i', strtotime($sale->created_at)); ?></small>
                                </span>
                                <strong>R<?= number_format((float)$sale->amount, 2); ?></strong>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Computer Stations -->
    <div class="section-heading mt-4">
        <h5>Computer Stations</h5>
        <p>Manage locked, active, and offline cafe PCs.</p>
    </div>

    <div class="row g-3 dashboard-pc-grid">
        <?php foreach ($pcs as $pc): ?>
            <?php
                $activeSession = $activeSessionsByPc[$pc->id] ?? null;

                $statusBadge = 'bg-secondary';
                $screenClass = $pc->status;

                if ($pc->status === 'active') {
                    $statusBadge = 'bg-success';
                } elseif ($pc->status === 'locked') {
                    $statusBadge = 'bg-dark';
                } elseif ($pc->status === 'offline') {
                    $statusBadge = 'bg-secondary';
                } elseif ($pc->status === 'maintenance') {
                    $statusBadge = 'bg-warning text-dark';
                }

                if ($pc->status === 'ending_soon') {
                    $screenClass = 'maintenance';
                }
            ?>

            <div class="col-xxl-3 col-xl-4 col-lg-6 col-md-6">
                <div class="dashboard-pc-card">

                    <div class="dashboard-pc-header">
                        <div>
                            <h6><?= htmlspecialchars($pc->pc_name); ?></h6>
                            <span>
                                <?= $pc->ip_address ? htmlspecialchars($pc->ip_address) : 'No IP registered yet'; ?>
                            </span>
                        </div>

                        <span class="badge <?= $statusBadge; ?>">
                            <?php if ($pc->status === 'locked'): ?>
                                <i class="bi bi-lock-fill me-1"></i>
                            <?php elseif ($pc->status === 'active'): ?>
                                <i class="bi bi-play-fill me-1"></i>
                            <?php elseif ($pc->status === 'maintenance'): ?>
                                <i class="bi bi-tools me-1"></i>
                            <?php elseif ($pc->status === 'offline'): ?>
                                <i class="bi bi-wifi-off me-1"></i>
                            <?php endif; ?>

                            <?= ucfirst(str_replace('_', ' ', $pc->status)); ?>
                        </span>
                    </div>

                    <div class="dashboard-pc-screen <?= htmlspecialchars($screenClass); ?>">
                        <div class="dashboard-pc-icon">
                            <i class="bi bi-pc-display"></i>
                        </div>

                        <div class="dashboard-pc-screen-text">
                            <?php if ($pc->status === 'active' && $activeSession): ?>
                                <strong>Session Active</strong>
                                <span>
                                    Ends at <?= date('H:i', strtotime($activeSession->end_time)); ?>
                                </span>
                            <?php elseif ($pc->status === 'maintenance'): ?>
                                <strong>Maintenance Mode</strong>
                                <span>This station is currently unavailable.</span>
                            <?php elseif ($pc->status === 'offline'): ?>
                                <strong>Offline</strong>
                                <span>This station is not available.</span>
                            <?php else: ?>
                                <strong>Locked</strong>
                                <span>Waiting for admin to start session.</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if ($activeSession): ?>
    <div class="dashboard-active-session-box">
        <div class="dashboard-active-session-title">
            <i class="bi bi-person-check-fill"></i>
            <span>Active Session</span>
        </div>

        <div class="dashboard-active-session-row">
            <span>Customer</span>
            <strong><?= htmlspecialchars($activeSession->customer_name ?? 'Walk-in Customer'); ?></strong>
        </div>

        <div class="dashboard-active-session-row">
            <span>Remaining</span>
            <strong
                class="js-session-countdown"
                data-end-timestamp="<?= strtotime($activeSession->end_time); ?>"
                data-end-time="<?= htmlspecialchars($activeSession->end_time); ?>">
                Calculating...
            </strong>
        </div>

        <div class="dashboard-active-session-row">
            <span>Amount</span>
            <strong>
                R<?= number_format((float)($activeSession->internet_income ?? $activeSession->amount_due ?? 0), 2); ?>
            </strong>
        </div>
    </div>
<?php endif; ?>

                    <div class="dashboard-pc-actions">
                        <?php if ($pc->status === 'locked'): ?>
                            <button
                                type="button"
                                class="btn btn-success btn-sm js-start-session"
                                data-bs-toggle="modal"
                                data-bs-target="#startSessionModal"
                                data-pc-id="<?= (int)$pc->id; ?>"
                                data-pc-name="<?= htmlspecialchars($pc->pc_name); ?>">
                                <i class="bi bi-play-fill me-1"></i>
                                Start Session
                            </button>

                        <?php elseif ($pc->status === 'active' && $activeSession): ?>
                            <button
                                type="button"
                                class="btn btn-outline-success btn-sm js-extend-session"
                                data-bs-toggle="modal"
                                data-bs-target="#extendSessionModal"
                                data-session-id="<?= (int)$activeSession->id; ?>"
                                data-pc-name="<?= htmlspecialchars($pc->pc_name); ?>">
                                <i class="bi bi-plus-circle me-1"></i>
                                Extend
                            </button>

                            <button
                                type="button"
                                class="btn btn-outline-danger btn-sm js-end-session"
                                data-session-id="<?= (int)$activeSession->id; ?>">
                                <i class="bi bi-stop-circle me-1"></i>
                                End
                            </button>

                        <?php else: ?>
                            <button type="button" class="btn btn-outline-secondary btn-sm" disabled>
                                Not Available
                            </button>
                        <?php endif; ?>
                    </div>

                </div>
            </div>
        <?php endforeach; ?>
    </div>

</main>

<!-- Start Session Modal -->
<div class="modal fade" id="startSessionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form
            class="modal-content"
            id="startSessionForm"
            action="<?= BASE_URL; ?>/index.php?route=sessions.start"
            method="POST">

            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken); ?>">
            <input type="hidden" name="pc_id" id="startPcId">

            <div class="modal-header">
                <h5 class="modal-title">Start Session</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">

                <div class="alert alert-success py-2">
                    <strong id="startPcName">Selected PC</strong>
                </div>

                <div class="mb-3">
                    <label class="form-label">Customer Name</label>
                    <input
                        type="text"
                        name="customer_name"
                        class="form-control"
                        value="Walk-in Customer"
                        placeholder="Walk-in Customer">
                </div>

                <div class="mb-3">
                    <label class="form-label">Internet Package</label>
                    <select name="package_id" id="startPackageId" class="form-select" required>
                        <option value="">Choose package</option>

                        <?php foreach ($packages as $package): ?>
                            <option
                                value="<?= (int)$package->id; ?>"
                                data-minutes="<?= (int)$package->minutes; ?>"
                                data-price="<?= number_format((float)$package->price, 2, '.', ''); ?>">
                                <?= htmlspecialchars($package->package_name); ?>
                                - <?= (int)$package->minutes; ?> min
                                - R<?= number_format((float)$package->price, 2); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Payment Method</label>
                    <select name="payment_method" class="form-select">
                        <option value="cash">Cash</option>
                        <option value="card">Card</option>
                        <option value="eft">EFT</option>
                        <option value="free">Free</option>
                    </select>
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-dark" data-bs-dismiss="modal">
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

<!-- Extend Session Modal -->
<div class="modal fade" id="extendSessionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form
            class="modal-content"
            id="extendSessionForm"
            action="<?= BASE_URL; ?>/index.php?route=sessions.extend"
            method="POST">

            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken); ?>">
            <input type="hidden" name="session_id" id="extendSessionId">

            <div class="modal-header">
                <h5 class="modal-title">Extend Session</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">

                <div class="alert alert-success py-2">
                    <strong id="extendPcName">Selected PC</strong>
                </div>

                <div class="mb-3">
                    <label class="form-label">Add Package</label>
                    <select name="package_id" id="extendPackageId" class="form-select" required>
                        <option value="">Choose package</option>

                        <?php foreach ($packages as $package): ?>
                            <option
                                value="<?= (int)$package->id; ?>"
                                data-minutes="<?= (int)$package->minutes; ?>"
                                data-price="<?= number_format((float)$package->price, 2, '.', ''); ?>">
                                <?= htmlspecialchars($package->package_name); ?>
                                - <?= (int)$package->minutes; ?> min
                                - R<?= number_format((float)$package->price, 2); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Payment Method</label>
                    <select name="payment_method" class="form-select">
                        <option value="cash">Cash</option>
                        <option value="card">Card</option>
                        <option value="eft">EFT</option>
                        <option value="free">Free</option>
                    </select>
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-dark" data-bs-dismiss="modal">
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
        baseUrl: "<?= BASE_URL; ?>",
        csrfToken: "<?= htmlspecialchars($csrfToken); ?>"
    };
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>