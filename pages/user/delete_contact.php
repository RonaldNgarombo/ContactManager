<?php
session_start();
require_once './../../database/db.php';

$user = isset($_SESSION['user']) ? $_SESSION['user'] : null;
$user_id = $user['id'];

// Ensure the user is logged in
if (!isset($user_id)) {
    header("Location: login.php");
    exit();
}


// Check if contact_id is provided
if (isset($_GET['contact_id']) && !empty($_GET['contact_id'])) {
    $contact_id = $_GET['contact_id'];

    try {
        // Start transaction
        $pdo->beginTransaction();

        // Verify the contact belongs to the user
        $sql = "DELETE FROM contacts WHERE id = :contact_id AND user_id = :user_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':contact_id', $contact_id);
        $stmt->bindParam(':user_id', $user_id);

        if ($stmt->execute() && $stmt->rowCount() > 0) {
            // Commit transaction
            $pdo->commit();
            $_SESSION['success_message'] = "Contact deleted successfully!";
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
header("Location: ./user_contacts.php");

exit();
