<?php
// session_start();
require_once './../../utilities/auth_check.php';

require_once './../../database/db.php';

userCan('delete-roles', 'page');

$user = isset($_SESSION['user']) ? $_SESSION['user'] : null;
$user_id = $user['id'];

// Check if role_id is provided
if (isset($_GET['role_id']) && !empty($_GET['role_id'])) {
    $role_id = $_GET['role_id'];

    try {
        // Start transaction
        $pdo->beginTransaction();

        // Verify the contact belongs to the user
        $sql = "DELETE FROM roles WHERE id = :role_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':role_id', $role_id);

        if ($stmt->execute() && $stmt->rowCount() > 0) {
            // Commit transaction
            $pdo->commit();
            $_SESSION['success_message'] = "Role deleted successfully!";
        } else {
            $_SESSION['error_message'] = "Failed to delete contact. It may not exist or belong to you.";
        }
    } catch (Exception $e) {
        $pdo->rollBack(); // Rollback on failure
        $_SESSION['error_message'] = "An error occurred: " . $e->getMessage();
    }
} else {
    $_SESSION['error_message'] = "Invalid request.";
}

// Redirect back to the contact list page
header("Location: ./view_roles.php");

exit();
