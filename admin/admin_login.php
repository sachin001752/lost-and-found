<?php
session_start();
header('Content-Type: application/json');

// Hardcoded admin credentials (same as in admin.html)
define('ADMIN_EMAIL', 'admin@me.com');
define('ADMIN_PASSWORD', 'admin1234');

// Get POST data
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? trim($_POST['password']) : '';

// Validate inputs
if (empty($email) || empty($password)) {
    echo json_encode([
        'success' => false,
        'message' => 'Please provide both email and password'
    ]);
    exit;
}

// Check credentials
if ($email === ADMIN_EMAIL && $password === ADMIN_PASSWORD) {
    // Set admin session
    $_SESSION['is_admin'] = true;
    $_SESSION['admin_email'] = $email;
    
    echo json_encode([
        'success' => true,
        'message' => 'Admin login successful',
        'admin' => [
            'email' => $email
        ]
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid admin credentials. Try admin@me.com / admin1234'
    ]);
}
?>
