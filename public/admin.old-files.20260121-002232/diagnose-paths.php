<?php
/**
 * Admin 路径诊断工具
 * 检查所有图片和链接路径是否正确
 */

// 扫描所有需要检查的页面视图
$viewFiles = [
    'app/Admin/Views/pages/body-single-works.php',
    'app/Admin/Views/pages/body-sketchbook.php',
    'app/Admin/Views/pages/body-comics.php',
    'app/Admin/Views/pages/body-video-gallery.php',
];

echo "<h2>Admin 路径诊断报告</h2>";
echo "<style>
body { font-family: monospace; padding: 20px; }
.ok { color: green; }
.warning { color: orange; }
.error { color: red; }
pre { background: #f5f5f5; padding: 10px; }
</style>";

foreach ($viewFiles as $viewFile) {
    echo "<h3>" . basename($viewFile) . "</h3>";
    
    if (!file_exists($viewFile)) {
        echo "<p class='error'>❌ 文件不存在</p>";
        continue;
    }
    
    $content = file_get_contents($viewFile);
    
    // 检查相对路径引用
    preg_match_all('/(src|href)=["\']([^"\']+)["\']/', $content, $matches);
    
    $relativeCount = 0;
    $absoluteCount = 0;
    $issues = [];
    
    foreach ($matches[2] as $path) {
        if (strpos($path, '../') === 0 || strpos($path, './') === 0) {
            $relativeCount++;
            $issues[] = "相对路径: $path";
        } elseif (strpos($path, '/') === 0) {
            $absoluteCount++;
        }
    }
    
    echo "<p>找到 <span class='ok'>$absoluteCount 个绝对路径</span>，";
    if ($relativeCount > 0) {
        echo "<span class='error'>$relativeCount 个相对路径（需要修复）</span></p>";
        echo "<details><summary>查看问题</summary><pre>" . implode("\n", array_slice($issues, 0, 10)) . "</pre></details>";
    } else {
        echo "<span class='ok'>0 个相对路径</span></p>";
    }
}

// 检查 trash.php 链接
echo "<h3>特殊链接检查</h3>";
$viewContent = file_get_contents('app/Admin/Views/pages/body-single-works.php');
if (strpos($viewContent, 'href="controllers/trash.php"') !== false) {
    echo "<p class='error'>❌ 发现旧的 trash 链接：href=\"controllers/trash.php\"</p>";
    echo "<p>应该改为：href=\"/admin?page=trash\"</p>";
} else {
    echo "<p class='ok'>✅ Trash 链接正确</p>";
}

echo "<hr><p><strong>建议：</strong></p>";
echo "<ul>";
echo "<li>所有图片路径应使用 /assets/ 开头的绝对路径</li>";
echo "<li>所有页面链接应使用 /admin?page=xxx 格式</li>";
echo "<li>避免使用 ../ 或 ./ 相对路径</li>";
echo "</ul>";
