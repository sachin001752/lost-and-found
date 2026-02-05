<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'lost_and_found');

// SMTP configuration for PHPMailer
// Set MAIL_MODE to 'live' for actual emails, or 'test' to only log OTPs locally
define('MAIL_MODE', 'test'); 

define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'modisachin705@gmail.com'); // Put your Gmail address here
define('SMTP_PASS', 'yazr lkhi goza apdt'); // Put your Gmail App Password here
define('SMTP_FROM_EMAIL', 'modisachin705@gmail.com');

define('SMTP_FROM_NAME', 'Kalri Lost & Found');




// Create database connection
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die(json_encode([
        'success' => false,
        'message' => 'Database connection failed: ' . $e->getMessage()
    ]));
}

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
