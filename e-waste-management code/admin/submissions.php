<?php
// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

include('../includes/config.php');

$success = '';
$error = '';

// Handle status update
if (isset($_POST['update_status'])) {
    $submission_id = intval($_POST['submission_id']);
    $new_status = $_POST['status'];
    
    $update_sql = "UPDATE e_waste_submissions SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("si", $new_status, $submission_id);
    
    if ($stmt->execute()) {
        $success = "Status updated successfully!";
    } else {
        $error = "Failed to update status: " . $stmt->error;
    }
    $stmt->close();
}

// Handle delete submission
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $delete_sql = "DELETE FROM e_waste_submissions WHERE id = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("i", $delete_id);
    
    if ($stmt->execute()) {
        $success = "Submission deleted successfully!";
    } else {
        $error = "Failed to delete submission: " . $stmt->error;
    }
    $stmt->close();
}

// Get filter parameters
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$center_filter = isset($_GET['center_id']) ? intval($_GET['center_id']) : 0;

// Build filter conditions
$where_conditions = ["1=1"];
if (!empty($status_filter)) {
    $where_conditions[] = "es.status = '$status_filter'";
}
if ($center_filter > 0) {
    $where_conditions[] = "es.center_id = $center_filter";
}
$where_clause = implode(' AND ', $where_conditions);

// Get all submissions with user and center info
$submissions_sql = "SELECT 
    es.*, 
    u.username, 
    u.email, 
    u.phone as user_phone,
    cc.name as center_name,
    cc.city as center_city
    FROM e_waste_submissions es
    LEFT JOIN users u ON es.user_id = u.id
    LEFT JOIN collection_centers cc ON es.center_id = cc.id
    WHERE $where_clause
    ORDER BY es.submission_date DESC";

$submissions_result = $conn->query($submissions_sql);

// Get all centers for filter
$centers_sql = "SELECT id, name, city FROM collection_centers ORDER BY name";
$centers_result = $conn->query($centers_sql);

// Store submissions in array for modals
$submissions_data = [];
if ($submissions_result->num_rows > 0) {
    while ($submission = $submissions_result->fetch_assoc()) {
        $submissions_data[] = $submission;
    }
    // Reset pointer
    $submissions_result->data_seek(0);
}
?>

<?php include('../includes/header.php'); ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>📋 Manage E-Waste Submissions</h2>
    <div>
        <a href="dashboard.php" class="btn btn-outline-primary">Back to Dashboard</a>
    </div>
</div>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Filter Submissions</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="confirmed" <?php echo $status_filter == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                    <option value="collected" <?php echo $status_filter == 'collected' ? 'selected' : ''; ?>>Collected</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Collection Center</label>
                <select name="center_id" class="form-select">
                    <option value="0">All Centers</option>
                    <?php 
                    $centers_result->data_seek(0);
                    while ($center = $centers_result->fetch_assoc()): ?>
                        <option value="<?php echo $center['id']; ?>" 
                            <?php echo $center_filter == $center['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($center['name'] . ' - ' . $center['city']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-success w-100">Apply Filters</button>
                <a href="submissions.php" class="btn btn-outline-secondary ms-2">Clear</a>
            </div>
        </form>
    </div>
</div>

<!-- Submissions Table -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">E-Waste Submissions (<?php echo count($submissions_data); ?> found)</h5>
    </div>
    <div class="card-body">
        <?php if (count($submissions_data) > 0): ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>User</th>
                            <th>Device Type</th>
                            <th>Condition</th>
                            <th>Quantity</th>
                            <th>Collection Center</th>
                            <th>Submission Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($submissions_data as $submission): ?>
                        <tr>
                            <td>
                                <div>
                                    <strong><?php echo htmlspecialchars($submission['username']); ?></strong>
                                </div>
                                <div>
                                    <small class="text-muted"><?php echo htmlspecialchars($submission['email']); ?></small>
                                </div>
                                <div>
                                    <small class="text-muted"><?php echo htmlspecialchars($submission['user_phone'] ?? 'N/A'); ?></small>
                                </div>
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($submission['device_type']); ?></strong>
                                <?php if (!empty($submission['notes'])): ?>
                                    <br>
                                    <small class="text-muted" title="<?php echo htmlspecialchars($submission['notes']); ?>">
                                        📝 Has notes
                                    </small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">
                                    <?php echo htmlspecialchars($submission['device_condition'] ?: 'Not specified'); ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-primary rounded-pill">
                                    <?php echo $submission['quantity']; ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($submission['center_name']): ?>
                                    <div>
                                        <strong><?php echo htmlspecialchars($submission['center_name']); ?></strong>
                                    </div>
                                    <div>
                                        <small class="text-muted"><?php echo htmlspecialchars($submission['center_city']); ?></small>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted fst-italic">Not assigned</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <small>
                                    <?php echo date('M j, Y', strtotime($submission['submission_date'])); ?><br>
                                    <span class="text-muted"><?php echo date('g:i A', strtotime($submission['submission_date'])); ?></span>
                                </small>
                            </td>
                            <td>
                                <form method="POST" action="" class="d-inline">
                                    <input type="hidden" name="submission_id" value="<?php echo $submission['id']; ?>">
                                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()" style="min-width: 120px;">
                                        <option value="pending" <?php echo $submission['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="confirmed" <?php echo $submission['status'] == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                        <option value="collected" <?php echo $submission['status'] == 'collected' ? 'selected' : ''; ?>>Collected</option>
                                    </select>
                                    <input type="hidden" name="update_status" value="1">
                                </form>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <button type="button" class="btn btn-outline-info" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#detailsModal"
                                            data-submission-id="<?php echo $submission['id']; ?>"
                                            data-username="<?php echo htmlspecialchars($submission['username']); ?>"
                                            data-email="<?php echo htmlspecialchars($submission['email']); ?>"
                                            data-phone="<?php echo htmlspecialchars($submission['user_phone'] ?? 'N/A'); ?>"
                                            data-device-type="<?php echo htmlspecialchars($submission['device_type']); ?>"
                                            data-device-condition="<?php echo htmlspecialchars($submission['device_condition'] ?: 'Not specified'); ?>"
                                            data-quantity="<?php echo $submission['quantity']; ?>"
                                            data-center-name="<?php echo htmlspecialchars($submission['center_name'] ?? 'Not assigned'); ?>"
                                            data-center-city="<?php echo htmlspecialchars($submission['center_city'] ?? ''); ?>"
                                            data-submission-date="<?php echo date('M j, Y g:i A', strtotime($submission['submission_date'])); ?>"
                                            data-status="<?php echo $submission['status']; ?>"
                                            data-notes="<?php echo htmlspecialchars($submission['notes'] ?? ''); ?>">
                                        👁️ View
                                    </button>
                                    <a href="?delete_id=<?php echo $submission['id']; ?>" class="btn btn-outline-danger" 
                                       onclick="return confirm('Are you sure you want to delete this submission? This action cannot be undone.')">
                                        🗑️ Delete
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <div class="text-muted mb-3">
                    <i class="fas fa-inbox fa-3x"></i>
                </div>
                <h5>No submissions found</h5>
                <p class="text-muted">No submissions match the selected filters.</p>
                <a href="submissions.php" class="btn btn-primary">View All Submissions</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Single Dynamic Modal -->
<div class="modal fade" id="detailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Submission Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="border-bottom pb-2">User Information</h6>
                        <div class="mb-2">
                            <strong>Username:</strong> <span id="modal-username" class="text-primary"></span>
                        </div>
                        <div class="mb-2">
                            <strong>Email:</strong> <span id="modal-email"></span>
                        </div>
                        <div class="mb-2">
                            <strong>Phone:</strong> <span id="modal-phone"></span>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <h6 class="border-bottom pb-2">Submission Details</h6>
                        <div class="mb-2">
                            <strong>Submission ID:</strong> <span id="modal-submission-id" class="text-muted"></span>
                        </div>
                        <div class="mb-2">
                            <strong>Submission Date:</strong> <span id="modal-submission-date"></span>
                        </div>
                        <div class="mb-2">
                            <strong>Status:</strong> <span id="modal-status-badge"></span>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-3">
                    <div class="col-md-6">
                        <h6 class="border-bottom pb-2">Device Information</h6>
                        <div class="mb-2">
                            <strong>Device Type:</strong> <span id="modal-device-type" class="fw-bold"></span>
                        </div>
                        <div class="mb-2">
                            <strong>Condition:</strong> <span id="modal-device-condition" class="badge bg-light text-dark"></span>
                        </div>
                        <div class="mb-2">
                            <strong>Quantity:</strong> <span id="modal-quantity" class="badge bg-primary rounded-pill"></span>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <h6 class="border-bottom pb-2">Collection Information</h6>
                        <div class="mb-2">
                            <strong>Collection Center:</strong> <span id="modal-center-name"></span>
                        </div>
                        <div class="mb-2">
                            <strong>City:</strong> <span id="modal-center-city"></span>
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
        const submissionId = button.getAttribute('data-submission-id');
        const username = button.getAttribute('data-username');
        const email = button.getAttribute('data-email');
        const phone = button.getAttribute('data-phone');
        const deviceType = button.getAttribute('data-device-type');
        const deviceCondition = button.getAttribute('data-device-condition');
        const quantity = button.getAttribute('data-quantity');
        const centerName = button.getAttribute('data-center-name');
        const centerCity = button.getAttribute('data-center-city');
        const submissionDate = button.getAttribute('data-submission-date');
        const status = button.getAttribute('data-status');
        const notes = button.getAttribute('data-notes');
        
        // Update modal content
        document.getElementById('modal-submission-id').textContent = submissionId;
        document.getElementById('modal-username').textContent = username;
        document.getElementById('modal-email').textContent = email;
        document.getElementById('modal-phone').textContent = phone;
        document.getElementById('modal-device-type').textContent = deviceType;
        document.getElementById('modal-device-condition').textContent = deviceCondition;
        document.getElementById('modal-quantity').textContent = quantity;
        document.getElementById('modal-center-name').textContent = centerName;
        document.getElementById('modal-center-city').textContent = centerCity;
        document.getElementById('modal-submission-date').textContent = submissionDate;
        
        // Status badge
        let statusBadge = '';
        switch(status) {
            case 'pending':
                statusBadge = '<span class="badge bg-secondary">Pending</span>';
                break;
            case 'confirmed':
                statusBadge = '<span class="badge bg-warning text-dark">Confirmed</span>';
                break;
            case 'collected':
                statusBadge = '<span class="badge bg-success">Collected</span>';
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

<?php include('../includes/header.php'); ?>