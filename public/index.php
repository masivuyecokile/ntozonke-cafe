<?php

ob_start();
session_start();

require_once __DIR__ . '/../app/config/bootstrap.php';

$route = $_GET['route'] ?? 'login';

$publicRoutes = [
    'login',
    'auth.login',
    'client.heartbeat',
    'client.register',
    'client.status',
    'client.session',
    'client.print-job'
];

if (!isset($_SESSION['user_id']) && !in_array($route, $publicRoutes, true)) {
    header('Location: ' . BASE_URL . '/index.php?route=login');
    exit;
}

$routes = [
    /*
     * Auth
     */
    'login' => [AuthController::class, 'showLogin'],
    'auth.login' => [AuthController::class, 'ajaxLogin'],
    'logout' => [AuthController::class, 'logout'],

    /*
     * Dashboard
     */
    'dashboard' => [DashboardController::class, 'index'],

    /*
     * PC Stations
     */
    'pc-stations' => [PCStationController::class, 'index'],
    'pc-stations.store' => [PCStationController::class, 'storeAjax'],
    'pc-stations.update' => [PCStationController::class, 'updateAjax'],
    'pc-stations.status' => [PCStationController::class, 'statusAjax'],
    'pc-stations.approve' => [PCStationController::class, 'approveAjax'],
    'pc-stations.reject' => [PCStationController::class, 'rejectAjax'],

    /*
     * Sessions
     */
    'sessions' => [SessionController::class, 'index'],
    'sessions.start' => [SessionController::class, 'startAjax'],
    'sessions.end' => [SessionController::class, 'endAjax'],
    'sessions.extend' => [SessionController::class, 'extendAjax'],
    'sessions.expire' => [SessionController::class, 'expireAjax'],

    /*
     * Print Jobs
     */
    'print-jobs' => [PrintJobController::class, 'index'],
    'print-jobs.test-incoming' => [PrintJobController::class, 'testIncomingAjax'],
    'print-jobs.approve' => [PrintJobController::class, 'approveAjax'],
    'print-jobs.reject' => [PrintJobController::class, 'rejectAjax'],
    'print-jobs.pending-summary' => [PrintJobController::class, 'pendingSummaryAjax'],
    'print-jobs.admin-direct' => [PrintJobController::class, 'adminDirectAjax'],

    /*
     * Members
     */
    'members' => [MemberController::class, 'index'],

    /*
     * Sales
     */
    'sales' => [SaleController::class, 'index'],

    /*
     * Expenses
     */
    'expenses' => [ExpenseController::class, 'index'],
    'expenses.store' => [ExpenseController::class, 'storeAjax'],

    /*
     * Reports
     */
    'reports' => [ReportController::class, 'index'],

    /*
     * Packages
     */
    'packages' => [PackageController::class, 'index'],
    'packages.store' => [PackageController::class, 'storeAjax'],
    'packages.update' => [PackageController::class, 'updateAjax'],
    'packages.toggle' => [PackageController::class, 'toggleAjax'],

    /*
     * Pricing
     */
    'pricing' => [PricingController::class, 'index'],
    'pricing.update' => [PricingController::class, 'updateAjax'],

    /*
     * Services Sales
     */
    'services' => [ServiceController::class, 'index'],
    'services.store' => [ServiceController::class, 'storeAjax'],

    /*
     * Settings
     */
    'settings' => [SettingsController::class, 'index'],

    /*
     * Python Client API
     */
    'client.heartbeat' => [ClientApiController::class, 'heartbeat'],
    'client.status' => [ClientApiController::class, 'status'],
    'client.session' => [ClientApiController::class, 'session'],
    'client.print-job' => [ClientApiController::class, 'printJob'],
    'client.register' => [ClientApiController::class, 'register'],
];

if (!isset($routes[$route])) {
    header('Location: ' . BASE_URL . '/index.php?route=login');
    exit;
}

[$controllerClass, $method] = $routes[$route];

if (!class_exists($controllerClass)) {
    http_response_code(500);
    echo 'Controller not found: ' . htmlspecialchars($controllerClass);
    exit;
}

$controller = new $controllerClass();

if (!method_exists($controller, $method)) {
    http_response_code(500);
    echo 'Method not found: ' . htmlspecialchars($controllerClass . '::' . $method);
    exit;
}

$controller->$method();