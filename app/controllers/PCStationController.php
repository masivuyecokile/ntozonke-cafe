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
}