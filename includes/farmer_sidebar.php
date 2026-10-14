<?php
// Sidebar for farmer
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="sidebar col-md-3 col-lg-2 d-md-block p-0">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/farmer/dashboard.php">
                    <i class="fas fa-home"></i> Dashboard
                </a>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'feed.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/farmer/feed.php">
                    <i class="fas fa-rss text-info"></i> Community Feed & Ads
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'farms.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/farmer/farms.php">
                    <i class="fas fa-tractor"></i> My Farms
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'crops.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/farmer/crops.php">
                    <i class="fas fa-seedling"></i> Crop Management
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'info_center.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/farmer/info_center.php">
                    <i class="fas fa-book-open text-warning"></i> Information Center
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'calendar.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/farmer/calendar.php">
                    <i class="far fa-calendar-alt"></i> Farming Calendar
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'plant_doctor.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/farmer/plant_doctor.php">
                    <i class="fas fa-leaf text-success"></i> Plant Doctor AI
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'weather.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/farmer/weather.php">
                    <i class="fas fa-cloud-sun"></i> Weather
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'finances.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/farmer/finances.php">
                    <i class="fas fa-wallet"></i> Financials
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'services.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/farmer/services.php">
                    <i class="fas fa-map-marker-alt"></i> Nearby Services
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'marketplace.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/farmer/marketplace.php">
                    <i class="fas fa-store text-warning"></i> Farmer Marketplace
                </a>
            </li>
        </ul>
    </div>
</div>
