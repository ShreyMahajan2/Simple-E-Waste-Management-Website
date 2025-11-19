<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Waste Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/e-waste-management/css/style.css" rel="stylesheet">

</head>
<body>
    <?php
    // Include security configurations (session starts only once)
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Simple access control without duplicate includes
    if (isset($_SESSION['user_id']) && strpos($_SERVER['REQUEST_URI'], '/admin/') !== false) {
        header('Location: ../user/dashboard.php?error=access_denied');
        exit();
    }
    
    $is_user_logged_in = isset($_SESSION['user_id']);
    $is_admin_logged_in = isset($_SESSION['admin_logged_in']);
    $username = $is_user_logged_in ? $_SESSION['username'] : '';
    ?>
    
    <nav class="navbar navbar-expand-lg navbar-dark bg-success">
        <div class="container">
            <a class="navbar-brand" href="/e-waste-management/index.php">
                <strong>🌱 E-Waste Manager</strong>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/e-waste-management/index.php">Home</a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link" href="/e-waste-management/awareness.php">Awareness</a>
                    </li>
                    
                    <?php if($is_user_logged_in): ?>
                        <!-- Logged-in User Menu -->
                        <li class="nav-item">
                            <a class="nav-link" href="/e-waste-management/user/submit_waste.php">Submit E-Waste</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                👋 <?php echo htmlspecialchars($username); ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="/e-waste-management/user/dashboard.php">My Dashboard</a></li>
                                <li><a class="dropdown-item" href="/e-waste-management/user/reports.php">My Reports</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="/e-waste-management/user/logout.php">Logout</a></li>
                            </ul>
                        </li>
                        
                    <?php elseif($is_admin_logged_in): ?>
                        <!-- Admin Menu -->
                        <li class="nav-item">
                            <a class="nav-link" href="/e-waste-management/admin/reports.php">Analytics</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                🔧 Admin
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="/e-waste-management/admin/dashboard.php">Dashboard</a></li>
                                <li><a class="dropdown-item" href="/e-waste-management/admin/reports.php">Analytics</a></li>
                                <li><a class="dropdown-item" href="/e-waste-management/admin/centers.php">Manage Centers</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="/e-waste-management/admin/logout.php">Logout</a></li>
                            </ul>
                        </li>
                        
                    <?php else: ?>
                        <!-- Guest Menu -->
                        <li class="nav-item">
                            <a class="nav-link" href="/e-waste-management/user/register.php">Register</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/e-waste-management/user/login.php">Login</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    
    <main>
        <div class="container mt-4">