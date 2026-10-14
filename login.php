<?php
require_once __DIR__ . '/config/core.php';

if (isLoggedIn()) {
    redirect('/index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($phone) || empty($password)) {
        $error = "Phone and Password are required.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE phone = ? OR email = ?");
            $stmt->execute([$phone, $phone]); // Allow email or phone
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = $user['role'];

                // Redirect based on role
                if ($user['role'] === 'admin') {
                    redirect('/admin/dashboard.php');
                } elseif ($user['role'] === 'officer') {
                    redirect('/officer/dashboard.php');
                } else {
                    redirect('/farmer/dashboard.php');
                }
            } else {
                $error = "Invalid phone/email or password.";
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}

$page_title = "Login";
require_once __DIR__ . '/includes/header.php';
?>

<div class="container my-5 py-4">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-lg border-0" style="background: rgba(255, 255, 255, 0.94); backdrop-filter: blur(16px); border-radius: 1.25rem;">
                <div class="card-header bg-success text-white text-center py-4 border-0" style="border-radius: 1.25rem 1.25rem 0 0; background: linear-gradient(135deg, #10b981, #059669) !important;">
                    <div class="bg-white bg-opacity-20 rounded-circle d-inline-flex p-3 mb-2">
                        <i class="fas fa-sign-in-alt fa-2x text-white"></i>
                    </div>
                    <h4 class="mb-0 fw-bold">Sign In to Smart Advisor</h4>
                    <p class="small mb-0 opacity-75">Access your farm, AI doctor & marketplace</p>
                </div>
                <div class="card-body p-4 p-md-5">
                    <?php if ($error): ?>
                        <div class="alert alert-danger rounded-3"><i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="phone" class="form-label fw-bold">Phone Number or Email</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="fas fa-user text-muted"></i></span>
                                <input type="text" class="form-control" id="phone" name="phone" required placeholder="76123456 or email" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="mb-4">
                            <label for="password" class="form-label fw-bold">Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="fas fa-lock text-muted"></i></span>
                                <input type="password" class="form-control" id="password" name="password" required placeholder="••••••••">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success btn-lg w-100 py-3 rounded-3 fw-bold">
                            <i class="fas fa-sign-in-alt me-2"></i>Login Now
                        </button>
                    </form>
                </div>
                <div class="card-footer text-center py-3 bg-transparent border-top">
                    Don't have an account? <a href="<?php echo BASE_URL; ?>/register.php" class="text-success fw-bold text-decoration-none">Register here</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
