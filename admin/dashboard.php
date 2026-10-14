<?php
require_once __DIR__ . '/../config/core.php';
requireRole('admin');

$page_title = "Admin Dashboard";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="dashboard-wrapper">
    <?php require_once __DIR__ . '/../includes/admin_sidebar.php'; ?>
    
    <div class="dashboard-content">
        <h2 class="mb-4">System Administration</h2>
        
        <div class="row">
            <div class="col-md-3 mb-4">
                <div class="card stat-card shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted text-uppercase mb-2">Total Users</h6>
                                <h3 class="mb-0 fw-bold text-dark">0</h3>
                            </div>
                            <i class="fas fa-users stat-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-4">
                <div class="card stat-card shadow-sm h-100 border-start border-4 border-info">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted text-uppercase mb-2">System Health</h6>
                                <h3 class="mb-0 fw-bold text-dark">100%</h3>
                            </div>
                            <i class="fas fa-server stat-icon text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">System Logs</h5>
            </div>
            <div class="card-body p-5 text-center">
                <p class="text-muted">No errors or logs reported.</p>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
