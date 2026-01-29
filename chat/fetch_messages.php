<?php
/**
 * Fetch Messages (AJAX endpoint)
 * Returns all messages between current user and selected user
 */
require_once '../config/db.php';

// Set JSON header
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

// Get receiver ID from request
$receiver_id = intval($_GET['receiver_id'] ?? 0);

if ($receiver_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid receiver ID']);
    exit();
}

$current_user_id = $_SESSION['user_id'];
$conn = getDBConnection();

// Fetch all messages between current user and receiver
$stmt = $conn->prepare("
    SELECT m.id, m.sender_id, m.receiver_id, m.message, m.created_at, u.name as sender_name
    FROM messages m
    JOIN users u ON m.sender_id = u.id
    WHERE (m.sender_id = ? AND m.receiver_id = ?) 
       OR (m.sender_id = ? AND m.receiver_id = ?)
    ORDER BY m.created_at ASC
");

$stmt->bind_param("iiii", $current_user_id, $receiver_id, $receiver_id, $current_user_id);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = [
        'id' => $row['id'],
        'sender_id' => $row['sender_id'],
        'receiver_id' => $row['receiver_id'],
        'message' => $row['message'],
        'sender_name' => $row['sender_name'],
        'created_at' => $row['created_at'],
        'is_sender' => ($row['sender_id'] == $current_user_id)
    ];
}

$stmt->close();
$conn->close();

echo json_encode(['success' => true, 'messages' => $messages]);
?>