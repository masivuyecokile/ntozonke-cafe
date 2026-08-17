<?php

class ClientApiController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = (new Database())->connect();
    }

    public function register(): void
    {
        $this->requireApiKey();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(false, 'Invalid request method.', [], 405);
        }

        $data = $this->getRequestData();

        try {
            $pcModel = new PC();

            $registrationToken = trim($data['registration_token'] ?? $data['client_token'] ?? '');
            $computerName = trim($data['computer_name'] ?? '');
            $ipAddress = trim($data['ip_address'] ?? $this->getRemoteIp());
            $macAddress = trim($data['mac_address'] ?? '');

            if ($registrationToken !== '') {
                $pc = $pcModel->findByClientToken($registrationToken);

                if (!$pc) {
                    jsonResponse(false, 'Registration token not found. Please register this PC again.', [], 404);
                }

                $pcModel->updateHeartbeat(
                    (int)$pc->id,
                    $ipAddress !== '' ? $ipAddress : null,
                    $macAddress !== '' ? $macAddress : null,
                    $computerName !== '' ? $computerName : null
                );

                $updatedPc = $pcModel->findById((int)$pc->id);

                jsonResponse(true, 'Client registration found.', $this->formatRegistrationResponse($updatedPc));
            }

            if ($computerName === '' && $macAddress === '') {
                jsonResponse(false, 'Computer name or MAC address is required for registration.', [], 422);
            }

            $existingPc = $pcModel->findByFingerprint(
                $macAddress !== '' ? $macAddress : null,
                $computerName !== '' ? $computerName : null
            );

            if ($existingPc) {
                $token = trim((string)($existingPc->client_token ?? ''));

                if ($token === '') {
                    $token = $this->generateClientToken();
                    $pcModel->updateClientToken((int)$existingPc->id, $token);
                }

                $pcModel->updateHeartbeat(
                    (int)$existingPc->id,
                    $ipAddress !== '' ? $ipAddress : null,
                    $macAddress !== '' ? $macAddress : null,
                    $computerName !== '' ? $computerName : null
                );

                $updatedPc = $pcModel->findById((int)$existingPc->id);

                jsonResponse(true, 'Existing client registration returned.', $this->formatRegistrationResponse($updatedPc));
            }

            $token = $this->generateClientToken();

            $pendingName = 'Pending - ' . ($computerName !== '' ? $computerName : 'Unknown PC');

            if (strlen($pendingName) > 100) {
                $pendingName = substr($pendingName, 0, 100);
            }

            $pcId = $pcModel->createPendingClient([
                'pc_name' => $pendingName,
                'computer_name' => $computerName,
                'ip_address' => $ipAddress,
                'mac_address' => $macAddress,
                'client_token' => $token
            ]);

            $pc = $pcModel->findById($pcId);

            jsonResponse(true, 'Client registered. Waiting for admin approval.', $this->formatRegistrationResponse($pc));

        } catch (Throwable $e) {
            jsonResponse(false, $e->getMessage(), [], 500);
        }
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
            $computerName = trim($data['computer_name'] ?? '');

            $pcModel->updateHeartbeat(
                (int)$pc->id,
                $ipAddress !== '' ? $ipAddress : null,
                $macAddress !== '' ? $macAddress : null,
                $computerName !== '' ? $computerName : null
            );

            $updatedPc = $pcModel->findById((int)$pc->id);
            $approvalStatus = $this->getApprovalStatus($updatedPc);

            jsonResponse(true, 'Heartbeat received.', [
                'pc' => [
                    'id' => (int)$updatedPc->id,
                    'pc_name' => $updatedPc->pc_name,
                    'computer_name' => $updatedPc->computer_name ?? null,
                    'status' => $updatedPc->status,
                    'approval_status' => $approvalStatus,
                    'ip_address' => $updatedPc->ip_address,
                    'mac_address' => $updatedPc->mac_address,
                    'last_heartbeat' => $updatedPc->last_heartbeat
                ],
                'approved' => $approvalStatus === 'approved',
                'action' => $this->approvalAction($approvalStatus),
                'should_lock' => $approvalStatus !== 'approved'
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
            $computerName = trim($data['computer_name'] ?? '');

            $pcModel->updateHeartbeat(
                (int)$pc->id,
                $ipAddress !== '' ? $ipAddress : null,
                $macAddress !== '' ? $macAddress : null,
                $computerName !== '' ? $computerName : null
            );

            $pc = $pcModel->findById((int)$pc->id);
            $approvalStatus = $this->getApprovalStatus($pc);

            if ($approvalStatus !== 'approved') {
                jsonResponse(true, 'PC is not approved yet.', [
                    'pc' => [
                        'id' => (int)$pc->id,
                        'pc_name' => $pc->pc_name,
                        'computer_name' => $pc->computer_name ?? null,
                        'status' => $pc->status,
                        'approval_status' => $approvalStatus,
                        'last_heartbeat' => $pc->last_heartbeat
                    ],
                    'approved' => false,
                    'action' => $this->approvalAction($approvalStatus),
                    'should_lock' => true,
                    'active_session' => null
                ]);
            }

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
                    'computer_name' => $pc->computer_name ?? null,
                    'status' => $pc->status,
                    'approval_status' => $approvalStatus,
                    'last_heartbeat' => $pc->last_heartbeat
                ],
                'approved' => true,
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

            if ($this->getApprovalStatus($pc) !== 'approved') {
                jsonResponse(false, 'This PC is waiting for admin approval.', [], 403);
            }

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

            if ($this->getApprovalStatus($pc) !== 'approved') {
                jsonResponse(false, 'This PC is waiting for admin approval.', [], 403);
            }

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
        $registrationToken = trim($data['registration_token'] ?? $data['client_token'] ?? '');

        if ($registrationToken !== '') {
            $pc = $pcModel->findByClientToken($registrationToken);

            if ($pc) {
                return $pc;
            }
        }

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

        jsonResponse(false, 'PC station not found. Register this client first or send a valid pc_id or pc_name.', [], 404);
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

    private function formatRegistrationResponse(object $pc): array
    {
        $approvalStatus = $this->getApprovalStatus($pc);

        return [
            'approved' => $approvalStatus === 'approved',
            'approval_status' => $approvalStatus,
            'action' => $this->approvalAction($approvalStatus),
            'should_lock' => $approvalStatus !== 'approved',
            'registration_token' => $pc->client_token,
            'pc' => [
                'id' => (int)$pc->id,
                'pc_name' => $pc->pc_name,
                'computer_name' => $pc->computer_name ?? null,
                'status' => $pc->status,
                'approval_status' => $approvalStatus,
                'ip_address' => $pc->ip_address,
                'mac_address' => $pc->mac_address,
                'last_heartbeat' => $pc->last_heartbeat ?? null,
                'registered_at' => $pc->registered_at ?? null,
                'approved_at' => $pc->approved_at ?? null
            ]
        ];
    }

    private function getApprovalStatus(object $pc): string
    {
        return $pc->approval_status ?? 'approved';
    }

    private function approvalAction(string $approvalStatus): string
    {
        if ($approvalStatus === 'approved') {
            return 'registered';
        }

        if ($approvalStatus === 'rejected') {
            return 'rejected';
        }

        return 'pending_approval';
    }

    private function generateClientToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    private function getRemoteIp(): string
    {
        return $_SERVER['REMOTE_ADDR'] ?? '';
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