<?php
// session_start();
require_once './../../utilities/auth_check.php';

require_once './../../database/db.php';
require_once './../../utilities/activity_logger.php';

// Set the headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=exported_activity_logs.csv');

// Open the output stream
$output = fopen('php://output', 'w');
fputcsv($output, ['ID', 'First name', 'Last name', 'Action', 'Details', 'Status']);

// Get the user ID
$user = isset($_SESSION['user']) ? $_SESSION['user'] : null;
$user_id = $user['id'];

// Initialize search and filter conditions
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status = isset($_GET['status']) ? trim($_GET['status']) : '';

// Prepare the SQL query
// $sql = "SELECT id, name, phone, phone_type, email, address FROM contacts WHERE user_id = :user_id";
// Initialize search and filter conditions
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status = isset($_GET['status']) ? trim($_GET['status']) : '';

$sql = "SELECT activity_logs.*, users.first_name, users.last_name, users.email 
        FROM activity_logs
        JOIN users ON activity_logs.user_id = users.id
        WHERE activity_logs.user_id = :user_id";

// Apply search filter if provided
if (!empty($search)) {
    $sql .= " AND (action LIKE :search OR details LIKE :search)";
}

// Apply phone type filter if provided
if (!empty($status)) {
    $sql .= " AND status = :status";
}

$sql .= " ORDER BY id DESC";

$stmt = $pdo->prepare($sql);

// Bind parameters
$stmt->bindParam(':user_id', $user_id);

if (!empty($search)) {
    $search_param = "%$search%";
    $stmt->bindParam(':search', $search_param);
}

if (!empty($status)) {
    $stmt->bindParam(':status', $status);
}

// Execute the query
$stmt->execute();

// Fetch all results
$activity_logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($activity_logs as $log) {
    // fputcsv($output, $log);
    // fputcsv($output, ['ID', 'First name', 'Last name', 'Action', 'Details', 'Status']);

    fputcsv($output, [$log['id'], $log['first_name'], $log['last_name'], $log['action'], $log['details'], $log['status']]);
}

log_action("Export activity logs", "Exported activity logs successfully");

fclose($output);
