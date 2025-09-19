<?php require_once __DIR__ . '/../includes/bootstrap.php'; ?>
<?php require_once __DIR__ . '/../includes/wallpaper.php'; ?>

<?php
echo "DOCUMENT_ROOT=" . (isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] : '(none)') . "\n";
echo "PROJECT_ROOT=" . realpath(__DIR__ . '/..') . "\n";

$wp = get_random_wallpaper(['img_folder' => 'home/']);
var_dump($wp);
?>