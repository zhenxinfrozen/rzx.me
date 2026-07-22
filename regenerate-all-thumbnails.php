<?php
/**
 * 强制重新生成所有视频缩略图
 * Force Regenerate All Thumbnails
 * 
 * 简单暴力：删除所有缩略图，重新生成
 */

require_once __DIR__ . '/app/Utils/VideoThumbnailGenerator.php';

echo "========================================\n";
echo "强制重新生成所有缩略图\n";
echo "========================================\n\n";

// 清除缓存
$cacheFile = __DIR__ . '/app/storage/cache/thumbnail_check.json';
if (file_exists($cacheFile)) {
    unlink($cacheFile);
    echo "✓ 已清除缓存\n";
}

echo "\n开始处理...\n";
echo "----------------------------------------\n";

$videoGalleryDir = __DIR__ . '/public/assets/videos/video-gallery';
$stats = [
    'total' => 0,
    'success' => 0,
    'failed' => 0
];

$subDirs = array_filter(glob($videoGalleryDir . '/*'), 'is_dir');

// 加载视频格式配置
require_once __DIR__ . '/app/Utils/VideoThumbnailGenerator.php';
$videoExtensions = VideoThumbnailGenerator::getSupportedFormats();

foreach ($subDirs as $subDir) {
    $groupName = basename($subDir);
    echo "\n📁 {$groupName}\n";
    
    $files = scandir($subDir);
    
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        
        $ext = pathinfo($file, PATHINFO_EXTENSION);
        if (!in_array($ext, $videoExtensions)) continue;
        
        $videoPath = $subDir . '/' . $file;
        $baseName = pathinfo($file, PATHINFO_FILENAME);
        $thumbnailPath = $subDir . '/' . $baseName . '.jpg';
        
        $stats['total']++;
        
        // 删除旧缩略图
        if (file_exists($thumbnailPath)) {
            unlink($thumbnailPath);
        }
        
        // 生成新缩略图
        $success = VideoThumbnailGenerator::generateSmartThumbnail(
            $videoPath,
            $thumbnailPath,
            320
        );
        
        if ($success && file_exists($thumbnailPath)) {
            $size = filesize($thumbnailPath);
            echo "  ✓ {$baseName}.jpg (" . number_format($size) . " 字节)\n";
            $stats['success']++;
        } else {
            echo "  ✗ {$baseName}.jpg - 生成失败\n";
            $stats['failed']++;
        }
    }
}

echo "\n========================================\n";
echo "完成\n";
echo "========================================\n\n";

echo "统计:\n";
echo "  总数: {$stats['total']}\n";
echo "  成功: {$stats['success']}\n";
echo "  失败: {$stats['failed']}\n\n";

if ($stats['success'] > 0) {
    echo "✅ 所有缩略图已重新生成\n";
    echo "💡 运行以下命令更新数据库:\n";
    echo "   php app/Console/ScanVideos.php -f\n";
}
