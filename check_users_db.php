<?php
require_once 'db_config.php';
header('Content-Type: application/json');

try {
    $stmt = $pdo->query("DESCRIBE users");
    echo json_encode(['success' => true, 'columns' => $stmt->fetchAll()]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
