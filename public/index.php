<?php

ob_start();
session_start();

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/helpers/response.php';

require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/controllers/DashboardController.php';
require_once __DIR__ . '/../app/controllers/SessionController.php';
require_once __DIR__ . '/../app/controllers/PrintJobController.php';
require_once __DIR__ . '/../app/controllers/SaleController.php';
require_once __DIR__ . '/../app/controllers/PackageController.php';
require_once __DIR__ . '/../app/controllers/PricingController.php';
require_once __DIR__ . '/../app/controllers/ServiceController.php';
require_once __DIR__ . '/../app/controllers/PCStationController.php';
require_once __DIR__ . '/../app/controllers/ReportController.php';
require_once __DIR__ . '/../app/controllers/MemberController.php';
require_once __DIR__ . '/../app/controllers/SettingsController.php';
require_once __DIR__ . '/../app/controllers/ExpenseController.php';

$route = $_GET['route'] ?? 'login';

$authController = new AuthController();
$dashboardController = new DashboardController();
$sessionController = new SessionController();
$printJobController = new PrintJobController();
$saleController = new SaleController();
$packageController = new PackageController();
$pricingController = new PricingController();
$serviceController = new ServiceController();
$pcStationController = new PCStationController();
$reportController = new ReportController();
$memberController = new MemberController();
$settingsController = new SettingsController();
$expenseController = new ExpenseController();


$publicRoutes = [
    'login',
    'auth.login'
];

if (!isset($_SESSION['user_id']) && !in_array($route, $publicRoutes, true)) {
    header('Location: ' . BASE_URL . '/index.php?route=login');
    exit;
}

switch ($route) {

    case 'login':
        $authController->showLogin();
        break;

    case 'auth.login':
        $authController->ajaxLogin();
        break;

    case 'logout':
        $authController->logout();
        break;

    case 'dashboard':
        $dashboardController->index();
        break;

    case 'pc-stations':
        $pcStationController->index();
        break;

    case 'sessions':
        $sessionController->index();
        break;

    case 'sessions.start':
        $sessionController->startAjax();
        break;

    case 'sessions.end':
        $sessionController->endAjax();
        break;

    case 'sessions.extend':
        $sessionController->extendAjax();
        break;

    case 'sessions.expire':
        $sessionController->expireAjax();
        break;

    case 'print-jobs':
        $printJobController->index();
        break;

    case 'print-jobs.test-incoming':
        $printJobController->testIncomingAjax();
        break;

    case 'print-jobs.approve':
        $printJobController->approveAjax();
        break;

    case 'print-jobs.reject':
        $printJobController->rejectAjax();
        break;

    case 'print-jobs.pending-summary':
        $printJobController->pendingSummaryAjax();
        break;

    case 'print-jobs.admin-direct':
        $printJobController->adminDirectAjax();
        break;

    case 'members':
        $memberController->index();
        break;

    case 'sales':
        $saleController->index();
        break;

    case 'reports':
        $reportController->index();
        break;

    case 'packages':
        $packageController->index();
        break;

    case 'packages.store':
        $packageController->storeAjax();
        break;

    case 'packages.update':
        $packageController->updateAjax();
        break;

    case 'packages.toggle':
        $packageController->toggleAjax();
        break;

    case 'pricing':
        $pricingController->index();
        break;

    case 'pricing.update':
        $pricingController->updateAjax();
        break;

    case 'services':
        $serviceController->index();
        break;

    case 'services.store':
        $serviceController->storeAjax();
        break;

    case 'settings':
        $settingsController->index();
        break;

    case 'expenses':
    $expenseController->index();
    break;

case 'expenses.store':
    $expenseController->storeAjax();
    break;

    default:
        header('Location: ' . BASE_URL . '/index.php?route=login');
        exit;
}