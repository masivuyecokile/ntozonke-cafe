<?php
$currentRoute = $_GET['route'] ?? 'dashboard';

function isActiveRoute(string $route, string $currentRoute): string
{
    return $route === $currentRoute ? 'active' : '';
}
?>

<aside class="sidebar">

    <div class="sidebar-brand">
        <div class="sidebar-logo">
            <img src="<?= BASE_URL; ?>/assets/img/logo.png" alt="Ntozonke Co Logo">
        </div>

        <div>
            <h5>Internet Cafe</h5>
            <span>Management</span>
        </div>
    </div>

    <nav class="sidebar-nav">

        <a href="<?= BASE_URL; ?>/index.php?route=dashboard" class="<?= isActiveRoute('dashboard', $currentRoute); ?>">
            <i class="bi bi-grid-fill"></i>
            Dashboard
        </a>

        <a href="<?= BASE_URL; ?>/index.php?route=pc-stations" class="<?= isActiveRoute('pc-stations', $currentRoute); ?>">
            <i class="bi bi-pc-display"></i>
            PC Stations
        </a>

        <a href="<?= BASE_URL; ?>/index.php?route=sessions" class="<?= isActiveRoute('sessions', $currentRoute); ?>">
            <i class="bi bi-clock-history"></i>
            Sessions
        </a>

        <a href="<?= BASE_URL; ?>/index.php?route=print-jobs" class="<?= isActiveRoute('print-jobs', $currentRoute); ?>">
            <i class="bi bi-printer-fill"></i>
            Print Jobs
            <span class="sidebar-badge d-none" id="pendingPrintBadge">0</span>
        </a>

        <a href="<?= BASE_URL; ?>/index.php?route=members" class="<?= isActiveRoute('members', $currentRoute); ?>">
            <i class="bi bi-people-fill"></i>
            Members
        </a>

        <a href="<?= BASE_URL; ?>/index.php?route=sales" class="<?= isActiveRoute('sales', $currentRoute); ?>">
            <i class="bi bi-cash-stack"></i>
            Sales
        </a>

        <a href="<?= BASE_URL; ?>/index.php?route=reports" class="<?= isActiveRoute('reports', $currentRoute); ?>">
            <i class="bi bi-graph-up-arrow"></i>
            Reports
        </a>

        <a href="<?= BASE_URL; ?>/index.php?route=packages" class="<?= isActiveRoute('packages', $currentRoute); ?>">
            <i class="bi bi-tags-fill"></i>
            Packages
        </a>

        <a href="<?= BASE_URL; ?>/index.php?route=pricing" class="<?= isActiveRoute('pricing', $currentRoute); ?>">
            <i class="bi bi-currency-exchange"></i>
            Services Pricing
        </a>

        <a href="<?= BASE_URL; ?>/index.php?route=services" class="<?= isActiveRoute('services', $currentRoute); ?>">
            <i class="bi bi-tools"></i>
            Services Sales
        </a>

        <a href="<?= BASE_URL; ?>/index.php?route=settings" class="<?= isActiveRoute('settings', $currentRoute); ?>">
            <i class="bi bi-gear-fill"></i>
            Settings
        </a>

    </nav>

    <div class="sidebar-footer">
        <a href="<?= BASE_URL; ?>/index.php?route=logout">
            <i class="bi bi-box-arrow-left"></i>
            Logout
        </a>
    </div>

</aside>