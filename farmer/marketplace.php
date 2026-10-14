<?php
require_once __DIR__ . '/../config/core.php';
requireRole('farmer');

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// 1. Ensure table exists in database
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS marketplace_listings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            title VARCHAR(150) NOT NULL,
            category ENUM('vegetables', 'fruits', 'grains', 'livestock', 'seeds_inputs', 'equipment', 'other') DEFAULT 'vegetables',
            price_szl DECIMAL(10, 2) NOT NULL,
            quantity VARCHAR(50) NOT NULL,
            location VARCHAR(100) NOT NULL,
            contact_phone VARCHAR(30) NOT NULL,
            description TEXT,
            image_path VARCHAR(255) DEFAULT NULL,
            status ENUM('active', 'sold') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // Seed sample listings if table is empty
    $check_count = $pdo->query("SELECT COUNT(*) FROM marketplace_listings")->fetchColumn();
    if ($check_count == 0) {
        $sample_sql = "INSERT INTO marketplace_listings (user_id, title, category, price_szl, quantity, location, contact_phone, description, status) VALUES 
        (?, 'Fresh Organic Cabbages (Hercules)', 'vegetables', 15.00, '500 heads', 'Manzini', '76123456', 'High quality fresh cabbage heads harvested this week in Manzini. Bulk buyers welcome.', 'active'),
        (?, 'Yellow Maize Seed (PAN 53)', 'grains', 450.00, '50 kg bag', 'Mbabane', '78901234', 'Quality certified yellow maize for processing or poultry feed.', 'active'),
        (?, 'Ripe Red Tomatoes (Star 9001)', 'vegetables', 120.00, '1 crate (20kg)', 'Nhlangano', '76998877', 'Firm red ripe tomatoes, great shelf life and ready for market delivery.', 'active'),
        (?, 'Sweet Potatoes (Ligwalagwala)', 'vegetables', 85.00, '25 kg bag', 'Siteki', '76443322', 'Fresh sweet potatoes with orange skin and high starch quality.', 'active')";
        $stmt_seed = $pdo->prepare($sample_sql);
        $stmt_seed->execute([$user_id, $user_id, $user_id, $user_id]);
    }
} catch (PDOException $e) {
    // Soft handle table creation
}

// 2. Handle Listing Actions (Create, Mark Sold, Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create') {
        $title = trim($_POST['title'] ?? '');
        $category = $_POST['category'] ?? 'vegetables';
        $price = floatval($_POST['price_szl'] ?? 0);
        $quantity = trim($_POST['quantity'] ?? '');
        $location = trim($_POST['location'] ?? '');
        $phone = trim($_POST['contact_phone'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if (empty($title) || $price <= 0 || empty($quantity) || empty($location) || empty($phone)) {
            $error = "Please fill in all required fields (Title, Price, Quantity, Location, and Phone).";
        } else {
            $image_path = null;
            
            // Handle optional image upload
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $target_dir = __DIR__ . '/../assets/uploads/marketplace/';
                if (!file_exists($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }
                
                $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                $allowed_exts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
                
                if (in_array($file_ext, $allowed_exts)) {
                    $filename = 'item_' . time() . '_' . rand(100, 999) . '.' . $file_ext;
                    $target_file = $target_dir . $filename;
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                        $image_path = 'assets/uploads/marketplace/' . $filename;
                    }
                }
            }

            try {
                $stmt = $pdo->prepare("
                    INSERT INTO marketplace_listings 
                    (user_id, title, category, price_szl, quantity, location, contact_phone, description, image_path) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                if ($stmt->execute([$user_id, $title, $category, $price, $quantity, $location, $phone, $description, $image_path])) {
                    $success = "Your listing has been successfully published to the marketplace!";
                } else {
                    $error = "Failed to post listing. Please try again.";
                }
            } catch (PDOException $e) {
                $error = "Database error: " . $e->getMessage();
            }
        }
    } elseif ($action === 'toggle_status') {
        $listing_id = intval($_POST['listing_id'] ?? 0);
        $new_status = $_POST['status'] === 'active' ? 'sold' : 'active';
        try {
            $stmt = $pdo->prepare("UPDATE marketplace_listings SET status = ? WHERE id = ? AND user_id = ?");
            $stmt->execute([$new_status, $listing_id, $user_id]);
            $success = "Listing status updated to " . ucfirst($new_status) . ".";
        } catch (PDOException $e) {
            $error = "Error updating listing status.";
        }
    } elseif ($action === 'delete') {
        $listing_id = intval($_POST['listing_id'] ?? 0);
        try {
            $stmt = $pdo->prepare("DELETE FROM marketplace_listings WHERE id = ? AND user_id = ?");
            $stmt->execute([$listing_id, $user_id]);
            $success = "Listing deleted successfully.";
        } catch (PDOException $e) {
            $error = "Error deleting listing.";
        }
    }
}

// 3. Fetch Listings with Filters
$search = trim($_GET['search'] ?? '');
$filter_category = $_GET['category'] ?? 'all';
$view_type = $_GET['view'] ?? 'all'; // 'all' or 'my'

$query = "SELECT ml.*, u.name as seller_name 
          FROM marketplace_listings ml 
          JOIN users u ON ml.user_id = u.id 
          WHERE 1=1 ";

$params = [];

if ($view_type === 'my') {
    $query .= " AND ml.user_id = ? ";
    $params[] = $user_id;
} else {
    $query .= " AND ml.status = 'active' ";
}

if (!empty($search)) {
    $query .= " AND (ml.title LIKE ? OR ml.description LIKE ? OR ml.location LIKE ?) ";
    $term = "%$search%";
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
}

if ($filter_category !== 'all' && !empty($filter_category)) {
    $query .= " AND ml.category = ? ";
    $params[] = $filter_category;
}

$query .= " ORDER BY ml.created_at DESC";

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $listings = $stmt->fetchAll();
} catch (PDOException $e) {
    $listings = [];
}

$category_icons = [
    'vegetables' => 'fa-carrot text-success',
    'fruits' => 'fa-apple-alt text-danger',
    'grains' => 'fa-wheat-awn text-warning',
    'livestock' => 'fa-cow text-primary',
    'seeds_inputs' => 'fa-seedling text-info',
    'equipment' => 'fa-tractor text-secondary',
    'other' => 'fa-box text-dark'
];

$page_title = "Farmer Marketplace";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="dashboard-wrapper">
    <?php require_once __DIR__ . '/../includes/farmer_sidebar.php'; ?>
    
    <div class="dashboard-content">
        <!-- Top Header & Actions -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
            <div>
                <h2><i class="fas fa-store text-warning me-2"></i>Eswatini Farmer Marketplace</h2>
                <p class="text-muted mb-0">Buy and sell fresh produce, seeds, equipment, and livestock directly with other farmers across Eswatini.</p>
            </div>
            <button class="btn btn-success btn-lg" data-bs-toggle="modal" data-bs-target="#postListingModal">
                <i class="fas fa-plus-circle me-1"></i> Post New Listing
            </button>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Search & Filtering Bar -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" action="" class="row g-3 align-items-center">
                    <div class="col-md-5">
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" class="form-control" name="search" placeholder="Search crops, location, or equipment..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="category" onchange="this.form.submit()">
                            <option value="all" <?php echo $filter_category === 'all' ? 'selected' : ''; ?>>All Categories</option>
                            <option value="vegetables" <?php echo $filter_category === 'vegetables' ? 'selected' : ''; ?>>Vegetables</option>
                            <option value="fruits" <?php echo $filter_category === 'fruits' ? 'selected' : ''; ?>>Fruits</option>
                            <option value="grains" <?php echo $filter_category === 'grains' ? 'selected' : ''; ?>>Grains & Pulses</option>
                            <option value="livestock" <?php echo $filter_category === 'livestock' ? 'selected' : ''; ?>>Livestock</option>
                            <option value="seeds_inputs" <?php echo $filter_category === 'seeds_inputs' ? 'selected' : ''; ?>>Seeds & Inputs</option>
                            <option value="equipment" <?php echo $filter_category === 'equipment' ? 'selected' : ''; ?>>Equipment & Tools</option>
                            <option value="other" <?php echo $filter_category === 'other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex gap-2">
                        <a href="?view=all" class="btn btn-outline-secondary flex-fill <?php echo $view_type === 'all' ? 'active' : ''; ?>">
                            <i class="fas fa-border-all me-1"></i> All Items
                        </a>
                        <a href="?view=my" class="btn btn-outline-primary flex-fill <?php echo $view_type === 'my' ? 'active' : ''; ?>">
                            <i class="fas fa-user-tag me-1"></i> My Listings
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Marketplace Cards Grid -->
        <div class="row">
            <?php if (empty($listings)): ?>
                <div class="col-12 text-center py-5">
                    <i class="fas fa-store-slash fa-4x text-muted mb-3 opacity-25"></i>
                    <h4 class="text-muted">No marketplace listings found</h4>
                    <p class="text-muted">Try adjusting your search criteria or be the first to post produce for sale!</p>
                </div>
            <?php else: ?>
                <?php foreach ($listings as $item): 
                    $cat_icon = $category_icons[$item['category']] ?? 'fa-box text-dark';
                    $clean_phone = preg_replace('/[^0-9]/', '', $item['contact_phone']);
                    if (!str_starts_with($clean_phone, '268') && strlen($clean_phone) == 8) {
                        $clean_phone = '268' . $clean_phone;
                    }
                ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card shadow-sm h-100 position-relative">
                            <?php if ($item['status'] === 'sold'): ?>
                                <span class="badge bg-danger position-absolute top-0 end-0 m-3 fs-6 z-1">SOLD</span>
                            <?php endif; ?>
                            
                            <?php if (!empty($item['image_path'])): ?>
                                <img src="<?php echo BASE_URL . '/' . htmlspecialchars($item['image_path']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($item['title']); ?>" style="height: 180px; object-fit: cover;">
                            <?php else: ?>
                                <div class="bg-light card-img-top d-flex align-items-center justify-content-center text-secondary" style="height: 180px;">
                                    <i class="fas <?php echo $cat_icon; ?> fa-4x opacity-50"></i>
                                </div>
                            <?php endif; ?>

                            <div class="card-body d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="badge bg-light text-dark border"><i class="fas <?php echo $cat_icon; ?> me-1"></i><?php echo ucfirst(str_replace('_', ' ', $item['category'])); ?></span>
                                    <small class="text-muted"><i class="far fa-clock me-1"></i><?php echo date('M d', strtotime($item['created_at'])); ?></small>
                                </div>

                                <h5 class="card-title fw-bold text-dark mb-1"><?php echo htmlspecialchars($item['title']); ?></h5>
                                <h4 class="text-success fw-bold mb-3"><?php echo formatSZL($item['price_szl']); ?> <small class="text-muted fs-6 fw-normal">/ <?php echo htmlspecialchars($item['quantity']); ?></small></h4>

                                <p class="card-text text-muted small mb-3 flex-grow-1">
                                    <?php echo htmlspecialchars(mb_strimwidth($item['description'] ?: 'No detailed description provided.', 0, 100, '...')); ?>
                                </p>

                                <div class="border-top pt-2 mt-auto">
                                    <div class="d-flex justify-content-between align-items-center small text-muted mb-3">
                                        <span><i class="fas fa-user-circle me-1"></i><?php echo htmlspecialchars($item['seller_name']); ?></span>
                                        <span><i class="fas fa-map-marker-alt text-danger me-1"></i><?php echo htmlspecialchars($item['location']); ?></span>
                                    </div>

                                    <?php if ($item['user_id'] == $user_id): ?>
                                        <div class="d-flex gap-2">
                                            <form method="POST" action="" class="flex-fill">
                                                <input type="hidden" name="action" value="toggle_status">
                                                <input type="hidden" name="listing_id" value="<?php echo $item['id']; ?>">
                                                <input type="hidden" name="status" value="<?php echo $item['status']; ?>">
                                                <button type="submit" class="btn btn-sm w-100 <?php echo $item['status'] === 'active' ? 'btn-outline-warning' : 'btn-outline-success'; ?>">
                                                    <?php echo $item['status'] === 'active' ? 'Mark as Sold' : 'Mark as Active'; ?>
                                                </button>
                                            </form>
                                            <form method="POST" action="" onsubmit="return confirm('Are you sure you want to delete this listing?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="listing_id" value="<?php echo $item['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                            </form>
                                        </div>
                                    <?php else: ?>
                                        <div class="d-grid gap-2 d-flex">
                                            <a href="tel:+<?php echo $clean_phone; ?>" class="btn btn-outline-primary btn-sm flex-fill">
                                                <i class="fas fa-phone-alt me-1"></i> Call Seller
                                            </a>
                                            <a href="https://wa.me/<?php echo $clean_phone; ?>?text=<?php echo urlencode('Hi ' . $item['seller_name'] . ', I saw your listing for "' . $item['title'] . '" on Smart Advisor Eswatini. Is it still available?'); ?>" target="_blank" class="btn btn-success btn-sm flex-fill">
                                                <i class="fab fa-whatsapp me-1"></i> WhatsApp
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal: Post New Listing -->
<div class="modal fade" id="postListingModal" tabindex="-1" aria-labelledby="postListingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="" enctype="multipart/form-data">
                <input type="hidden" name="action" value="create">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="postListingModalLabel"><i class="fas fa-plus-circle me-2"></i>Post Item for Sale</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label fw-bold">Listing Title *</label>
                            <input type="text" class="form-control" name="title" required placeholder="e.g. Fresh Red Tomatoes, Yellow Maize Bags">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Category *</label>
                            <select class="form-select" name="category" required>
                                <option value="vegetables">Vegetables</option>
                                <option value="fruits">Fruits</option>
                                <option value="grains">Grains & Pulses</option>
                                <option value="livestock">Livestock</option>
                                <option value="seeds_inputs">Seeds & Fertilizer</option>
                                <option value="equipment">Equipment & Tools</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Price (SZL / Emalangeni) *</label>
                            <div class="input-group">
                                <span class="input-group-text">E</span>
                                <input type="number" step="0.01" min="0" class="form-control" name="price_szl" required placeholder="150.00">
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Quantity Unit *</label>
                            <input type="text" class="form-control" name="quantity" required placeholder="e.g. 50 kg bag, 1 crate, 100 heads">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Location in Eswatini *</label>
                            <input type="text" class="form-control" name="location" required placeholder="e.g. Manzini, Mbabane, Siteki">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Contact Phone Number *</label>
                            <div class="input-group">
                                <span class="input-group-text">+268</span>
                                <input type="text" class="form-control" name="contact_phone" required placeholder="76123456" value="<?php echo htmlspecialchars($_SESSION['user_phone'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Product Photo (Optional)</label>
                            <input type="file" class="form-control" name="image" accept="image/*">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Product Description</label>
                        <textarea class="form-control" name="description" rows="3" placeholder="Provide details about freshness, variety, delivery options, or bulk discounts..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success"><i class="fas fa-check-circle me-1"></i> Publish Listing</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
