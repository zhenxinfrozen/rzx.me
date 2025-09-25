<?php
// regenerate-thumbs.php - 重新生成small-works目录的缩略图
require_once 'app/Utils/GalleryManager.php';

$gm = new GalleryManager();
$categories = $gm->getGalleryCategories('single-works');
echo 'Found categories: ' . implode(', ', $categories) . PHP_EOL;

foreach ($categories as $cat) {
    echo "\nProcessing category: $cat" . PHP_EOL;
    $images = $gm->getCategoryImages('single-works', $cat);
    echo "Found " . count($images) . " images in $cat" . PHP_EOL;
    
    if (count($images) > 0) {
        echo "Generating thumbnails for $cat..." . PHP_EOL;
        $result = $gm->generateThumbnails('single-works/' . $cat);
        echo 'Generated ' . count($result) . ' thumbnails for ' . $cat . PHP_EOL;
    }
}