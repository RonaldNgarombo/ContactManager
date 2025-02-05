<?php
// handle_register.php

// Include the database connection file
require_once './../../database/db.php';

// Initialize an array to store errors
$errors = [];

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form data
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $terms = isset($_POST['terms']) ? true : false;

    // Validate form data
    if (empty($first_name)) {
        $errors['first_name'] = "First name is required.";
    }

    if (empty($last_name)) {
        $errors['last_name'] = "Last name is required.";
    }

    if (empty($email)) {
        $errors['email'] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Invalid email format.";
    }

    if (empty($password)) {
        $errors['password'] = "Password is required.";
    } elseif (strlen($password) < 8) {
        $errors['password'] = "Password must be at least 8 characters long.";
    }

    if (empty($confirm_password)) {
        $errors['confirm_password'] = "Please confirm your password.";
    } elseif ($password !== $confirm_password) {
        $errors['confirm_password'] = "Passwords do not match.";
    }

    if (!$terms) {
        $errors['terms'] = "You must agree to the terms and conditions.";
    }

    // If there are no errors, proceed with database insertion
    if (empty($errors)) {
        try {
            // Begin transaction
            $pdo->beginTransaction();

            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);

            // Prepare the SQL statement
            $sql = "INSERT INTO users (first_name, last_name, email, password) VALUES (:first_name, :last_name, :email, :password)";
            $stmt = $pdo->prepare($sql);

            // Bind parameters
            $stmt->bindParam(':first_name', $first_name, PDO::PARAM_STR);
            $stmt->bindParam(':last_name', $last_name, PDO::PARAM_STR);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->bindParam(':password', $hashed_password, PDO::PARAM_STR);

            // Execute the query
            if ($stmt->execute()) {
                // Commit transaction if successful
                $pdo->commit();

                // Redirect to login page
                header("Location: ./login.php");
                exit();
            } else {
                // Rollback in case of failure
                $pdo->rollBack();
                $errors['database'] = "Error: Could not execute query.";
            }
        } catch (PDOException $e) {
            // Rollback transaction on error
            $pdo->rollBack();
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

        header("Location: ./register.php"); // Redirect back to the registration form
        exit();
    }

    // Close the database connection
    $conn->close();
}
