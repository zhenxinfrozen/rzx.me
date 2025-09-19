<?php
// Simple config for environment and common includes
// Set APP_ENV to 'development' to enable debug, otherwise 'production'
if (!defined('APP_ENV')) {
    define('APP_ENV', getenv('APP_ENV') ?: 'production');
}

// Turn off display errors in production
if (APP_ENV === 'production') {
    ini_set('display_errors', 0);
    error_reporting(0);
} else {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}

// Common header/footer include variables (optional)
$INCLUDE_HEADER = __DIR__ . '/../public/includes/header.php';
$INCLUDE_FOOTER = __DIR__ . '/../public/includes/footer.php';

// Placeholder - create includes/header.php or override in future
// ...existing code...

?>
