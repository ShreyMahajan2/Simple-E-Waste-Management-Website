<?php
// Only include session config if not already included
if (!function_exists('require_user_login')) {
    include('session_config.php');
}

function require_user_login() {
    if (!isset($_SESSION['user_id'])) {
        $redirect = urlencode($_SERVER['REQUEST_URI']);
        header('Location: ../user/login.php?redirect=' . $redirect);
        exit();
    }
}

function is_user_logged_in() {
    return isset($_SESSION['user_id']);
}

function redirect_if_user_logged_in() {
    if (isset($_SESSION['user_id'])) {
        header('Location: dashboard.php');
        exit();
    }
}
?>