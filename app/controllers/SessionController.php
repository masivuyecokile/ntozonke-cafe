<?php

require_once __DIR__ . '/../models/InternetPackage.php';
require_once __DIR__ . '/../models/CafeSession.php';

class SessionController
{
    public function startAjax(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(false, 'Invalid request method.', [], 405);
        }

        $csrfToken = $_POST['csrf_token'] ?? '';

        if (!$csrfToken || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrfToken)) {
            jsonResponse(false, 'Security token expired. Please refresh and try again.', [], 419);
        }

        $pcId = (int)($_POST['pc_id'] ?? 0);
        $packageId = (int)($_POST['package_id'] ?? 0);
        $customerName = trim($_POST['customer_name'] ?? 'Walk-in Customer');

        if ($pcId <= 0 || $packageId <= 0) {
            jsonResponse(false, 'Please select a valid PC and package.', [], 422);
        }

        if ($customerName === '') {
            $customerName = 'Walk-in Customer';
        }

        try {
            $packageModel = new InternetPackage();
            $package = $packageModel->findActiveById($packageId);

            if (!$package) {
                jsonResponse(false, 'Selected package was not found.', [], 404);
            }

            $ratePerMinute = $package->price / $package->minutes;

            $sessionModel = new CafeSession();

            $sessionId = $sessionModel->startSession([
                'pc_id' => $pcId,
                'customer_name' => $customerName,
                'minutes' => (int)$package->minutes,
                'rate_per_minute' => $ratePerMinute,
                'amount_due' => (float)$package->price,
                'created_by' => (int)$_SESSION['user_id']
            ]);

            jsonResponse(true, 'Session started successfully.', [
                'session_id' => $sessionId
            ]);

        } catch (Exception $e) {
            jsonResponse(false, $e->getMessage(), [], 500);
        }
    }

    public function endAjax(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(false, 'Invalid request method.', [], 405);
        }

        $csrfToken = $_POST['csrf_token'] ?? '';

        if (!$csrfToken || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrfToken)) {
            jsonResponse(false, 'Security token expired. Please refresh and try again.', [], 419);
        }

        $sessionId = (int)($_POST['session_id'] ?? 0);

        if ($sessionId <= 0) {
            jsonResponse(false, 'Invalid session selected.', [], 422);
        }

        try {
            $sessionModel = new CafeSession();
            $session = $sessionModel->endSession($sessionId, (int)$_SESSION['user_id']);

            jsonResponse(true, 'Session ended successfully. PC has been locked.', [
                'session_id' => $sessionId,
                'pc_id' => $session->pc_id
            ]);

        } catch (Exception $e) {
            jsonResponse(false, $e->getMessage(), [], 500);
        }
    }

    public function extendAjax(): void
    {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(false, 'Invalid request method.', [], 405);
    }

    $csrfToken = $_POST['csrf_token'] ?? '';

    if (!$csrfToken || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrfToken)) {
        jsonResponse(false, 'Security token expired. Please refresh and try again.', [], 419);
    }

    $sessionId = (int)($_POST['session_id'] ?? 0);
    $packageId = (int)($_POST['package_id'] ?? 0);

    if ($sessionId <= 0 || $packageId <= 0) {
        jsonResponse(false, 'Please select a valid session and package.', [], 422);
    }

    try {
        $packageModel = new InternetPackage();
        $package = $packageModel->findActiveById($packageId);

        if (!$package) {
            jsonResponse(false, 'Selected extension package was not found.', [], 404);
        }

        $sessionModel = new CafeSession();

        $updatedSession = $sessionModel->extendSession(
            $sessionId,
            (int)$package->minutes,
            (float)$package->price,
            (int)$_SESSION['user_id']
        );

        jsonResponse(true, 'Session extended successfully.', [
            'session_id' => $sessionId,
            'new_end_time' => $updatedSession->end_time,
            'amount_due' => $updatedSession->amount_due
        ]);

    } catch (Exception $e) {
        jsonResponse(false, $e->getMessage(), [], 500);
    }
}

public function expireAjax(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(false, 'Invalid request method.', [], 405);
    }

    $csrfToken = $_POST['csrf_token'] ?? '';

    if (!$csrfToken || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrfToken)) {
        jsonResponse(false, 'Security token expired. Please refresh and try again.', [], 419);
    }

    try {
        $sessionModel = new CafeSession();
        $result = $sessionModel->expireOverdueSessions();

        jsonResponse(true, 'Expired sessions checked successfully.', $result);

    } catch (Exception $e) {
        jsonResponse(false, $e->getMessage(), [], 500);
    }
}
}