<?php
/**
 * Send Message (AJAX endpoint)
 * Handles sending messages from current user to receiver
 */
require_once '../config/db.php';

// Set JSON header
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);
$receiver_id = intval($input['receiver_id'] ?? 0);
$message = trim($input['message'] ?? '');

// Validation
if ($receiver_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid receiver ID']);
    exit();
}

if (empty($message)) {
    echo json_encode(['success' => false, 'message' => 'Message cannot be empty']);
    exit();
}

$current_user_id = $_SESSION['user_id'];
$conn = getDBConnection();

// Insert message into database
$stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
$stmt->bind_param("iis", $current_user_id, $receiver_id, $message);

if ($stmt->execute()) {
    $message_id = $stmt->insert_id;
    
    // Get the inserted message details
    $stmt2 = $conn->prepare("SELECT id, sender_id, receiver_id, message, created_at FROM messages WHERE id = ?");
    $stmt2->bind_param("i", $message_id);
    $stmt2->execute();
    $result = $stmt2->get_result();
    $message_data = $result->fetch_assoc();
    
    $message_data['is_sender'] = true;
    
    $stmt2->close();
    
    echo json_encode(['success' => true, 'message' => $message_data]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to send message']);
}

$stmt->close();
$conn->close();
?>