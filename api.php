<?php
require 'config.php';
header('Content-Type: application/json');

// All API requests require a logged-in user.
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    http_response_code(401); // Unauthorized
    echo json_encode(['status' => 'error', 'message' => 'User not logged in.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];

// Handle different request methods
switch ($method) {
    case 'GET':
        // Action: Fetch all tasks for the logged-in user
        try {
            $stmt = $conn->prepare("SELECT id, task_description, is_completed FROM tasks WHERE user_id = :user_id ORDER BY created_at ASC");
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($tasks);
        } catch(PDOException $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'POST':
        // Check for an 'action' parameter to determine the operation
        if (!isset($_POST['action'])) {
            http_response_code(400); // Bad Request
            echo json_encode(['status' => 'error', 'message' => 'No action specified.']);
            exit;
        }

        $action = $_POST['action'];

        try {
            switch ($action) {
                case 'add':
                    // Action: Add a new task
                    $task = htmlspecialchars(strip_tags($_POST['task']));
                    $stmt = $conn->prepare("INSERT INTO tasks (user_id, task_description) VALUES (:user_id, :task)");
                    $stmt->bindParam(':user_id', $user_id);
                    $stmt->bindParam(':task', $task);
                    $stmt->execute();
                    $lastId = $conn->lastInsertId();
                    echo json_encode([
                        'status' => 'success',
                        'task' => ['id' => $lastId, 'task_description' => $task, 'is_completed' => 0]
                    ]);
                    break;

                case 'rename': // --- NEW ACTION ---
                    // Action: Rename an existing task
                    $id = $_POST['id'];
                    $new_text = htmlspecialchars(strip_tags($_POST['task_description']));
                    $stmt = $conn->prepare("UPDATE tasks SET task_description = :task_description WHERE id = :id AND user_id = :user_id");
                    $stmt->bindParam(':task_description', $new_text);
                    $stmt->bindParam(':id', $id);
                    $stmt->bindParam(':user_id', $user_id);
                    $stmt->execute();
                    echo json_encode(['status' => 'success']);
                    break;

                case 'update':
                    // Action: Mark a task as complete/incomplete
                    $id = $_POST['id'];
                    $stmt = $conn->prepare("UPDATE tasks SET is_completed = NOT is_completed WHERE id = :id AND user_id = :user_id");
                    $stmt->bindParam(':id', $id);
                    $stmt->bindParam(':user_id', $user_id);
                    $stmt->execute();
                    echo json_encode(['status' => 'success']);
                    break;

                case 'delete':
                    // Action: Delete a task
                    $id = $_POST['id'];
                    $stmt = $conn->prepare("DELETE FROM tasks WHERE id = :id AND user_id = :user_id");
                    $stmt->bindParam(':id', $id);
                    $stmt->bindParam(':user_id', $user_id);
                    $stmt->execute();
                    echo json_encode(['status' => 'success']);
                    break;

                default:
                    http_response_code(400); // Bad Request
                    echo json_encode(['status' => 'error', 'message' => 'Invalid action specified.']);
                    break;
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    default:
        // Handle other methods like PUT, DELETE if necessary
        http_response_code(405); // Method Not Allowed
        echo json_encode(['status' => 'error', 'message' => 'Method not allowed.']);
        break;
}
?>
