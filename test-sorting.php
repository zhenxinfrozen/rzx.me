<?php
/**
 * Single-Works 分组排序测试工具
 * 用于测试不同的排序配置
 */

require_once 'app/bootstrap.php';

echo "=== Single-Works 分组排序测试 ===\n\n";

$galleryManager = new GalleryManager();

// 测试不同的排序方式
$testSortMethods = ['alphabetical', 'custom_order', 'prefix_sort', 'date_modified'];

foreach ($testSortMethods as $method) {
    echo "📁 排序方式: $method\n";
    echo "─────────────────────────────\n";
    
    // 临时修改配置
    $configPath = 'app/Config/single_works_sort.php';
    $originalConfig = file_get_contents($configPath);
    $modifiedConfig = str_replace("'sort_method' => 'custom_order'", "'sort_method' => '$method'", $originalConfig);
    file_put_contents($configPath, $modifiedConfig);
    
    // 清除require缓存（如果可能）
    if (function_exists('opcache_invalidate')) {
        opcache_invalidate($configPath, true);
    }
    
    $categories = $galleryManager->getSortedCategories('single-works');
    
    foreach ($categories as $index => $categoryData) {
        $displayName = $categoryData['display_name'];
        $name = $categoryData['name'];
        $description = $categoryData['description'] ? ' - ' . $categoryData['description'] : '';
        
        echo sprintf("%d. %s (%s)%s\n", 
            $index + 1, 
            $displayName, 
            $name,
            $description
        );
    }
    
    echo "\n";
    
    // 恢复原配置
    file_put_contents($configPath, $originalConfig);
}

echo "🔧 配置文件位置: app/Config/single_works_sort.php\n";
echo "💡 你可以编辑配置文件来自定义排序顺序和显示名称\n";

// 显示当前目录结构
echo "\n📂 当前目录结构:\n";
echo "─────────────────\n";
$dirs = scandir('public/assets/images/single-works');
foreach ($dirs as $dir) {
    if ($dir === '.' || $dir === '..') continue;
    if (is_dir("public/assets/images/single-works/$dir")) {
        $count = count(glob("public/assets/images/single-works/$dir/*.{jpg,jpeg,png,gif,webp}", GLOB_BRACE));
        echo "📁 $dir ($count 张图片)\n";
    }
}