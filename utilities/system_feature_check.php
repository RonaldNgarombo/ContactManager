<?php

function checkIfSystemFeatureIsActive($feature, $type = "interface")
{
    // return true;
    global $pdo; // Use the global PDO instance

    // Get active system features from the db
    $stmt = $pdo->prepare("SELECT * FROM system_features WHERE is_active = 1");
    $stmt->execute();
    $active_system_features = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // If there are no active features
    if (count($active_system_features) == 0) {
        return false;
    }

    // Get the names of the active features
    $active_system_features = array_map(function ($feature) {
        return $feature['name'];
    }, $active_system_features);

    // If there are no features recorded in the system
    if (empty($active_system_features)) {
        $_SESSION['error_message'] = "No system features found!";

        header("Location: ./user_dashboard.php"); // Redirect to home
        exit();
    }

    // Check if the feature is active
    if (in_array($feature, $active_system_features)) {
        return true;
    }

    switch ($type) {
        case "interface":
            return false;
        default:
            header("Location: ./user_dashboard.php"); // Redirect to home
            exit();
    }
}
