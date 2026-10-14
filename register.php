<?php
require_once __DIR__ . '/config/core.php';

if (isLoggedIn()) {
    redirect('/index.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($name) || empty($phone) || empty($password)) {
        $error = "Name, Phone, and Password are required.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        try {
            // Check if phone already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE phone = ?");
            $stmt->execute([$phone]);
            if ($stmt->fetch()) {
                $error = "Phone number is already registered.";
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (name, email, phone, password_hash, role) VALUES (?, ?, ?, ?, 'farmer')");
                if ($stmt->execute([$name, $email, $phone, $hash])) {
                    $success = "Registration successful! You can now login.";
                } else {
                    $error = "Registration failed. Please try again.";
                }
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}

$page_title = "Register";
require_once __DIR__ . '/includes/header.php';
?>

<div class="container my-5 py-4">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-lg border-0" style="background: rgba(255, 255, 255, 0.94); backdrop-filter: blur(16px); border-radius: 1.25rem;">
                <div class="card-header bg-success text-white text-center py-4 border-0" style="border-radius: 1.25rem 1.25rem 0 0; background: linear-gradient(135deg, #10b981, #059669) !important;">
                    <div class="bg-white bg-opacity-20 rounded-circle d-inline-flex p-3 mb-2">
                        <i class="fas fa-user-plus fa-2x text-white"></i>
                    </div>
                    <h4 class="mb-0 fw-bold">Farmer Registration</h4>
                    <p class="small mb-0 opacity-75">Join Eswatini's Smart Advisory Network</p>
                </div>
                <div class="card-body p-4 p-md-5">
                    <?php if ($error): ?>
                        <div class="alert alert-danger rounded-3"><i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    <?php if ($success): ?>
                        <div class="alert alert-success rounded-3 text-center py-4">
                            <i class="fas fa-check-circle fa-3x text-success mb-2"></i>
                            <h5 class="fw-bold mb-2"><?php echo htmlspecialchars($success); ?></h5>
                            <a href="<?php echo BASE_URL; ?>/login.php" class="btn btn-success px-4 rounded-pill mt-2">Click Here to Login</a>
                        </div>
                    <?php else: ?>
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="name" class="form-label fw-bold">Full Name *</label>
                                <input type="text" class="form-control" id="name" name="name" required placeholder="e.g. Sipho Dlamini" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                            </div>
                            <div class="mb-3">
                                <label for="phone" class="form-label fw-bold">Phone Number *</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white">+268</span>
                                    <input type="text" class="form-control" id="phone" name="phone" placeholder="76123456" required value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label fw-bold">Email Address (Optional)</label>
                                <input type="email" class="form-control" id="email" name="email" placeholder="sipho@example.com" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label fw-bold">Password *</label>
                                <input type="password" class="form-control" id="password" name="password" required placeholder="••••••••">
                            </div>
                            <div class="mb-4">
                                <label for="confirm_password" class="form-label fw-bold">Confirm Password *</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required placeholder="••••••••">
                            </div>
                            <button type="submit" class="btn btn-success btn-lg w-100 py-3 rounded-3 fw-bold">
                                <i class="fas fa-user-check me-2"></i>Create Farmer Account
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
                <div class="card-footer text-center py-3 bg-transparent border-top">
                    Already have an account? <a href="<?php echo BASE_URL; ?>/login.php" class="text-success fw-bold text-decoration-none">Login here</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
