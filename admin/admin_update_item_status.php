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
$itemId = isset($_POST['item_id']) ? intval($_POST['item_id']) : 0;
$status = isset($_POST['status']) ? trim($_POST['status']) : '';

// Validate inputs
if (empty($itemId) || empty($status)) {
    echo json_encode([
        'success' => false,
        'message' => 'Item ID and status are required'
    ]);
    exit;
}

// Validate status value
$validStatuses = ['Pending', 'Resolved', 'Closed'];
if (!in_array($status, $validStatuses)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid status value'
    ]);
    exit;
}

try {
    // Update item status
    $stmt = $pdo->prepare("UPDATE items SET status = :status WHERE id = :id");
    $stmt->execute([
        ':status' => $status,
        ':id' => $itemId
    ]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Item status updated successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Item not found or status unchanged'
        ]);
    }
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
