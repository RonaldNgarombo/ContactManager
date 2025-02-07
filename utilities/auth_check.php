<?php
session_start();

// Check if the session is set and active
if (!isset($_SESSION['user']) || !isset($_SESSION['LAST_ACTIVITY'])) {
    session_destroy();

    header("Location: ../auth/login.php");
    exit();
}

// Auto-expire session if inactive for too long
$timeout = 1800; // 30 minutes
// $timeout = 1; // 1 second
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $timeout)) {
    session_unset();
    session_destroy();

    header("Location: ../auth/login.php");
    exit();
}

// Update last activity timestamp
$_SESSION['LAST_ACTIVITY'] = time();
