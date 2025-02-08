<?php
// session_start();
require_once './../../utilities/auth_check.php';

require_once './../../database/db.php';
require_once './../../utilities/activity_logger.php';

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

            log_action("Import contacts", "Imported contacts successfully");
        } else {
            $_SESSION['error_message'] = "No valid contacts found in the file!";

            log_action("Import contacts", "No valid contacts found in the file!", 2);
        }
    } else {
        $_SESSION['error_message'] = "Failed to open file.";

        log_action("Import contacts", "Failed to open file.", 2);
    }
}

header('Location: ./user_contacts.php');
exit;
