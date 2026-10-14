<?php
require_once __DIR__ . '/../config/core.php';
requireRole('farmer');

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'Farmer';
$success = '';
$error = '';

// 1. Ensure DB tables exist
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS community_posts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            post_type ENUM('general', 'question', 'tip', 'ad') DEFAULT 'general',
            content TEXT NOT NULL,
            image_path VARCHAR(255) DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        CREATE TABLE IF NOT EXISTS post_likes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            post_id INT NOT NULL,
            user_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_like (post_id, user_id),
            FOREIGN KEY (post_id) REFERENCES community_posts(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        CREATE TABLE IF NOT EXISTS post_comments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            post_id INT NOT NULL,
            user_id INT NOT NULL,
            comment_text TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (post_id) REFERENCES community_posts(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // Seed sample posts if feed is completely empty
    $check_count = $pdo->query("SELECT COUNT(*) FROM community_posts")->fetchColumn();
    if ($check_count == 0) {
        $sample_posts = [
            [
                'user_id' => $user_id,
                'type' => 'tip',
                'content' => "💡 **Top Tip for Maize Farmers in Middleveld**: Always ensure top-dressing fertilizer (LAN) is applied right before rain or immediately followed by light irrigation to avoid nitrogen evaporation!"
            ],
            [
                'user_id' => $user_id,
                'type' => 'question',
                'content' => "❓ **Help needed**: Seeing small yellow spots forming on my tomato leaves in Manzini. Has anyone experienced early blight this month? Which fungicide works best between Bravo and Ridomil?"
            ],
            [
                'user_id' => $user_id,
                'type' => 'ad',
                'content' => "📢 **SPECIAL OFFER / AD**: High-yield Certified Bean Seeds (Phaseolus vulgaris) available in bulk packages at discounted rates! Delivery available to Mbabane and Manzini. Call/WhatsApp 76123456."
            ]
        ];

        $stmt_seed = $pdo->prepare("INSERT INTO community_posts (user_id, post_type, content) VALUES (?, ?, ?)");
        foreach ($sample_posts as $sp) {
            $stmt_seed->execute([$sp['user_id'], $sp['type'], $sp['content']]);
        }
    }
} catch (PDOException $e) {
    // Soft error handling
}

// 2. Handle New Post Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'create_post') {
        $content = trim($_POST['content'] ?? '');
        $post_type = $_POST['post_type'] ?? 'general';
        $image_path = null;

        if (empty($content)) {
            $error = "Post content cannot be empty.";
        } else {
            // Handle optional image upload
            if (isset($_FILES['post_image']) && $_FILES['post_image']['error'] === UPLOAD_ERR_OK) {
                $target_dir = __DIR__ . '/../assets/uploads/feed/';
                if (!file_exists($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }
                
                $file_ext = strtolower(pathinfo($_FILES['post_image']['name'], PATHINFO_EXTENSION));
                $allowed_exts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
                
                if (in_array($file_ext, $allowed_exts)) {
                    $filename = 'feed_' . time() . '_' . rand(100, 999) . '.' . $file_ext;
                    $target_file = $target_dir . $filename;
                    if (move_uploaded_file($_FILES['post_image']['tmp_name'], $target_file)) {
                        $image_path = 'assets/uploads/feed/' . $filename;
                    }
                }
            }

            try {
                $stmt = $pdo->prepare("INSERT INTO community_posts (user_id, post_type, content, image_path) VALUES (?, ?, ?, ?)");
                if ($stmt->execute([$user_id, $post_type, $content, $image_path])) {
                    $success = "Your post has been shared with the community!";
                } else {
                    $error = "Failed to create post.";
                }
            } catch (PDOException $e) {
                $error = "Database error: " . $e->getMessage();
            }
        }
    } elseif ($action === 'toggle_like') {
        $post_id = intval($_POST['post_id'] ?? 0);
        try {
            $stmt = $pdo->prepare("SELECT id FROM post_likes WHERE post_id = ? AND user_id = ?");
            $stmt->execute([$post_id, $user_id]);
            if ($stmt->fetch()) {
                // Unlike
                $del = $pdo->prepare("DELETE FROM post_likes WHERE post_id = ? AND user_id = ?");
                $del->execute([$post_id, $user_id]);
            } else {
                // Like
                $ins = $pdo->prepare("INSERT INTO post_likes (post_id, user_id) VALUES (?, ?)");
                $ins->execute([$post_id, $user_id]);
            }
        } catch (PDOException $e) {
            // Soft fail
        }
    } elseif ($action === 'add_comment') {
        $post_id = intval($_POST['post_id'] ?? 0);
        $comment_text = trim($_POST['comment_text'] ?? '');
        if (!empty($comment_text)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO post_comments (post_id, user_id, comment_text) VALUES (?, ?, ?)");
                $stmt->execute([$post_id, $user_id, $comment_text]);
            } catch (PDOException $e) {
                // Soft fail
            }
        }
    } elseif ($action === 'delete_post') {
        $post_id = intval($_POST['post_id'] ?? 0);
        try {
            $stmt = $pdo->prepare("DELETE FROM community_posts WHERE id = ? AND user_id = ?");
            $stmt->execute([$post_id, $user_id]);
            $success = "Post deleted.";
        } catch (PDOException $e) {
            $error = "Could not delete post.";
        }
    }
}

// 3. Fetch Posts & Interactions
$filter_type = $_GET['type'] ?? 'all';

$query = "SELECT cp.*, u.name as author_name, u.role as author_role,
          (SELECT COUNT(*) FROM post_likes WHERE post_id = cp.id) as likes_count,
          (SELECT COUNT(*) FROM post_likes WHERE post_id = cp.id AND user_id = ?) as user_liked,
          (SELECT COUNT(*) FROM post_comments WHERE post_id = cp.id) as comments_count
          FROM community_posts cp
          JOIN users u ON cp.user_id = u.id 
          WHERE 1=1 ";

$params = [$user_id];

if ($filter_type !== 'all' && in_array($filter_type, ['general', 'question', 'tip', 'ad'])) {
    $query .= " AND cp.post_type = ? ";
    $params[] = $filter_type;
}

$query .= " ORDER BY cp.created_at DESC";

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $posts = $stmt->fetchAll();
} catch (PDOException $e) {
    $posts = [];
}

// Fetch comments for each post
$comments_by_post = [];
try {
    $stmt_c = $pdo->query("SELECT pc.*, u.name as commenter_name FROM post_comments pc JOIN users u ON pc.user_id = u.id ORDER BY pc.created_at ASC");
    $raw_comments = $stmt_c->fetchAll();
    foreach ($raw_comments as $c) {
        $comments_by_post[$c['post_id']][] = $c;
    }
} catch (PDOException $e) {
    $comments_by_post = [];
}

$post_type_badges = [
    'general' => ['label' => 'General', 'class' => 'bg-secondary'],
    'question' => ['label' => 'Question & Help', 'class' => 'bg-warning text-dark'],
    'tip' => ['label' => 'Farming Tip', 'class' => 'bg-success'],
    'ad' => ['label' => 'Sponsored Ad / Offer', 'class' => 'bg-danger']
];

$page_title = "Farmer Community Feed & Ads";
require_once __DIR__ . '/../includes/header.php';
?>

<style>
.feed-post-card {
    border-radius: 0.75rem;
    border: none;
    box-shadow: 0 0.125rem 0.3rem rgba(0,0,0,0.08);
}
.post-author-avatar {
    width: 45px;
    height: 45px;
    background-color: #198754;
    color: white;
    font-weight: bold;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
}
.comment-avatar {
    width: 32px;
    height: 32px;
    background-color: #6c757d;
    color: white;
    font-size: 0.85rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}
.post-image-container img {
    max-height: 380px;
    width: 100%;
    object-fit: cover;
    border-radius: 0.5rem;
}
</style>

<div class="dashboard-wrapper">
    <?php require_once __DIR__ . '/../includes/farmer_sidebar.php'; ?>
    
    <div class="dashboard-content">
        <div class="row">
            <!-- Left & Main Feed Column -->
            <div class="col-lg-8">
                <!-- Top Header -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h3 class="fw-bold mb-1"><i class="fas fa-rss text-info me-2"></i>Farmer Community Feed & Ads</h3>
                        <p class="text-muted small mb-0">Share tips, ask questions, browse sponsored ads, and connect with farmers in Eswatini.</p>
                    </div>
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

                <!-- Composer Card (Like Facebook "What's on your mind?") -->
                <div class="card feed-post-card mb-4">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center mb-3">
                            <div class="post-author-avatar me-3">
                                <?php echo strtoupper(substr($user_name, 0, 1)); ?>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($user_name); ?></h6>
                                <small class="text-muted"><i class="fas fa-globe-africa me-1"></i>Posting to Eswatini Community</small>
                            </div>
                        </div>

                        <form method="POST" action="" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="create_post">
                            <div class="mb-3">
                                <textarea class="form-control border-0 bg-light p-3" name="content" rows="3" required placeholder="What's happening on your farm, <?php echo htmlspecialchars($user_name); ?>? Ask a question, share a tip, or post an ad..."></textarea>
                            </div>
                            
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 border-top pt-2">
                                <div class="d-flex gap-2 align-items-center">
                                    <select class="form-select form-select-sm" name="post_type" style="width: auto;">
                                        <option value="general">📝 General Post</option>
                                        <option value="question">❓ Question / Help</option>
                                        <option value="tip">💡 Farming Tip</option>
                                        <option value="ad">📢 Sponsored / Ad</option>
                                    </select>
                                    <label class="btn btn-outline-secondary btn-sm mb-0">
                                        <i class="fas fa-image text-success me-1"></i> Add Photo
                                        <input type="file" name="post_image" class="d-none" accept="image/*">
                                    </label>
                                </div>
                                <button type="submit" class="btn btn-success px-4">
                                    <i class="fas fa-paper-plane me-1"></i> Share Post
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Feed Filter Tabs -->
                <div class="d-flex gap-2 mb-3 overflow-auto py-1">
                    <a href="?type=all" class="btn btn-sm <?php echo $filter_type === 'all' ? 'btn-dark' : 'btn-outline-secondary'; ?> rounded-pill px-3">All Posts</a>
                    <a href="?type=question" class="btn btn-sm <?php echo $filter_type === 'question' ? 'btn-warning text-dark' : 'btn-outline-warning text-dark'; ?> rounded-pill px-3"><i class="fas fa-question-circle me-1"></i>Questions</a>
                    <a href="?type=tip" class="btn btn-sm <?php echo $filter_type === 'tip' ? 'btn-success' : 'btn-outline-success'; ?> rounded-pill px-3"><i class="fas fa-lightbulb me-1"></i>Farming Tips</a>
                    <a href="?type=ad" class="btn btn-sm <?php echo $filter_type === 'ad' ? 'btn-danger' : 'btn-outline-danger'; ?> rounded-pill px-3"><i class="fas fa-ad me-1"></i>Ads & Offers</a>
                </div>

                <!-- Feed Posts Stream -->
                <?php if (empty($posts)): ?>
                    <div class="card feed-post-card text-center py-5">
                        <div class="card-body">
                            <i class="fas fa-newspaper fa-4x text-muted mb-3 opacity-25"></i>
                            <h5 class="text-muted">No posts found in this category</h5>
                            <p class="text-muted">Be the first to share an update or question with fellow farmers!</p>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($posts as $post): 
                        $badge = $post_type_badges[$post['post_type']] ?? $post_type_badges['general'];
                        $post_comments = $comments_by_post[$post['id']] ?? [];
                    ?>
                        <div class="card feed-post-card mb-4">
                            <div class="card-body p-3">
                                <!-- Post Header -->
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="d-flex align-items-center">
                                        <div class="post-author-avatar me-3">
                                            <?php echo strtoupper(substr($post['author_name'], 0, 1)); ?>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($post['author_name']); ?></h6>
                                            <small class="text-muted">
                                                <i class="far fa-clock me-1"></i><?php echo date('M d, Y \a\t H:i', strtotime($post['created_at'])); ?>
                                            </small>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge <?php echo $badge['class']; ?> p-2"><?php echo $badge['label']; ?></span>
                                        <?php if ($post['user_id'] == $user_id): ?>
                                            <form method="POST" action="" onsubmit="return confirm('Delete this post?');" class="d-inline">
                                                <input type="hidden" name="action" value="delete_post">
                                                <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                                <button type="submit" class="btn btn-link text-muted p-0 ms-1"><i class="fas fa-trash-alt"></i></button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Post Content -->
                                <div class="post-content mb-3" style="white-space: pre-line;">
                                    <?php echo htmlspecialchars($post['content']); ?>
                                </div>

                                <!-- Post Image Attachment -->
                                <?php if (!empty($post['image_path'])): ?>
                                    <div class="post-image-container mb-3 text-center">
                                        <img src="<?php echo BASE_URL . '/' . htmlspecialchars($post['image_path']); ?>" alt="Attachment" class="img-fluid">
                                    </div>
                                <?php endif; ?>

                                <!-- Post Stats (Likes & Comments Count) -->
                                <div class="d-flex justify-content-between align-items-center border-top pt-2 pb-2 text-muted small">
                                    <span>
                                        <i class="fas fa-thumbs-up text-primary me-1"></i> <?php echo $post['likes_count']; ?> Likes
                                    </span>
                                    <span>
                                        <i class="far fa-comment me-1"></i> <?php echo $post['comments_count']; ?> Comments
                                    </span>
                                </div>

                                <!-- Action Buttons (Like & Comment Toggle) -->
                                <div class="d-flex border-top border-bottom py-1">
                                    <form method="POST" action="" class="flex-fill text-center">
                                        <input type="hidden" name="action" value="toggle_like">
                                        <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                        <button type="submit" class="btn btn-link text-decoration-none w-100 py-1 <?php echo $post['user_liked'] ? 'text-primary fw-bold' : 'text-muted'; ?>">
                                            <i class="<?php echo $post['user_liked'] ? 'fas' : 'far'; ?> fa-thumbs-up me-1"></i> Like
                                        </button>
                                    </form>
                                    <button class="btn btn-link text-muted text-decoration-none flex-fill text-center py-1" type="button" data-bs-toggle="collapse" data-bs-target="#comments-<?php echo $post['id']; ?>">
                                        <i class="far fa-comment me-1"></i> Comment
                                    </button>
                                </div>

                                <!-- Comments Section (Collapsible) -->
                                <div class="collapse mt-3 <?php echo !empty($post_comments) ? 'show' : ''; ?>" id="comments-<?php echo $post['id']; ?>">
                                    <!-- Comment List -->
                                    <?php if (!empty($post_comments)): ?>
                                        <div class="comments-list mb-3">
                                            <?php foreach ($post_comments as $comment): ?>
                                                <div class="d-flex align-items-start mb-2">
                                                    <div class="comment-avatar me-2 flex-shrink-0">
                                                        <?php echo strtoupper(substr($comment['commenter_name'], 0, 1)); ?>
                                                    </div>
                                                    <div class="bg-light rounded p-2 flex-grow-1">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <strong class="small"><?php echo htmlspecialchars($comment['commenter_name']); ?></strong>
                                                            <small class="text-muted" style="font-size: 0.75rem;"><?php echo date('M d, H:i', strtotime($comment['created_at'])); ?></small>
                                                        </div>
                                                        <p class="mb-0 small text-dark"><?php echo htmlspecialchars($comment['comment_text']); ?></p>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Add Comment Form -->
                                    <form method="POST" action="" class="d-flex gap-2">
                                        <input type="hidden" name="action" value="add_comment">
                                        <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                        <input type="text" class="form-control form-control-sm rounded-pill" name="comment_text" required placeholder="Write a comment...">
                                        <button type="submit" class="btn btn-success btn-sm rounded-pill px-3">Post</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Right Sidebar Widgets Column (Ads & Highlights) -->
            <div class="col-lg-4">
                <!-- Sponsored Ads Card -->
                <div class="card feed-post-card mb-4 border-start border-4 border-danger">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold text-danger"><i class="fas fa-bullhorn me-2"></i>Featured Ads & Supplies</h6>
                        <span class="badge bg-light text-muted border">Ad</span>
                    </div>
                    <div class="card-body">
                        <div class="mb-3 border-bottom pb-3">
                            <h6 class="fw-bold mb-1"><a href="marketplace.php" class="text-dark text-decoration-none">NPK 2:3:2 Fertilizer Sale</a></h6>
                            <p class="small text-muted mb-2">Manzini Agro-Dealers offering E50 discount on bulk fertilizer bags for early order bookings.</p>
                            <a href="marketplace.php" class="btn btn-outline-danger btn-sm w-100">View Marketplace Offer</a>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1"><a href="services.php" class="text-dark text-decoration-none">Mobile Vet Service (Shiselweni)</a></h6>
                            <p class="small text-muted mb-2">Vaccination and livestock checkups available on call across Shiselweni region.</p>
                            <a href="services.php" class="btn btn-outline-primary btn-sm w-100">Contact Vet Service</a>
                        </div>
                    </div>
                </div>

                <!-- Trending Topics Card -->
                <div class="card feed-post-card mb-4">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0 fw-bold"><i class="fas fa-hashtag text-primary me-2"></i>Trending in Eswatini Farming</h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">
                                <a href="feed.php?type=tip" class="text-decoration-none fw-bold text-success">#MaizePlantingSeason</a>
                                <div class="small text-muted">14 posts & discussions</div>
                            </li>
                            <li class="mb-2">
                                <a href="feed.php?type=question" class="text-decoration-none fw-bold text-primary">#EarlyBlightControl</a>
                                <div class="small text-muted">8 questions answered</div>
                            </li>
                            <li>
                                <a href="marketplace.php" class="text-decoration-none fw-bold text-warning">#VegetableMarketPrices</a>
                                <div class="small text-muted">Live listings updated</div>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Quick Help / Extension Officer Card -->
                <div class="card feed-post-card bg-success text-white">
                    <div class="card-body p-4 text-center">
                        <i class="fas fa-user-tie fa-3x mb-3"></i>
                        <h5 class="fw-bold">Need Official Advice?</h5>
                        <p class="small mb-3">Post a question under the <strong>"Question / Help"</strong> category to get answers from Agricultural Extension Officers.</p>
                        <a href="?type=question" class="btn btn-light text-success btn-sm fw-bold px-3">View Open Questions</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
