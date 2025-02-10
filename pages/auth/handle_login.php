<?php
// handle_login.php

// Include the database connection file
require_once './../../database/db.php';

// Initialize an array to store errors
$errors = [];

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form data
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Validate form data
    if (empty($email)) {
        $errors['email'] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Invalid email format.";
    }

    if (empty($password)) {
        $errors['password'] = "Password is required.";
    }

    // If there are no errors, proceed with database insertion
    if (empty($errors)) {
        try {
            // Prepare the SQL statement
            $sql = "SELECT users.*, roles.name AS role_name, roles.permissions 
                    FROM users 
                    JOIN roles ON users.role_id = roles.id 
                    WHERE users.email = :email";

            // $sql = "SELECT * FROM users WHERE email = :email";
            $stmt = $pdo->prepare($sql);

            // Bind parameters
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);

            // Execute the query
            $stmt->execute();

            // Fetch the user record
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verify the password
            if ($user && password_verify($password, $user['password'])) {
                // Start the session
                session_start();

                // Store user data in session
                $_SESSION['user'] = $user;
                $_SESSION['LAST_ACTIVITY'] = time();

                // Redirect to dashboard
                header("Location: ./../user/user_dashboard.php");

                exit();
            } else {
                $errors['login'] = "Invalid email or password.";
            }
        } catch (PDOException $e) {
            $errors['database'] = "Database error: " . $e->getMessage();
        }
    }

    // If there were errors, display them (for debugging)
    if (!empty($errors)) {
        foreach ($errors as $error) {
            echo "<p style='color: red;'>$error</p>";
        }
    }

    // If there are errors, pass them back to the form
    if (!empty($errors)) {
        // Store errors and form data in session or pass them via query string
        session_start();
        $_SESSION['errors'] = $errors;
        $_SESSION['form_data'] = $_POST; // Store all form data in session to repopulate the form

        header("Location: ./login.php"); // Redirect back to the login form
        exit();
    }

    // Close the database connection
    $conn->close();
}
