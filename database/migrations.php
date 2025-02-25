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

    // Create the system features table
    $sql = "CREATE TABLE IF NOT EXISTS system_features (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        description VARCHAR(255) NULL,
        is_active BOOLEAN DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);

    // Create default system features
    $sql = "INSERT INTO system_features (name, description) VALUES
            ('User Management', 'Manage users and roles'),
            ('Contact Management', 'Manage contacts'),
            ('Profile Management', 'Manage profile')";
    $pdo->exec($sql);

    // Create the roles table
    $sql = "CREATE TABLE IF NOT EXISTS roles (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            description VARCHAR(255) NULL,
            permissions TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
    $pdo->exec($sql);

    // Create default roles
    $sql = "INSERT INTO roles (name, description) VALUES
            ('Admin', 'Can perform any task'),
            ('User', 'Can view and manage contacts')";
    $pdo->exec($sql);

    // Create the users table
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        role_id INT NULL,
        avatar VARCHAR(255) NULL,
        first_name VARCHAR(100) NOT NULL,
        last_name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        user_type ENUM('admin', 'user') DEFAULT 'user',
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE SET NULL
    )";
    $pdo->exec($sql);


    $hashed_password = password_hash('12345678', PASSWORD_BCRYPT);

    // Create the super admin with email: nronald@nugsoft.com, password: 123456
    $sql = "INSERT INTO users (role_id, first_name, last_name, email, user_type, password) VALUES
            (1, 'Ronald', 'Ngz', 'nronald@nugsoft.com', 'admin', '$hashed_password')";
    $pdo->exec($sql);

    // Create the activity_logs table
    $sql = "CREATE TABLE IF NOT EXISTS activity_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        action VARCHAR(255) NOT NULL,
        details TEXT NULL,
        status INT DEFAULT 1,
        timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);

    // Create the contacts table
    $sql = "CREATE TABLE IF NOT EXISTS contacts (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        user_id INT(11) NOT NULL,
        name VARCHAR(100) NOT NULL,
        phone VARCHAR(100) NOT NULL UNIQUE,
        phone_type ENUM('Personal', 'Family', 'Business') DEFAULT 'Personal',
        email VARCHAR(100) NULL,
        address VARCHAR(255) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);

    echo "\n\nMigrations run successfully. 🥳\n";
    return;
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
