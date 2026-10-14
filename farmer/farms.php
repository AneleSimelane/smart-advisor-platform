<?php
require_once __DIR__ . '/../config/core.php';
requireRole('farmer');

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $name = trim($_POST['name']);
        $size = trim($_POST['size']);
        $lat = trim($_POST['lat']);
        $lng = trim($_POST['lng']);

        if (empty($name) || empty($size)) {
            $error = "Farm name and size are required.";
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO farms (user_id, name, size_hectares, latitude, longitude) VALUES (?, ?, ?, ?, ?)");
                if ($stmt->execute([$user_id, $name, $size, $lat ?: null, $lng ?: null])) {
                    $success = "Farm added successfully.";
                } else {
                    $error = "Failed to add farm.";
                }
            } catch (PDOException $e) {
                $error = "Database error: " . $e->getMessage();
            }
        }
    }
}

// Fetch farms
try {
    $stmt = $pdo->prepare("SELECT * FROM farms WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
    $farms = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Database error.");
}

$page_title = "My Farms";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="dashboard-wrapper">
    <?php require_once __DIR__ . '/../includes/farmer_sidebar.php'; ?>
    
    <div class="dashboard-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>My Farms</h2>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addFarmModal">
                <i class="fas fa-plus me-1"></i> Add New Farm
            </button>
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

        <div class="row">
            <?php if (empty($farms)): ?>
                <div class="col-12 text-center py-5">
                    <i class="fas fa-tractor fa-4x text-muted mb-3 opacity-25"></i>
                    <h4 class="text-muted">No farms found</h4>
                    <p class="text-muted">Add your first farm to start managing your crops.</p>
                </div>
            <?php else: ?>
                <?php foreach ($farms as $farm): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card shadow-sm h-100">
                            <div class="card-body">
                                <h5 class="card-title fw-bold text-success mb-3">
                                    <i class="fas fa-map me-2"></i><?php echo htmlspecialchars($farm['name']); ?>
                                </h5>
                                <ul class="list-unstyled mb-3 text-muted">
                                    <li class="mb-2"><i class="fas fa-ruler-combined me-2"></i> <strong>Size:</strong> <?php echo htmlspecialchars($farm['size_hectares']); ?> Hectares</li>
                                    <li><i class="fas fa-map-pin me-2"></i> <strong>Location:</strong> 
                                        <?php echo ($farm['latitude'] && $farm['longitude']) ? "Mapped" : "Not specified"; ?>
                                    </li>
                                </ul>
                                <hr>
                                <div class="d-flex justify-content-between">
                                    <a href="<?php echo BASE_URL; ?>/farmer/crops.php?farm_id=<?php echo $farm['id']; ?>" class="btn btn-outline-success btn-sm">Manage Crops</a>
                                    <button class="btn btn-outline-primary btn-sm"><i class="fas fa-edit"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add Farm Modal -->
<div class="modal fade" id="addFarmModal" tabindex="-1" aria-labelledby="addFarmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="">
                <input type="hidden" name="action" value="add">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="addFarmModalLabel">Register New Farm</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Farm Name</label>
                            <input type="text" class="form-control" name="name" required placeholder="e.g., North Valley Field">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Size (Hectares)</label>
                            <input type="number" step="0.01" class="form-control" name="size" required placeholder="e.g., 2.5">
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Location</label>
                            <div class="alert alert-info py-2"><small><i class="fas fa-info-circle me-1"></i> Click on the map to set your farm's location.</small></div>
                            <div id="farmMap" class="map-container mb-2"></div>
                            <input type="hidden" name="lat" id="lat">
                            <input type="hidden" name="lng" id="lng">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Save Farm</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php 
$extra_js = "
<script>
document.addEventListener('DOMContentLoaded', function() {
    let map = null;
    let marker = null;
    
    const myModalEl = document.getElementById('addFarmModal');
    myModalEl.addEventListener('shown.bs.modal', function () {
        if (!map) {
            // Default center Eswatini
            map = L.map('farmMap').setView([-26.3054, 31.1367], 9);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            map.on('click', function(e) {
                if (marker) {
                    map.removeLayer(marker);
                }
                marker = L.marker(e.latlng).addTo(map);
                document.getElementById('lat').value = e.latlng.lat;
                document.getElementById('lng').value = e.latlng.lng;
            });
        }
        setTimeout(() => { map.invalidateSize(); }, 100);
    });
});
</script>
";
require_once __DIR__ . '/../includes/footer.php'; 
?>
