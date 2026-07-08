<?php

session_start();

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/helpers/response.php';

require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/controllers/DashboardController.php';
require_once __DIR__ . '/../app/controllers/SessionController.php';

$route = $_GET['route'] ?? 'login';

$authController = new AuthController();
$dashboardController = new DashboardController();
$sessionController = new SessionController();

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

    case 'sessions.start':
    $sessionController->startAjax();
    break;

    case 'sessions.end':
    $sessionController->endAjax();
    break;

    default:
        header('Location: ' . BASE_URL . '/index.php?route=login');
        exit;
}