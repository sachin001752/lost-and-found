<?php
require_once 'db_config.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

// Get query parameters
$type = $_GET['type'] ?? 'all'; // 'lost', 'found', or 'all'
$myPosts = isset($_GET['my_posts']) && $_GET['my_posts'] === 'true';

// Get current user email for filtering my posts
$currentUserEmail = '';
try {
    $stmt = $pdo->prepare("SELECT email FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    $currentUserEmail = $user ? $user['email'] : '';
} catch (PDOException $e) {}

try {
    // Build query - use LEFT JOIN and COALESCE to ensure data remains visible even if user is gone
    $sql = "
        SELECT 
            i.*,
            COALESCE(u.fullname, i.user_name) as user_name,
            COALESCE(u.email, i.user_email) as user_email
        FROM items i
        LEFT JOIN users u ON i.user_id = u.id
    ";
    
    $conditions = [];
    $params = [];
    
    // Filter by type
    if ($type !== 'all' && in_array($type, ['lost', 'found'])) {
        $conditions[] = "i.type = ?";
        $params[] = $type;
    }
    
    // Filter by user for "my posts" - Link by EMAIL instead of transient user_id
    if ($myPosts && !empty($currentUserEmail)) {
        $conditions[] = "(i.user_id = ? OR i.user_email = ?)";
        $params[] = $_SESSION['user_id'];
        $params[] = $currentUserEmail;
    }
    
    // Add WHERE clause if conditions exist
    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(" AND ", $conditions);
    }
    
    // Order by newest first
    $sql .= " ORDER BY i.created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $items = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'items' => $items
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch items: ' . $e->getMessage()
    ]);
}
?>
