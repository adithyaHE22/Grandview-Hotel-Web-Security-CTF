<?php
// Database configuration for Grandview Hotel
// VULNERABILITY: Hardcoded credentials (OWASP A07:2021 - Identification and Authentication Failures)

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', ''); // Default XAMPP MySQL password
define('DB_NAME', 'grandview_hotel');

// Database connection
function getDBConnection() {
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch(PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}

// Start session for user management
session_start();

// Security headers (minimal implementation)
header("X-Content-Type-Options: nosniff");

// Application settings
define('SITE_NAME', 'Grandview Hotel');
define('SITE_URL', 'http://localhost/grandview-hotel');

// Feature flags
define('SHOW_TEST_CREDENTIALS', false);

// Admin flag hidden in comments
// flag{config_exposure_found}
?>
