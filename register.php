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
    
    // Handle Photo Upload
    $photoPath = null;
    $uploadDir = 'uploads/profiles/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    if (!empty($_POST['captured_photo'])) {
        // Handle captured photo (base64)
        $img = $_POST['captured_photo'];
        $img = str_replace('data:image/jpeg;base64,', '', $img);
        $img = str_replace(' ', '+', $img);
        $data = base64_decode($img);
        $fileName = 'profile_' . time() . '_' . uniqid() . '.jpg';
        $photoPath = $uploadDir . $fileName;
        file_put_contents($photoPath, $data);
    } elseif (isset($_FILES['user_photo']) && $_FILES['user_photo']['error'] === UPLOAD_ERR_OK) {
        // Handle file upload
        $ext = pathinfo($_FILES['user_photo']['name'], PATHINFO_EXTENSION);
        $fileName = 'profile_' . time() . '_' . uniqid() . '.' . $ext;
        $photoPath = $uploadDir . $fileName;
        move_uploaded_file($_FILES['user_photo']['tmp_name'], $photoPath);
    }
    
    // Insert user as unverified
    $stmt = $pdo->prepare("
        INSERT INTO users (fullname, email, password, gender, photo, otp, is_verified) 
        VALUES (?, ?, ?, ?, ?, ?, 0)
    ");
    
    $stmt->execute([
        $fullname,
        $email,
        $hashedPassword,
        $gender,
        $photoPath,
        $otp
    ]);

    // Send email using our helper function
    $mailResult = sendOTPMail($email, $fullname, $otp);
    
    if ($mailResult === true) {
        $message = 'Registration successful! OTP has been sent to your email.';
            
        echo json_encode([
            'success' => true,
            'message' => $message
        ]);
    } else {
        // Mail failed but user is created in DB (they can retry or check logs)
        echo json_encode([
            'success' => true, 
            'message' => "Registration successful, but email failed to send. Please contact support or check your connection."
        ]);
    }
    
} catch (Throwable $e) {

    echo json_encode([
        'success' => false,
        'message' => 'Registration failed: ' . $e->getMessage()
    ]);
}
?>
