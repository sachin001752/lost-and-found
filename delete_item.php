<?php
require_once 'db_config.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$itemId = $_POST['item_id'] ?? 0;

if (empty($itemId)) {
    echo json_encode(['success' => false, 'message' => 'Item ID is required']);
    exit;
}

try {
    // Get current user details to check ownership
    $stmtUser = $pdo->prepare("SELECT email FROM users WHERE id = ?");
    $stmtUser->execute([$_SESSION['user_id']]);
    $currentUser = $stmtUser->fetch();
    $currentUserEmail = $currentUser ? $currentUser['email'] : '';

    // Get item to check ownership and photo path
    $stmt = $pdo->prepare("SELECT user_id, user_email, photo FROM items WHERE id = ?");
    $stmt->execute([$itemId]);
    $item = $stmt->fetch();
    
    if (!$item) {
        echo json_encode(['success' => false, 'message' => 'Item not found']);
        exit;
    }
    
    // Check if user owns the item (by ID or by Email for persistence across re-registration)
    $isOwner = ($item['user_id'] == $_SESSION['user_id']) || 
               (!empty($item['user_email']) && !empty($currentUserEmail) && $item['user_email'] === $currentUserEmail);

    if (!$isOwner) {
        echo json_encode(['success' => false, 'message' => 'You can only delete your own items']);
        exit;
    }
    
    // Delete photo file if exists
    if ($item['photo'] && file_exists($item['photo'])) {
        unlink($item['photo']);
    }
    
    // Delete item from database
    $stmt = $pdo->prepare("DELETE FROM items WHERE id = ?");
    $stmt->execute([$itemId]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Item deleted successfully'
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to delete item: ' . $e->getMessage()
    ]);
}
?>
