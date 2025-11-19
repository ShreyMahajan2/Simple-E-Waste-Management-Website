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
$error = '';
$success = '';

// Get collection centers for dropdown
$centers_sql = "SELECT id, name, city FROM collection_centers ORDER BY name";
$centers_result = $conn->query($centers_sql);

// Handle form submission
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $device_type = trim($_POST['device_type']);
    $device_condition = trim($_POST['device_condition']);
    $quantity = intval($_POST['quantity']);
    $center_id = !empty($_POST['center_id']) ? intval($_POST['center_id']) : NULL;
    $notes = trim($_POST['notes']);

    if(empty($device_type) || $quantity < 1) {
        $error = 'Please fill in all required fields';
    } else {
        $insert_sql = "INSERT INTO e_waste_submissions (user_id, center_id, device_type, device_condition, quantity, notes) 
                      VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_sql);
        $stmt->bind_param("iissis", $user_id, $center_id, $device_type, $device_condition, $quantity, $notes);
        
        if($stmt->execute()) {
            $success = 'E-waste submission successful!';
            // Clear form
            $device_type = $device_condition = $notes = '';
            $quantity = 1;
            $center_id = NULL;
        } else {
            $error = 'Submission failed. Please try again.';
        }
        $stmt->close();
    }
}
?>

<?php include('../includes/header.php'); ?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h4 class="mb-0">Submit E-Waste</h4>
                <small class="opacity-75">Logged in as: <?php echo htmlspecialchars($username); ?></small>
            </div>
            <div class="card-body">
                <?php if($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="device_type" class="form-label">Device Type *</label>
                            <input type="text" class="form-control" id="device_type" name="device_type" 
                                   value="<?php echo isset($device_type) ? htmlspecialchars($device_type) : ''; ?>" 
                                   placeholder="e.g., Laptop, Mobile Phone, Printer" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="device_condition" class="form-label">Device Condition</label>
                            <select class="form-control" id="device_condition" name="device_condition">
                                <option value="">Select Condition</option>
                                <option value="working" <?php echo (isset($device_condition) && $device_condition == 'working') ? 'selected' : ''; ?>>Working</option>
                                <option value="not_working" <?php echo (isset($device_condition) && $device_condition == 'not_working') ? 'selected' : ''; ?>>Not Working</option>
                                <option value="damaged" <?php echo (isset($device_condition) && $device_condition == 'damaged') ? 'selected' : ''; ?>>Damaged</option>
                                <option value="for_parts" <?php echo (isset($device_condition) && $device_condition == 'for_parts') ? 'selected' : ''; ?>>For Parts Only</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="quantity" class="form-label">Quantity *</label>
                            <input type="number" class="form-control" id="quantity" name="quantity" 
                                   value="<?php echo isset($quantity) ? $quantity : 1; ?>" min="1" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="center_id" class="form-label">Preferred Collection Center</label>
                            <select class="form-control" id="center_id" name="center_id">
                                <option value="">Select Center (Optional)</option>
                                <?php 
                                // Reset pointer for centers result
                                $centers_result->data_seek(0);
                                while($center = $centers_result->fetch_assoc()): ?>
                                    <option value="<?php echo $center['id']; ?>" 
                                        <?php echo (isset($center_id) && $center_id == $center['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($center['name'] . ' - ' . $center['city']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Additional Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3" 
                                  placeholder="Any additional information about the device..."><?php echo isset($notes) ? htmlspecialchars($notes) : ''; ?></textarea>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success">Submit E-Waste</button>
                        <a href="dashboard.php" class="btn btn-outline-secondary">Back to Dashboard</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include('../includes/footer.php'); ?>