<?php
session_start();
require_once '../db_config.php';

header('Content-Type: application/json');

// Check admin session
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized. Admin access required.'
    ]);
    exit;
}

// Get POST data
$userId = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;

// Validate input
if (empty($userId)) {
    echo json_encode([
        'success' => false,
        'message' => 'User ID is required'
    ]);
    exit;
}

try {
    // Toggle verification status
    $stmt = $pdo->prepare("UPDATE users SET verified = NOT verified WHERE id = :id");
    $stmt->execute([':id' => $userId]);
    
    if ($stmt->rowCount() > 0) {
        // Get new status
        $stmt = $pdo->prepare("SELECT verified, fullname FROM users WHERE id = :id");
        $stmt->execute([':id' => $userId]);
        $user = $stmt->fetch();
        
        echo json_encode([
            'success' => true,
            'message' => 'User verification status updated',
            'verified' => (bool)$user['verified'],
            'user_name' => $user['fullname']
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'User not found'
        ]);
    }
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
