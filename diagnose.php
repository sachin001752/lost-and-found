<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db_config.php';

echo "<h2>Diagnostic Report</h2>";

// 1. Check Database
try {
    $stmt = $pdo->query("SELECT 1");
    echo "<p style='color:green'>Database Connection: OK</p>";
} catch (PDOException $e) {
    echo "<p style='color:red'>Database Connection: FAILED (" . $e->getMessage() . ")</p>";
}

// 2. Check Session
if (session_status() === PHP_SESSION_ACTIVE) {
    echo "<p style='color:green'>Session Status: ACTIVE</p>";
    if (isset($_SESSION['user_id'])) {
        echo "<p style='color:green'>User Logged In: YES (ID: " . $_SESSION['user_id'] . ")</p>";
    } else {
        echo "<p style='color:red'>User Logged In: NO (Session ID missing)</p>";
        echo "<p>NOTE: You seem to be logged out in PHP. Please login again.</p>";
    }
} else {
    echo "<p style='color:red'>Session Status: INACTIVE</p>";
}

// 3. Check Table Structure
try {
    $stmt = $pdo->query("DESCRIBE items");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "<p style='color:green'>Table 'items' exists. Columns: " . implode(', ', $columns) . "</p>";
} catch (PDOException $e) {
    echo "<p style='color:red'>Table 'items' check failed: " . $e->getMessage() . "</p>";
}

// 4. Check Upload Directory
$uploadDir = __DIR__ . '/uploads/';
if (is_dir($uploadDir)) {
    echo "<p style='color:green'>Upload Directory: EXISTS ($uploadDir)</p>";
    if (is_writable($uploadDir)) {
        echo "<p style='color:green'>Upload Directory Writable: YES</p>";
    } else {
        echo "<p style='color:red'>Upload Directory Writable: NO</p>";
    }
} else {
    echo "<p style='color:orange'>Upload Directory: MISSING (Will be created by script)</p>";
}
// 5. Check Test Insert
if (isset($_GET['test_insert'])) {
    try {
        $stmt = $pdo->prepare("INSERT INTO items (user_id, type, title, category, date, location, phone, description, user_email, user_name) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_SESSION['user_id'] ?? 37,
            'lost',
            'Test Item ' . time(),
            'Electronics',
            date('Y-m-d'),
            'Test Location',
            '1234567890',
            'This is a diagnostic test insert',
            'test@example.com',
            'Test User'
        ]);
        echo "<p style='color:green; font-weight:bold;'>TEST INSERT: SUCCESS! Deleted the test row immediately.</p>";
        $pdo->exec("DELETE FROM items WHERE title LIKE 'Test Item %'");
    } catch (PDOException $e) {
        echo "<p style='color:red; font-weight:bold;'>TEST INSERT: FAILED (" . $e->getMessage() . ")</p>";
    }
}
echo "<p><a href='?test_insert=1' style='padding:10px; background:#667eea; color:white; text-decoration:none; border-radius:5px;'>Click Here to Test Database Insert</a></p>";
?>
