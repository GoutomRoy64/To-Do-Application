<?php
require 'config.php'; // Includes session_start()
header('Content-Type: application/json');

// Determine the action. For POST it's in the body, for GET (logout) it's in the URL.
$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'register':
        handle_register($conn);
        break;
    case 'login':
        handle_login($conn);
        break;
    case 'logout':
        handle_logout();
        break;
    default:
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'No valid action specified.']);
        break;
}

function handle_register($conn) {
    $response = ['status' => 'error', 'message' => 'Invalid registration data.'];
    if (isset($_POST['username']) && isset($_POST['password']) && isset($_POST['email'])) {
        $username = trim($_POST['username']);
        $password = $_POST['password'];
        $email = trim($_POST['email']);

        if (empty($username) || empty($password) || empty($email)) {
            $response['message'] = 'Username, email, and password cannot be empty.';
        } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $response['message'] = 'Invalid email format.';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            try {
                $stmt = $conn->prepare("SELECT id FROM users WHERE  email = :email");
                $stmt->bindParam(':email', $email);
                $stmt->execute();

                if ($stmt->rowCount() > 0) {
                    $response['message'] = 'This email address is already registered.';
                } else {
                    $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (:username, :email, :password)");
                    $stmt->bindParam(':username', $username);
                    $stmt->bindParam(':email', $email);
                    $stmt->bindParam(':password',  $hashed_password);
                    
                    if ($stmt->execute()) {
                        $response = ['status' => 'success', 'message' => 'Registration successful. You can now log in.'];
                    } else {
                        $response['message'] = 'Registration failed. Please try again.';
                    }
                }
            } catch (PDOException $e) {
                $response['message'] = 'Database error: ' . $e->getMessage();
            }
        }
    }
    echo json_encode($response);
}

function handle_login($conn) {
    $response = ['status' => 'error', 'message' => 'Invalid login data.'];
    // --- UPDATED: Check for email instead of username ---
    if (isset($_POST['email']) && isset($_POST['password'])) {
        $email = trim($_POST['email']);
        $password = $_POST['password'];

        try {
            // --- UPDATED: Query by email ---
            $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                if (password_verify($password, $user['password'])) {
                    $_SESSION['loggedin'] = true;
                    $_SESSION['user_id'] = $user['id'];
                    // We still save the username to the session to greet the user in the app
                    $_SESSION['username'] = $user['username'];
                    $response = ['status' => 'success', 'message' => 'Login successful.'];
                } else {
                    $response['message'] = 'Invalid email or password.';
                }
            } else {
                $response['message'] = 'Invalid email or password.';
            }
        } catch (PDOException $e) {
            $response['message'] = 'Database error: ' . $e->getMessage();
        }
    }
    echo json_encode($response);
}

function handle_logout() {
    $_SESSION = array();
    session_destroy();
    header("location: index.html");
    exit;
}
?>
