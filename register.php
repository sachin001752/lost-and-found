<?php
require_once 'db_config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get form data
$fullname = trim($_POST['fullname'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$gender = $_POST['gender'] ?? '';

// Validate required fields
if (empty($fullname) || empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all required fields']);
    exit;
}

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address']);
    exit;
}

// Include Mailer functions
require_once 'mailer.php';

try {
    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id, is_verified FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $existingUser = $stmt->fetch();
    
    if ($existingUser) {
        if ($existingUser['is_verified']) {
            echo json_encode(['success' => false, 'message' => 'Email already registered and verified']);
            exit;
        } else {
            // User exists but not verified - allows "re-registering" to get a new OTP
            $stmt = $pdo->prepare("DELETE FROM users WHERE email = ?");
            $stmt->execute([$email]);
        }
    }
    
    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Generate 6-digit OTP
    $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    
    // Insert user as unverified
    $stmt = $pdo->prepare("
        INSERT INTO users (fullname, email, password, gender, otp, is_verified) 
        VALUES (?, ?, ?, ?, ?, 0)
    ");
    
    $stmt->execute([
        $fullname,
        $email,
        $hashedPassword,
        $gender,
        $otp
    ]);

    // Send email using our helper function
    $mailResult = sendOTPMail($email, $fullname, $otp);
    
    if ($mailResult === true) {
        $message = (MAIL_MODE === 'test') 
            ? 'Registration successful! (TEST MODE: OTP saved to otp_log.txt)' 
            : 'Registration successful! OTP has been sent to your email.';
            
        echo json_encode([
            'success' => true,
            'message' => $message
        ]);
    } else {
        // Mail failed but user is created in DB (they can retry or check logs)
        echo json_encode([
            'success' => true, 
            'message' => "Registration successful, but email failed. Error: $mailResult. (Check otp_log.txt if you are in development)."
        ]);
    }
    
} catch (PDOException $e) {

    echo json_encode([
        'success' => false,
        'message' => 'Registration failed: ' . $e->getMessage()
    ]);
}
?>
