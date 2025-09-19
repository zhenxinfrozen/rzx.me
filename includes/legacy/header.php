<?php
// Backup of original includes/header.php
// Moved to includes/legacy on 2025-09-19
if (!defined('APP_RUNNING')) {
    http_response_code(403);
    exit('Forbidden');
}
$headerPath = __DIR__ . '/../views/header.php';
if (file_exists($headerPath)) {
    require_once $headerPath;
}
