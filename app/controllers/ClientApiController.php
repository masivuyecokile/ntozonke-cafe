<?php

require_once __DIR__ . '/../models/PC.php';
require_once __DIR__ . '/../models/CafeSession.php';
require_once __DIR__ . '/../models/PrintJob.php';

class ClientApiController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = (new Database())->connect();
    }

    public function heartbeat(): void
    {
        $this->requireApiKey();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(false, 'Invalid request method.', [], 405);
        }

        $data = $this->getRequestData();

        try {
            $pcModel = new PC();
            $pc = $this->getPcFromRequest($data, $pcModel);

            $ipAddress = trim($data['ip_address'] ?? '');
            $macAddress = trim($data['mac_address'] ?? '');

            $pcModel->updateHeartbeat(
                (int)$pc->id,
                $ipAddress !== '' ? $ipAddress : null,
                $macAddress !== '' ? $macAddress : null
            );

            $updatedPc = $pcModel->findById((int)$pc->id);

            jsonResponse(true, 'Heartbeat received.', [
                'pc' => [
                    'id' => (int)$updatedPc->id,
                    'pc_name' => $updatedPc->pc_name,
                    'status' => $updatedPc->status,
                    'ip_address' => $updatedPc->ip_address,
                    'mac_address' => $updatedPc->mac_address,
                    'last_heartbeat' => $updatedPc->last_heartbeat
                ]
            ]);

        } catch (Throwable $e) {
            jsonResponse(false, $e->getMessage(), [], 500);
        }
    }

    public function status(): void
    {
        $this->requireApiKey();

        $data = $this->getRequestData();

        try {
            $pcModel = new PC();
            $sessionModel = new CafeSession();

            $pc = $this->getPcFromRequest($data, $pcModel);

            $ipAddress = trim($data['ip_address'] ?? '');
            $macAddress = trim($data['mac_address'] ?? '');

            $pcModel->updateHeartbeat(
                (int)$pc->id,
                $ipAddress !== '' ? $ipAddress : null,
                $macAddress !== '' ? $macAddress : null
            );

            $pc = $pcModel->findById((int)$pc->id);
            $activeSession = $sessionModel->getActiveByPcId((int)$pc->id);

            $action = 'lock';
            $shouldLock = true;

            if ($pc->status === 'active' && $activeSession) {
                $action = 'unlock';
                $shouldLock = false;
            } elseif ($pc->status === 'maintenance') {
                $action = 'maintenance';
                $shouldLock = true;
            } elseif ($pc->status === 'offline') {
                $action = 'offline';
                $shouldLock = true;
            }

            jsonResponse(true, 'Client status returned.', [
                'pc' => [
                    'id' => (int)$pc->id,
                    'pc_name' => $pc->pc_name,
                    'status' => $pc->status,
                    'last_heartbeat' => $pc->last_heartbeat
                ],
                'action' => $action,
                'should_lock' => $shouldLock,
                'active_session' => $activeSession ? $this->formatSession($activeSession) : null
            ]);

        } catch (Throwable $e) {
            jsonResponse(false, $e->getMessage(), [], 500);
        }
    }

    public function session(): void
    {
        $this->requireApiKey();

        $data = $this->getRequestData();

        try {
            $pcModel = new PC();
            $sessionModel = new CafeSession();

            $pc = $this->getPcFromRequest($data, $pcModel);
            $activeSession = $sessionModel->getActiveByPcId((int)$pc->id);

            jsonResponse(true, 'Session status returned.', [
                'pc_id' => (int)$pc->id,
                'pc_name' => $pc->pc_name,
                'active_session' => $activeSession ? $this->formatSession($activeSession) : null
            ]);

        } catch (Throwable $e) {
            jsonResponse(false, $e->getMessage(), [], 500);
        }
    }

    public function printJob(): void
    {
        $this->requireApiKey();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(false, 'Invalid request method.', [], 405);
        }

        $data = $this->getRequestData();

        try {
            $pcModel = new PC();
            $sessionModel = new CafeSession();
            $printJobModel = new PrintJob();

            $pc = $this->getPcFromRequest($data, $pcModel);
            $activeSession = $sessionModel->getActiveByPcId((int)$pc->id);

            if (!$activeSession) {
                jsonResponse(false, 'No active session found for this PC.', [], 422);
            }

            $documentName = trim($data['document_name'] ?? '');
            $printerName = trim($data['printer_name'] ?? '');
            $pages = (int)($data['pages'] ?? 0);
            $copies = (int)($data['copies'] ?? 1);
            $printType = $data['print_type'] ?? 'black_white';

            if ($documentName === '') {
                jsonResponse(false, 'Document name is required.', [], 422);
            }

            if ($pages <= 0) {
                jsonResponse(false, 'Pages must be greater than zero.', [], 422);
            }

            if ($copies <= 0) {
                jsonResponse(false, 'Copies must be greater than zero.', [], 422);
            }

            if (!in_array($printType, ['black_white', 'colour'], true)) {
                jsonResponse(false, 'Invalid print type.', [], 422);
            }

            $printJobId = $printJobModel->createIncomingJob([
                'pc_id' => (int)$pc->id,
                'session_id' => (int)$activeSession->id,
                'document_name' => $documentName,
                'printer_name' => $printerName,
                'pages' => $pages,
                'copies' => $copies,
                'print_type' => $printType
            ]);

            jsonResponse(true, 'Print job submitted for admin approval.', [
                'print_job_id' => $printJobId
            ]);

        } catch (Throwable $e) {
            jsonResponse(false, $e->getMessage(), [], 500);
        }
    }

    private function getRequestData(): array
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        $data = $_POST;

        if (stripos($contentType, 'application/json') !== false) {
            $rawInput = file_get_contents('php://input');
            $jsonData = json_decode($rawInput, true);

            if (is_array($jsonData)) {
                $data = array_merge($data, $jsonData);
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $data = array_merge($data, $_GET);
        }

        return $data;
    }

    private function getPcFromRequest(array $data, PC $pcModel): object
    {
        $pcId = (int)($data['pc_id'] ?? 0);
        $pcName = trim($data['pc_name'] ?? '');

        if ($pcId > 0) {
            $pc = $pcModel->findById($pcId);

            if ($pc) {
                return $pc;
            }
        }

        if ($pcName !== '') {
            $pc = $pcModel->findByName($pcName);

            if ($pc) {
                return $pc;
            }
        }

        jsonResponse(false, 'PC station not found. Send a valid pc_id or pc_name.', [], 404);
    }

    private function formatSession(object $session): array
    {
        $endTimestamp = strtotime($session->end_time);
        $remainingSeconds = max(0, $endTimestamp - time());

        return [
            'id' => (int)$session->id,
            'customer_name' => $session->customer_name ?? 'Walk-in Customer',
            'start_time' => $session->start_time,
            'end_time' => $session->end_time,
            'end_timestamp' => $endTimestamp,
            'remaining_seconds' => $remainingSeconds,
            'minutes_purchased' => (int)$session->minutes_purchased,
            'extended_minutes' => (int)$session->extended_minutes,
            'internet_income' => (float)($session->internet_income ?? $session->amount_due ?? 0),
            'status' => $session->status
        ];
    }

    private function requireApiKey(): void
    {
        $expectedKey = $this->getSettingValue('client_api_key');

        if ($expectedKey === '') {
            jsonResponse(false, 'Client API key is not configured.', [], 500);
        }

        $providedKey = $_SERVER['HTTP_X_CLIENT_KEY'] ?? '';

        if ($providedKey === '') {
            $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

            if (stripos($authHeader, 'Bearer ') === 0) {
                $providedKey = trim(substr($authHeader, 7));
            }
        }

        if ($providedKey === '') {
            $providedKey = $_POST['client_key'] ?? $_GET['client_key'] ?? '';
        }

        if (!hash_equals($expectedKey, $providedKey)) {
            jsonResponse(false, 'Invalid client API key.', [], 401);
        }
    }

    private function getSettingValue(string $key): string
    {
        $stmt = $this->db->prepare("
            SELECT setting_value
            FROM settings
            WHERE setting_key = :setting_key
            LIMIT 1
        ");

        $stmt->execute([
            ':setting_key' => $key
        ]);

        $row = $stmt->fetch();

        return $row ? (string)$row->setting_value : '';
    }
}