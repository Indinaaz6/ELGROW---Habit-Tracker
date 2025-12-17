<?php
session_start();
include __DIR__ . '/../config/config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['id_user'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['id_user'];
$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

// ensure table exists
$conn->query("CREATE TABLE IF NOT EXISTS goals (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    target_date DATE NULL,
    is_complete TINYINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id_user) ON DELETE CASCADE
)");

try {
    switch ($action) {
        case 'create':
            $stmt = $conn->prepare("INSERT INTO goals (user_id, title, description, target_date) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isss", $user_id, $input['title'], $input['description'], $input['target_date']);
            $stmt->execute();
            echo json_encode(['success' => true, 'id' => $conn->insert_id]);
            break;

        case 'update':
            $stmt = $conn->prepare("UPDATE goals SET title = ?, description = ?, target_date = ? WHERE id = ? AND user_id = ?");
            $stmt->bind_param("sssii", $input['title'], $input['description'], $input['target_date'], $input['goal_id'], $user_id);
            $stmt->execute();
            echo json_encode(['success' => true]);
            break;

        case 'delete':
            $stmt = $conn->prepare("DELETE FROM goals WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ii", $input['goal_id'], $user_id);
            $stmt->execute();
            echo json_encode(['success' => true]);
            break;

        case 'toggle':
            $stmt = $conn->prepare("UPDATE goals SET is_complete = ? WHERE id = ? AND user_id = ?");
            $stmt->bind_param("iii", $input['is_complete'], $input['goal_id'], $user_id);
            $stmt->execute();
            echo json_encode(['success' => true]);
            break;

        case 'get':
            $stmt = $conn->prepare("SELECT * FROM goals WHERE id = ? AND user_id = ? LIMIT 1");
            $stmt->bind_param("ii", $input['goal_id'], $user_id);
            $stmt->execute();
            $g = $stmt->get_result()->fetch_assoc();
            if ($g) echo json_encode(['success'=>true, 'goal'=>$g]);
            else echo json_encode(['success'=>false, 'message'=>'Not found']);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Unknown action']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

?>
