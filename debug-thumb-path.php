<?php
// 调试缩略图路径
require_once 'app/bootstrap.php';

$singleWorksDir = 'single-works';
$category = 'Paintings';
$imageName = 'ray-pic-64-1989.png';

echo "=== 调试缩略图路径 ===\n";
echo "__DIR__: " . __DIR__ . "\n";

// 模拟single-works.php中的路径构建
$thumbUrl = '/assets/images/' . $singleWorksDir . '/' . $category . '/thumbs/' . $imageName;
$thumbPath = __DIR__ . '/../../public' . $thumbUrl;

echo "thumbUrl: $thumbUrl\n";
echo "thumbPath: $thumbPath\n";
echo "文件存在: " . (file_exists($thumbPath) ? '是' : '否') . "\n";

// 尝试正确的路径
$correctThumbPath = __DIR__ . '/public' . $thumbUrl;
echo "正确路径: $correctThumbPath\n";
echo "正确路径文件存在: " . (file_exists($correctThumbPath) ? '是' : '否') . "\n";

// 实际文件路径
$actualPath = "D:/VS CODE/rzx-me/public/assets/images/single-works/Paintings/thumbs/ray-pic-64-1989.png";
echo "实际路径: $actualPath\n";
echo "实际路径文件存在: " . (file_exists($actualPath) ? '是' : '否') . "\n";