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
    // Get total items count
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM items");
    $totalItems = $stmt->fetch()['total'];
    
    // Get lost items count
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM items WHERE type = 'lost'");
    $lostItems = $stmt->fetch()['total'];
    
    // Get found items count
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM items WHERE type = 'found'");
    $foundItems = $stmt->fetch()['total'];
    
    // Get total users count
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
    $totalUsers = $stmt->fetch()['total'];
    
    // Get verified users count
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE verified = 1");
    $verifiedUsers = $stmt->fetch()['total'];
    
    // Get unverified users count
    $unverifiedUsers = $totalUsers - $verifiedUsers;
    
    // Get items by category
    $stmt = $pdo->query("SELECT category, COUNT(*) as count FROM items GROUP BY category");
    $categoryStats = [];
    while ($row = $stmt->fetch()) {
        $categoryStats[$row['category']] = $row['count'];
    }
    
    echo json_encode([
        'success' => true,
        'stats' => [
            'totalSubmissions' => $totalItems,
            'lostItems' => $lostItems,
            'foundItems' => $foundItems,
            'totalUsers' => $totalUsers,
            'verifiedUsers' => $verifiedUsers,
            'unverifiedUsers' => $unverifiedUsers,
            'categoryStats' => $categoryStats
        ]
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
