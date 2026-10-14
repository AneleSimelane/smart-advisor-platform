<?php
require_once __DIR__ . '/../config/core.php';
requireRole('officer');

$page_title = "Officer Dashboard";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="dashboard-wrapper">
    <?php require_once __DIR__ . '/../includes/officer_sidebar.php'; ?>
    
    <div class="dashboard-content">
        <h2 class="mb-4">Agricultural Extension Officer Portal</h2>
        
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card stat-card shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted text-uppercase mb-2">Registered Farmers</h6>
                                <h3 class="mb-0 fw-bold text-dark">0</h3>
                            </div>
                            <i class="fas fa-users stat-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="card stat-card shadow-sm h-100 border-start border-4 border-danger">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted text-uppercase mb-2">Active Disease Alerts</h6>
                                <h3 class="mb-0 fw-bold text-dark">0</h3>
                            </div>
                            <i class="fas fa-bug stat-icon text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Recent Farmer Activity (Region: Manzini)</h5>
            </div>
            <div class="card-body p-5 text-center">
                <p class="text-muted">No recent activity to display.</p>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
