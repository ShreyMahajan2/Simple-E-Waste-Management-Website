<?php
// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include('../includes/config.php');

// If user is already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password';
    } else {
        // Check user credentials
        $sql = "SELECT id, username, password FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                // Login successful
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['last_activity'] = time();
                
                // Debug: Check if session is set
                error_log("Login successful - User ID: " . $user['id']);
                
                // Simple redirect
                header('Location: dashboard.php');
                exit();
            } else {
                $error = 'Invalid password';
            }
        } else {
            $error = 'No account found with this email';
        }
        $stmt->close();
    }
}
?>

<?php include('../includes/header.php'); ?>

<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h4 class="mb-0">User Login</h4>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>

                    <button type="submit" class="btn btn-success w-100">Login</button>
                </form>

                <div class="text-center mt-3">
                    <p>Don't have an account? <a href="register.php">Register here</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('../includes/footer.php'); ?>