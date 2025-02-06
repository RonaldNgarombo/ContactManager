<?php

/**
 * This file works as a router/resolver for the application.
 * It checks the URL path and loads the appropriate page.
 */

// Include the database connection file
require_once './database/db.php';

// die('Die 1');
// Start the session
session_start();

// Get the user from the session
$user = isset($_SESSION['user']) ? $_SESSION['user'] : null;

// die(var_dump($user));

// If no user, navigate to login page
if (!$user) {
    header("Location: ./pages/auth/login.php");
    exit();
}

// Retrieve the current page from the URL
$page = isset($_GET['page']) ? $_GET['page'] : 'home';
// die('Die 3');

// Check if the user is logged in
if ($page !== 'login' && $page !== 'register' && $page !== 'handle_login' && $page !== 'handle_register' && $page !== 'logout' && !isset($_SESSION['user'])) {
    // Redirect to the login page
    header("Location: ./?page=login");
    exit();
}

// Depending on the user type, use a switch statement to load the appropriate page
if ($user['user_type'] === 'user') {
    switch ($page) {
        case 'user-dashboard':
            // Load the admin dashboard
            header("Location: ./pages/user/user_dashboard.php");
            break;

        default:
            // Load the 404 page
            header("Location: ./pages/user/user_dashboard.php");
            break;
    }
} else {
}




// die('Die 2');


// Retrieve the current page from the URL
$page = isset($_GET['page']) ? $_GET['page'] : 'home';
// die('Die 3');

// Check if the user is logged in
if ($page !== 'login' && $page !== 'register' && $page !== 'handle_login' && $page !== 'handle_register' && $page !== 'logout' && !isset($_SESSION['user'])) {
    // Redirect to the login page
    header("Location: ./?page=login");
    exit();
}
