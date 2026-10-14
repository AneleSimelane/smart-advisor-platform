<?php
require_once __DIR__ . '/../config/core.php';
requireRole('farmer');

$page_title = "Farmers Information Center";
require_once __DIR__ . '/../includes/header.php';
?>

<style>
.info-nav-pills .nav-link {
    color: #334155;
    font-weight: 700;
    border-radius: 0.5rem;
    padding: 0.75rem 1.25rem;
    transition: all 0.2s ease;
}
.info-nav-pills .nav-link.active {
    background: linear-gradient(135deg, #10b981, #059669) !important;
    color: white !important;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}
.crop-card-item {
    border-radius: 0.75rem;
    transition: transform 0.25s ease, box-shadow 0.25s ease;
}
.crop-card-item:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.15) !important;
}
</style>

<div class="dashboard-wrapper">
    <?php require_once __DIR__ . '/../includes/farmer_sidebar.php'; ?>
    
    <div class="dashboard-content">
        <!-- Header Banner -->
        <div class="card shadow-sm border-0 mb-4" style="background: linear-gradient(135deg, rgba(6, 95, 70, 0.95), rgba(4, 47, 34, 0.98)), url('https://images.unsplash.com/photo-1500382017468-9049fed747ef?auto=format&fit=crop&w=1200&q=80') center/cover; color: white;">
            <div class="card-body p-4 p-md-5">
                <div class="d-flex align-items-center mb-2">
                    <div class="bg-white bg-opacity-20 rounded-circle p-3 me-3">
                        <i class="fas fa-graduation-cap fa-2x text-warning"></i>
                    </div>
                    <div>
                        <h2 class="fw-bold mb-1">Farmers' Information & Knowledge Center</h2>
                        <p class="mb-0 opacity-90">Official Eswatini Ministry of Agriculture Guidelines for conventional vegetable & crop production.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Real-Time Search Bar -->
        <div class="card shadow-sm mb-4">
            <div class="card-body p-3">
                <div class="input-group input-group-lg">
                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-success"></i></span>
                    <input type="text" id="infoSearchInput" class="form-control border-start-0" placeholder="Search any crop, disease, chemical dosage, or land prep tip (e.g., Tomato, LAN fertilizer, Early Blight)...">
                </div>
            </div>
        </div>

        <!-- Category Tabs -->
        <ul class="nav nav-pills info-nav-pills mb-4 gap-2 border-bottom pb-3" id="infoTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="crops-tab" data-bs-toggle="pill" data-bs-target="#crops-content" type="button" role="tab"><i class="fas fa-seedling me-2"></i>Crop Production Manuals</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="seasons-tab" data-bs-toggle="pill" data-bs-target="#seasons-content" type="button" role="tab"><i class="fas fa-calendar-check me-2"></i>Regional Seasons & Max Yield</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="pests-tab" data-bs-toggle="pill" data-bs-target="#pests-content" type="button" role="tab"><i class="fas fa-bug me-2"></i>Pest & Disease Control Table</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="soil-tab" data-bs-toggle="pill" data-bs-target="#soil-content" type="button" role="tab"><i class="fas fa-mountain me-2"></i>Soil & Fertilizer Masterclass</button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="infoTabContent">
            
            <!-- TAB 1: CROP PRODUCTION MANUALS -->
            <div class="tab-pane fade show active" id="crops-content" role="tabpanel">
                <div class="row g-4" id="cropManualsList">
                    
                    <!-- TOMATOES -->
                    <div class="col-md-6 search-item" data-keywords="tomatoes tomato early blight late blight solanum lycopersicum staking star 9001">
                        <div class="card h-100 shadow-sm crop-card-item">
                            <div class="card-header bg-danger bg-opacity-10 text-danger border-0 d-flex justify-content-between align-items-center">
                                <h5 class="fw-bold mb-0"><i class="fas fa-apple-alt me-2"></i>Tomatoes (Solanum lycopersicum)</h5>
                                <span class="badge bg-danger">90 - 120 Days</span>
                            </div>
                            <div class="card-body">
                                <p class="small text-muted mb-3"><strong>Optimum Temp:</strong> 18°C – 24°C | <strong>Soil pH:</strong> 5.5 – 6.5 (Well-drained sandy loam)</p>
                                
                                <h6 class="fw-bold text-dark mb-2"><i class="fas fa-calendar-alt text-success me-2"></i>Planting Calendar (Eswatini):</h6>
                                <ul class="small text-muted mb-3">
                                    <li><strong>Highveld:</strong> Best in Jan, Oct, Nov; Suitable in Feb, Aug, Dec.</li>
                                    <li><strong>Middleveld:</strong> Best in Feb, Mar, Aug, Sept, Oct; Suitable all year round.</li>
                                    <li><strong>Lowveld:</strong> Best in Mar, Apr, Jul, Aug; Avoid Dec–Jan heat waves.</li>
                                </ul>

                                <h6 class="fw-bold text-dark mb-2"><i class="fas fa-flask text-primary me-2"></i>Fertilizer Schedule:</h6>
                                <ul class="small text-muted mb-3">
                                    <li><strong>Basal:</strong> Apply 600 kg/ha (12 bags) 2:3:4 (39) at or 1-2 weeks before planting.</li>
                                    <li><strong>Top Dressing:</strong> Apply 100 kg/ha LAN + 50 kg/ha KCl 4 weeks after planting, repeat every 2-3 weeks.</li>
                                </ul>

                                <h6 class="fw-bold text-dark mb-2"><i class="fas fa-hand-holding-water text-info me-2"></i>Critical Care:</h6>
                                <p class="small text-muted mb-0">Staking must be done within 2 weeks of planting (poles 1.7–2m long) to prevent soil contact and rot.</p>
                            </div>
                        </div>
                    </div>

                    <!-- CABBAGE -->
                    <div class="col-md-6 search-item" data-keywords="cabbage brassica oleracea diamond back moth black rot lan top dressing conquistador star 3301">
                        <div class="card h-100 shadow-sm crop-card-item">
                            <div class="card-header bg-success bg-opacity-10 text-success border-0 d-flex justify-content-between align-items-center">
                                <h5 class="fw-bold mb-0"><i class="fas fa-box-tissue me-2"></i>Cabbage (Brassica oleracea)</h5>
                                <span class="badge bg-success">75 - 120 Days</span>
                            </div>
                            <div class="card-body">
                                <p class="small text-muted mb-3"><strong>Optimum Temp:</strong> 18°C – 24°C | <strong>Soil pH:</strong> 5.5 – 6.5 (Clay-loam preferred)</p>
                                
                                <h6 class="fw-bold text-dark mb-2"><i class="fas fa-calendar-alt text-success me-2"></i>Planting Calendar:</h6>
                                <ul class="small text-muted mb-3">
                                    <li><strong>Highveld & Middleveld:</strong> Suitable year-round. Best in Feb, Mar, Apr, Aug, Sept, Oct.</li>
                                    <li><strong>Lowveld:</strong> Avoid Dec–Jan heat waves. Best in Mar, Apr, May.</li>
                                </ul>

                                <h6 class="fw-bold text-dark mb-2"><i class="fas fa-flask text-primary me-2"></i>Fertilizer Schedule:</h6>
                                <ul class="small text-muted mb-3">
                                    <li><strong>Kraal Manure:</strong> 25–30 tons/ha applied 1 month before planting.</li>
                                    <li><strong>Basal:</strong> 600 kg/ha (12 bags) 2:3:2 (37) in bands mixed with soil.</li>
                                    <li><strong>Top Dressing:</strong> 5g LAN per plant at 4 WAP, repeat at 6 WAP.</li>
                                </ul>

                                <h6 class="fw-bold text-dark mb-2"><i class="fas fa-ruler-combined me-2"></i>Spacing & Density:</h6>
                                <p class="small text-muted mb-0">Row spacing 90cm, in-row spacing 40–60cm (24,000 to 27,000 plants/ha).</p>
                            </div>
                        </div>
                    </div>

                    <!-- POTATOES -->
                    <div class="col-md-6 search-item" data-keywords="potatoes potato solanum tuberosum earthing up tuber late blight mondial sifra">
                        <div class="card h-100 shadow-sm crop-card-item">
                            <div class="card-header bg-warning bg-opacity-10 text-warning border-0 d-flex justify-content-between align-items-center">
                                <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-drumstick-bite me-2"></i>Potatoes (Solanum tuberosum)</h5>
                                <span class="badge bg-warning text-dark">90 - 120 Days</span>
                            </div>
                            <div class="card-body">
                                <p class="small text-muted mb-3"><strong>Optimum Temp:</strong> 18°C – 20°C | <strong>Soil pH:</strong> 5.0 – 6.5 (Sandy loams)</p>
                                
                                <h6 class="fw-bold text-dark mb-2"><i class="fas fa-calendar-alt text-success me-2"></i>Planting Calendar:</h6>
                                <ul class="small text-muted mb-3">
                                    <li><strong>Highveld:</strong> Best in Feb, Mar, Aug, Sept.</li>
                                    <li><strong>Middleveld:</strong> Best in Mar, Apr, Aug, Sept.</li>
                                    <li><strong>Lowveld:</strong> Best in Apr, May, Jun.</li>
                                </ul>

                                <h6 class="fw-bold text-dark mb-2"><i class="fas fa-layer-group me-2"></i>Earthing Up & Vine Killing:</h6>
                                <ul class="small text-muted mb-0">
                                    <li>Earth up when plants reach 10–15cm height to prevent tuber greening.</li>
                                    <li>Kill vines 2–3 weeks before harvesting to set skin. Harvest when 95% of leaves dry off.</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- SPINACH -->
                    <div class="col-md-6 search-item" data-keywords="spinach spinacia oleracea swiss chard ford hook giant leaf miner cercospora">
                        <div class="card h-100 shadow-sm crop-card-item">
                            <div class="card-header bg-info bg-opacity-10 text-info border-0 d-flex justify-content-between align-items-center">
                                <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-leaf me-2"></i>Spinach / Swiss Chard (Spinacia oleracea)</h5>
                                <span class="badge bg-info text-dark">40 - 50 Days</span>
                            </div>
                            <div class="card-body">
                                <p class="small text-muted mb-3"><strong>Optimum Temp:</strong> 8°C – 24°C | <strong>Soil pH:</strong> 6.0 – 6.5 (Moist, high organic matter)</p>
                                
                                <h6 class="fw-bold text-dark mb-2"><i class="fas fa-calendar-alt text-success me-2"></i>Planting Calendar:</h6>
                                <ul class="small text-muted mb-3">
                                    <li><strong>Highveld & Middleveld:</strong> Best in May, Jun, Jul, Aug, Sept.</li>
                                    <li><strong>Lowveld:</strong> Best in Apr, May, Jun, Jul, Aug.</li>
                                </ul>

                                <h6 class="fw-bold text-dark mb-2"><i class="fas fa-cut me-2"></i>Harvesting Practice:</h6>
                                <p class="small text-muted mb-0">Start harvesting at 4–5 weeks. Cut outer leaves carefully without damaging growing points. Yield: ~15 tons/ha.</p>
                            </div>
                        </div>
                    </div>

                    <!-- CARROTS -->
                    <div class="col-md-6 search-item" data-keywords="carrots carrot daucus carota chantenay nantes thinning root knot">
                        <div class="card h-100 shadow-sm crop-card-item">
                            <div class="card-header bg-secondary bg-opacity-10 text-secondary border-0 d-flex justify-content-between align-items-center">
                                <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-carrot me-2"></i>Carrots (Daucus carota L.)</h5>
                                <span class="badge bg-secondary">70 - 100 Days</span>
                            </div>
                            <div class="card-body">
                                <p class="small text-muted mb-3"><strong>Optimum Temp:</strong> 15°C – 24°C | <strong>Soil pH:</strong> 5.5 – 6.5 (Deep 20-30cm loose soil)</p>
                                <p class="small text-muted mb-2"><strong>Thinning:</strong> Thin plants 3–4 weeks after germination on moist soil to achieve final 3cm plant spacing.</p>
                                <p class="small text-muted mb-0"><strong>Top Dressing:</strong> Apply 80 kg/ha LAN (28%) at 4–8 weeks post-germination. Avoid overwatering to prevent root splitting.</p>
                            </div>
                        </div>
                    </div>

                    <!-- BEETROOT -->
                    <div class="col-md-6 search-item" data-keywords="beetroot beta vulgaris globe dark red zoning Lan topdressing">
                        <div class="card h-100 shadow-sm crop-card-item">
                            <div class="card-header bg-danger bg-opacity-10 text-danger border-0 d-flex justify-content-between align-items-center">
                                <h5 class="fw-bold mb-0"><i class="fas fa-circle me-2"></i>Beetroot (Beta vulgaris)</h5>
                                <span class="badge bg-danger">60 - 90 Days</span>
                            </div>
                            <div class="card-body">
                                <p class="small text-muted mb-3"><strong>Optimum Temp:</strong> 15°C – 20°C | <strong>Soil pH:</strong> 5.5 – 6.5</p>
                                <p class="small text-muted mb-2"><strong>Land Prep:</strong> Add 15–25 tons/ha manure 30 days before planting. <em>NEVER use chicken manure</em> as it negatively affects shelf life.</p>
                                <p class="small text-muted mb-0"><strong>Top Dressing:</strong> Apply 100kg/ha LAN + 50kg KCl at the 3-leaf stage (~3 weeks after emergence).</p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- TAB 2: REGIONAL SEASONS & MAX YIELD MATRIX -->
            <div class="tab-pane fade" id="seasons-content" role="tabpanel">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="fw-bold text-success mb-1"><i class="fas fa-calendar-alt me-2"></i>Eswatini Regional Planting Seasons Matrix</h5>
                        <p class="small text-muted mb-0">Best planting months for maximum production across Eswatini's ecological zones (MoA Guidelines).</p>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered align-middle text-center mb-0">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Crop</th>
                                        <th>Ecological Zone</th>
                                        <th>Jan</th><th>Feb</th><th>Mar</th><th>Apr</th><th>May</th><th>Jun</th><th>Jul</th><th>Aug</th><th>Sep</th><th>Oct</th><th>Nov</th><th>Dec</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Tomatoes -->
                                    <tr class="table-danger bg-opacity-10">
                                        <td rowspan="3" class="fw-bold">Tomatoes</td>
                                        <td class="small fw-bold bg-light">Highveld</td>
                                        <td class="bg-success text-white">B</td><td class="bg-info text-white">S</td><td>N</td><td>N</td><td>N</td><td>N</td><td>N</td><td class="bg-info text-white">S</td><td class="bg-success text-white">B</td><td class="bg-success text-white">B</td><td class="bg-success text-white">B</td><td class="bg-info text-white">S</td>
                                    </tr>
                                    <tr class="table-danger bg-opacity-10">
                                        <td class="small fw-bold bg-light">Middleveld</td>
                                        <td class="bg-info text-white">S</td><td class="bg-success text-white">B</td><td class="bg-success text-white">B</td><td class="bg-info text-white">S</td><td class="bg-info text-white">S</td><td class="bg-info text-white">S</td><td class="bg-info text-white">S</td><td class="bg-success text-white">B</td><td class="bg-success text-white">B</td><td class="bg-success text-white">B</td><td class="bg-info text-white">S</td><td class="bg-info text-white">S</td>
                                    </tr>
                                    <tr class="table-danger bg-opacity-10 border-bottom">
                                        <td class="small fw-bold bg-light">Lowveld</td>
                                        <td>N</td><td class="bg-info text-white">S</td><td class="bg-success text-white">B</td><td class="bg-success text-white">B</td><td class="bg-info text-white">S</td><td class="bg-info text-white">S</td><td class="bg-success text-white">B</td><td class="bg-success text-white">B</td><td class="bg-info text-white">S</td><td>N</td><td>N</td><td>N</td>
                                    </tr>

                                    <!-- Cabbage -->
                                    <tr class="table-success bg-opacity-10">
                                        <td rowspan="3" class="fw-bold">Cabbage</td>
                                        <td class="small fw-bold bg-light">Highveld</td>
                                        <td class="bg-info text-white">S</td><td class="bg-success text-white">B</td><td class="bg-success text-white">B</td><td class="bg-success text-white">B</td><td class="bg-info text-white">S</td><td class="bg-info text-white">S</td><td class="bg-info text-white">S</td><td class="bg-success text-white">B</td><td class="bg-success text-white">B</td><td class="bg-success text-white">B</td><td class="bg-info text-white">S</td><td class="bg-info text-white">S</td>
                                    </tr>
                                    <tr class="table-success bg-opacity-10">
                                        <td class="small fw-bold bg-light">Middleveld</td>
                                        <td class="bg-info text-white">S</td><td class="bg-info text-white">S</td><td class="bg-success text-white">B</td><td class="bg-success text-white">B</td><td class="bg-info text-white">S</td><td class="bg-info text-white">S</td><td class="bg-info text-white">S</td><td class="bg-success text-white">B</td><td class="bg-success text-white">B</td><td class="bg-success text-white">B</td><td class="bg-info text-white">S</td><td class="bg-info text-white">S</td>
                                    </tr>
                                    <tr class="table-success bg-opacity-10 border-bottom">
                                        <td class="small fw-bold bg-light">Lowveld</td>
                                        <td>N</td><td class="bg-info text-white">S</td><td class="bg-info text-white">S</td><td class="bg-success text-white">B</td><td class="bg-success text-white">B</td><td class="bg-success text-white">B</td><td class="bg-info text-white">S</td><td class="bg-info text-white">S</td><td class="bg-info text-white">S</td><td class="bg-info text-white">S</td><td class="bg-info text-white">S</td><td>N</td>
                                    </tr>

                                    <!-- Potatoes -->
                                    <tr class="table-warning bg-opacity-10">
                                        <td rowspan="3" class="fw-bold">Potatoes</td>
                                        <td class="small fw-bold bg-light">Highveld</td>
                                        <td class="bg-info text-white">S</td><td class="bg-success text-white">B</td><td class="bg-success text-white">B</td><td>N</td><td>N</td><td>N</td><td>N</td><td class="bg-info text-white">S</td><td class="bg-info text-white">S</td><td>N</td><td>N</td><td>N</td>
                                    </tr>
                                    <tr class="table-warning bg-opacity-10">
                                        <td class="small fw-bold bg-light">Middleveld</td>
                                        <td>N</td><td class="bg-info text-white">S</td><td class="bg-success text-white">B</td><td class="bg-success text-white">B</td><td class="bg-info text-white">S</td><td>N</td><td class="bg-info text-white">S</td><td class="bg-success text-white">B</td><td class="bg-success text-white">B</td><td class="bg-info text-white">S</td><td>N</td><td>N</td>
                                    </tr>
                                    <tr class="table-warning bg-opacity-10">
                                        <td class="small fw-bold bg-light">Lowveld</td>
                                        <td>N</td><td>N</td><td class="bg-info text-white">S</td><td class="bg-success text-white">B</td><td class="bg-success text-white">B</td><td class="bg-success text-white">B</td><td class="bg-info text-white">S</td><td>N</td><td>N</td><td>N</td><td>N</td><td>N</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-light">
                        <div class="d-flex justify-content-center gap-4 small fw-bold">
                            <span><span class="badge bg-success me-1">B</span> Best Planting Time</span>
                            <span><span class="badge bg-info text-white me-1">S</span> Suitable Planting Time</span>
                            <span><span class="badge bg-secondary me-1">N</span> Not Suitable (Avoid)</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 3: PEST & DISEASE CONTROL TABLE -->
            <div class="tab-pane fade" id="pests-content" role="tabpanel">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold text-success"><i class="fas fa-shield-virus me-2"></i>Eswatini MoA Pest & Disease Control Guide</h5>
                        <small class="text-muted">Recommended chemicals, mixing rates per 20L knapsack sprayer, and mandatory withdrawal periods before harvest.</small>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped align-middle mb-0" id="pestsTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Target Pest / Disease</th>
                                        <th>Key Symptoms</th>
                                        <th>Recommended Chemical</th>
                                        <th>Dosage (per 20L Water)</th>
                                        <th>Withdrawal Period</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="fw-bold">Early Blight (Tomato/Potato)</td>
                                        <td class="small">Circular brown spots on lower leaves with target concentric rings</td>
                                        <td>Bravo / Amistar / Ridomil Gold</td>
                                        <td>20 ml (Bravo) / 50 g (Ridomil)</td>
                                        <td><span class="badge bg-warning text-dark">3 – 7 Days</span></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Late Blight (Tomato/Potato)</td>
                                        <td class="small">Blue-grey water soaked spots on leaves, greasy stem lesions</td>
                                        <td>Ridomil Gold / Mancozeb / Bravo</td>
                                        <td>50 g (Ridomil) / 40 g (Mancozeb)</td>
                                        <td><span class="badge bg-danger">7 Days</span></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Diamond Back Moth (Cabbage)</td>
                                        <td class="small">Larva feeds on lower leaf surface creating a "window-paning" effect</td>
                                        <td>Malasol / Warlock</td>
                                        <td>12 ml (Malasol) / 130 ml (Warlock)</td>
                                        <td><span class="badge bg-warning text-dark">3 – 7 Days</span></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Aphids (General Crops)</td>
                                        <td class="small">Clusters sucking sap on lower leaf surface, curled shoots</td>
                                        <td>Profenofos / Decis / Aphicide Plus</td>
                                        <td>20 ml (Profenofos) / 5 ml (Decis)</td>
                                        <td><span class="badge bg-success">2 – 4 Days</span></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Cutworm (Seedlings)</td>
                                        <td class="small">Cutting down young seedling stems at soil level at night</td>
                                        <td>Cypermethrin / Decis Forte / Bait</td>
                                        <td>20 ml (Cypermethrin) / 5 ml (Decis)</td>
                                        <td><span class="badge bg-secondary">Pre-Planting</span></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Downy Mildew (Onion/Cabbage)</td>
                                        <td class="small">Grey to purplish mounds on leaves, leaves collapse and turn yellow</td>
                                        <td>Dithane M45 / Copper Oxychloride</td>
                                        <td>40 g (Dithane) / 60 g (Copper)</td>
                                        <td><span class="badge bg-warning text-dark">3 – 14 Days</span></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">American Bollworm (Tomato/Pepper)</td>
                                        <td class="small">Larvae bore holes into fruits causing rot and fruit drop</td>
                                        <td>Decis Forte / Cypermethrin</td>
                                        <td>5 ml (Decis) / 20 ml (Cypermethrin)</td>
                                        <td><span class="badge bg-warning text-dark">3 – 7 Days</span></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Leaf Miner (Spinach/Tomato)</td>
                                        <td class="small">Larvae create white/yellow scribbly serpentine mines inside leaves</td>
                                        <td>Agromectin / Abamectin</td>
                                        <td>12 ml – 15 ml</td>
                                        <td><span class="badge bg-success">3 – 7 Days</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 3: SOIL & FERTILIZER MASTERCLASS -->
            <div class="tab-pane fade" id="soil-content" role="tabpanel">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="card h-100 shadow-sm">
                            <div class="card-header bg-white py-3">
                                <h5 class="fw-bold text-success mb-0"><i class="fas fa-flask me-2"></i>Basal vs. Top-Dressing Fertilization</h5>
                            </div>
                            <div class="card-body">
                                <h6 class="fw-bold text-dark">1. Basal Dressing (At Planting):</h6>
                                <p class="small text-muted mb-3">Basal fertilizers (e.g. 2:3:2 or 2:3:4) are high in Phosphorus to stimulate early root development. They should be thoroughly worked into the soil or applied in bands 1-2 weeks before or at planting.</p>
                                
                                <h6 class="fw-bold text-dark">2. Top-Dressing (Growth Stage):</h6>
                                <p class="small text-muted mb-0">Top-dressings (e.g. LAN 28% and KCl) provide Nitrogen and Potassium needed for rapid foliage development and fruit sizing. Always apply on moist soil 5-10cm away from plant stems, followed immediately by light watering.</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card h-100 shadow-sm">
                            <div class="card-header bg-white py-3">
                                <h5 class="fw-bold text-success mb-0"><i class="fas fa-vial me-2"></i>Soil pH & Manure Guidelines</h5>
                            </div>
                            <div class="card-body">
                                <h6 class="fw-bold text-dark">Ideal Soil pH:</h6>
                                <p class="small text-muted mb-3">Most vegetables thrive at pH 5.5 to 6.5. If pH falls below 5.3, apply agricultural lime at least 2-3 months prior to planting to neutralize acidity.</p>
                                
                                <h6 class="fw-bold text-dark">Kraal vs. Organic Manure:</h6>
                                <p class="small text-muted mb-0">Apply well-decomposed kraal manure at 15-25 tons/ha 30 days before planting. <em>Important:</em> Avoid unrotted manure or fresh chicken manure on root crops (like beetroot and carrots) as it reduces shelf life and causes forked roots.</p>
                            </div>
                        </div>
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
    const searchInput = document.getElementById('infoSearchInput');
    
    searchInput.addEventListener('keyup', function() {
        const query = this.value.toLowerCase().trim();
        
        // Filter Crop Cards
        const cropItems = document.querySelectorAll('.search-item');
        cropItems.forEach(item => {
            const keywords = item.getAttribute('data-keywords').toLowerCase();
            const textContent = item.innerText.toLowerCase();
            if (keywords.includes(query) || textContent.includes(query)) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });

        // Filter Table Rows
        const tableRows = document.querySelectorAll('#pestsTable tbody tr');
        tableRows.forEach(row => {
            const rowText = row.innerText.toLowerCase();
            if (rowText.includes(query)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
});
</script>
EOT;
require_once __DIR__ . '/../includes/footer.php'; 
?>
