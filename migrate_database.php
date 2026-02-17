<?php
require_once 'db_config.php';

try {
    // Drop 'dob' column if it exists
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'dob'");
    if ($stmt->fetch()) {
        $pdo->exec("ALTER TABLE users DROP COLUMN dob");
        echo "Column 'dob' removed successfully.<br>";
    }

    // Drop 'address' column if it exists
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'address'");
    if ($stmt->fetch()) {
        $pdo->exec("ALTER TABLE users DROP COLUMN address");
        echo "Column 'address' removed successfully.<br>";
    }

    echo "Database cleanup completed.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
