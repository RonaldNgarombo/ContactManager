<?php

require_once './../../utilities/auth_check.php';

require_once './../../database/db.php';
require_once './../../utilities/activity_logger.php';

$user = isset($_SESSION['user']) ? $_SESSION['user'] : null;
$user_id = $user['id'];

// Define the upload directory
$uploadDir = './../../avatars/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Check if file was uploaded
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['avatar'])) {
    $file = $_FILES['avatar'];

    // die(var_dump($file));

    // Validation checks
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    $maxSize = 2 * 1024 * 1024; // 2MB

    if ($file['error'] !== UPLOAD_ERR_OK) {

        $_SESSION['error_message'] = "File upload error.";

        log_action("Change avatar", "File upload error.", 2);
    } elseif (!in_array($file['type'], $allowedTypes)) {

        $_SESSION['error_message'] = "Invalid file type. Only JPG, PNG, and GIF are allowed.";

        log_action("Change avatar", "Invalid file type. Only JPG, PNG, and GIF are allowed.", 2);
    } elseif ($file['size'] > $maxSize) {

        $_SESSION['error_message'] = "File size exceeds 2MB limit.";

        log_action("Change avatar", "File size exceeds 2MB limit.", 2);
    } else {
        // Generate a unique filename
        $fileExt = pathinfo($file['name'], PATHINFO_EXTENSION);
        $newFileName = uniqid('', true) . '.' . $fileExt;
        $uploadPath = $uploadDir . $newFileName;

        // Move the file to the avatars directory
        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            // Update user's profile in the database (example)

            if ($user_id) {
                $stmt = $pdo->prepare("UPDATE users SET avatar = ? WHERE id = ?");
                if ($stmt->execute([$newFileName, $user_id])) {
                    $_SESSION['success_message'] = "Profile picture updated successfully!";

                    log_action("Change avatar", "Profile picture updated successfully!");
                } else {
                    $_SESSION['error_message'] = "Database update failed.";

                    log_action("Change avatar", "Database update failed.", 2);
                }
            }
        } else {
            $_SESSION['error_message'] = "Failed to upload the file.";

            log_action("Change avatar", "Failed to upload the file.", 2);
        }
    }
} else {
    $_SESSION['error_message'] = "No file uploaded.";

    log_action("Change avatar", "No file uploaded.", 2);
}

// Redirect back to profile page
header("Location: ./update_profile.php");
exit();
