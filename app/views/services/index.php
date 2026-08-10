<?php require_once __DIR__.'/../layouts/header.php';
?>
<?php require_once __DIR__.'/../layouts/sidebar.php';
?>

<main class="main-content">

    <div class="topbar">
        <div>
            <h4 class="mb-0">Services Sales</h4>
            <small class="text-muted">Record scanning, photocopying, lamination, and binding sales.</small>
        </div>

        <div class="topbar-actions">
            <a href="<?=BASE_URL;
?>/index.php?route=sales" class="btn btn-outline-success btn-sm">
                <i class="bi bi-cash-stack me-1"></i>
                View Sales
            </a>

            <a href="<?=BASE_URL;
?>/index.php?route=dashboard" class="btn btn-outline-dark btn-sm">
                <i class="bi bi-grid me-1"></i>
                Dashboard
            </a>
        </div>
    </div>

    <div class="section-heading mt-4">
        <h5>Record Service Sale</h5>
        <p>Select the service, enter quantity, and the system will calculate the total.</p>
    </div>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">

                    <form 
                        id="serviceSaleForm"
                        action="<?=BASE_URL;
?>/index.php?route=services.store"
                        method="POST">

                        <input type="hidden" name="csrf_token" value="<?=htmlspecialchars($csrfToken);
?>">

                        <div class="mb-3">
                            <label class="form-label">Service</label>
                            <select name="service_type" id="serviceType" class="form-select" required>
                                <option value="">Choose service</option>
                                <option value="scan" data-rate="<?=htmlspecialchars($settings->scan_rate??'3.00');
?>">
                                    Scanning - R<?=number_format((float)($settings->scan_rate??3.00), 2);
?> per page
                                </option>
                                <option value="photocopy_bw" data-rate="<?=htmlspecialchars($settings->photocopy_bw_rate??'1.00');
?>">
                                    Photocopy B/W - R<?=number_format((float)($settings->photocopy_bw_rate??1.00), 2);
?> per page
                                </option>
                                <option value="lamination" data-rate="<?=htmlspecialchars($settings->lamination_rate??'10.00');
?>">
                                    Lamination - R<?=number_format((float)($settings->lamination_rate??10.00), 2);
?> each
                                </option>
                                <option value="binding" data-rate="<?=htmlspecialchars($settings->binding_rate??'15.00');
?>">
                                    Binding - R<?=number_format((float)($settings->binding_rate??15.00), 2);
?> each
                                </option>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Quantity / Pages</label>
                                <input 
                                    type="number" 
                                    name="quantity" 
                                    id="serviceQuantity"
                                    class="form-control" 
                                    min="1"
                                    value="1"
                                    required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Payment Method</label>
                                <select name="payment_method" id="servicePaymentMethod" class="form-select" required>
                                    <option value="cash">Cash</option>
                                    <option value="card">Card</option>
                                    <option value="eft">EFT</option>
                                    <option value="free">Free / Comp</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <input 
                                type="text" 
                                name="notes" 
                                class="form-control"
                                placeholder="Optional: customer name, document type, etc.">
                        </div>

                        <div class="service-total-box">
                            <span>Total Amount</span>
                            <strong id="serviceTotalAmount">R0.00</strong>
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" class="btn btn-success" id="saveServiceSaleBtn">
                                <i class="bi bi-check-circle me-1"></i>
                                Record Sale
                            </button>
                        </div>

                    </form>

                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h6 class="mb-0 fw-bold">Current Service Rates</h6>
                    <small class="text-muted">Change these under Services Pricing.</small>
                </div>

                <div class="card-body px-4">
                    <div class="payment-row">
                        <span>Scanning</span>
                        <strong>R<?=number_format((float)($settings->scan_rate??3.00), 2);
?></strong>
                    </div>

                    <div class="payment-row">
                        <span>Photocopy B/W</span>
                        <strong>R<?=number_format((float)($settings->photocopy_bw_rate??1.00), 2);
?></strong>
                    </div>

                    <div class="payment-row">
                        <span>Lamination</span>
                        <strong>R<?=number_format((float)($settings->lamination_rate??10.00), 2);
?></strong>
                    </div>

                    <div class="payment-row">
                        <span>Binding</span>
                        <strong>R<?=number_format((float)($settings->binding_rate??15.00), 2);
?></strong>
                    </div>

                    <a href="<?=BASE_URL;
?>/index.php?route=pricing" class="btn btn-outline-success w-100 mt-3">
                        <i class="bi bi-currency-exchange me-1"></i>
                        Edit Pricing
                    </a>
                </div>
            </div>
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
