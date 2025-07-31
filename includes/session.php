<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Regenerate session ID periodically for security
if (!isset($_SESSION['last_regeneration'])) {
    $_SESSION['last_regeneration'] = time();
} elseif (time() - $_SESSION['last_regeneration'] > 1800) { // 30 minutes
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}

// Session timeout (2 hours)
$timeout = 2 * 60 * 60;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
    session_unset();
    session_destroy();
    header('Location: ../index.php?timeout=1');
    exit();
}
$_SESSION['last_activity'] = time();
?>
