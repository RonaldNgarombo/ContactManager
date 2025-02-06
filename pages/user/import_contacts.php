<?php
session_start();
require_once './../../database/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file']['tmp_name'];

    if (!file_exists($file) || filesize($file) == 0) {
        $_SESSION['error_message'] = "The uploaded file is empty!";
        header('Location: ./user_contacts.php');
        exit;
    }

    if (($handle = fopen($file, 'r')) !== FALSE) {
        $header = fgetcsv($handle); // Read the header row

        if ($header === false) {
            $_SESSION['error_message'] = "Invalid CSV format!";
            header('Location: ./user_contacts.php');
            exit;
        }

        $imported = false;

        while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
            if (count($data) < 6) {
                continue; // Skip invalid rows
            }

            $name = $data[1];
            $phone = $data[2];
            $phone_type = $data[3];
            $email = $data[4];
            $address = $data[5];

            if (empty($name) || empty($phone)) {
                continue; // Skip if required fields are missing
            }

            // Insert into database
            $sql = "INSERT INTO contacts (user_id, name, phone, phone_type, email, address) 
                    VALUES (:user_id, :name, :phone, :phone_type, :email, :address)";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':user_id', $_SESSION['user']['id']);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':phone_type', $phone_type);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':address', $address);
            $stmt->execute();

            $imported = true;
        }

        fclose($handle);

        if ($imported) {
            $_SESSION['success_message'] = "Contacts imported successfully!";
        } else {
            $_SESSION['error_message'] = "No valid contacts found in the file!";
        }
    } else {
        $_SESSION['error_message'] = "Failed to open file.";
    }
}

header('Location: ./user_contacts.php');
exit;
