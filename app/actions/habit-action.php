<?php
session_start();
include __DIR__ . '/../config/config.php';

if (!isset($_SESSION['id_user'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['id_user'];
$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

header('Content-Type: application/json');

try {
    switch ($action) {
        case 'create':
            $daily_days = $input['daily_days'] ?? '';
            $weekly_count = isset($input['weekly_count']) ? (int)$input['weekly_count'] : 0;
            $end_date = $input['end_date'] ?? '';

            $stmt = $conn->prepare("INSERT INTO habits (user_id, name, frequency, category, daily_days, weekly_count, end_date) VALUES (?, ?, ?, ?, ?, ?, NULLIF(?, ''))");
            $stmt->bind_param("issssis", $user_id, $input['name'], $input['frequency'], $input['category'], $daily_days, $weekly_count, $end_date);
            $stmt->execute();
            echo json_encode(['success' => true, 'habit_id' => $conn->insert_id]);
            break;

        case 'complete':
            $habit_id = $input['habit_id'];
            $date = $input['date'];
            
            // Check if already completed
            $check = $conn->prepare("SELECT id FROM habit_completions WHERE habit_id = ? AND user_id = ? AND completion_date = ?");
            $check->bind_param("iis", $habit_id, $user_id, $date);
            $check->execute();
            $exists = $check->get_result()->num_rows > 0;
            
            if (!$exists) {
                // Get current streak
                $streak_query = $conn->prepare("SELECT streak FROM habits WHERE id = ?");
                $streak_query->bind_param("i", $habit_id);
                $streak_query->execute();
                $streak_result = $streak_query->get_result()->fetch_assoc();
                $streak = ($streak_result['streak'] ?? 0) + 1;
                
                // Insert completion
                $stmt = $conn->prepare("INSERT INTO habit_completions (habit_id, user_id, completion_date, streak) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("iisi", $habit_id, $user_id, $date, $streak);
                $stmt->execute();
                
                // Update habit streak
                $update = $conn->prepare("UPDATE habits SET streak = ? WHERE id = ?");
                $update->bind_param("ii", $streak, $habit_id);
                $update->execute();
            }
            
            echo json_encode(['success' => true]);
            break;

        case 'uncomplete':
            $habit_id = $input['habit_id'];
            $date = $input['date'];
            
            $stmt = $conn->prepare("DELETE FROM habit_completions WHERE habit_id = ? AND user_id = ? AND completion_date = ?");
            $stmt->bind_param("iis", $habit_id, $user_id, $date);
            $stmt->execute();
            
            // Recalculate streak
            $streak_query = $conn->prepare("SELECT COUNT(*) as count FROM habit_completions WHERE habit_id = ? ORDER BY completion_date DESC LIMIT 30");
            $streak_query->bind_param("i", $habit_id);
            $streak_query->execute();
            $result = $streak_query->get_result()->fetch_assoc();
            $streak = $result['count'] ?? 0;
            
            $update = $conn->prepare("UPDATE habits SET streak = ? WHERE id = ?");
            $update->bind_param("ii", $streak, $habit_id);
            $update->execute();
            
            echo json_encode(['success' => true]);
            break;

        case 'delete':
            $habit_id = $input['habit_id'];
            
            // Delete related completions
            $del_completions = $conn->prepare("DELETE FROM habit_completions WHERE habit_id = ?");
            $del_completions->bind_param("i", $habit_id);
            $del_completions->execute();
            
            // Delete habit
            $stmt = $conn->prepare("DELETE FROM habits WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ii", $habit_id, $user_id);
            $stmt->execute();
            
            echo json_encode(['success' => true]);
            break;

        case 'update':
            $habit_id = $input['habit_id'];
            $daily_days = $input['daily_days'] ?? '';
            $weekly_count = isset($input['weekly_count']) ? (int)$input['weekly_count'] : 0;
            $end_date = $input['end_date'] ?? '';

            $stmt = $conn->prepare("UPDATE habits SET name = ?, frequency = ?, category = ?, daily_days = ?, weekly_count = ?, end_date = NULLIF(?, '') WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ssssisii", $input['name'], $input['frequency'], $input['category'], $daily_days, $weekly_count, $end_date, $habit_id, $user_id);
            $stmt->execute();
            echo json_encode(['success' => true]);
            break;

        case 'get':
            $habit_id = $input['habit_id'];
            $stmt = $conn->prepare("SELECT id, name, frequency, category, daily_days, weekly_count, DATE_FORMAT(end_date, '%Y-%m-%d') as end_date FROM habits WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ii", $habit_id, $user_id);
            $stmt->execute();
            $habit = $stmt->get_result()->fetch_assoc();
            if ($habit) {
                echo json_encode(['success' => true, 'habit' => $habit]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Habit not found']);
            }
            break;

        case 'add_note':
            $habit_id = $input['habit_id'];
            $date = $input['date'];
            $notes = $input['notes'];
            
            $stmt = $conn->prepare("UPDATE habit_completions SET notes = ? WHERE habit_id = ? AND user_id = ? AND completion_date = ?");
            $stmt->bind_param("siis", $notes, $habit_id, $user_id, $date);
            $stmt->execute();
            
            echo json_encode(['success' => true]);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Unknown action']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
