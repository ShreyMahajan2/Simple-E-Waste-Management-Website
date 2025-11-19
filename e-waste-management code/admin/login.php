<?php
// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include('../includes/config.php');

// If admin is already logged in, redirect to dashboard
if (isset($_SESSION['admin_logged_in'])) {
    header('Location: dashboard.php');
    exit();
}

// Hardcoded admin credentials
$admin_username = 'admin';
$admin_password = 'admin123';

$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password';
    } elseif ($username === $admin_username && $password === $admin_password) {
        // Login successful
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        $_SESSION['last_activity'] = time();
        
        // Simple redirect
        header('Location: dashboard.php');
        exit();
    } else {
        $error = 'Invalid admin credentials';
    }
}
?>

<?php include('../includes/header.php'); ?>

<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card">
            <div class="card-header bg-danger text-white">
                <h4 class="mb-0">🔧 Admin Login</h4>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <div class="alert alert-info">
                    <strong>Demo Credentials:</strong><br>
                    Username: <code>admin</code><br>
                    Password: <code>admin123</code>
                </div>

                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>

                    <button type="submit" class="btn btn-danger w-100">Admin Login</button>
                </form>

                <div class="text-center mt-3">
                    <p><a href="../index.php">← Back to Home</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('../includes/footer.php'); ?>