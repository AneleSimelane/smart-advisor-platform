<?php
// Sidebar for Officer
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="sidebar col-md-3 col-lg-2 d-md-block p-0">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/officer/dashboard.php">
                    <i class="fas fa-home"></i> Officer Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">
                    <i class="fas fa-users"></i> Farmer Directory
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">
                    <i class="fas fa-bug text-danger"></i> Disease Reports
                </a>
            </li>
        </ul>
    </div>
</div>
