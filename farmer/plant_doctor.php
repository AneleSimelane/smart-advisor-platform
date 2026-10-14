<?php
require_once __DIR__ . '/../config/core.php';
requireRole('farmer');

$user_id = $_SESSION['user_id'];

// Get farmer's crops for the dropdown
try {
    $stmt = $pdo->prepare("SELECT c.id, ct.name as crop_name, f.name as farm_name FROM crops c JOIN crop_types ct ON c.crop_type_id = ct.id JOIN farms f ON c.farm_id = f.id WHERE f.user_id = ? AND c.status = 'active'");
    $stmt->execute([$user_id]);
    $crops = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Database error.");
}

$page_title = "Plant Doctor AI";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="dashboard-wrapper">
    <?php require_once __DIR__ . '/../includes/farmer_sidebar.php'; ?>
    
    <div class="dashboard-content">
        <h2 class="mb-4"><i class="fas fa-leaf text-success me-2"></i>Plant Doctor AI</h2>
        <p class="text-muted mb-4">Upload a clear photo of your plant's diseased leaves, and our AI model will diagnose the problem and provide treatment recommendations.</p>

        <div class="row">
            <div class="col-lg-5 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">New Diagnosis</h5>
                    </div>
                    <div class="card-body">
                        <form id="diagnosisForm">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Select Affected Crop (Optional)</label>
                                <select class="form-select" id="crop_id" name="crop_id">
                                    <option value="">-- General Diagnosis --</option>
                                    <?php foreach ($crops as $crop): ?>
                                        <option value="<?php echo $crop['id']; ?>"><?php echo htmlspecialchars($crop['crop_name'] . ' at ' . $crop['farm_name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Upload Plant Image</label>
                                <div class="border rounded p-3 text-center bg-light" id="uploadArea" style="cursor: pointer;" onclick="document.getElementById('plantImage').click()">
                                    <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-2"></i>
                                    <p class="mb-0 text-muted">Click to select an image</p>
                                    <input type="file" id="plantImage" name="image" class="d-none" accept="image/*">
                                </div>
                                <div class="text-center mt-3">
                                    <img id="imagePreview" src="" alt="Preview" class="plant-doctor-preview img-fluid shadow-sm">
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-success w-100" id="analyzeBtn" disabled>
                                <i class="fas fa-search me-1"></i> Analyze Image
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-7 mb-4">
                <div class="card shadow-sm h-100" id="resultsCard" style="display: none;">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-microscope me-2"></i>Analysis Results</h5>
                    </div>
                    <div class="card-body" id="resultsContent">
                        <!-- Populated by JS -->
                    </div>
                    <div class="card-footer bg-white border-top-0 pt-0 text-end">
                        <button class="btn btn-outline-primary" id="saveDiagnosisBtn" style="display:none;">Save to History</button>
                    </div>
                </div>
                
                <div class="card shadow-sm h-100" id="waitingCard">
                    <div class="card-body d-flex flex-column align-items-center justify-content-center text-center text-muted p-5">
                        <i class="fas fa-robot fa-4x mb-3 opacity-25"></i>
                        <h5>Waiting for image...</h5>
                        <p>Upload a picture of a diseased leaf to see the AI diagnosis here.</p>
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
    const fileInput = document.getElementById('plantImage');
    const imagePreview = document.getElementById('imagePreview');
    const analyzeBtn = document.getElementById('analyzeBtn');
    const form = document.getElementById('diagnosisForm');
    
    // Handle image preview
    fileInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                imagePreview.src = e.target.result;
                imagePreview.style.display = 'inline-block';
                analyzeBtn.disabled = false;
            }
            reader.readAsDataURL(this.files[0]);
        }
    });
    
    // Handle form submission via AJAX to Python Microservice
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (!fileInput.files[0]) return;
        
        // Show loading state
        analyzeBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Analyzing...';
        analyzeBtn.disabled = true;
        
        const formData = new FormData();
        formData.append('image', fileInput.files[0]);
        formData.append('crop_id', document.getElementById('crop_id').value);
        
        // Assuming Python Flask API is running on localhost:5000
        fetch('http://localhost:5000/predict', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('AI Service is currently offline or unreachable. Ensure the Python API is running.');
            }
            return response.json();
        })
        .then(data => {
            document.getElementById('waitingCard').style.display = 'none';
            const resultsCard = document.getElementById('resultsCard');
            resultsCard.style.display = 'flex';
            resultsCard.style.flexDirection = 'column';
            
            if (data.success === false) {
                // Handle irrelevant picture rejection
                document.getElementById('resultsContent').innerHTML = `
                    <div class='alert alert-warning text-center mb-4'>
                        <i class='fas fa-exclamation-circle fa-2x mb-2'></i>
                        <h4 class='fw-bold mb-2'>${data.prediction}</h4>
                        <p class='mb-0'>${data.error}</p>
                    </div>
                `;
            } else {
                let alertClass = 'alert-danger';
                if (data.prediction.toLowerCase() === 'healthy') alertClass = 'alert-success';
                
                document.getElementById('resultsContent').innerHTML = `
                    <div class='alert ${alertClass} mb-4 text-center'>
                        <h3 class='fw-bold mb-0'>${data.prediction}</h3>
                        <p class='mb-0 mt-2'>Confidence Score: <strong>${data.confidence}%</strong></p>
                    </div>
                    
                    <h6 class='fw-bold text-muted text-uppercase mb-2'>Observed Symptoms</h6>
                    <p class='mb-4'>${data.symptoms}</p>
                    
                    <h6 class='fw-bold text-muted text-uppercase mb-2'>Recommended Action</h6>
                    <div class='p-3 bg-light border rounded border-start border-4 border-primary'>
                        ${data.treatment}
                    </div>
                `;
            }
        })
        .catch(error => {
            document.getElementById('waitingCard').style.display = 'none';
            document.getElementById('resultsCard').style.display = 'flex';
            document.getElementById('resultsContent').innerHTML = `
                <div class='alert alert-warning'>
                    <i class='fas fa-exclamation-triangle me-2'></i> ${error.message}
                </div>
                <p class='text-muted small'>For the demonstration, please start the Python Flask server located in the <code>/ai</code> folder.</p>
            `;
        })
        .finally(() => {
            analyzeBtn.innerHTML = '<i class="fas fa-search me-1"></i> Analyze Image';
            analyzeBtn.disabled = false;
        });
    });
});
</script>
EOT;
require_once __DIR__ . '/../includes/footer.php'; 
?>
