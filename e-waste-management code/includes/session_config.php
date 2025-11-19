<?php
// Session security configuration - only start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Regenerate session ID periodically to prevent fixation attacks
if (!isset($_SESSION['last_regeneration'])) {
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
} elseif (time() - $_SESSION['last_regeneration'] > 1800) { // 30 minutes
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}

// Set session timeout (1 hour)
$timeout = 3600;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
    // Session expired
    session_unset();
    session_destroy();
    header('Location: ../index.php?session=expired');
    exit();
}
$_SESSION['last_activity'] = time();
?>