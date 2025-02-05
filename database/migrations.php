<?php
$host = "127.0.0.1";
$dbname = "contact_manager"; // Your database name
$username = "root";
$password = "";

try {
    // Connect to MySQL without selecting a database
    $pdo = new PDO("mysql:host=$host", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // Create the database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname`");

    // Reconnect to select the newly created database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // Create the users table
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        first_name VARCHAR(100) NOT NULL,
        last_name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        user_type ENUM('admin', 'user') DEFAULT 'user',
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);

    // Create the contacts table
    $sql = "CREATE TABLE IF NOT EXISTS contacts (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        user_id INT(11) NOT NULL,
        name VARCHAR(100) NOT NULL,
        phone VARCHAR(100) NOT NULL,
        phone_type ENUM('Personal', 'Family', 'Business') DEFAULT 'Personal',
        email VARCHAR(100) NULL,
        address VARCHAR(255) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);

    // // Create the user_contacts table
    // $sql = "CREATE TABLE IF NOT EXISTS user_contacts (
    //     id INT(11) AUTO_INCREMENT PRIMARY KEY,
    //     user_id INT(11) NOT NULL,
    //     contact_id INT(11) NOT NULL,
    //     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    //     FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    //     FOREIGN KEY (contact_id) REFERENCES contacts(id) ON DELETE CASCADE
    // )";
    // $pdo->exec($sql);

    echo "\n\nMigrations run successfully. 🥳";
    return;
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
