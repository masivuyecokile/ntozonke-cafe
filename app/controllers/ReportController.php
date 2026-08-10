<?php

require_once __DIR__ . '/../models/Sale.php';
require_once __DIR__ . '/../models/CafeSession.php';

class ReportController
{
    public function index(): void
    {
        $saleModel = new Sale();
        $sessionModel = new CafeSession();

        $selectedDate = $_GET['date'] ?? date('Y-m-d');

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $selectedDate)) {
            $selectedDate = date('Y-m-d');
        }

        $salesStats = $saleModel->getStatsByDate($selectedDate);
        $sessions = $sessionModel->getRecent(20);

        $csrfToken = $_SESSION['csrf_token'] ?? bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;

        require_once __DIR__ . '/../views/reports/index.php';
    }
}