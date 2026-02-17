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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['item_id'] ?? '';
    $title = $_POST['title'] ?? '';
    $category = $_POST['category'] ?? '';
    $location = $_POST['location'] ?? '';
    $description = $_POST['description'] ?? '';
    $status = $_POST['status'] ?? '';
    $type = $_POST['type'] ?? '';

    if (empty($id)) {
        echo json_encode(['success' => false, 'message' => 'Item ID is required']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("
            UPDATE items 
            SET title = ?, category = ?, location = ?, description = ?, status = ?, type = ?
            WHERE id = ?
        ");
        
        $stmt->execute([$title, $category, $location, $description, $status, $type, $id]);

        echo json_encode([
            'success' => true,
            'message' => 'Item updated successfully'
        ]);
        
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
