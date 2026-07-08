<?php

require_once __DIR__ . '/../models/PC.php';
require_once __DIR__ . '/../models/InternetPackage.php';
require_once __DIR__ . '/../models/CafeSession.php';

class DashboardController
{
    public function index(): void
    {
        $pcModel = new PC();
        $packageModel = new InternetPackage();
        $sessionModel = new CafeSession();

        $pcs = $pcModel->getAll();
        $packages = $packageModel->getActive();
        $activeSessionsByPc = $sessionModel->getActiveSessionsIndexedByPc();

        $totalPCs = count($pcs);
        $lockedPCs = $pcModel->countByStatus('locked');
        $activePCs = $pcModel->countByStatus('active');
        $offlinePCs = $pcModel->countByStatus('offline');

        $csrfToken = $_SESSION['csrf_token'] ?? bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;

        require_once __DIR__ . '/../views/dashboard/index.php';
    }
}