<?php
session_start();

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/config/database.php';

$page = $_GET['page'] ?? 'login';

if ($page === 'logout') {
    session_destroy();
    header('Location: ' . BASE_URL . '/index.php?page=login');
    exit;
}

if (!isset($_SESSION['user_id']) && $page !== 'login') {
    header('Location: ' . BASE_URL . '/index.php?page=login');
    exit;
}

switch ($page) {
    case 'login':
        require_once __DIR__ . '/../app/views/auth/login.php';
        break;

    case 'dashboard':
        require_once __DIR__ . '/../app/views/dashboard/index.php';
        break;

    default:
        require_once __DIR__ . '/../app/views/auth/login.php';
        break;
}