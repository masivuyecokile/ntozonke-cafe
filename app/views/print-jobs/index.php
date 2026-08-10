<?php require_once __DIR__.'/../layouts/header.php';
?>
<?php require_once __DIR__.'/../layouts/sidebar.php';
?>

<main class="main-content">

    <div class="topbar">
        <div>
            <h4 class="mb-0">Print Jobs</h4>
            <small class="text-muted">Approve, reject, and track all printing jobs.</small>
        </div>

        <div class="topbar-actions">
            <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#testPrintJobModal">
                <i class="bi bi-plus-circle me-1"></i>
                Test Incoming Job
            </button>

            <button class="btn btn-outline-success btn-sm" data-bs-toggle="modal" data-bs-target="#adminDirectPrintModal">
                <i class="bi bi-printer-fill me-1"></i>
                Admin Direct Print
            </button>

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
                <span>Total Jobs Today</span>
                <h3><?=(int)($stats->total_jobs??0);
?></h3>
            </div>
            <i class="bi bi-printer"></i>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card">
            <div>
                <span>Pending Jobs</span>
                <h3><?=(int)($stats->pending_jobs??0);
?></h3>
            </div>
            <i class="bi bi-hourglass-split"></i>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card">
            <div>
                <span>Pages Printed</span>
                <h3><?=(int)($stats->total_pages??0);
?></h3>
            </div>
            <i class="bi bi-file-earmark-text"></i>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card">
            <div>
                <span>Print Revenue</span>
                <h3>R<?=number_format((float)($stats->print_revenue??0), 2);
?></h3>
            </div>
            <i class="bi bi-cash-stack"></i>
        </div>
    </div>
</div>

<div class="section-heading mt-4">
    <h5>Incoming Print Queue</h5>
    <p>Customer PCs submit jobs here first. Only admin can approve and release printing.</p>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0 print-table">
                <thead>
                    <tr>
                        <th>Job</th>
                        <th>Source</th>
                        <th>Pages</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Time</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if(!$printJobs):?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                No print jobs yet.
                            </td>
                        </tr>
                    <?php endif;
?>

                    <?php foreach($printJobs as $job):?>
                        <?php
$statusClass=match($job->status) {
    'pending'=>'warning', 'printed'=>'success', 'approved'=>'info', 'rejected'=>'danger', 'held'=>'secondary', default=>'secondary'
};
$sourceText=$job->source==='admin_direct'?'Admin Direct':($job->pc_name??'Unknown PC');
?>

                    <tr>
                        <td>
                            <strong><?=htmlspecialchars($job->document_name);
?></strong><br>
                            <small class="text-muted"><?=htmlspecialchars($job->printer_name??'Print Queue');
?></small>
                        </td>

                        <td><?=htmlspecialchars($sourceText);
?></td>

                        <td>
                            <?=(int)$job->pages;
?> page(s)
                            <?php if((int)$job->copies>1):?>
                                Ã— <?=(int)$job->copies;
?> copies
                            <?php endif;
?>
                        </td>

                        <td>
                            <?=$job->print_type==='colour'?'Colour':'Black & White';
?>
                        </td>

                        <td>
                            <strong>R<?=number_format((float)$job->amount, 2);
?></strong>
                        </td>

                        <td>
                            <span class="badge bg-<?=$statusClass;
?>">
                            <?=ucfirst($job->status);
?>
                        </span>
                    </td>

                    <td>
                        <small><?=date('d M Y H:i', strtotime($job->created_at));
?></small>
                    </td>

                    <td class="text-end">
                        <?php if(in_array($job->status, ['pending', 'held'], true)):?>
                            <button
                            type="button"
                            class="btn btn-success btn-sm js-approve-print"
                            data-print-job-id="<?=$job->id;
?>"
                            data-document-name="<?=htmlspecialchars($job->document_name);
?>">
                            <i class="bi bi-check2-circle me-1"></i>
                            Approve
                        </button>

                        <button
                        type="button"
                        class="btn btn-outline-danger btn-sm js-reject-print"
                        data-print-job-id="<?=$job->id;
?>"
                        data-document-name="<?=htmlspecialchars($job->document_name);
?>">
                        <i class="bi bi-x-circle me-1"></i>
                        Reject
                    </button>
                <?php else:?>
                <span class="text-muted small">Processed</span>
            <?php endif;
?>
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

<div class="modal fade" id="testPrintJobModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form
        id="testPrintJobForm"
        class="modal-content"
        action="<?=BASE_URL;
?>/index.php?route=print-jobs.test-incoming"
        method="POST">

        <input type="hidden" name="csrf_token" value="<?=htmlspecialchars($csrfToken);
?>">

        <div class="modal-header">
            <div>
                <h5 class="modal-title">Test Incoming Print Job</h5>
                <small class="text-muted">Simulates a job from a customer PC.</small>
            </div>

            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">

            <div class="mb-3">
                <label class="form-label">Source PC</label>
                <select name="pc_id" class="form-select" required>
                    <option value="">Choose PC</option>
                    <?php foreach($pcs as $pc):?>
                        <option value="<?=$pc->id;
?>">
                        <?=htmlspecialchars($pc->pc_name);
?> - <?=ucfirst($pc->status);
?>
                    </option>
                <?php endforeach;
?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Document Name</label>
            <input
            type="text"
            name="document_name"
            class="form-control"
            value="Assignment.pdf"
            required>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Pages</label>
                <input type="number" name="pages" class="form-control" min="1" value="5" required>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Copies</label>
                <input type="number" name="copies" class="form-control" min="1" value="1" required>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Print Type</label>
            <select name="print_type" class="form-select" required>
                <option value="black_white">Black & White</option>
                <option value="colour">Colour</option>
            </select>
        </div>

        <div class="alert alert-success-subtle border-0 mb-0">
            Customer PCs will later send this automatically using the Python client.
        </div>

    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
            Cancel
        </button>

        <button type="submit" class="btn btn-success" id="testPrintJobBtn">
            <i class="bi bi-send me-1"></i>
            Send Test Job
        </button>
    </div>

</form>
</div>
</div>

<div class="modal fade" id="adminDirectPrintModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form
        id="adminDirectPrintForm"
        class="modal-content"
        action="<?=BASE_URL;
?>/index.php?route=print-jobs.admin-direct"
        method="POST">

        <input type="hidden" name="csrf_token" value="<?=htmlspecialchars($csrfToken);
?>">

        <div class="modal-header">
            <div>
                <h5 class="modal-title">Admin Direct Print</h5>
                <small class="text-muted">Record a print done directly from the admin/server PC.</small>
            </div>

            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">

            <div class="mb-3">
                <label class="form-label">Document Name</label>
                <input
                type="text"
                name="document_name"
                class="form-control"
                placeholder="Example: CV.pdf"
                required>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Pages</label>
                    <input type="number" name="pages" class="form-control" min="1" value="1" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Copies</label>
                    <input type="number" name="copies" class="form-control" min="1" value="1" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Print Type</label>
                <select name="print_type" class="form-select" required>
                    <option value="black_white">Black & White</option>
                    <option value="colour">Colour</option>
                </select>
            </div>

            <div class="alert alert-warning-subtle border-0 mb-0">
                This records the sale and print job. Later the admin Python watcher will detect this automatically.
            </div>

        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                Cancel
            </button>

            <button type="submit" class="btn btn-success" id="adminDirectPrintBtn">
                <i class="bi bi-printer-fill me-1"></i>
                Record Print
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

