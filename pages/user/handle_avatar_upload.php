<?php
// Start session if needed
session_start();

require_once './../../database/db.php';

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
    } elseif (!in_array($file['type'], $allowedTypes)) {

        $_SESSION['error_message'] = "Invalid file type. Only JPG, PNG, and GIF are allowed.";
    } elseif ($file['size'] > $maxSize) {

        $_SESSION['error_message'] = "File size exceeds 2MB limit.";
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
                } else {
                    $_SESSION['error_message'] = "Database update failed.";
                }
            }
        } else {
            $_SESSION['error_message'] = "Failed to upload the file.";
        }
    }
} else {
    $_SESSION['error_message'] = "No file uploaded.";
}

// Redirect back to profile page
header("Location: ./update_profile.php");
exit();
