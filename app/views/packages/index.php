<?php require_once __DIR__.'/../layouts/header.php';
?>
<?php require_once __DIR__.'/../layouts/sidebar.php';
?>

<main class="main-content">

    <div class="topbar">
        <div>
            <h4 class="mb-0">Internet Packages</h4>
            <small class="text-muted">Manage walk-in internet time packages and prices.</small>
        </div>

        <div class="topbar-actions">
            <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#packageModal" id="addPackageBtn">
                <i class="bi bi-plus-circle me-1"></i>
                Add Package
            </button>

            <a href="<?=BASE_URL;
?>/index.php?route=dashboard" class="btn btn-outline-dark btn-sm">
                <i class="bi bi-grid me-1"></i>
                Dashboard
            </a>
        </div>
    </div>

    <div class="section-heading mt-4">
        <h5>Package List</h5>
        <p>Active packages appear when starting or extending a session.</p>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                    <tr>
                        <th>Package</th>
                        <th>Minutes</th>
                        <th>Price</th>
                        <th>Rate / Minute</th>
                        <th>Status</th>
                        <th>Sort</th>
                        <th class="text-end">Actions</th>
                    </tr>
                    </thead>

                    <tbody>
                    <?php if(!$packages):?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                No packages created yet.
                            </td>
                        </tr>
                    <?php endif;
?>

                    <?php foreach($packages as $package):?>
                        <?php
$ratePerMinute=(float)$package->price/(int)$package->minutes;
$statusClass=$package->status==='active'?'success':'secondary';
?>

                        <tr>
                            <td>
                                <strong><?=htmlspecialchars($package->package_name);
?></strong>
                            </td>

                            <td><?=(int)$package->minutes;
?> min</td>

                            <td><strong>R<?=number_format((float)$package->price, 2);
?></strong></td>

                            <td>R<?=number_format($ratePerMinute, 2);
?></td>

                            <td>
                                <span class="badge bg-<?=$statusClass;
?>">
                                    <?=ucfirst($package->status);
?>
                                </span>
                            </td>

                            <td><?=(int)$package->sort_order;
?></td>

                            <td class="text-end">
                                <button 
                                    type="button"
                                    class="btn btn-outline-dark btn-sm js-edit-package"
                                    data-package-id="<?=$package->id;
?>"
                                    data-package-name="<?=htmlspecialchars($package->package_name);
?>"
                                    data-minutes="<?=(int)$package->minutes;
?>"
                                    data-price="<?=(float)$package->price;
?>"
                                    data-status="<?=htmlspecialchars($package->status);
?>"
                                    data-sort-order="<?=(int)$package->sort_order;
?>">
                                    <i class="bi bi-pencil-square me-1"></i>
                                    Edit
                                </button>

                                <button 
                                    type="button"
                                    class="btn btn-outline-<?=$package->status==='active'?'danger':'success';
?> btn-sm js-toggle-package"
                                    data-package-id="<?=$package->id;
?>"
                                    data-package-name="<?=htmlspecialchars($package->package_name);
?>">
                                    <?=$package->status==='active'?'Deactivate':'Activate';
?>
                                </button>
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

<div class="modal fade" id="packageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form 
            id="packageForm"
            class="modal-content"
            action="<?=BASE_URL;
?>/index.php?route=packages.store"
            method="POST">

            <input type="hidden" name="csrf_token" value="<?=htmlspecialchars($csrfToken);
?>">
            <input type="hidden" name="package_id" id="packageId">

            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="packageModalTitle">Add Internet Package</h5>
                    <small class="text-muted">Create or update time-based pricing.</small>
                </div>

                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <div class="mb-3">
                    <label class="form-label">Package Name</label>
                    <input 
                        type="text" 
                        name="package_name" 
                        id="packageName"
                        class="form-control" 
                        placeholder="Example: 30 Minutes"
                        required>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Minutes</label>
                        <input 
                            type="number" 
                            name="minutes" 
                            id="packageMinutes"
                            class="form-control" 
                            min="1"
                            required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Price</label>
                        <input 
                            type="number" 
                            name="price" 
                            id="packagePrice"
                            class="form-control" 
                            step="0.01"
                            min="0.01"
                            required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" id="packageStatus" class="form-select">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Sort Order</label>
                        <input 
                            type="number" 
                            name="sort_order" 
                            id="packageSortOrder"
                            class="form-control" 
                            value="0">
                    </div>
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                    Cancel
                </button>

                <button type="submit" class="btn btn-success" id="savePackageBtn">
                    <i class="bi bi-save me-1"></i>
                    Save Package
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
