<?php

require_once __DIR__ . '/../models/PC.php';
require_once __DIR__ . '/../models/InternetPackage.php';
require_once __DIR__ . '/../models/CafeSession.php';
require_once __DIR__ . '/../models/Sale.php';
require_once __DIR__ . '/../models/Expense.php';
require_once __DIR__ . '/../models/PrintJob.php';

class DashboardController
{
    public function index(): void
    {
        $pcModel = new PC();
        $packageModel = new InternetPackage();
        $sessionModel = new CafeSession();
        $saleModel = new Sale();
        $expenseModel = new Expense();
        $printJobModel = new PrintJob();

        $pcs = $pcModel->getAll();
        $packages = $packageModel->getActive();
        $activeSessionsByPc = $sessionModel->getActiveSessionsIndexedByPc();

        /*
         * PC counts.
         * These individual variables are kept because dashboard/index.php uses them.
         */
        $totalPCs = count($pcs);
        $activePCs = $pcModel->countByStatus('active');
        $lockedPCs = $pcModel->countByStatus('locked');
        $offlinePCs = $pcModel->countByStatus('offline');

        $stats = [
            'total_pcs' => $totalPCs,
            'active_pcs' => $activePCs,
            'locked_pcs' => $lockedPCs,
            'offline_pcs' => $offlinePCs
        ];

        /*
         * Today business summary.
         */
        $todayStats = $saleModel->getTodayStats();
        $todayExpenseStats = $expenseModel->getStatsByDate(date('Y-m-d'));
        $recentSales = $saleModel->getRecentToday(8);
        $printStats = $printJobModel->getStatsToday();

        $todayRevenue = (float)($todayStats->total_revenue ?? 0);
        $todayExpenses = (float)($todayExpenseStats->total_expense_amount ?? 0);
        $todayNetProfit = $todayRevenue - $todayExpenses;
        $pendingPrintJobs = (int)($printStats->pending_jobs ?? 0);

        $csrfToken = $_SESSION['csrf_token'] ?? bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;

        require_once __DIR__ . '/../views/dashboard/index.php';
    }
}