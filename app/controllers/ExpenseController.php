<?php

require_once __DIR__ . '/../models/Expense.php';

class ExpenseController
{
    public function index(): void
    {
        $expenseModel = new Expense();

        $selectedDate = $_GET['date'] ?? date('Y-m-d');

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $selectedDate)) {
            $selectedDate = date('Y-m-d');
        }

        $stats = $expenseModel->getStatsByDate($selectedDate);
        $expenses = $expenseModel->getByDate($selectedDate);

        $csrfToken = $_SESSION['csrf_token'] ?? bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;

        require_once __DIR__ . '/../views/expenses/index.php';
    }

    public function storeAjax(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(false, 'Invalid request method.', [], 405);
        }

        $this->checkCsrf();

        $expenseDate = $_POST['expense_date'] ?? date('Y-m-d');
        $category = $_POST['category'] ?? 'other';
        $description = trim($_POST['description'] ?? '');
        $amount = (float)($_POST['amount'] ?? 0);
        $paymentMethod = $_POST['payment_method'] ?? 'cash';

        $allowedCategories = [
            'paper',
            'ink_toner',
            'electricity',
            'rent',
            'internet',
            'repairs',
            'stock',
            'other'
        ];

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $expenseDate)) {
            jsonResponse(false, 'Invalid expense date.', [], 422);
        }

        if (!in_array($category, $allowedCategories, true)) {
            jsonResponse(false, 'Invalid expense category.', [], 422);
        }

        if ($description === '') {
            jsonResponse(false, 'Expense description is required.', [], 422);
        }

        if ($amount <= 0) {
            jsonResponse(false, 'Amount must be greater than zero.', [], 422);
        }

        if (!in_array($paymentMethod, ['cash', 'card', 'eft'], true)) {
            jsonResponse(false, 'Invalid payment method.', [], 422);
        }

        try {
            $expenseModel = new Expense();

            $expenseId = $expenseModel->create([
                'expense_date' => $expenseDate,
                'category' => $category,
                'description' => $description,
                'amount' => $amount,
                'payment_method' => $paymentMethod
            ], (int)$_SESSION['user_id']);

            jsonResponse(true, 'Expense recorded successfully.', [
                'expense_id' => $expenseId
            ]);

        } catch (Throwable $e) {
            jsonResponse(false, $e->getMessage(), [], 500);
        }
    }

    private function checkCsrf(): void
    {
        $csrfToken = $_POST['csrf_token'] ?? '';

        if (!$csrfToken || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrfToken)) {
            jsonResponse(false, 'Security token expired. Please refresh and try again.', [], 419);
        }
    }
}