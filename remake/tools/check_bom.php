<?php
$path = __DIR__ . '/../public/admin/thumbnail-config-manager.php';
if (!file_exists($path)) { echo "MISSING\n"; exit(1); }
$h = fopen($path, 'rb');
$bytes = fread($h, 16);
fclose($h);
$out = [];
for ($i = 0; $i < strlen($bytes); $i++) {
    $out[] = sprintf('%02X', ord($bytes[$i]));
}
echo implode(' ', $out) . "\n";
$enc = mb_detect_encoding($bytes, ['UTF-8', 'UTF-16LE', 'UTF-16BE', 'ASCII'], true);
echo "mb_detect_encoding: " . ($enc ?: 'unknown') . "\n";
?>