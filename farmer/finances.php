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
} catch (PDOException $e) {
    die("Database error.");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $farm_id = $_POST['farm_id'];
    $type = $_POST['type'];
    $category = $_POST['category'];
    $amount = $_POST['amount'];
    $record_date = $_POST['record_date'];
    $description = $_POST['description'];

    if (empty($farm_id) || empty($type) || empty($category) || empty($amount) || empty($record_date)) {
        $error = "Please fill all required fields.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO financial_records (farm_id, type, category, amount_szl, description, record_date) VALUES (?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$farm_id, $type, $category, $amount, $description, $record_date])) {
                $success = "Financial record added successfully.";
            } else {
                $error = "Failed to add record.";
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}

// Fetch financials
try {
    $stmt = $pdo->prepare("
        SELECT fr.*, f.name as farm_name 
        FROM financial_records fr 
        JOIN farms f ON fr.farm_id = f.id 
        WHERE f.user_id = ? 
        ORDER BY fr.record_date DESC
    ");
    $stmt->execute([$user_id]);
    $records = $stmt->fetchAll();
    
    // Totals
    $total_income = 0;
    $total_expense = 0;
    foreach ($records as $r) {
        if ($r['type'] === 'income') $total_income += $r['amount_szl'];
        else $total_expense += $r['amount_szl'];
    }
    $profit_loss = $total_income - $total_expense;
} catch (PDOException $e) {
    die("Database error.");
}

$page_title = "Financial Management";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="dashboard-wrapper">
    <?php require_once __DIR__ . '/../includes/farmer_sidebar.php'; ?>
    
    <div class="dashboard-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Financial Management</h2>
            <?php if (!empty($farms)): ?>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addRecordModal">
                <i class="fas fa-plus me-1"></i> Add Record
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

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <div class="card shadow-sm h-100 border-start border-4 border-success">
                    <div class="card-body">
                        <h6 class="text-muted text-uppercase mb-2">Total Income</h6>
                        <h3 class="mb-0 fw-bold text-success"><?php echo formatSZL($total_income); ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card shadow-sm h-100 border-start border-4 border-danger">
                    <div class="card-body">
                        <h6 class="text-muted text-uppercase mb-2">Total Expenses</h6>
                        <h3 class="mb-0 fw-bold text-danger"><?php echo formatSZL($total_expense); ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card shadow-sm h-100 border-start border-4 <?php echo $profit_loss >= 0 ? 'border-primary' : 'border-warning'; ?>">
                    <div class="card-body">
                        <h6 class="text-muted text-uppercase mb-2">Net Profit / Loss</h6>
                        <h3 class="mb-0 fw-bold <?php echo $profit_loss >= 0 ? 'text-primary' : 'text-warning'; ?>"><?php echo formatSZL($profit_loss); ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Transaction History</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Farm</th>
                                <th>Type</th>
                                <th>Category</th>
                                <th>Description</th>
                                <th class="text-end">Amount (SZL)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($records)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">No financial records found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($records as $r): ?>
                                    <tr>
                                        <td><?php echo date('M d, Y', strtotime($r['record_date'])); ?></td>
                                        <td><?php echo htmlspecialchars($r['farm_name']); ?></td>
                                        <td>
                                            <?php if ($r['type'] === 'income'): ?>
                                                <span class="badge bg-success">Income</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Expense</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($r['category']); ?></td>
                                        <td class="text-truncate" style="max-width: 200px;"><?php echo htmlspecialchars($r['description']); ?></td>
                                        <td class="text-end fw-bold <?php echo $r['type'] === 'income' ? 'text-success' : 'text-danger'; ?>">
                                            <?php echo ($r['type'] === 'income' ? '+' : '-') . formatSZL($r['amount_szl']); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Record Modal -->
<?php if (!empty($farms)): ?>
<div class="modal fade" id="addRecordModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="">
                <input type="hidden" name="action" value="add">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Add Financial Record</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Type</label>
                            <select class="form-select" name="type" required id="recordType">
                                <option value="expense">Expense</option>
                                <option value="income">Income</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Date</label>
                            <input type="date" class="form-control" name="record_date" required value="<?php echo date('Y-m-d'); ?>" max="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Farm</label>
                        <select class="form-select" name="farm_id" required>
                            <option value="">Select farm...</option>
                            <?php foreach ($farms as $farm): ?>
                                <option value="<?php echo $farm['id']; ?>"><?php echo htmlspecialchars($farm['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select class="form-select" name="category" required id="categorySelect">
                            <optgroup label="Expenses">
                                <option value="Seeds">Seeds</option>
                                <option value="Fertilizer">Fertilizer</option>
                                <option value="Pesticides">Pesticides</option>
                                <option value="Labour">Labour</option>
                                <option value="Transport">Transport</option>
                                <option value="Equipment">Equipment</option>
                                <option value="Other Expense">Other Expense</option>
                            </optgroup>
                            <optgroup label="Income" style="display:none;" id="incomeCategories">
                                <option value="Crop Sales">Crop Sales</option>
                                <option value="Subsidies">Subsidies</option>
                                <option value="Other Income">Other Income</option>
                            </optgroup>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Amount (SZL)</label>
                        <input type="number" step="0.01" min="0" class="form-control" name="amount" required placeholder="0.00">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description (Optional)</label>
                        <textarea class="form-control" name="description" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Save Record</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php 
$extra_js = "
<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('recordType');
    const categorySelect = document.getElementById('categorySelect');
    if (typeSelect) {
        typeSelect.addEventListener('change', function() {
            if (this.value === 'income') {
                categorySelect.innerHTML = `
                    <option value=\"Crop Sales\">Crop Sales</option>
                    <option value=\"Subsidies\">Subsidies</option>
                    <option value=\"Other Income\">Other Income</option>
                `;
            } else {
                categorySelect.innerHTML = `
                    <option value=\"Seeds\">Seeds</option>
                    <option value=\"Fertilizer\">Fertilizer</option>
                    <option value=\"Pesticides\">Pesticides</option>
                    <option value=\"Labour\">Labour</option>
                    <option value=\"Transport\">Transport</option>
                    <option value=\"Equipment\">Equipment</option>
                    <option value=\"Other Expense\">Other Expense</option>
                `;
            }
        });
    }
});
</script>
";
require_once __DIR__ . '/../includes/footer.php'; 
?>
