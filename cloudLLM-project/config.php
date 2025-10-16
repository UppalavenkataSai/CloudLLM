<?php
/**
 * Aviz Academy Configuration File
 * Author: Avinash Reddy Thipparthi
 * 
 * This file contains database configuration and other settings.
 * In production, these should be environment variables or stored securely.
 */

// Database Configuration
define('DB_HOST', 'capstone.ursamigo.shop');
define('DB_USERNAME', 'capstoneuser');
define('DB_PASSWORD', 'Avinash12345');
define('DB_NAME', 'capstone');

// Application Settings
define('APP_NAME', 'Aviz Academy');
define('APP_VERSION', '2.0');
define('DEBUG_MODE', false);

// CloudFront Configuration
define('CLOUDFRONT_DOMAIN', 'd211a3m6dyhyfh.cloudfront.net');

// Security Settings
define('ENABLE_CSRF_PROTECTION', true);
define('SESSION_TIMEOUT', 3600); // 1 hour

// Error Reporting
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

/**
 * Get database connection
 * @return mysqli Database connection object
 */
function getDbConnection() {
    $mysqli = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
    
    if ($mysqli->connect_errno) {
        if (DEBUG_MODE) {
            die('Database connection failed: ' . $mysqli->connect_error);
        } else {
            die('Database connection failed. Please try again later.');
        }
    }
    
    // Set charset to prevent character encoding issues
    $mysqli->set_charset("utf8");
    
    return $mysqli;
}

/**
 * Sanitize input data
 * @param string $data Input data to sanitize
 * @return string Sanitized data
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Generate CSRF token
 * @return string CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 * @param string $token Token to verify
 * @return bool True if valid, false otherwise
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
?>