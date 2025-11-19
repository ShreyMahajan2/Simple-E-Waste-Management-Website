<?php
include('../includes/config.php');
session_start();

// Redirect if already logged in
if(isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
$success = '';

// Handle form submission
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);

    // Basic validation
    if(empty($username) || empty($email) || empty($password)) {
        $error = 'Please fill in all required fields';
    } elseif($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif(strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long';
    } else {
        // Check if username or email already exists
        $check_sql = "SELECT id FROM users WHERE username = ? OR email = ?";
        $stmt = $conn->prepare($check_sql);
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if($result->num_rows > 0) {
            $error = 'Username or email already exists';
        } else {
            // Hash password and insert user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $insert_sql = "INSERT INTO users (username, email, password, phone, address) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_sql);
            $stmt->bind_param("sssss", $username, $email, $hashed_password, $phone, $address);
            
            if($stmt->execute()) {
                $success = 'Registration successful! You can now <a href="login.php">login</a>.';
                // Clear form
                $username = $email = $phone = $address = '';
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
        $stmt->close();
    }
}
?>

<?php include('../includes/header.php'); ?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h4 class="mb-0">Create Your Account</h4>
            </div>
            <div class="card-body">
                <?php if($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username *</label>
                        <input type="text" class="form-control" id="username" name="username" 
                               value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address *</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="tel" class="form-control" id="phone" name="phone" 
                               value="<?php echo isset($phone) ? htmlspecialchars($phone) : ''; ?>">
                    </div>

                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea class="form-control" id="address" name="address" rows="3"><?php echo isset($address) ? htmlspecialchars($address) : ''; ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password *</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <div class="form-text">Password must be at least 6 characters long.</div>
                    </div>

                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm Password *</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>

                    <button type="submit" class="btn btn-success w-100">Register</button>
                </form>

                <div class="text-center mt-3">
                    <p>Already have an account? <a href="login.php">Login here</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('../includes/footer.php'); ?>