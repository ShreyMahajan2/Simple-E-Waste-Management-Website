<?php
include('../includes/config.php');
session_start();

// Redirect to login if not authenticated
if(!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Get user statistics
$total_submissions = $conn->query("SELECT COUNT(*) as count FROM e_waste_submissions WHERE user_id = $user_id")->fetch_assoc()['count'];
$pending_submissions = $conn->query("SELECT COUNT(*) as count FROM e_waste_submissions WHERE user_id = $user_id AND status = 'pending'")->fetch_assoc()['count'];
$collected_submissions = $conn->query("SELECT COUNT(*) as count FROM e_waste_submissions WHERE user_id = $user_id AND status = 'collected'")->fetch_assoc()['count'];

// Get user's e-waste submissions
$submissions_sql = "SELECT es.*, cc.name as center_name 
                   FROM e_waste_submissions es 
                   LEFT JOIN collection_centers cc ON es.center_id = cc.id 
                   WHERE es.user_id = ? 
                   ORDER BY es.submission_date DESC";
$stmt = $conn->prepare($submissions_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$submissions = $stmt->get_result();

// Store submissions in array for modals
$submissions_data = [];
if ($submissions->num_rows > 0) {
    while ($row = $submissions->fetch_assoc()) {
        $submissions_data[] = $row;
    }
    // Reset pointer
    $submissions->data_seek(0);
}
?>

<?php include('../includes/header.php'); ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Welcome, <?php echo htmlspecialchars($username); ?>! 👋</h2>
    <a href="logout.php" class="btn btn-outline-danger">Logout</a>
</div>

<!-- User Statistics -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <h5>Total Submissions</h5>
                <h3><?php echo $total_submissions; ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-warning text-dark">
            <div class="card-body text-center">
                <h5>Pending</h5>
                <h3><?php echo $pending_submissions; ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <h5>Collected</h5>
                <h3><?php echo $collected_submissions; ?></h3>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Your E-Waste Submissions</h5>
                <a href="submit_waste.php" class="btn btn-success btn-sm">+ New Submission</a>
            </div>
            <div class="card-body">
                <?php if(count($submissions_data) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Device Type</th>
                                    <th>Condition</th>
                                    <th>Quantity</th>
                                    <th>Collection Center</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($submissions_data as $row): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($row['device_type']); ?></strong>
                                        <?php if(!empty($row['notes'])): ?>
                                            <br>
                                            <small class="text-muted" title="<?php echo htmlspecialchars($row['notes']); ?>">
                                                📝 Has notes
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark">
                                            <?php echo htmlspecialchars($row['device_condition'] ?: 'Not specified'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary rounded-pill">
                                            <?php echo $row['quantity']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['center_name'] ?? 'Not assigned'); ?></td>
                                    <td>
                                        <small>
                                            <?php echo date('M j, Y', strtotime($row['submission_date'])); ?><br>
                                            <span class="text-muted"><?php echo date('g:i A', strtotime($row['submission_date'])); ?></span>
                                        </small>
                                    </td>
                                    <td>
                                        <?php 
                                        $status_text = '';
                                        $status_class = '';
                                        switch($row['status']) {
                                            case 'confirmed': 
                                                $status_class = 'bg-warning text-dark';
                                                $status_text = 'Confirmed - Ready for collection';
                                                break;
                                            case 'collected': 
                                                $status_class = 'bg-success';
                                                $status_text = 'Completed - Successfully collected';
                                                break;
                                            default: 
                                                $status_class = 'bg-secondary';
                                                $status_text = 'Pending - Awaiting confirmation';
                                        }
                                        ?>
                                        <span class="badge <?php echo $status_class; ?>" title="<?php echo $status_text; ?>">
                                            <?php echo ucfirst($row['status']); ?>
                                        </span>
                                        <?php if($row['status'] == 'collected'): ?>
                                            <br><small class="text-success">✓ Completed</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-outline-info btn-sm" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#detailsModal"
                                                data-device-type="<?php echo htmlspecialchars($row['device_type']); ?>"
                                                data-device-condition="<?php echo htmlspecialchars($row['device_condition'] ?: 'Not specified'); ?>"
                                                data-quantity="<?php echo $row['quantity']; ?>"
                                                data-center-name="<?php echo htmlspecialchars($row['center_name'] ?? 'Not assigned'); ?>"
                                                data-submission-date="<?php echo date('M j, Y g:i A', strtotime($row['submission_date'])); ?>"
                                                data-status="<?php echo $row['status']; ?>"
                                                data-notes="<?php echo htmlspecialchars($row['notes'] ?? ''); ?>">
                                            👁️ View
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <p class="text-muted">No e-waste submissions yet.</p>
                        <a href="submit_waste.php" class="btn btn-success">Submit Your First E-Waste</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Single Dynamic Modal -->
<div class="modal fade" id="detailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Submission Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-6">
                        <h6 class="border-bottom pb-2">Device Information</h6>
                        <div class="mb-2">
                            <strong>Device Type:</strong> <span id="modal-device-type" class="fw-bold text-primary"></span>
                        </div>
                        <div class="mb-2">
                            <strong>Condition:</strong> <span id="modal-device-condition" class="badge bg-light text-dark"></span>
                        </div>
                        <div class="mb-2">
                            <strong>Quantity:</strong> <span id="modal-quantity" class="badge bg-primary rounded-pill"></span>
                        </div>
                    </div>
                    
                    <div class="col-6">
                        <h6 class="border-bottom pb-2">Collection Details</h6>
                        <div class="mb-2">
                            <strong>Collection Center:</strong> <span id="modal-center-name"></span>
                        </div>
                        <div class="mb-2">
                            <strong>Submission Date:</strong> <span id="modal-submission-date"></span>
                        </div>
                        <div class="mb-2">
                            <strong>Status:</strong> <span id="modal-status-badge"></span>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-3" id="notes-section" style="display: none;">
                    <div class="col-12">
                        <h6 class="border-bottom pb-2">Additional Notes</h6>
                        <div class="bg-light p-3 rounded">
                            <p id="modal-notes" class="mb-0"></p>
                        </div>
                    </div>
                </div>
                
                <!-- Status Explanation -->
                <div class="mt-3 p-3 bg-light rounded">
                    <h6>Status Information:</h6>
                    <ul class="mb-0 small">
                        <li><strong>Pending:</strong> Your submission is awaiting review by our team</li>
                        <li><strong>Confirmed:</strong> Your e-waste has been accepted and is ready for collection</li>
                        <li><strong>Collected:</strong> Your e-waste has been successfully collected and processed</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
// JavaScript to handle modal data
document.addEventListener('DOMContentLoaded', function() {
    const detailsModal = document.getElementById('detailsModal');
    
    detailsModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        
        // Extract info from data-* attributes
        const deviceType = button.getAttribute('data-device-type');
        const deviceCondition = button.getAttribute('data-device-condition');
        const quantity = button.getAttribute('data-quantity');
        const centerName = button.getAttribute('data-center-name');
        const submissionDate = button.getAttribute('data-submission-date');
        const status = button.getAttribute('data-status');
        const notes = button.getAttribute('data-notes');
        
        // Update modal content
        document.getElementById('modal-device-type').textContent = deviceType;
        document.getElementById('modal-device-condition').textContent = deviceCondition;
        document.getElementById('modal-quantity').textContent = quantity;
        document.getElementById('modal-center-name').textContent = centerName;
        document.getElementById('modal-submission-date').textContent = submissionDate;
        
        // Status badge
        let statusBadge = '';
        let statusText = '';
        switch(status) {
            case 'pending':
                statusBadge = '<span class="badge bg-secondary">Pending</span>';
                statusText = 'Pending - Awaiting confirmation';
                break;
            case 'confirmed':
                statusBadge = '<span class="badge bg-warning text-dark">Confirmed</span>';
                statusText = 'Confirmed - Ready for collection';
                break;
            case 'collected':
                statusBadge = '<span class="badge bg-success">Collected</span>';
                statusText = 'Completed - Successfully collected';
                break;
        }
        document.getElementById('modal-status-badge').innerHTML = statusBadge;
        
        // Notes section
        const notesSection = document.getElementById('notes-section');
        const modalNotes = document.getElementById('modal-notes');
        if (notes && notes.trim() !== '') {
            modalNotes.textContent = notes;
            notesSection.style.display = 'block';
        } else {
            notesSection.style.display = 'none';
        }
    });
});
</script>

<?php include('../includes/footer.php'); ?>