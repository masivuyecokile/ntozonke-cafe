<aside class="sidebar">
    <div class="sidebar-brand">
        <img src="<?=BASE_URL;
?>/assets/img/logo.png" alt="Logo">
        <div>
            <strong>Internet Cafe</strong>
            <span>Management</span>
        </div>
    </div>

    <nav class="sidebar-nav">
        <a href="<?=BASE_URL;
?>/index.php?route=dashboard" class="active">
        <i class="bi bi-grid-1x2-fill"></i>
        Dashboard
    </a>

    <a href="#">
        <i class="bi bi-pc-display-horizontal"></i>
        PC Stations
    </a>

    <a href="#">
        <i class="bi bi-clock-history"></i>
        Sessions
    </a>

    <a href="<?=BASE_URL;
?>/index.php?route=print-jobs">
    <i class="bi bi-printer-fill"></i>
    Print Jobs
    <span class="sidebar-badge d-none" id="pendingPrintBadge">0</span>
</a>

<a href="#">
    <i class="bi bi-people-fill"></i>
    Members
</a>

<a href="<?=BASE_URL;
?>/index.php?route=sales">
<i class="bi bi-cash-stack"></i>
Sales
</a>

<a href="#">
    <i class="bi bi-graph-up-arrow"></i>
    Reports
</a>

<a href="<?=BASE_URL;
?>/index.php?route=packages">
<i class="bi bi-tags-fill"></i>
Packages
</a>

<a href="<?=BASE_URL;
?>/index.php?route=pricing">
    <i class="bi bi-currency-exchange"></i>
    Services Pricing
</a>
<a href="<?=BASE_URL;
?>/index.php?route=services">
    <i class="bi bi-tools"></i>
    Services Sales
</a>

<a href="#">
    <i class="bi bi-gear-fill"></i>
    Settings
</a>
</nav>
</aside>
