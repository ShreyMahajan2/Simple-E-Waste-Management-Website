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

// Get statistics
$users_count = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$centers_count = $conn->query("SELECT COUNT(*) as count FROM collection_centers")->fetch_assoc()['count'];
$submissions_count = $conn->query("SELECT COUNT(*) as count FROM e_waste_submissions")->fetch_assoc()['count'];
$pending_count = $conn->query("SELECT COUNT(*) as count FROM e_waste_submissions WHERE status = 'pending'")->fetch_assoc()['count'];

// Get recent submissions
$recent_sql = "SELECT es.*, u.username, cc.name as center_name 
               FROM e_waste_submissions es 
               LEFT JOIN users u ON es.user_id = u.id 
               LEFT JOIN collection_centers cc ON es.center_id = cc.id 
               ORDER BY es.submission_date DESC 
               LIMIT 10";
$recent_submissions = $conn->query($recent_sql);
?>

<?php include('../includes/header.php'); ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>🔧 Admin Dashboard</h2>
    <div>
        <a href="submissions.php" class="btn btn-primary">Manage Submissions</a>
        <a href="logout.php" class="btn btn-outline-danger">Logout</a>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <h5>Total Users</h5>
                <h3><?php echo $users_count; ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <h5>Collection Centers</h5>
                <h3><?php echo $centers_count; ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-dark">
            <div class="card-body text-center">
                <h5>Total Submissions</h5>
                <h3><?php echo $submissions_count; ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-danger text-white">
            <div class="card-body text-center">
                <h5>Pending</h5>
                <h3><?php echo $pending_count; ?></h3>
            </div>
        </div>
    </div>
</div>

<!-- Recent Submissions -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Recent E-Waste Submissions</h5>
            </div>
            <div class="card-body">
                <?php if($recent_submissions->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Device Type</th>
                                    <th>Condition</th>
                                    <th>Quantity</th>
                                    <th>Center</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = $recent_submissions->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                                    <td><?php echo htmlspecialchars($row['device_type']); ?></td>
                                    <td><?php echo htmlspecialchars($row['device_condition']); ?></td>
                                    <td><?php echo $row['quantity']; ?></td>
                                    <td><?php echo htmlspecialchars($row['center_name'] ?? 'Not assigned'); ?></td>
                                    <td><?php echo date('M j, Y', strtotime($row['submission_date'])); ?></td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            switch($row['status']) {
                                                case 'confirmed': echo 'warning'; break;
                                                case 'collected': echo 'success'; break;
                                                default: echo 'secondary';
                                            }
                                        ?>">
                                            <?php echo ucfirst($row['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-center text-muted">No submissions yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include('../includes/footer.php'); ?>