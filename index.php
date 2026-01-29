<?php
/**
 * Landing Page - Redirects to login or dashboard based on session
 */
require_once 'config/db.php';

// If user is already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome - Chat Application</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-lg">
                    <div class="card-body text-center p-5">
                        <h1 class="mb-4">💬 Chat App</h1>
                        <p class="text-muted mb-4">Connect with friends in real-time</p>
                        <div class="d-grid gap-2">
                            <a href="auth/login.php" class="btn btn-primary btn-lg">Login</a>
                            <a href="auth/register.php" class="btn btn-outline-secondary btn-lg">Register</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>