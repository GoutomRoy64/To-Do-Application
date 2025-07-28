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


?>
