<?php
require_once __DIR__ . '/../config/core.php';
requireRole('farmer');

// Fetch services from DB
try {
    $stmt = $pdo->prepare("SELECT * FROM agricultural_services");
    $stmt->execute();
    $services = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Database error.");
}

$page_title = "Nearby Services";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="dashboard-wrapper">
    <?php require_once __DIR__ . '/../includes/farmer_sidebar.php'; ?>
    
    <div class="dashboard-content">
        <h2 class="mb-4"><i class="fas fa-map-marker-alt text-danger me-2"></i>Nearby Agricultural Services</h2>
        <p class="text-muted mb-4">Find agro-dealers, veterinary services, and government agricultural offices near you in Eswatini.</p>

        <div class="card shadow-sm mb-4">
            <div class="card-body p-0">
                <div id="servicesMap" style="height: 500px; width: 100%; border-radius: 0.5rem;"></div>
            </div>
        </div>
        
        <div class="row">
            <?php if (empty($services)): ?>
                <div class="col-12 text-center py-4">
                    <p class="text-muted">No agricultural services found in the database.</p>
                </div>
            <?php else: ?>
                <?php foreach ($services as $srv): 
                    $icon = 'fa-store';
                    if ($srv['type'] == 'veterinary') $icon = 'fa-clinic-medical text-danger';
                    elseif ($srv['type'] == 'office') $icon = 'fa-building text-primary';
                ?>
                <div class="col-md-4 mb-3">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title fw-bold"><i class="fas <?php echo $icon; ?> me-2"></i><?php echo htmlspecialchars($srv['name']); ?></h5>
                            <h6 class="card-subtitle mb-2 text-muted text-capitalize"><?php echo str_replace('_', ' ', $srv['type']); ?></h6>
                            <p class="card-text small mb-0 mt-3"><i class="fas fa-phone-alt me-2 text-muted"></i><?php echo htmlspecialchars($srv['contact_info']); ?></p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php 
// Convert PHP array to JS array for the map
$js_services = json_encode($services);

$extra_js = "
<script>
document.addEventListener('DOMContentLoaded', function() {
    const services = $js_services;
    
    // Default center Eswatini
    const map = L.map('servicesMap').setView([-26.3054, 31.1367], 9);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    // Add markers
    if (services.length > 0) {
        services.forEach(function(srv) {
            if (srv.latitude && srv.longitude) {
                const marker = L.marker([srv.latitude, srv.longitude]).addTo(map);
                
                let iconClass = 'fa-store';
                if (srv.type === 'veterinary') iconClass = 'fa-clinic-medical text-danger';
                else if (srv.type === 'office') iconClass = 'fa-building text-primary';
                
                const popupContent = `
                    <div class='text-center'>
                        <i class='fas \${iconClass} fa-2x mb-2'></i>
                        <h6 class='fw-bold mb-1'>\${srv.name}</h6>
                        <span class='badge bg-secondary mb-2'>\${srv.type.replace('_', ' ')}</span>
                        <p class='small mb-0'>\${srv.contact_info}</p>
                    </div>
                `;
                marker.bindPopup(popupContent);
            }
        });
    } else {
        // Add some dummy markers for Eswatini if DB is empty for demo purposes
        L.marker([-26.3194, 31.1444]).addTo(map).bindPopup('<b>Eswatini Ministry of Agriculture</b><br>Mbabane');
        L.marker([-26.4939, 31.3719]).addTo(map).bindPopup('<b>Manzini Agro-Dealers</b><br>Seeds & Fertilizer');
        L.marker([-27.1158, 31.1983]).addTo(map).bindPopup('<b>Nhlangano Vet Services</b><br>Livestock health');
    }
});
</script>
";
require_once __DIR__ . '/../includes/footer.php'; 
?>
