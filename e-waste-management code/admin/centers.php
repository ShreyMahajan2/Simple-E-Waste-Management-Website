<?php
include('../includes/config.php');
session_start();

// Redirect to login if not authenticated
if(!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

$error = '';
$success = '';

// Handle add center form
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_center'])) {
    $name = trim($_POST['name']);
    $address = trim($_POST['address']);
    $city = trim($_POST['city']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $operating_hours = trim($_POST['operating_hours']);

    if(empty($name) || empty($address) || empty($city)) {
        $error = 'Please fill in all required fields';
    } else {
        $insert_sql = "INSERT INTO collection_centers (name, address, city, phone, email, operating_hours) 
                      VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_sql);
        $stmt->bind_param("ssssss", $name, $address, $city, $phone, $email, $operating_hours);
        
        if($stmt->execute()) {
            $success = 'Collection center added successfully!';
        } else {
            $error = 'Failed to add collection center';
        }
        $stmt->close();
    }
}

// Handle delete center
if(isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $delete_sql = "DELETE FROM collection_centers WHERE id = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("i", $delete_id);
    
    if($stmt->execute()) {
        $success = 'Collection center deleted successfully!';
    } else {
        $error = 'Failed to delete collection center';
    }
    $stmt->close();
}

// Get all collection centers
$centers_sql = "SELECT * FROM collection_centers ORDER BY city, name";
$centers_result = $conn->query($centers_sql);
?>

<?php include('../includes/header.php'); ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>🏢 Manage Collection Centers</h2>
    <div>
        <a href="dashboard.php" class="btn btn-outline-primary">Back to Dashboard</a>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addCenterModal">+ Add Center</button>
    </div>
</div>

<?php if($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<?php if($success): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Collection Centers List</h5>
    </div>
    <div class="card-body">
        <?php if($centers_result->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Address</th>
                            <th>City</th>
                            <th>Phone</th>
                            <th>Operating Hours</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($center = $centers_result->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($center['name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($center['address']); ?></td>
                            <td><?php echo htmlspecialchars($center['city']); ?></td>
                            <td><?php echo htmlspecialchars($center['phone']); ?></td>
                            <td><?php echo htmlspecialchars($center['operating_hours']); ?></td>
                            <td>
                                <a href="?delete_id=<?php echo $center['id']; ?>" class="btn btn-sm btn-danger" 
                                   onclick="return confirm('Are you sure you want to delete this center?')">Delete</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-center text-muted">No collection centers found.</p>
        <?php endif; ?>
    </div>
</div>

<!-- Add Center Modal -->
<div class="modal fade" id="addCenterModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Add New Collection Center</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Center Name *</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address *</label>
                        <textarea class="form-control" name="address" rows="2" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">City *</label>
                        <input type="text" class="form-control" name="city" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="tel" class="form-control" name="phone">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Operating Hours</label>
                        <input type="text" class="form-control" name="operating_hours" placeholder="e.g., Mon-Fri: 9AM-5PM">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_center" class="btn btn-success">Add Center</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include('../includes/footer.php'); ?>