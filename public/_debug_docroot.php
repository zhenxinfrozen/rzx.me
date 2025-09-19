<?php
require __DIR__ . '/../includes/config.php';
require __DIR__ . '/../includes/wallpaper.php';
echo "DOCUMENT_ROOT=" . (isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] : '(none)') . "\n";
echo "PROJECT_ROOT=" . realpath(__DIR__ . '/..') . "\n";
$wp = get_random_wallpaper(['img_folder' => 'home/']);
var_dump($wp);
?>