<?php
// Only include session config if not already included
if (!function_exists('require_admin_login')) {
    include('session_config.php');
}

function require_admin_login() {
    if (!isset($_SESSION['admin_logged_in'])) {
        $redirect = urlencode($_SERVER['REQUEST_URI']);
        header('Location: login.php?redirect=' . $redirect);
        exit();
    }
}

function is_admin_logged_in() {
    return isset($_SESSION['admin_logged_in']);
}

function redirect_if_admin_logged_in() {
    if (isset($_SESSION['admin_logged_in'])) {
        header('Location: dashboard.php');
        exit();
    }
}

// Prevent user from accessing admin pages and vice versa
function prevent_cross_access() {
    if (isset($_SESSION['user_id']) && strpos($_SERVER['REQUEST_URI'], '/admin/') !== false) {
        // User trying to access admin area
        header('Location: ../user/dashboard.php?error=access_denied');
        exit();
    }
    
    if (isset($_SESSION['admin_logged_in']) && strpos($_SERVER['REQUEST_URI'], '/user/') !== false) {
        // Admin trying to access user area (except for browsing)
        // Allow admin to view user pages but redirect to admin dashboard for actions
        if (basename($_SERVER['PHP_SELF']) !== 'reports.php') {
            header('Location: ../admin/dashboard.php?error=use_admin_panel');
            exit();
        }
    }
}
?>