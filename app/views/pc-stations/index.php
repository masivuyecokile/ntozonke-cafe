<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

<main class="main-content">

    <div class="topbar">
        <div>
            <h4 class="mb-0">PC Stations</h4>
            <small class="text-muted">Register, edit, and control café computers.</small>
        </div>

        <div class="topbar-actions">
            <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addPcModal">
                <i class="bi bi-plus-circle me-1"></i>
                Add PC
            </button>

            <a href="<?= BASE_URL; ?>/index.php?route=dashboard" class="btn btn-outline-dark btn-sm">
                <i class="bi bi-grid me-1"></i>
                Dashboard
            </a>
        </div>
    </div>

    <div class="section-heading mt-4">
        <h5>Registered PC Stations</h5>
        <p>Manage all computers connected to the café system.</p>
    </div>

    <div class="row g-3 mt-1">
        <?php foreach ($pcs as $pc): ?>
            <?php
                $activeSession = $activeSessionsByPc[$pc->id] ?? null;

                $screenClass = $pc->status;
                if ($pc->status === 'ending_soon') {
                    $screenClass = 'maintenance';
                }

                $statusBadge = 'bg-secondary';

                if ($pc->status === 'active') {
                    $statusBadge = 'bg-success';
                } elseif ($pc->status === 'locked') {
                    $statusBadge = 'bg-dark';
                } elseif ($pc->status === 'offline') {
                    $statusBadge = 'bg-secondary';
                } elseif ($pc->status === 'maintenance') {
                    $statusBadge = 'bg-warning text-dark';
                }
            ?>

            <div class="col-xxl-3 col-xl-4 col-lg-6 col-md-6">
                <div class="pc-card">

                    <div class="pc-card-header">
                        <h6><?= htmlspecialchars($pc->pc_name); ?></h6>

                        <span class="badge <?= $statusBadge; ?>">
                            <?= ucfirst(str_replace('_', ' ', $pc->status)); ?>
                        </span>
                    </div>

                    <div class="pc-card-body">

                        <div class="pc-screen <?= htmlspecialchars($screenClass); ?>">
                            <div>
                                <i class="bi bi-pc-display"></i>
                                <strong><?= strtoupper(htmlspecialchars($pc->status)); ?></strong>
                            </div>
                        </div>

                        <div class="pc-meta mb-3">
                            <div>
                                <strong>IP:</strong>
                                <?= htmlspecialchars($pc->ip_address ?? 'Not set'); ?>
                            </div>

                            <div>
                                <strong>MAC:</strong>
                                <?= htmlspecialchars($pc->mac_address ?? 'Not set'); ?>
                            </div>

                            <div>
                                <strong>Heartbeat:</strong>
                                <?= $pc->last_heartbeat ? date('d M Y H:i', strtotime($pc->last_heartbeat)) : 'Never'; ?>
                            </div>
                        </div>

                        <?php if ($activeSession): ?>
                            <div class="alert alert-success py-2 small mb-3">
                                <strong>Active Session</strong><br>
                                <?= htmlspecialchars($activeSession->customer_name ?? 'Walk-in Customer'); ?><br>
                                Ends: <?= date('H:i', strtotime($activeSession->end_time)); ?>
                            </div>
                        <?php endif; ?>

                        <div class="d-grid gap-2">

                            <button
                                type="button"
                                class="btn btn-outline-success btn-sm js-edit-pc"
                                data-bs-toggle="modal"
                                data-bs-target="#editPcModal"
                                data-pc-id="<?= (int)$pc->id; ?>"
                                data-pc-name="<?= htmlspecialchars($pc->pc_name); ?>"
                                data-ip-address="<?= htmlspecialchars($pc->ip_address ?? ''); ?>"
                                data-mac-address="<?= htmlspecialchars($pc->mac_address ?? ''); ?>">
                                <i class="bi bi-pencil-square me-1"></i>
                                Edit PC
                            </button>

                            <?php if (!$activeSession): ?>
                                <div class="pc-status-actions">
                                    <button
                                        type="button"
                                        class="btn btn-outline-dark btn-sm js-pc-status"
                                        data-pc-id="<?= (int)$pc->id; ?>"
                                        data-status="locked">
                                        <i class="bi bi-lock-fill me-1"></i>
                                        Locked / Available
                                    </button>

                                    <button
                                        type="button"
                                        class="btn btn-outline-warning btn-sm js-pc-status"
                                        data-pc-id="<?= (int)$pc->id; ?>"
                                        data-status="maintenance">
                                        <i class="bi bi-tools me-1"></i>
                                        Maintenance
                                    </button>

                                    <button
                                        type="button"
                                        class="btn btn-outline-secondary btn-sm js-pc-status"
                                        data-pc-id="<?= (int)$pc->id; ?>"
                                        data-status="offline">
                                        <i class="bi bi-wifi-off me-1"></i>
                                        Offline
                                    </button>
                                </div>
                            <?php else: ?>
                                <small class="text-muted">
                                    End active session before changing PC status.
                                </small>
                            <?php endif; ?>

                        </div>

                    </div>
                </div>
            </div>

        <?php endforeach; ?>
    </div>

</main>

<!-- Add PC Modal -->
<div class="modal fade" id="addPcModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form
            class="modal-content"
            id="addPcForm"
            action="<?= BASE_URL; ?>/index.php?route=pc-stations.store"
            method="POST">

            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken); ?>">

            <div class="modal-header">
                <h5 class="modal-title">Add PC Station</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">

                <div class="mb-3">
                    <label class="form-label">PC Name</label>
                    <input
                        type="text"
                        name="pc_name"
                        class="form-control"
                        placeholder="Example: PC 5"
                        required>
                </div>

                <div class="mb-3">
                    <label class="form-label">IP Address</label>
                    <input
                        type="text"
                        name="ip_address"
                        class="form-control"
                        placeholder="Example: 192.168.1.20">
                    <small class="text-muted">Optional for now. Useful later for Python client communication.</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">MAC Address</label>
                    <input
                        type="text"
                        name="mac_address"
                        class="form-control"
                        placeholder="Optional">
                </div>

                <div class="mb-3">
                    <label class="form-label">Initial Status</label>
                    <select name="status" class="form-select">
                        <option value="locked">Locked / Available</option>
                        <option value="offline">Offline</option>
                        <option value="maintenance">Maintenance</option>
                    </select>
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-dark" data-bs-dismiss="modal">
                    Cancel
                </button>

                <button type="submit" class="btn btn-success" id="savePcBtn">
                    <i class="bi bi-save me-1"></i>
                    Save PC
                </button>
            </div>

        </form>
    </div>
</div>

<!-- Edit PC Modal -->
<div class="modal fade" id="editPcModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form
            class="modal-content"
            id="editPcForm"
            action="<?= BASE_URL; ?>/index.php?route=pc-stations.update"
            method="POST">

            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken); ?>">
            <input type="hidden" name="pc_id" id="editPcId">

            <div class="modal-header">
                <h5 class="modal-title">Edit PC Station</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">

                <div class="mb-3">
                    <label class="form-label">PC Name</label>
                    <input
                        type="text"
                        name="pc_name"
                        id="editPcName"
                        class="form-control"
                        required>
                </div>

                <div class="mb-3">
                    <label class="form-label">IP Address</label>
                    <input
                        type="text"
                        name="ip_address"
                        id="editIpAddress"
                        class="form-control">
                </div>

                <div class="mb-3">
                    <label class="form-label">MAC Address</label>
                    <input
                        type="text"
                        name="mac_address"
                        id="editMacAddress"
                        class="form-control">
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-dark" data-bs-dismiss="modal">
                    Cancel
                </button>

                <button type="submit" class="btn btn-success" id="updatePcBtn">
                    <i class="bi bi-save me-1"></i>
                    Update PC
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