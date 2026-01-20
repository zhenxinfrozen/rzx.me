#!/usr/bin/env php
<?php
/**
 * 检测哪些文件包含乱码（快速版本）
 */

$files = [
    'app/Admin/Views/pages/_thumbnail-center-batch.php',
    'app/Admin/Views/pages/_thumbnail-center-config.php',
    'app/Admin/Views/pages/thumbnail-center.php',
    'app/Admin/Views/pages/body-single-works.php',
    'app/Admin/Views/pages/body-sketchbook.php',
    'app/Admin/Views/pages/body-video-gallery.php',
    'app/Admin/Views/pages/body-comics.php',
];

echo "检测乱码文件...\n\n";

foreach ($files as $file) {
    if (!file_exists($file)) {
        echo "[-] $file - 文件不存在\n";
        continue;
    }
    
    $content = file_get_contents($file);
    $first200 = mb_substr($content, 0, 200);
    
    // 检测常见的乱码模式
    if (preg_match('/[缂╃暐鍥句腑蹇?鎵归噺绠＄悊閮ㄥ垎姝ｅ湪鍔犺浇娓呯悊鎵€鏈変腑蹇?]/u', $first200)) {
        echo "[❌] $file - 检测到乱码！\n";
        echo "     前50个字符: " . mb_substr($first200, 0, 50) . "\n\n";
    } else {
        echo "[✅] $file - 看起来正常\n";
    }
}

echo "\n需要恢复的文件请从宝塔服务器下载！\n";
