<?php

require_once __DIR__ . '/../models/PC.php';
require_once __DIR__ . '/../models/CafeSession.php';

class PCStationController
{
    public function index(): void
    {
        $pcModel = new PC();
        $sessionModel = new CafeSession();

        $pcs = $pcModel->getAll();
        $activeSessionsByPc = $sessionModel->getActiveSessionsIndexedByPc();

        $csrfToken = $_SESSION['csrf_token'] ?? bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;

        require_once __DIR__ . '/../views/pc-stations/index.php';
    }

    public function storeAjax(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(false, 'Invalid request method.', [], 405);
        }

        $this->checkCsrf();

        $pcName = trim($_POST['pc_name'] ?? '');
        $ipAddress = trim($_POST['ip_address'] ?? '');
        $macAddress = trim($_POST['mac_address'] ?? '');
        $status = $_POST['status'] ?? 'locked';

        if ($pcName === '') {
            jsonResponse(false, 'PC name is required.', [], 422);
        }

        if (!in_array($status, ['locked', 'offline', 'maintenance'], true)) {
            jsonResponse(false, 'Invalid PC status.', [], 422);
        }

        try {
            $pcModel = new PC();

            $pcId = $pcModel->create([
                'pc_name' => $pcName,
                'ip_address' => $ipAddress,
                'mac_address' => $macAddress,
                'status' => $status
            ]);

            jsonResponse(true, 'PC station added successfully.', [
                'pc_id' => $pcId
            ]);

        } catch (Throwable $e) {
            jsonResponse(false, $e->getMessage(), [], 500);
        }
    }

    public function updateAjax(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(false, 'Invalid request method.', [], 405);
        }

        $this->checkCsrf();

        $pcId = (int)($_POST['pc_id'] ?? 0);
        $pcName = trim($_POST['pc_name'] ?? '');
        $ipAddress = trim($_POST['ip_address'] ?? '');
        $macAddress = trim($_POST['mac_address'] ?? '');

        if ($pcId <= 0) {
            jsonResponse(false, 'Invalid PC station.', [], 422);
        }

        if ($pcName === '') {
            jsonResponse(false, 'PC name is required.', [], 422);
        }

        try {
            $pcModel = new PC();
            $pc = $pcModel->findById($pcId);

            if (!$pc) {
                jsonResponse(false, 'PC station not found.', [], 404);
            }

            $pcModel->update($pcId, [
                'pc_name' => $pcName,
                'ip_address' => $ipAddress,
                'mac_address' => $macAddress
            ]);

            jsonResponse(true, 'PC station updated successfully.');

        } catch (Throwable $e) {
            jsonResponse(false, $e->getMessage(), [], 500);
        }
    }

    public function statusAjax(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(false, 'Invalid request method.', [], 405);
        }

        $this->checkCsrf();

        $pcId = (int)($_POST['pc_id'] ?? 0);
        $status = $_POST['status'] ?? '';

        if ($pcId <= 0) {
            jsonResponse(false, 'Invalid PC station.', [], 422);
        }

        if (!in_array($status, ['locked', 'offline', 'maintenance'], true)) {
            jsonResponse(false, 'Invalid PC status.', [], 422);
        }

        try {
            $pcModel = new PC();
            $sessionModel = new CafeSession();

            $pc = $pcModel->findById($pcId);

            if (!$pc) {
                jsonResponse(false, 'PC station not found.', [], 404);
            }

            $activeSessionsByPc = $sessionModel->getActiveSessionsIndexedByPc();

            if (isset($activeSessionsByPc[$pcId])) {
                jsonResponse(false, 'This PC has an active session. End the session before changing status.', [], 422);
            }

            $pcModel->updateStatus($pcId, $status);

            jsonResponse(true, 'PC status updated successfully.');

        } catch (Throwable $e) {
            jsonResponse(false, $e->getMessage(), [], 500);
        }
    }

    private function checkCsrf(): void
    {
        $csrfToken = $_POST['csrf_token'] ?? '';

        if (!$csrfToken || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrfToken)) {
            jsonResponse(false, 'Security token expired. Please refresh and try again.', [], 419);
        }
    }
}