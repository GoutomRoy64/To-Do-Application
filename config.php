<?php
    // --- Start Session ---
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    // --- Database Configuration ---
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "todo_db";

    // --- Create Connection ---
    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch(PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }

    // --- SQL to update your database (run this once in phpMyAdmin) ---
    /*
    1. Create the 'users' table:
    (if not already created)
    CREATE TABLE users (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

    2. Add the 'user_id' column to your 'tasks' table:
    (if not already created)
    ALTER TABLE tasks
    ADD COLUMN user_id INT(11) UNSIGNED NOT NULL AFTER id,
    ADD FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

    3. Add the 'email' column to your 'users' table:

    ALTER TABLE users
    ADD COLUMN email VARCHAR(100) NOT NULL UNIQUE AFTER username;

    */
?>
