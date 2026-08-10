<?php
require_once __DIR__.'/../models/InternetPackage.php';
require_once __DIR__.'/../models/CafeSession.php';
class SessionController {
    public function startAjax():void {
        if($_SERVER['REQUEST_METHOD']!=='POST') {
            jsonResponse(false, 'Invalid request method.', [], 405);
        }$csrfToken=$_POST['csrf_token']??'';
        if(!$csrfToken||!isset($_SESSION['csrf_token'])||!hash_equals($_SESSION['csrf_token'], $csrfToken)) {
            jsonResponse(false, 'Security token expired. Please refresh and try again.', [], 419);
        }$pcId=(int)($_POST['pc_id']??0);
        $packageId=(int)($_POST['package_id']??0);
        $customerName=trim($_POST['customer_name']??'Walk-in Customer');
        if($pcId<=0||$packageId<=0) {
            jsonResponse(false, 'Please select a valid PC and package.', [], 422);
        }if($customerName==='') {
            $customerName='Walk-in Customer';
        }try {
            $packageModel=new InternetPackage();
            $package=$packageModel->findActiveById($packageId);
            if(!$package) {
                jsonResponse(false, 'Selected package was not found.', [], 404);
            }$ratePerMinute=$package->price/$package->minutes;
            $sessionModel=new CafeSession();
            $sessionId=$sessionModel->startSession(['pc_id'=>$pcId, 'customer_name'=>$customerName, 'minutes'=>(int)$package->minutes, 'rate_per_minute'=>$ratePerMinute, 'amount_due'=>(float)$package->price, 'created_by'=>(int)$_SESSION['user_id']]);
            jsonResponse(true, 'Session started successfully.', ['session_id'=>$sessionId]);
        }catch(Exception $e) {
            jsonResponse(false, $e->getMessage(), [], 500);
        }
    }public function endAjax():void {
        if($_SERVER['REQUEST_METHOD']!=='POST') {
            jsonResponse(false, 'Invalid request method.', [], 405);
        }$csrfToken=$_POST['csrf_token']??'';
        if(!$csrfToken||!isset($_SESSION['csrf_token'])||!hash_equals($_SESSION['csrf_token'], $csrfToken)) {
            jsonResponse(false, 'Security token expired. Please refresh and try again.', [], 419);
        }$sessionId=(int)($_POST['session_id']??0);
        if($sessionId<=0) {
            jsonResponse(false, 'Invalid session selected.', [], 422);
        }try {
            $sessionModel=new CafeSession();
            $session=$sessionModel->endSession($sessionId, (int)$_SESSION['user_id']);
            jsonResponse(true, 'Session ended successfully. PC has been locked.', ['session_id'=>$sessionId, 'pc_id'=>$session->pc_id]);
        }catch(Exception $e) {
            jsonResponse(false, $e->getMessage(), [], 500);
        }
    }public function extendAjax():void {
        if($_SERVER['REQUEST_METHOD']!=='POST') {
            jsonResponse(false, 'Invalid request method.', [], 405);
        }$csrfToken=$_POST['csrf_token']??'';
        if(!$csrfToken||!isset($_SESSION['csrf_token'])||!hash_equals($_SESSION['csrf_token'], $csrfToken)) {
            jsonResponse(false, 'Security token expired. Please refresh and try again.', [], 419);
        }$sessionId=(int)($_POST['session_id']??0);
        $packageId=(int)($_POST['package_id']??0);
        if($sessionId<=0||$packageId<=0) {
            jsonResponse(false, 'Please select a valid session and package.', [], 422);
        }try {
            $packageModel=new InternetPackage();
            $package=$packageModel->findActiveById($packageId);
            if(!$package) {
                jsonResponse(false, 'Selected extension package was not found.', [], 404);
            }$sessionModel=new CafeSession();
            $updatedSession=$sessionModel->extendSession($sessionId, (int)$package->minutes, (float)$package->price, (int)$_SESSION['user_id']);
            jsonResponse(true, 'Session extended successfully.', ['session_id'=>$sessionId, 'new_end_time'=>$updatedSession->end_time, 'amount_due'=>$updatedSession->amount_due]);
        }catch(Exception $e) {
            jsonResponse(false, $e->getMessage(), [], 500);
        }
    }public function expireAjax():void {
        if($_SERVER['REQUEST_METHOD']!=='POST') {
            jsonResponse(false, 'Invalid request method.', [], 405);
        }$csrfToken=$_POST['csrf_token']??'';
        if(!$csrfToken||!isset($_SESSION['csrf_token'])||!hash_equals($_SESSION['csrf_token'], $csrfToken)) {
            jsonResponse(false, 'Security token expired. Please refresh and try again.', [], 419);
        }try {
            $sessionModel=new CafeSession();
            $result=$sessionModel->expireOverdueSessions();
            jsonResponse(true, 'Expired sessions checked successfully.', $result);
        }catch(Exception $e) {
            jsonResponse(false, $e->getMessage(), [], 500);
        }
    }

public function index(): void
{
    $sessionModel = new CafeSession();

    $period = $_GET['period'] ?? 'today';

    /*
     * Backward support for old links like:
     * index.php?route=sessions&date=2026-08-10
     */
    if (isset($_GET['date']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['date'])) {
        $_GET['start_date'] = $_GET['date'];
        $_GET['end_date'] = $_GET['date'];
        $period = 'custom';
    }

    [$startDate, $endDate, $periodLabel] = $this->resolveDateRange($period);

    $stats = $sessionModel->getStatsByRange($startDate, $endDate);
    $sessions = $sessionModel->getByRange($startDate, $endDate);

    $csrfToken = $_SESSION['csrf_token'] ?? bin2hex(random_bytes(32));
    $_SESSION['csrf_token'] = $csrfToken;

    require_once __DIR__ . '/../views/sessions/index.php';
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
                } else {
                    $label = 'Custom Range';
                }
            } else {
                $start = clone $today;
                $end = clone $today;
                $label = 'Today';
            }
            break;

        case 'today':
        default:
            $start = clone $today;
            $end = clone $today;
            $label = 'Today';
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
