<?php

define('APP_DEBUG', true);

error_reporting(E_ALL);
ini_set('display_errors', APP_DEBUG ? '1' : '0');
ini_set('display_startup_errors', APP_DEBUG ? '1' : '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../storage/logs/php-error.log');

ob_start();

set_error_handler(function ($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

set_exception_handler(function (Throwable $e) {
    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    http_response_code(500);

    $isAjax = (
        isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
    );

    if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');

        echo json_encode([
            'success' => false,
            'message' => APP_DEBUG ? $e->getMessage() : 'Server error.',
            'debug' => APP_DEBUG ? [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ] : null
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    echo '<h2>PHP Error</h2>';
    echo '<p><strong>Message:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<p><strong>File:</strong> ' . htmlspecialchars($e->getFile()) . '</p>';
    echo '<p><strong>Line:</strong> ' . htmlspecialchars((string)$e->getLine()) . '</p>';
    echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    exit;
});

register_shutdown_function(function () {
    $error = error_get_last();

    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        http_response_code(500);

        $isAjax = (
            isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
        );

        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');

            echo json_encode([
                'success' => false,
                'message' => APP_DEBUG ? $error['message'] : 'Fatal server error.',
                'debug' => APP_DEBUG ? [
                    'file' => $error['file'],
                    'line' => $error['line']
                ] : null
            ], JSON_UNESCAPED_UNICODE);

            exit;
        }

        echo '<h2>Fatal PHP Error</h2>';
        echo '<p><strong>Message:</strong> ' . htmlspecialchars($error['message']) . '</p>';
        echo '<p><strong>File:</strong> ' . htmlspecialchars($error['file']) . '</p>';
        echo '<p><strong>Line:</strong> ' . htmlspecialchars((string)$error['line']) . '</p>';
        exit;
    }
});
ob_start();
session_start();

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/helpers/response.php';

$route = $_GET['route'] ?? 'login';

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
        require_once __DIR__ . '/../app/controllers/AuthController.php';
        $authController = new AuthController();
        $authController->showLogin();
        break;

    case 'auth.login':
        require_once __DIR__ . '/../app/controllers/AuthController.php';
        $authController = new AuthController();
        $authController->ajaxLogin();
        break;

    case 'logout':
        require_once __DIR__ . '/../app/controllers/AuthController.php';
        $authController = new AuthController();
        $authController->logout();
        break;

    case 'dashboard':
        require_once __DIR__ . '/../app/controllers/DashboardController.php';
        $dashboardController = new DashboardController();
        $dashboardController->index();
        break;

    case 'sessions.start':
        require_once __DIR__ . '/../app/controllers/SessionController.php';
        $sessionController = new SessionController();
        $sessionController->startAjax();
        break;

    case 'sessions.end':
        require_once __DIR__ . '/../app/controllers/SessionController.php';
        $sessionController = new SessionController();
        $sessionController->endAjax();
        break;

    case 'sessions.extend':
        require_once __DIR__ . '/../app/controllers/SessionController.php';
        $sessionController = new SessionController();
        $sessionController->extendAjax();
        break;

    case 'sessions.expire':
        require_once __DIR__ . '/../app/controllers/SessionController.php';
        $sessionController = new SessionController();
        $sessionController->expireAjax();
        break;

    case 'print-jobs':
        require_once __DIR__ . '/../app/controllers/PrintJobController.php';
        $printJobController = new PrintJobController();
        $printJobController->index();
        break;

    case 'print-jobs.test-incoming':
        require_once __DIR__ . '/../app/controllers/PrintJobController.php';
        $printJobController = new PrintJobController();
        $printJobController->testIncomingAjax();
        break;

    case 'print-jobs.approve':
        require_once __DIR__ . '/../app/controllers/PrintJobController.php';
        $printJobController = new PrintJobController();
        $printJobController->approveAjax();
        break;

    case 'print-jobs.reject':
        require_once __DIR__ . '/../app/controllers/PrintJobController.php';
        $printJobController = new PrintJobController();
        $printJobController->rejectAjax();
        break;

    case 'print-jobs.pending-summary':
        require_once __DIR__ . '/../app/controllers/PrintJobController.php';
        $printJobController = new PrintJobController();
        $printJobController->pendingSummaryAjax();
        break;

    case 'print-jobs.admin-direct':
        require_once __DIR__ . '/../app/controllers/PrintJobController.php';
        $printJobController = new PrintJobController();
        $printJobController->adminDirectAjax();
        break;

    case 'sales':
        require_once __DIR__ . '/../app/controllers/SaleController.php';
        $saleController = new SaleController();
        $saleController->index();
        break;

    case 'packages':
        require_once __DIR__ . '/../app/controllers/PackageController.php';
        $packageController = new PackageController();
        $packageController->index();
        break;

    case 'packages.store':
        require_once __DIR__ . '/../app/controllers/PackageController.php';
        $packageController = new PackageController();
        $packageController->storeAjax();
        break;

    case 'packages.update':
        require_once __DIR__ . '/../app/controllers/PackageController.php';
        $packageController = new PackageController();
        $packageController->updateAjax();
        break;

    case 'packages.toggle':
        require_once __DIR__ . '/../app/controllers/PackageController.php';
        $packageController = new PackageController();
        $packageController->toggleAjax();
        break;

    case 'pricing':
        require_once __DIR__ . '/../app/controllers/PricingController.php';
        $pricingController = new PricingController();
        $pricingController->index();
        break;

    case 'pricing.update':
        require_once __DIR__ . '/../app/controllers/PricingController.php';
        $pricingController = new PricingController();
        $pricingController->updateAjax();
        break;

    case 'services':
        require_once __DIR__ . '/../app/controllers/ServiceController.php';
        $serviceController = new ServiceController();
        $serviceController->index();
        break;

    case 'services.store':
        require_once __DIR__ . '/../app/controllers/ServiceController.php';
        $serviceController = new ServiceController();
        $serviceController->storeAjax();
        break;

    default:
        header('Location: ' . BASE_URL . '/index.php?route=login');
        exit;
}