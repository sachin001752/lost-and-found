<?php
require_once 'db_config.php';
require_once 'mailer.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$email = trim($_POST['email'] ?? '');

if (empty($email)) {
    echo json_encode(['success' => false, 'message' => 'Email is required']);
    exit;
}

try {
    // Check if user exists
    $stmt = $pdo->prepare("SELECT id, fullname FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'No account found with this email']);
        exit;
    }
    
    // Generate 6-digit OTP
    $otp = sprintf("%06d", mt_rand(1, 999999));
    
    // Update OTP in database
    $stmt = $pdo->prepare("UPDATE users SET otp = ? WHERE id = ?");
    $stmt->execute([$otp, $user['id']]);
    
    // Send OTP via email
    $mailResult = sendOTPMail($email, $user['fullname'], $otp);
    
    if ($mailResult === true) {
        echo json_encode(['success' => true, 'message' => 'OTP sent to your email!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to send email: ' . $mailResult]);
    }
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Request failed: ' . $e->getMessage()
    ]);
}
?>
