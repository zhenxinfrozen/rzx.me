<?php
// Backup of original includes/footer.php
// Moved to includes/legacy on 2025-09-19
if (!defined('APP_RUNNING')) {
    http_response_code(403);
    exit('Forbidden');
}
$footerPath = __DIR__ . '/../views/footer.php';
if (file_exists($footerPath)) {
    require_once $footerPath;
}
