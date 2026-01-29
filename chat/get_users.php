<?php
/**
 * Get Users List (AJAX endpoint)
 * Returns all users except the current user
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

// Get all users except current user
$stmt = $conn->prepare("SELECT id, name, email, status FROM users WHERE id != ? ORDER BY status DESC, name ASC");
$stmt->bind_param("i", $current_user_id);
$stmt->execute();
$result = $stmt->get_result();

$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

$stmt->close();
$conn->close();

echo json_encode(['success' => true, 'users' => $users]);
?>