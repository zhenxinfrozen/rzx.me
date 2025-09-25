<?php
/**
 * 测试配置同步 - 验证后台配置是否能正确影响前端
 */
require_once 'app/bootstrap.php';

echo "=== 配置同步测试 ===\n\n";

// 检查配置文件
$configPath = 'app/Config/single_works_sort.php';
echo "📂 配置文件路径: $configPath\n";
echo "📝 配置文件存在: " . (file_exists($configPath) ? '是' : '否') . "\n\n";

if (file_exists($configPath)) {
    $config = require $configPath;
    echo "🔧 当前配置:\n";
    echo "  - 排序方式: " . ($config['sort_method'] ?? '未设置') . "\n";
    echo "  - 自定义顺序: " . implode(', ', $config['custom_order'] ?? []) . "\n\n";
}

// 测试 GalleryManager
$galleryManager = new GalleryManager();
$categoriesData = $galleryManager->getSortedCategories('single-works');

echo "🎯 前端实际显示顺序:\n";
foreach ($categoriesData as $index => $categoryData) {
    $displayName = $categoryData['display_name'];
    $name = $categoryData['name'];
    echo "  " . ($index + 1) . ". $displayName ($name)\n";
}

echo "\n✅ 测试完成！\n";
echo "💡 如果顺序不符合预期，请检查配置文件内容是否正确保存\n";
?>