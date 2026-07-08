<?php

session_start();

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/helpers/response.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';

$route = $_GET['route'] ?? 'login';

$authController = new AuthController();

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
        require_once __DIR__ . '/../app/views/dashboard/index.php';
        break;

    default:
        header('Location: ' . BASE_URL . '/index.php?route=login');
        exit;
}