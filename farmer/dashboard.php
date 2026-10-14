<?php
require_once __DIR__ . '/../config/core.php';
requireRole('farmer');

$user_id = $_SESSION['user_id'];

// Get basic stats
try {
    // Total farms
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM farms WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $total_farms = $stmt->fetchColumn();

    // Active crops
    $stmt = $pdo->prepare("SELECT COUNT(c.id) FROM crops c JOIN farms f ON c.farm_id = f.id WHERE f.user_id = ? AND c.status = 'active'");
    $stmt->execute([$user_id]);
    $active_crops = $stmt->fetchColumn();

    // Recommendations
    $stmt = $pdo->prepare("SELECT * FROM recommendations WHERE user_id = ? ORDER BY date_created DESC LIMIT 3");
    $stmt->execute([$user_id]);
    $recommendations = $stmt->fetchAll();
    
    // Financials
    $stmt = $pdo->prepare("SELECT SUM(amount_szl) FROM financial_records fr JOIN farms f ON fr.farm_id = f.id WHERE f.user_id = ? AND type = 'income'");
    $stmt->execute([$user_id]);
    $total_income = $stmt->fetchColumn() ?: 0;
    
    $stmt = $pdo->prepare("SELECT SUM(amount_szl) FROM financial_records fr JOIN farms f ON fr.farm_id = f.id WHERE f.user_id = ? AND type = 'expense'");
    $stmt->execute([$user_id]);
    $total_expense = $stmt->fetchColumn() ?: 0;

} catch (PDOException $e) {
    die("Error fetching dashboard data: " . $e->getMessage());
}

$page_title = "Farmer Dashboard";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="dashboard-wrapper">
    <?php require_once __DIR__ . '/../includes/farmer_sidebar.php'; ?>
    
    <div class="dashboard-content">
        <h2 class="mb-4">Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h2>
        
        <?php if ($total_farms == 0): ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i> You haven't added any farms yet. <a href="<?php echo BASE_URL; ?>/farmer/farms.php" class="alert-link">Add a farm to get started.</a>
            </div>
        <?php endif; ?>

        <!-- Statistics Row -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card stat-card shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted text-uppercase mb-2">My Farms</h6>
                                <h3 class="mb-0 fw-bold text-dark"><?php echo $total_farms; ?></h3>
                            </div>
                            <i class="fas fa-map-marked-alt stat-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card shadow-sm h-100" style="border-left-color: #0dcaf0;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted text-uppercase mb-2">Active Crops</h6>
                                <h3 class="mb-0 fw-bold text-dark"><?php echo $active_crops; ?></h3>
                            </div>
                            <i class="fas fa-seedling stat-icon" style="color: #0dcaf0;"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card shadow-sm h-100" style="border-left-color: #ffc107;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted text-uppercase mb-2">Total Income</h6>
                                <h4 class="mb-0 fw-bold text-dark"><?php echo formatSZL($total_income); ?></h4>
                            </div>
                            <i class="fas fa-hand-holding-usd stat-icon" style="color: #ffc107;"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card shadow-sm h-100" style="border-left-color: #dc3545;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted text-uppercase mb-2">Total Expenses</h6>
                                <h4 class="mb-0 fw-bold text-dark"><?php echo formatSZL($total_expense); ?></h4>
                            </div>
                            <i class="fas fa-file-invoice-dollar stat-icon" style="color: #dc3545;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Weather Widget -->
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-cloud-sun text-primary me-2"></i>Live Weather Forecast</h5>
                        <small class="text-muted">Mbabané, Eswatini</small>
                    </div>
                    <div class="card-body" id="weatherWidget">
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading weather...</span>
                            </div>
                            <p class="mt-2 text-muted">Fetching live data...</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Smart Advisory -->
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-brain text-success me-2"></i>Smart Advisory</h5>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($recommendations)): ?>
                            <div class="p-4 text-center text-muted">
                                <i class="fas fa-check-circle fa-2x mb-2 text-success opacity-50"></i>
                                <p>No new recommendations at this time.</p>
                            </div>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($recommendations as $rec): ?>
                                    <div class="list-group-item p-3">
                                        <div class="d-flex w-100 justify-content-between mb-1">
                                            <h6 class="mb-1 fw-bold"><?php echo htmlspecialchars($rec['issue_type']); ?></h6>
                                            <small class="text-muted"><?php echo date('M d, Y', strtotime($rec['date_created'])); ?></small>
                                        </div>
                                        <p class="mb-1 text-dark"><?php echo htmlspecialchars($rec['recommendation_text']); ?></p>
                                        <?php if ($rec['urgency'] == 'high' || $rec['urgency'] == 'critical'): ?>
                                            <span class="badge bg-danger">Urgent</span>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer bg-white text-center">
                        <a href="#" class="text-decoration-none">View all recommendations</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
$extra_js = <<<'EOT'
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Fetch live weather data for Eswatini (approximate center coordinates)
    const lat = -26.3054;
    const lon = 31.1367;
    const weatherUrl = `https://api.open-meteo.com/v1/forecast?latitude=${lat}&longitude=${lon}&current=temperature_2m,relative_humidity_2m,is_day,precipitation,wind_speed_10m&daily=temperature_2m_max,temperature_2m_min,precipitation_sum&timezone=Africa%2FJohannesburg`;

    fetch(weatherUrl)
        .then(response => {
            if (!response.ok) throw new Error('Network error');
            return response.json();
        })
        .then(data => {
            const current = data.current;
            const widget = document.getElementById('weatherWidget');
            widget.innerHTML = `
                <div class='row align-items-center mb-3'>
                    <div class='col-6 text-center'>
                        <h1 class='display-3 fw-bold mb-0'>${current.temperature_2m}°C</h1>
                        <p class='text-muted lead'>Current Temp</p>
                    </div>
                    <div class='col-6'>
                        <ul class='list-unstyled mb-0'>
                            <li class='mb-2'><i class='fas fa-tint text-primary me-2'></i> <strong>Humidity:</strong> ${current.relative_humidity_2m}%</li>
                            <li class='mb-2'><i class='fas fa-wind text-secondary me-2'></i> <strong>Wind:</strong> ${current.wind_speed_10m} km/h</li>
                            <li><i class='fas fa-cloud-showers-heavy text-info me-2'></i> <strong>Precip:</strong> ${current.precipitation} mm</li>
                        </ul>
                    </div>
                </div>
                <hr>
                <div class='alert alert-info mb-0'>
                    <i class='fas fa-info-circle me-1'></i> <strong>Weather Tip:</strong> Based on current conditions, ${current.precipitation > 0 ? 'skip irrigation today as rain is detected.' : 'good conditions for standard farming activities.'}
                </div>
            `;
        })
        .catch(error => {
            document.getElementById('weatherWidget').innerHTML = '<div class="alert alert-danger">Unable to load weather data. Please check your internet connection.</div>';
        });
});
</script>
EOT;
require_once __DIR__ . '/../includes/footer.php'; 
?>
