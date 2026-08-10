<?php require_once __DIR__.'/../layouts/header.php';
?>
<?php require_once __DIR__.'/../layouts/sidebar.php';
?>

<main class="main-content">

    <div class="topbar">
        <div>
            <h4 class="mb-0">Services Pricing</h4>
            <small class="text-muted">Manage printing, scanning, photocopying, and service rates.</small>
        </div>

        <div class="topbar-actions">
            <a href="<?=BASE_URL;
?>/index.php?route=dashboard" class="btn btn-outline-dark btn-sm">
                <i class="bi bi-grid me-1"></i>
                Dashboard
            </a>
        </div>
    </div>

    <div class="section-heading mt-4">
        <h5>Print & Service Rates</h5>
        <p>These prices are used when calculating print jobs and manual services.</p>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">

            <form 
                id="pricingForm"
                action="<?=BASE_URL;
?>/index.php?route=pricing.update"
                method="POST">

                <input type="hidden" name="csrf_token" value="<?=htmlspecialchars($csrfToken);
?>">

                <div class="row g-3">

                    <div class="col-md-6">
                        <label class="form-label">Black & White Print Rate</label>
                        <div class="input-group">
                            <span class="input-group-text">R</span>
                            <input 
                                type="number"
                                step="0.01"
                                min="0"
                                name="print_bw_rate"
                                class="form-control"
                                value="<?=htmlspecialchars($settings->print_bw_rate??'1.00');
?>"
                                required>
                            <span class="input-group-text">per page</span>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Colour Print Rate</label>
                        <div class="input-group">
                            <span class="input-group-text">R</span>
                            <input 
                                type="number"
                                step="0.01"
                                min="0"
                                name="print_colour_rate"
                                class="form-control"
                                value="<?=htmlspecialchars($settings->print_colour_rate??'5.00');
?>"
                                required>
                            <span class="input-group-text">per page</span>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Scanning Rate</label>
                        <div class="input-group">
                            <span class="input-group-text">R</span>
                            <input 
                                type="number"
                                step="0.01"
                                min="0"
                                name="scan_rate"
                                class="form-control"
                                value="<?=htmlspecialchars($settings->scan_rate??'3.00');
?>"
                                required>
                            <span class="input-group-text">per page</span>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Photocopy B/W Rate</label>
                        <div class="input-group">
                            <span class="input-group-text">R</span>
                            <input 
                                type="number"
                                step="0.01"
                                min="0"
                                name="photocopy_bw_rate"
                                class="form-control"
                                value="<?=htmlspecialchars($settings->photocopy_bw_rate??'1.00');
?>"
                                required>
                            <span class="input-group-text">per page</span>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Lamination Rate</label>
                        <div class="input-group">
                            <span class="input-group-text">R</span>
                            <input 
                                type="number"
                                step="0.01"
                                min="0"
                                name="lamination_rate"
                                class="form-control"
                                value="<?=htmlspecialchars($settings->lamination_rate??'10.00');
?>"
                                required>
                            <span class="input-group-text">each</span>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Binding Rate</label>
                        <div class="input-group">
                            <span class="input-group-text">R</span>
                            <input 
                                type="number"
                                step="0.01"
                                min="0"
                                name="binding_rate"
                                class="form-control"
                                value="<?=htmlspecialchars($settings->binding_rate??'15.00');
?>"
                                required>
                            <span class="input-group-text">each</span>
                        </div>
                    </div>

                </div>

                <div class="alert alert-success-subtle border-0 mt-4 mb-0">
                    <strong>Note:</strong> New print jobs will use the latest prices. Existing sales will not be changed.
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn btn-success" id="savePricingBtn">
                        <i class="bi bi-save me-1"></i>
                        Save Pricing
                    </button>
                </div>

            </form>

        </div>
    </div>

</main>

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
