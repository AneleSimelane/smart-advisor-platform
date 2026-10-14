<?php
require_once __DIR__ . '/../config/core.php';
requireRole('farmer');

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Check if user has farms
try {
    $stmt = $pdo->prepare("SELECT id, name FROM farms WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $farms = $stmt->fetchAll();
    
    $stmt = $pdo->prepare("SELECT * FROM crop_types ORDER BY name ASC");
    $stmt->execute();
    $crop_types = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Database error.");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $farm_id = $_POST['farm_id'];
    $crop_type_id = $_POST['crop_type_id'];
    $planted_date = $_POST['planted_date'];
    $expected_harvest = $_POST['expected_harvest_date'];
    $area_size = $_POST['area_size'];

    if (empty($farm_id) || empty($crop_type_id) || empty($planted_date)) {
        $error = "Please fill all required fields.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO crops (farm_id, crop_type_id, status, planted_date, expected_harvest_date, area_size) VALUES (?, ?, 'active', ?, ?, ?)");
            if ($stmt->execute([$farm_id, $crop_type_id, $planted_date, $expected_harvest ?: null, $area_size ?: null])) {
                $success = "Crop added successfully.";
            } else {
                $error = "Failed to add crop.";
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}

// Fetch crops
try {
    $query = "SELECT c.*, ct.name as crop_name, ct.typical_duration_days, f.name as farm_name 
              FROM crops c 
              JOIN crop_types ct ON c.crop_type_id = ct.id 
              JOIN farms f ON c.farm_id = f.id 
              WHERE f.user_id = ? ";
              
    if (isset($_GET['farm_id']) && is_numeric($_GET['farm_id'])) {
        $query .= " AND f.id = " . intval($_GET['farm_id']);
    }
    $query .= " ORDER BY c.planted_date DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$user_id]);
    $crops = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Database error.");
}

$page_title = "Crop Management";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="dashboard-wrapper">
    <?php require_once __DIR__ . '/../includes/farmer_sidebar.php'; ?>
    
    <div class="dashboard-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Crop Management</h2>
            <?php if (!empty($farms)): ?>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addCropModal">
                <i class="fas fa-plus me-1"></i> Add Crop
            </button>
            <?php endif; ?>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?php echo htmlspecialchars($success); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (empty($farms)): ?>
            <div class="alert alert-warning">
                You need to add a farm before you can add crops. <a href="farms.php" class="alert-link">Go to My Farms</a>.
            </div>
        <?php endif; ?>

        <div class="row">
            <?php if (empty($crops) && !empty($farms)): ?>
                <div class="col-12 text-center py-5">
                    <i class="fas fa-seedling fa-4x text-muted mb-3 opacity-25"></i>
                    <h4 class="text-muted">No crops found</h4>
                    <p class="text-muted">Start by adding the crops you are currently growing.</p>
                </div>
            <?php else: ?>
                <?php foreach ($crops as $crop): 
                    $planted = new DateTime($crop['planted_date']);
                    $now = new DateTime();
                    $days_grown = $planted->diff($now)->days;
                    
                    $duration = $crop['typical_duration_days'] ?: 100;
                    $progress = min(100, round(($days_grown / $duration) * 100));
                    
                    $badge_class = 'bg-primary';
                    if ($crop['status'] == 'harvested') $badge_class = 'bg-success';
                    elseif ($crop['status'] == 'failed') $badge_class = 'bg-danger';
                ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card shadow-sm h-100">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                <span class="fw-bold text-success"><?php echo htmlspecialchars($crop['crop_name']); ?></span>
                                <span class="badge <?php echo $badge_class; ?>"><?php echo ucfirst($crop['status']); ?></span>
                            </div>
                            <div class="card-body">
                                <p class="text-muted small mb-3"><i class="fas fa-map-marker-alt me-1"></i> <?php echo htmlspecialchars($crop['farm_name']); ?></p>
                                
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between small mb-1">
                                        <span>Growth Progress (<?php echo $days_grown; ?> days)</span>
                                        <span><?php echo $progress; ?>%</span>
                                    </div>
                                    <div class="progress" style="height: 10px;">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $progress; ?>%" aria-valuenow="<?php echo $progress; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                                
                                <ul class="list-unstyled small text-muted mb-0">
                                    <li class="mb-1"><i class="far fa-calendar-alt me-2"></i><strong>Planted:</strong> <?php echo date('M d, Y', strtotime($crop['planted_date'])); ?></li>
                                    <li><i class="far fa-calendar-check me-2"></i><strong>Exp. Harvest:</strong> <?php echo $crop['expected_harvest_date'] ? date('M d, Y', strtotime($crop['expected_harvest_date'])) : 'N/A'; ?></li>
                                </ul>
                            </div>
                            <div class="card-footer bg-white border-top-0 pt-0">
                                <div class="d-grid">
                                    <button class="btn btn-outline-success btn-sm">Log Activity</button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add Crop Modal -->
<?php if (!empty($farms)): ?>
<div class="modal fade" id="addCropModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="">
                <input type="hidden" name="action" value="add">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Add New Crop</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Select Farm</label>
                        <select class="form-select" name="farm_id" required>
                            <option value="">Choose a farm...</option>
                            <?php foreach ($farms as $farm): ?>
                                <option value="<?php echo $farm['id']; ?>"><?php echo htmlspecialchars($farm['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Crop Type</label>
                        <select class="form-select" name="crop_type_id" required>
                            <option value="">Choose a crop...</option>
                            <?php foreach ($crop_types as $type): ?>
                                <option value="<?php echo $type['id']; ?>"><?php echo htmlspecialchars($type['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Planting Date</label>
                            <input type="date" class="form-control" name="planted_date" required max="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Expected Harvest</label>
                            <input type="date" class="form-control" name="expected_harvest_date">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Area Size (Hectares) - Optional</label>
                        <input type="number" step="0.01" class="form-control" name="area_size">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Save Crop</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
