<?php
require_once __DIR__ . '/../config/core.php';
requireRole('farmer');

$user_id = $_SESSION['user_id'];

// Fetch user's active crops to generate recommended activities
try {
    $query = "SELECT c.id, c.planted_date, ct.name as crop_name, ct.typical_duration_days, f.name as farm_name 
              FROM crops c 
              JOIN crop_types ct ON c.crop_type_id = ct.id 
              JOIN farms f ON c.farm_id = f.id 
              WHERE f.user_id = ? AND c.status = 'active'";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$user_id]);
    $crops = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Database error.");
}

// Load knowledge base
$crop_knowledge = require __DIR__ . '/../config/crop_knowledge.php';

// Generate calendar events dynamically based on planting date
$events = [];

foreach ($crops as $crop) {
    $planted_date = new DateTime($crop['planted_date']);
    
    // 1. Planting Date Event
    $events[] = [
        'title' => 'Planted ' . $crop['crop_name'],
        'start' => $planted_date->format('Y-m-d'),
        'color' => '#20c997', // teal
        'description' => 'Crop planted at ' . $crop['farm_name']
    ];

    // Check if we have specific knowledge for this crop
    if (isset($crop_knowledge[$crop['crop_name']])) {
        $knowledge = $crop_knowledge[$crop['crop_name']];
        
        // Add specific activities from the Eswatini Guidelines
        foreach ($knowledge['activities'] as $activity) {
            $act_date = clone $planted_date;
            $act_date->modify('+' . $activity['day'] . ' days');
            
            $events[] = [
                'title' => $activity['title'] . ' (' . $crop['crop_name'] . ')',
                'start' => $act_date->format('Y-m-d'),
                'color' => $activity['color'],
                'description' => $activity['description']
            ];
        }
    } else {
        // Fallback for crops not fully defined in knowledge base yet
        $weeding1 = clone $planted_date;
        $weeding1->modify('+14 days');
        $events[] = [
            'title' => 'Recommended: Weeding (' . $crop['crop_name'] . ')',
            'start' => $weeding1->format('Y-m-d'),
            'color' => '#ffc107',
            'description' => 'Remove weeds early to reduce competition for nutrients.'
        ];

        $fert1 = clone $planted_date;
        $fert1->modify('+21 days');
        $events[] = [
            'title' => 'Recommended: Fertilizer (' . $crop['crop_name'] . ')',
            'start' => $fert1->format('Y-m-d'),
            'color' => '#0d6efd',
            'description' => 'Apply top-dressing fertilizer to boost growth.'
        ];
        
        $duration = $crop['typical_duration_days'] ?: 90;
        $harvest = clone $planted_date;
        $harvest->modify('+' . $duration . ' days');
        $events[] = [
            'title' => 'Expected Harvest: ' . $crop['crop_name'],
            'start' => $harvest->format('Y-m-d'),
            'color' => '#dc3545',
            'description' => 'Estimated time to begin harvesting from ' . $crop['farm_name']
        ];
    }
}

$events_json = json_encode($events);

$page_title = "Farming Calendar";
require_once __DIR__ . '/../includes/header.php';
?>

<!-- FullCalendar CSS -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">

<div class="dashboard-wrapper">
    <?php require_once __DIR__ . '/../includes/farmer_sidebar.php'; ?>
    
    <div class="dashboard-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="far fa-calendar-alt text-primary me-2"></i>Farming Calendar</h2>
            <button class="btn btn-outline-primary btn-sm" onclick="alert('In a full version, this would allow you to add custom tasks.')">
                <i class="fas fa-plus"></i> Add Custom Task
            </button>
        </div>
        <p class="text-muted mb-4">This calendar automatically generates recommended days for vital farming activities (like weeding, applying fertilizer, and mulching) based on your active crops' planting dates.</p>

        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div id="calendar"></div>
            </div>
        </div>
        
        <div class="card shadow-sm border-0 bg-light">
            <div class="card-body">
                <h6 class="fw-bold mb-3">Legend</h6>
                <div class="d-flex flex-wrap gap-3">
                    <span class="badge bg-success" style="background-color: #20c997 !important;"><i class="fas fa-seedling me-1"></i> Planting</span>
                    <span class="badge bg-warning text-dark"><i class="fas fa-hand-sparkles me-1"></i> Weeding</span>
                    <span class="badge bg-primary"><i class="fas fa-flask me-1"></i> Fertilizer</span>
                    <span class="badge bg-secondary"><i class="fas fa-leaf me-1"></i> Mulching</span>
                    <span class="badge bg-danger"><i class="fas fa-tractor me-1"></i> Harvest</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Event Details Modal -->
<div class="modal fade" id="eventModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="eventTitle">Activity Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p><strong>Date:</strong> <span id="eventDate"></span></p>
                <p id="eventDescription" class="mb-0 text-muted"></p>
            </div>
            <div class="modal-footer border-top-0 pt-0">
                <button type="button" class="btn btn-outline-success btn-sm w-100">Mark as Completed</button>
            </div>
        </div>
    </div>
</div>

<?php 
$extra_js = "
<!-- FullCalendar JS -->
<script src=\"https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js\"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var eventModal = new bootstrap.Modal(document.getElementById('eventModal'));
    
    var eventsData = " . $events_json . ";

    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,listWeek'
        },
        events: eventsData,
        eventClick: function(info) {
            document.getElementById('eventTitle').innerText = info.event.title;
            
            // Format date nicely
            const d = info.event.start;
            document.getElementById('eventDate').innerText = d.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
            
            document.getElementById('eventDescription').innerText = info.event.extendedProps.description || 'No additional details provided.';
            
            eventModal.show();
        },
        height: 600
    });

    calendar.render();
});
</script>
";
require_once __DIR__ . '/../includes/footer.php'; 
?>
