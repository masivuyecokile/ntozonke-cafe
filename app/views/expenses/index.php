<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

<main class="main-content">

    <div class="topbar">
        <div>
            <h4 class="mb-0">Expenses</h4>
            <small class="text-muted">Record and review daily business expenses.</small>
        </div>

        <div class="topbar-actions">
            <form method="GET" action="<?= BASE_URL; ?>/index.php" class="d-flex gap-2">
                <input type="hidden" name="route" value="expenses">

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

            <a href="<?= BASE_URL; ?>/index.php?route=dashboard" class="btn btn-outline-dark btn-sm">
                <i class="bi bi-grid me-1"></i>
                Dashboard
            </a>
        </div>
    </div>

    <div class="row g-3 mt-3">
        <div class="col-md-6">
            <div class="stat-card">
                <div>
                    <span>Total Expenses</span>
                    <h3>R<?= number_format((float)($stats->total_expense_amount ?? 0), 2); ?></h3>
                </div>
                <i class="bi bi-cash-stack"></i>
            </div>
        </div>

        <div class="col-md-6">
            <div class="stat-card">
                <div>
                    <span>Expense Entries</span>
                    <h3><?= (int)($stats->total_expenses ?? 0); ?></h3>
                </div>
                <i class="bi bi-receipt-cutoff"></i>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h6 class="mb-0 fw-bold">Record Expense</h6>
                    <small class="text-muted">Add daily operating expenses.</small>
                </div>

                <div class="card-body p-4">
                    <form 
                        id="expenseForm"
                        action="<?= BASE_URL; ?>/index.php?route=expenses.store"
                        method="POST">

                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken); ?>">

                        <div class="mb-3">
                            <label class="form-label">Expense Date</label>
                            <input 
                                type="date" 
                                name="expense_date" 
                                class="form-control"
                                value="<?= htmlspecialchars($selectedDate); ?>"
                                required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Category</label>
                            <select name="category" class="form-select" required>
                                <option value="paper">Paper</option>
                                <option value="ink_toner">Ink / Toner</option>
                                <option value="electricity">Electricity</option>
                                <option value="rent">Rent</option>
                                <option value="internet">Internet</option>
                                <option value="repairs">Repairs</option>
                                <option value="stock">Stock</option>
                                <option value="other">Other</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <input 
                                type="text" 
                                name="description" 
                                class="form-control"
                                placeholder="Example: Bought A4 paper"
                                required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Amount</label>
                            <div class="input-group">
                                <span class="input-group-text">R</span>
                                <input 
                                    type="number" 
                                    name="amount" 
                                    class="form-control"
                                    step="0.01"
                                    min="0.01"
                                    required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Payment Method</label>
                            <select name="payment_method" class="form-select">
                                <option value="cash">Cash</option>
                                <option value="card">Card</option>
                                <option value="eft">EFT</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-success w-100" id="saveExpenseBtn">
                            <i class="bi bi-save me-1"></i>
                            Save Expense
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h6 class="mb-0 fw-bold">Expense List</h6>
                    <small class="text-muted">Showing expenses for <?= date('d M Y', strtotime($selectedDate)); ?>.</small>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                            <tr>
                                <th>Category</th>
                                <th>Description</th>
                                <th>Payment</th>
                                <th>Created By</th>
                                <th class="text-end">Amount</th>
                            </tr>
                            </thead>

                            <tbody>
                            <?php if (!$expenses): ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-5">
                                        No expenses recorded for this date.
                                    </td>
                                </tr>
                            <?php endif; ?>

                            <?php foreach ($expenses as $expense): ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-secondary">
                                            <?= ucwords(str_replace('_', ' ', $expense->category)); ?>
                                        </span>
                                    </td>

                                    <td>
                                        <strong><?= htmlspecialchars($expense->description); ?></strong>
                                        <br>
                                        <small class="text-muted"><?= date('H:i', strtotime($expense->created_at)); ?></small>
                                    </td>

                                    <td><?= strtoupper($expense->payment_method); ?></td>

                                    <td><?= htmlspecialchars($expense->created_by_name ?? 'System'); ?></td>

                                    <td class="text-end">
                                        <strong>R<?= number_format((float)$expense->amount, 2); ?></strong>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>

                        </table>
                    </div>
                </div>
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