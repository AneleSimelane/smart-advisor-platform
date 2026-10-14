<?php
require_once __DIR__ . '/config/core.php';

if (isLoggedIn()) {
    // Redirect based on role
    if (hasRole('admin')) redirect('/admin/dashboard.php');
    elseif (hasRole('officer')) redirect('/officer/dashboard.php');
    else redirect('/farmer/dashboard.php');
}

$page_title = "Welcome";
require_once __DIR__ . '/includes/header.php';
?>

<div class="container my-5">
    <div class="row align-items-center g-5 py-4">
        <!-- Hero Left Column -->
        <div class="col-lg-6">
            <div class="badge bg-success bg-opacity-20 text-white border border-success px-3 py-2 rounded-pill mb-3">
                <i class="fas fa-leaf text-success me-1"></i> Eswatini Agricultural Innovation Platform
            </div>
            <h1 class="display-3 fw-extrabold text-white mb-3" style="text-shadow: 0 4px 12px rgba(0,0,0,0.5);">
                Smart Farming in <span class="text-success">Eswatini</span>
            </h1>
            <p class="lead text-light mb-4 fs-5" style="text-shadow: 0 2px 8px rgba(0,0,0,0.6); opacity: 0.95;">
                Empowering farmers across Hhohho, Manzini, Shiselweni, and Lubombo with AI disease diagnosis, Eswatini vegetable guidelines, live weather forecasts, community social feeds, and direct produce marketplaces.
            </p>
            <div class="d-flex flex-wrap gap-3">
                <a href="<?php echo BASE_URL; ?>/register.php" class="btn btn-success btn-lg px-4 py-3 rounded-pill shadow-lg">
                    <i class="fas fa-rocket me-2"></i>Get Started Free
                </a>
                <a href="<?php echo BASE_URL; ?>/login.php" class="btn btn-outline-light btn-lg px-4 py-3 rounded-pill">
                    <i class="fas fa-sign-in-alt me-2"></i>Sign In
                </a>
            </div>
        </div>

        <!-- Hero Right Glass Card -->
        <div class="col-lg-6">
            <div class="hero-glass-card">
                <h3 class="fw-bold text-dark mb-4 border-bottom pb-3">
                    <i class="fas fa-seedling text-success me-2"></i> Why Farmers Choose Smart Advisor
                </h3>
                <div class="d-flex align-items-start mb-4">
                    <div class="bg-success bg-opacity-10 text-success rounded-circle p-3 me-3 fs-4">
                        <i class="fas fa-leaf"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-1">AI Plant Doctor</h5>
                        <p class="text-muted mb-0 small">Scan diseased leaves with your phone to get instant diagnosis and pesticide dosages from Eswatini MoA guidelines.</p>
                    </div>
                </div>
                
                <div class="d-flex align-items-start mb-4">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-3 me-3 fs-4">
                        <i class="fas fa-cloud-sun"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-1">Localized Weather Forecasts</h5>
                        <p class="text-muted mb-0 small">Real-time OpenMeteo weather predictions to optimize irrigation and prevent crop loss.</p>
                    </div>
                </div>

                <div class="d-flex align-items-start mb-4">
                    <div class="bg-warning bg-opacity-10 text-warning rounded-circle p-3 me-3 fs-4">
                        <i class="fas fa-store"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-1">Farmer Marketplace & Feed</h5>
                        <p class="text-muted mb-0 small">Buy & sell produce directly with fellow farmers via Call and WhatsApp, plus post community tips like Facebook.</p>
                    </div>
                </div>

                <div class="d-flex align-items-start mb-4">
                    <div class="bg-info bg-opacity-10 text-info rounded-circle p-3 me-3 fs-4">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-1">Automated Farming Calendar</h5>
                        <p class="text-muted mb-0 small">Auto-schedules weeding, top-dressing LAN fertilizer, and harvest dates tailored to your planting date.</p>
                    </div>
                </div>

                <div class="d-flex align-items-start">
                    <div class="bg-warning bg-opacity-10 text-warning rounded-circle p-3 me-3 fs-4">
                        <i class="fas fa-book-open"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-1">Farmers' Information Center</h5>
                        <p class="text-muted mb-0 small">Eswatini MoA crop production manuals, regional planting matrices, and chemical dosage tables.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
