<?php
// 简单测试
require_once 'app/Utils/GalleryManager.php';

$galleryManager = new GalleryManager();
echo "测试开始...\n";

try {
    $categories = $galleryManager->getSortedCategories('single-works');
    print_r($categories);
} catch (Exception $e) {
    echo "错误: " . $e->getMessage() . "\n";
    echo "文件: " . $e->getFile() . ":" . $e->getLine() . "\n";
}