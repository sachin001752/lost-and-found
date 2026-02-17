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

try {
    // Get filter parameters
    $type = isset($_GET['type']) ? $_GET['type'] : '';
    $category = isset($_GET['category']) ? $_GET['category'] : '';
    $status = isset($_GET['status']) ? $_GET['status'] : '';
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    
    // Original query restored: Admin can see ALL user submissions
    $sql = "SELECT 
                items.*, 
                users.fullname as user_name,
                users.email as user_email
            FROM items 
            LEFT JOIN users ON items.user_id = users.id 
            WHERE 1=1";
    
    $params = [];
    
    // Apply filters
    if (!empty($type)) {
        $sql .= " AND items.type = :type";
        $params[':type'] = $type;
    }
    
    if (!empty($category)) {
        $sql .= " AND items.category = :category";
        $params[':category'] = $category;
    }
    
    if (!empty($status)) {
        $sql .= " AND items.status = :status";
        $params[':status'] = $status;
    }
    
    // Apply search
    if (!empty($search)) {
        $sql .= " AND (items.title LIKE :search1 
                    OR items.category LIKE :search2 
                    OR items.location LIKE :search3 
                    OR items.description LIKE :search4
                    OR users.fullname LIKE :search5
                    OR users.email LIKE :search6)";
        $searchParam = '%' . $search . '%';
        $params[':search1'] = $searchParam;
        $params[':search2'] = $searchParam;
        $params[':search3'] = $searchParam;
        $params[':search4'] = $searchParam;
        $params[':search5'] = $searchParam;
        $params[':search6'] = $searchParam;
    }
    
    $sql .= " ORDER BY items.created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $items = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'items' => $items,
        'count' => count($items)
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
