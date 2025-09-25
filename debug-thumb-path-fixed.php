<?php
// 调试修复后的缩略图路径
require_once 'app/bootstrap.php';

$singleWorksDir = 'single-works';
$category = 'Paintings';
$imageName = 'ray-pic-64-1989.png';

echo "=== 调试修复后的缩略图路径 ===\n";

// 模拟修复后single-works.php中的路径构建（从app/Views/pages/目录）
$thumbUrl = '/assets/images/' . $singleWorksDir . '/' . $category . '/thumbs/' . $imageName;

// 模拟从 app/Views/pages/ 目录构建路径
$baseDir = __DIR__ . '/app/Views/pages';
$thumbPath = $baseDir . '/../../../public' . $thumbUrl;

echo "thumbUrl: $thumbUrl\n";
echo "从pages目录构建的路径: $thumbPath\n";
echo "文件存在: " . (file_exists($thumbPath) ? '是' : '否') . "\n";

// 标准化路径
$realPath = realpath($thumbPath);
echo "标准化路径: $realPath\n";
echo "标准化路径文件存在: " . ($realPath && file_exists($realPath) ? '是' : '否') . "\n";