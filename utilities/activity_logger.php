<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

function log_action($pdo, $action, $details = null, $status = 1)
{
    try {
        if (!isset($_SESSION['user']) || !isset($_SESSION['user']['id'])) {
            throw new Exception("User ID not found in session.");
        }

        $user_id = $_SESSION['user']['id'];

        $sql = "INSERT INTO activity_logs (user_id, action, details, status) VALUES (:user_id, :action, :details, :status)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':action', $action, PDO::PARAM_STR);
        $stmt->bindParam(':details', $details, PDO::PARAM_STR);
        $stmt->bindParam(':status', $status, PDO::PARAM_INT);
        $stmt->execute();
    } catch (Exception $e) {
        error_log("Error logging action: " . $e->getMessage());
    }
}
