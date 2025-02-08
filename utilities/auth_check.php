<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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

/**
 * Summary of userCan
 * @param mixed $permission
 * @param mixed $type
 * @return bool
 * 
 * Determines if a user can view or perform some actions on the system.
 */
function userCan($permission, $type = 'interface')
{
    global $pdo; // Use the global PDO instance

    // Retrieve user data from session
    $user = isset($_SESSION['user']) ? $_SESSION['user'] : null;

    // Super admin bypass
    if ($user && isset($user['email']) && $user['email'] === "nronald@nugsoft.com") {
        return true;
    }

    // Get user from the db
    $stmt = $pdo->prepare("SELECT users.*, roles.permissions 
                       FROM users 
                       JOIN roles ON users.role_id = roles.id 
                       WHERE users.id = :user_id");

    $stmt->bindParam(':user_id', $user['id'], PDO::PARAM_INT);
    $stmt->execute();
    $existing_user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Retrieve user permissions
    $permissions = json_decode($existing_user['permissions']) ?? [];

    // If user has no permissions, restrict access
    if (empty($permissions)) {
        $_SESSION['error_message'] = "You have no permission to view this page!";

        header("Location: ./user_dashboard.php"); // Redirect to home
        exit();
    }

    // Check if user has the required permission
    if (in_array($permission, $permissions)) {
        return true;
    }

    // Handle different restriction types
    switch ($type) {
        case 'interface':
            // die('Die 1');
            return false; // Hide UI elements

        default:
            $_SESSION['error_message'] = "You do not have permission to perform this action.";

            header("Location: ./user_dashboard.php"); // Redirect if accessing a restricted page
            exit();
    }
}
