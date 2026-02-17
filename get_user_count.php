<?php
require_once 'db_config.php';
header('Content-Type: application/json');

try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $row = $stmt->fetch();
    echo json_encode(['success' => true, 'count' => (int)$row['count']]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
