<?php

/**
 * This file works as a resolver/guard that determines if the user
 * is an admin or user and redirects them to the appropriate page.
 */

// Include the database connection file
require_once './database/db.php';
require_once './router.php';

// Start the session
session_start();

// Retrieve the current user from the session
$user = isset($_SESSION['user']) ? $_SESSION['user'] : null;

// Check if the user is logged in
// if ($user) {
//     // Check if the user is an admin
//     if ($user['role'] === 'admin') {
//         // Redirect to the admin dashboard
//         header("Location: ./admin/");
//         exit();
//     } else {
//         // Redirect to the user dashboard
//         header("Location: ./user/");
//         exit();
//     }
// } else {
//     // Redirect to the login page
//     header("Location: ./?page=login");
//     exit();
// }
