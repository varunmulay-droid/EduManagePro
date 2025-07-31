<?php
session_start();
require_once '../includes/functions.php';

if (isset($_SESSION['user_id'])) {
    logActivity($_SESSION['user_id'], $_SESSION['user_type'], 'logout', 'User logged out');
}

session_unset();
session_destroy();
header('Location: ../index.php');
exit();
?>
