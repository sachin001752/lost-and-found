<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1); // Display errors for debugging

require_once 'db_config.php';

$logFile = __DIR__ . '/debug_item.log';
function logMsg($msg) {
    global $logFile;
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - " . $msg . "\n", FILE_APPEND);
}

header('Content-Type: application/json');

logMsg("=== New API Request ===");

// Check if user is logged in
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

logMsg("Session User ID: " . ($_SESSION['user_id'] ?? 'Not set'));

if (!isset($_SESSION['user_id'])) {
    logMsg("Error: User not logged in");
    echo json_encode(['success' => false, 'message' => 'Please login first (Session missing)']);
    exit;
}

// Verify user in DB
try {
    $checkUser = $pdo->prepare("SELECT id, fullname, email FROM users WHERE id = ?");
    $checkUser->execute([$_SESSION['user_id']]);
    $userData = $checkUser->fetch();
    if (!$userData) {
        logMsg("Error: User ID " . $_SESSION['user_id'] . " not found in DB");
        session_destroy();
        echo json_encode(['success' => false, 'message' => 'Session invalid. Please login again.']);
        exit;
    }
} catch (PDOException $e) {
    logMsg("DB Error checking user: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get form data
$type = $_POST['type'] ?? '';
$title = trim($_POST['title'] ?? '');
$category = $_POST['category'] ?? '';
$date = $_POST['date'] ?? '';
$location = trim($_POST['location'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$description = trim($_POST['description'] ?? '');

logMsg("Data - Type: $type, Title: $title");

// Validate required fields
if (empty($type) || empty($title) || empty($category) || empty($date) || empty($location) || empty($phone) || empty($description)) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all required fields']);
    exit;
}

// Handle photo upload
$photoPath = null;
if (isset($_FILES['photo'])) {
    logMsg("File upload detected: " . print_r($_FILES['photo'], true));
    
    if ($_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/uploads/';
        
        // Ensure directory exists and is writable
        if (!is_dir($uploadDir)) {
            logMsg("Creating uploads directory: $uploadDir");
            if (!mkdir($uploadDir, 0777, true)) {
                 logMsg("Error: Failed to create uploads directory");
                 echo json_encode(['success' => false, 'message' => 'Server Configuration Error: Cannot create upload folder']);
                 exit;
            }
        }
        
        if (!is_writable($uploadDir)) {
             logMsg("Error: Directory not writable, attempting chmod");
             chmod($uploadDir, 0777);
             if (!is_writable($uploadDir)) {
                 logMsg("Error: Still not writable");
                 echo json_encode(['success' => false, 'message' => 'Server Permission Error: Upload folder not writable']);
                 exit;
             }
        }
        
        $fileExtension = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (!in_array($fileExtension, $allowedExtensions)) {
            echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, and GIF allowed']);
            exit;
        }
        
        // Generate unique filename
        $fileName = uniqid() . '_' . time() . '.' . $fileExtension;
        $absolutePath = $uploadDir . $fileName;
        $relativePath = 'uploads/' . $fileName;
        
        if (move_uploaded_file($_FILES['photo']['tmp_name'], $absolutePath)) {
            logMsg("File uploaded successfully to: $absolutePath");
            $photoPath = $relativePath;
        } else {
            logMsg("Error: move_uploaded_file failed");
            echo json_encode(['success' => false, 'message' => 'Failed to save uploaded file. Server error.']);
            exit;
        }
    } else if ($_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
        logMsg("Upload Error Code: " . $_FILES['photo']['error']);
        echo json_encode(['success' => false, 'message' => 'Photo upload error code: ' . $_FILES['photo']['error']]);
        exit;
    }
}

try {
    // Insert item
    $stmt = $pdo->prepare("
        INSERT INTO items (user_id, type, title, category, date, location, phone, description, photo, user_email, user_name) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $_SESSION['user_id'],
        $type,
        $title,
        $category,
        $date,
        $location,
        $phone,
        $description,
        $photoPath,
        $userData['email'],
        $userData['fullname']
    ]);
    
    $newItemId = $pdo->lastInsertId();
    logMsg("Item inserted successfully. ID: " . $newItemId);
    
    echo json_encode([
        'success' => true,
        'message' => 'Item posted successfully!',
        'item_id' => $newItemId
    ]);
    
} catch (PDOException $e) {
    logMsg("DB Insert Error: " . $e->getMessage());
    
    // Delete uploaded photo if database insert fails
    if ($photoPath) {
        $absPath = __DIR__ . '/' . $photoPath;
        if (file_exists($absPath)) {
            unlink($absPath);
        }
    }
    
    echo json_encode([
        'success' => false,
        'message' => 'Database Error: ' . $e->getMessage()
    ]);
}
?>
