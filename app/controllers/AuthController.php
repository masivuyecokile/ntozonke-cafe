<?php

require_once __DIR__ . '/../models/User.php';

class AuthController
{
    public function showLogin(): void
    {
        if (isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/index.php?route=dashboard');
            exit;
        }

        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $csrfToken = $_SESSION['csrf_token'];

        require_once __DIR__ . '/../views/auth/login.php';
    }

    public function ajaxLogin(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(false, 'Invalid request method.', [], 405);
        }

        $csrfToken = $_POST['csrf_token'] ?? '';

        if (!$csrfToken || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrfToken)) {
            jsonResponse(false, 'Security token expired. Please refresh and try again.', [], 419);
        }

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($username === '' || $password === '') {
            jsonResponse(false, 'Please enter username and password.', [], 422);
        }

        try {
            $userModel = new User();
            $user = $userModel->findActiveByUsername($username);

            if (!$user || !password_verify($password, $user->password)) {
                jsonResponse(false, 'Invalid username or password.', [], 401);
            }

            session_regenerate_id(true);

            $_SESSION['user_id'] = $user->id;
            $_SESSION['user_name'] = $user->name;
            $_SESSION['user_role'] = $user->role;

            jsonResponse(true, 'Login successful. Redirecting...', [
                'redirect' => BASE_URL . '/index.php?route=dashboard'
            ]);

        } catch (Exception $e) {
            jsonResponse(false, 'System error. Please try again.', [], 500);
        }
    }

    public function logout(): void
    {
        session_destroy();
        header('Location: ' . BASE_URL . '/index.php?route=login');
        exit;
    }
}