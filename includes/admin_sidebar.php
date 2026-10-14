<?php
// Sidebar for Admin
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="sidebar col-md-3 col-lg-2 d-md-block p-0">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/admin/dashboard.php">
                    <i class="fas fa-home"></i> Admin Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">
                    <i class="fas fa-users-cog"></i> User Management
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">
                    <i class="fas fa-seedling"></i> Crop Types
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">
                    <i class="fas fa-map-marker-alt"></i> Services & Maps
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">
                    <i class="fas fa-cogs"></i> System Settings
                </a>
            </li>
        </ul>
    </div>
</div>
