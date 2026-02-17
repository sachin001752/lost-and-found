<?php
require_once 'db_config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$email = trim($_POST['email'] ?? '');
$otp = trim($_POST['otp'] ?? '');
$newPassword = $_POST['newPassword'] ?? '';

if (empty($email) || empty($otp) || empty($newPassword)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

try {
    // Verify OTP
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND otp = ?");
    $stmt->execute([$email, $otp]);
    $user = $stmt->fetch();
    
    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'Invalid OTP']);
        exit;
    }
    
    // Hash new password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    
    // Update password and clear OTP
    $stmt = $pdo->prepare("UPDATE users SET password = ?, otp = NULL, is_verified = 1 WHERE id = ?");
    $stmt->execute([$hashedPassword, $user['id']]);
    
    // Fetch updated user info for session
    $stmt = $pdo->prepare("SELECT id, fullname, email FROM users WHERE id = ?");
    $stmt->execute([$user['id']]);
    $updatedUser = $stmt->fetch();

    // Set session
    $_SESSION['user_id'] = $updatedUser['id'];
    $_SESSION['user_email'] = $updatedUser['email'];
    $_SESSION['user_name'] = $updatedUser['fullname'];

    echo json_encode([
        'success' => true,
        'message' => 'Password reset successful! Logging you in...',
        'user' => [
            'email' => $updatedUser['email'],
            'name' => $updatedUser['fullname']
        ]
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Reset failed: ' . $e->getMessage()
    ]);
}
?>
