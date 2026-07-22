<?php
$files = [
    __DIR__ . '/../public/admin/thumbnail-config-manager.php',
    __DIR__ . '/../public/admin/views/layouts/header.php',
    __DIR__ . '/../public/admin/views/layouts/footer.php',
    __DIR__ . '/../public/admin/index.php'
];
foreach ($files as $f) {
    if (!file_exists($f)) { echo "$f: MISSING\n"; continue; }
    $h = fopen($f, 'rb');
    $bytes = fread($h, 16);
    fclose($h);
    $out = [];
    for ($i = 0; $i < strlen($bytes); $i++) $out[] = sprintf('%02X', ord($bytes[$i]));
    echo basename($f) . ': ' . implode(' ', array_slice($out, 0, 8)) . ' ... '; 
    $enc = mb_detect_encoding($bytes, ['UTF-8', 'UTF-16LE', 'UTF-16BE', 'ASCII'], true);
    echo 'detected=' . ($enc?:'unknown') . "\n";
}
?>