<?php
require_once 'db_config.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

// Verify that the user actually exists in the database (handles cases where DB was reset)
try {
    $checkUser = $pdo->prepare("SELECT id, fullname, email FROM users WHERE id = ?");
    $checkUser->execute([$_SESSION['user_id']]);
    $userData = $checkUser->fetch();
    if (!$userData) {
        // User doesn't exist in DB, clear session
        session_destroy();
        echo json_encode(['success' => false, 'message' => 'Session invalid or user deleted. Please login again.']);
        exit;
    }
} catch (PDOException $e) {
    // Database error
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

// Validate required fields
if (empty($type) || empty($title) || empty($category) || empty($date) || empty($location) || empty($phone) || empty($description)) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all required fields']);
    exit;
}

// Validate type
if (!in_array($type, ['lost', 'found'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid item type']);
    exit;
}

// Handle photo upload
$photoPath = null;
if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = 'uploads/';
    
    // Create uploads directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $fileExtension = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    
    if (!in_array($fileExtension, $allowedExtensions)) {
        echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, and GIF allowed']);
        exit;
    }
    
    // Generate unique filename
    $fileName = uniqid() . '_' . time() . '.' . $fileExtension;
    $photoPath = $uploadDir . $fileName;
    
    if (!move_uploaded_file($_FILES['photo']['tmp_name'], $photoPath)) {
        echo json_encode(['success' => false, 'message' => 'Failed to upload photo']);
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
    
    echo json_encode([
        'success' => true,
        'message' => 'Item posted successfully!',
        'item_id' => $pdo->lastInsertId()
    ]);
    
} catch (PDOException $e) {
    // Delete uploaded photo if database insert fails
    if ($photoPath && file_exists($photoPath)) {
        unlink($photoPath);
    }
    
    echo json_encode([
        'success' => false,
        'message' => 'Failed to post item: ' . $e->getMessage()
    ]);
}
?>
