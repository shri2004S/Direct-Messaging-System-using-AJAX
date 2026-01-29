<?php
/**
 * User Logout Handler
 */
require_once '../config/db.php';

// Update user status to offline if logged in
if (isset($_SESSION['user_id'])) {
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("UPDATE users SET status = 'offline' WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $stmt->close();
    $conn->close();
}

// Destroy session
session_unset();
session_destroy();

// Redirect to login page
header('Location: ../auth/login.php');
exit();
?>