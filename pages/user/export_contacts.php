<?php

require_once './../../utilities/auth_check.php';

require_once './../../database/db.php';
require_once './../../utilities/activity_logger.php';

// Set the headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=exported_contacts.csv');

// Open the output stream
$output = fopen('php://output', 'w');
fputcsv($output, ['ID', 'Name', 'Phone', 'Phone Type', 'Email', 'Address']);

// Get the user ID
$user = isset($_SESSION['user']) ? $_SESSION['user'] : null;
$user_id = $user['id'];

// Get the search and phone type filters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$phone_type = isset($_GET['phone_type']) ? trim($_GET['phone_type']) : '';

// Prepare the SQL query
$sql = "SELECT id, name, phone, phone_type, email, address FROM contacts WHERE user_id = :user_id";

// Apply filtering if needed
if (!empty($search)) {
    $sql .= " AND (name LIKE :search OR phone LIKE :search OR email LIKE :search)";
}
if (!empty($phone_type)) {
    $sql .= " AND phone_type = :phone_type";
}

$sql .= " ORDER BY id DESC";

// Execute the query
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':user_id', $user_id);

if (!empty($search)) {
    $searchParam = "%{$search}%";
    $stmt->bindParam(':search', $searchParam);
}
if (!empty($phone_type)) {
    $stmt->bindParam(':phone_type', $phone_type);
}

// Fetch the results and write them to the CSV file
$stmt->execute();
$contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($contacts as $contact) {
    fputcsv($output, $contact);
}

log_action("Export contacts", "Exported contacts successfully");

fclose($output);
