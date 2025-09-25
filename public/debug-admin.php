<?php
// 调试管理界面
echo "=== 调试管理界面 ===\n";

// 检查路径
echo "当前目录: " . __DIR__ . "\n";
echo "GalleryManager路径: " . realpath('../app/Utils/GalleryManager.php') . "\n";
echo "配置文件路径: " . realpath('../app/Config/single_works_sort.php') . "\n";

// 测试GalleryManager
require_once '../app/Utils/GalleryManager.php';
$galleryManager = new GalleryManager();

echo "\n--- 测试分组获取 ---\n";
$categories = $galleryManager->getGalleryCategories('single-works');
echo "找到的分组: " . implode(', ', $categories) . "\n";

// 测试配置文件
echo "\n--- 测试配置文件 ---\n";
$configPath = '../app/Config/single_works_sort.php';
if (file_exists($configPath)) {
    $config = require $configPath;
    echo "配置文件存在\n";
    echo "排序方式: " . ($config['sort_method'] ?? '未设置') . "\n";
    echo "自定义顺序: " . implode(', ', $config['custom_order'] ?? []) . "\n";
} else {
    echo "配置文件不存在\n";
}

// 测试图片计数
echo "\n--- 测试图片计数 ---\n";
foreach ($categories as $category) {
    $imageCount = count(glob("assets/images/single-works/$category/*.{jpg,jpeg,png,gif,webp}", GLOB_BRACE));
    echo "$category: $imageCount 张图片\n";
}