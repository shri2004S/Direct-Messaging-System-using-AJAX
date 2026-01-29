<?php
/**
 * Chat Dashboard - Main Chat Interface
 */
require_once 'config/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'User'; // Added fallback

// If user_name is not set, fetch it from database
if (!isset($_SESSION['user_name'])) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user_data = $result->fetch_assoc();
        $_SESSION['user_name'] = $user_data['name'];
        $user_name = $user_data['name'];
    }
    
    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Header -->
    <nav class="navbar navbar-dark bg-primary">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1">💬 Chat App</span>
            <div class="d-flex align-items-center">
                <span class="text-white me-3">Welcome, <?php echo htmlspecialchars($user_name); ?></span>
                <a href="auth/logout.php" class="btn btn-outline-light btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Main Chat Container -->
    <div class="container-fluid chat-container">
        <div class="row h-100">
            <!-- User List Sidebar -->
            <div class="col-md-4 col-lg-3 p-0 border-end bg-light">
                <div class="users-header p-3 border-bottom bg-white">
                    <h5 class="mb-0">Users</h5>
                </div>
                <div class="users-list" id="usersList">
                    <!-- Users will be loaded here via AJAX -->
                    <div class="text-center p-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Chat Area -->
            <div class="col-md-8 col-lg-9 p-0 d-flex flex-column">
                <div class="chat-area" id="chatArea">
                    <!-- Default state when no user is selected -->
                    <div class="no-chat-selected">
                        <div class="text-center">
                            <h3 class="text-muted">Select a user to start chatting</h3>
                            <p class="text-muted">Choose someone from the users list</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chat Template (Hidden, will be cloned by JavaScript) -->
    <template id="chatTemplate">
        <div class="chat-header p-3 border-bottom bg-white d-flex align-items-center">
            <div class="flex-grow-1">
                <h5 class="mb-0" id="chatUserName"></h5>
                <small class="text-muted" id="chatUserStatus"></small>
            </div>
        </div>
        <div class="messages-container flex-grow-1 p-3" id="messagesContainer">
            <!-- Messages will be loaded here -->
        </div>
        <div class="message-input-container p-3 border-top bg-white">
            <form id="messageForm" class="d-flex gap-2">
                <input type="text" class="form-control" id="messageInput" 
                       placeholder="Type a message..." autocomplete="off" required>
                <button type="submit" class="btn btn-primary">Send</button>
            </form>
        </div>
    </template>

    <script>
        // Pass PHP session data to JavaScript
        const currentUserId = <?php echo $user_id; ?>;
        const currentUserName = <?php echo json_encode($user_name); ?>;
    </script>
    <script src="assets/js/chat.js"></script>
</body>
</html>