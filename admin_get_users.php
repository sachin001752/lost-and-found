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

try {
    // Get all users with their item counts
    $sql = "SELECT 
                users.id,
                users.fullname as name,
                users.email,
                users.dob,
                users.gender,
                users.address,
                users.verified,
                users.created_at,
                COUNT(CASE WHEN items.type = 'lost' THEN 1 END) as lost_count,
                COUNT(CASE WHEN items.type = 'found' THEN 1 END) as found_count,
                COUNT(items.id) as total_items
            FROM users
            LEFT JOIN items ON users.id = items.user_id
            GROUP BY users.id
            ORDER BY users.created_at DESC";
    
    $stmt = $pdo->query($sql);
    $users = $stmt->fetchAll();
    
    // Format the response
    $formattedUsers = [];
    foreach ($users as $user) {
        $formattedUsers[] = [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'phone' => 'N/A', // Phone is stored in items, not users table
            'dob' => $user['dob'],
            'gender' => $user['gender'],
            'address' => $user['address'],
            'verified' => (bool)$user['verified'],
            'lost_count' => (int)$user['lost_count'],
            'found_count' => (int)$user['found_count'],
            'total_items' => (int)$user['total_items'],
            'created_at' => $user['created_at']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'users' => $formattedUsers,
        'count' => count($formattedUsers)
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
