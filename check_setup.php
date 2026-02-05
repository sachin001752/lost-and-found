<?php
// Database Setup Verification Script
echo "<h2>Lost & Found - Database Setup Verification</h2>";
echo "<hr>";

// Check database connection
try {
    $pdo = new PDO("mysql:host=localhost", "root", "");
    echo "✅ <strong>MySQL Connection:</strong> Success<br>";
    
    // Check if database exists
    $stmt = $pdo->query("SHOW DATABASES LIKE 'lost_and_found'");
    if ($stmt->rowCount() > 0) {
        echo "✅ <strong>Database 'lost_and_found':</strong> Exists<br>";
        
        // Connect to the database
        $pdo = new PDO("mysql:host=localhost;dbname=lost_and_found", "root", "");
        
        // Check users table
        $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
        if ($stmt->rowCount() > 0) {
            echo "✅ <strong>Table 'users':</strong> Exists<br>";
            
            // Count users
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
            $count = $stmt->fetch()['count'];
            echo "&nbsp;&nbsp;&nbsp;📊 Total users: {$count}<br>";
        } else {
            echo "❌ <strong>Table 'users':</strong> Not found<br>";
            echo "&nbsp;&nbsp;&nbsp;⚠️ Run setup_database.sql in phpMyAdmin<br>";
        }
        
        // Check items table
        $stmt = $pdo->query("SHOW TABLES LIKE 'items'");
        if ($stmt->rowCount() > 0) {
            echo "✅ <strong>Table 'items':</strong> Exists<br>";
            
            // Count items
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM items");
            $count = $stmt->fetch()['count'];
            echo "&nbsp;&nbsp;&nbsp;📊 Total items: {$count}<br>";
        } else {
            echo "❌ <strong>Table 'items':</strong> Not found<br>";
            echo "&nbsp;&nbsp;&nbsp;⚠️ Run setup_database.sql in phpMyAdmin<br>";
        }
        
        echo "<br><h3>✅ Setup Complete!</h3>";
        echo "<p>You can now <a href='registration.html'>register</a> or <a href='login.html'>login</a></p>";
        
    } else {
        echo "❌ <strong>Database 'lost_and_found':</strong> Not found<br>";
        echo "<br><h3>⚠️ Setup Required</h3>";
        echo "<ol>";
        echo "<li>Open phpMyAdmin: <a href='http://localhost/phpmyadmin' target='_blank'>http://localhost/phpmyadmin</a></li>";
        echo "<li>Click 'Import' tab</li>";
        echo "<li>Choose file: setup_database.sql</li>";
        echo "<li>Click 'Go'</li>";
        echo "<li>Refresh this page</li>";
        echo "</ol>";
    }
    
} catch (PDOException $e) {
    echo "❌ <strong>MySQL Connection:</strong> Failed<br>";
    echo "&nbsp;&nbsp;&nbsp;⚠️ Error: " . $e->getMessage() . "<br>";
    echo "<br><h3>⚠️ Action Required</h3>";
    echo "<p>Please start MySQL in XAMPP Control Panel</p>";
}

echo "<hr>";
echo "<p><small>Setup verification script - Delete this file after setup is complete</small></p>";
?>
