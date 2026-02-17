<?php
session_start();
require_once 'db_config.php';

header('Content-Type: application/json');

// Check if user is logged in via session (if you use them) or check localStorage via JS
// Here we assume the client will send the email or we use session
// For now, let's use the session or a passed email for simplicity

$email = $_GET['email'] ?? '';

if (empty($email)) {
    echo json_encode(['success' => false, 'message' => 'Email is required']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT fullname, email, gender, photo, created_at FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        echo json_encode(['success' => true, 'user' => $user]);
    } else {
        echo json_encode(['success' => false, 'message' => 'User not found']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
