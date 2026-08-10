<?php

require_once __DIR__ . '/../models/Sale.php';

class SaleController
{
    public function index(): void
    {
        $saleModel = new Sale();

        $period = $_GET['period'] ?? 'today';

        /**
         * Backward support for old links like:
         * index.php?route=sales&date=2026-08-10
         */
        if (isset($_GET['date']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['date'])) {
            $_GET['start_date'] = $_GET['date'];
            $_GET['end_date'] = $_GET['date'];
            $period = 'custom';
        }

        [$startDate, $endDate, $periodLabel] = $this->resolveDateRange($period);

        $stats = $saleModel->getStatsByRange($startDate, $endDate);
        $sales = $saleModel->getByRange($startDate, $endDate);

        $csrfToken = $_SESSION['csrf_token'] ?? bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;

        require_once __DIR__ . '/../views/sales/index.php';
    }

    private function resolveDateRange(string $period): array
    {
        $timezone = new DateTimeZone('Africa/Johannesburg');
        $today = new DateTime('today', $timezone);

        switch ($period) {
            case 'yesterday':
                $start = (clone $today)->modify('-1 day');
                $end = (clone $today)->modify('-1 day');
                $label = 'Yesterday';
                break;

            case 'this_week':
                $start = (clone $today)->modify('monday this week');
                $end = (clone $today)->modify('sunday this week');
                $label = 'This Week';
                break;

            case 'last_week':
                $start = (clone $today)->modify('monday last week');
                $end = (clone $today)->modify('sunday last week');
                $label = 'Last Week';
                break;

            case 'this_month':
                $start = new DateTime($today->format('Y-m-01'), $timezone);
                $end = new DateTime($today->format('Y-m-t'), $timezone);
                $label = 'This Month';
                break;

            case 'last_month':
                $start = (clone $today)->modify('first day of last month');
                $end = (clone $today)->modify('last day of last month');
                $label = 'Last Month';
                break;

            case 'this_quarter':
                [$start, $end] = $this->getQuarterRange((int)$today->format('Y'), (int)$today->format('n'));
                $label = 'This Quarter';
                break;

            case 'last_quarter':
                $currentMonth = (int)$today->format('n');
                $currentYear = (int)$today->format('Y');
                $currentQuarter = (int)ceil($currentMonth / 3);

                $lastQuarter = $currentQuarter - 1;
                $year = $currentYear;

                if ($lastQuarter < 1) {
                    $lastQuarter = 4;
                    $year--;
                }

                $startMonth = (($lastQuarter - 1) * 3) + 1;
                [$start, $end] = $this->getQuarterRange($year, $startMonth);
                $label = 'Last Quarter';
                break;

            case 'this_year':
                $start = new DateTime($today->format('Y-01-01'), $timezone);
                $end = new DateTime($today->format('Y-12-31'), $timezone);
                $label = 'This Year';
                break;

            case 'custom':
                $customStart = $_GET['start_date'] ?? '';
                $customEnd = $_GET['end_date'] ?? '';

                if (
                    preg_match('/^\d{4}-\d{2}-\d{2}$/', $customStart) &&
                    preg_match('/^\d{4}-\d{2}-\d{2}$/', $customEnd)
                ) {
                    $start = new DateTime($customStart, $timezone);
                    $end = new DateTime($customEnd, $timezone);

                    if ($start > $end) {
                        $start = clone $today;
                        $end = clone $today;
                        $label = 'Today';
                        $period = 'today';
                    } else {
                        $label = 'Custom Range';
                    }
                } else {
                    $start = clone $today;
                    $end = clone $today;
                    $label = 'Today';
                    $period = 'today';
                }
                break;

            case 'today':
            default:
                $start = clone $today;
                $end = clone $today;
                $label = 'Today';
                $period = 'today';
                break;
        }

        return [
            $start->format('Y-m-d'),
            $end->format('Y-m-d'),
            $label
        ];
    }

    private function getQuarterRange(int $year, int $month): array
    {
        $quarter = (int)ceil($month / 3);
        $startMonth = (($quarter - 1) * 3) + 1;
        $endMonth = $startMonth + 2;

        $timezone = new DateTimeZone('Africa/Johannesburg');

        $start = new DateTime(sprintf('%04d-%02d-01', $year, $startMonth), $timezone);
        $end = new DateTime(sprintf('%04d-%02d-01', $year, $endMonth), $timezone);
        $end->modify('last day of this month');

        return [$start, $end];
    }
}