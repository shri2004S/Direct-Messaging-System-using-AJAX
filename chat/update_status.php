<?php
/**
 * Update User Status (AJAX endpoint)
 * Updates user's online/offline status
 */
require_once '../config/db.php';

// Set JSON header
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

$current_user_id = $_SESSION['user_id'];
$conn = getDBConnection();

// Update user status to online
$stmt = $conn->prepare("UPDATE users SET status = 'online' WHERE id = ?");
$stmt->bind_param("i", $current_user_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update status']);
}

$stmt->close();
$conn->close();
?>