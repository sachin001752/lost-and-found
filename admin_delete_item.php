<?php
session_start();
require_once 'db_config.php';

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

// Validate input
if (empty($itemId)) {
    echo json_encode([
        'success' => false,
        'message' => 'Item ID is required'
    ]);
    exit;
}

try {
    // Get item details first (to delete photo)
    $stmt = $pdo->prepare("SELECT photo FROM items WHERE id = :id");
    $stmt->execute([':id' => $itemId]);
    $item = $stmt->fetch();
    
    if (!$item) {
        echo json_encode([
            'success' => false,
            'message' => 'Item not found'
        ]);
        exit;
    }
    
    // Delete the item from database
    $stmt = $pdo->prepare("DELETE FROM items WHERE id = :id");
    $stmt->execute([':id' => $itemId]);
    
    // Delete photo file if exists
    if (!empty($item['photo']) && file_exists($item['photo'])) {
        unlink($item['photo']);
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Item deleted successfully'
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
