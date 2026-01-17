<?php
/**
 * 批量重新生成视频缩略图
 * 删除现有的自动生成的缩略图，使用智能提取重新生成
 */

require_once __DIR__ . '/app/Utils/VideoThumbnailGenerator.php';

echo "========================================\n";
echo "批量重新生成视频缩略图工具\n";
echo "========================================\n\n";

// 检查FFmpeg
if (!VideoThumbnailGenerator::isFFmpegAvailable()) {
    echo "❌ 错误: FFmpeg未安装或不可用\n";
    echo "此工具需要FFmpeg来提取视频帧\n";
    echo "请先安装FFmpeg: https://ffmpeg.org/download.html\n\n";
    exit(1);
}

echo "✅ FFmpeg可用\n\n";

$videoDir = __DIR__ . '/public/assets/videos/video-gallery';

if (!is_dir($videoDir)) {
    echo "❌ 错误: 视频目录不存在\n";
    echo "目录: {$videoDir}\n";
    exit(1);
}

echo "📂 扫描视频目录: {$videoDir}\n\n";

$videoExtensions = ['mp4', 'webm', 'mov', 'avi', 'ogv'];
$subDirs = glob($videoDir . '/*', GLOB_ONLYDIR);

$totalProcessed = 0;
$totalRegenerated = 0;
$totalFailed = 0;

foreach ($subDirs as $subDir) {
    $groupName = basename($subDir);
    echo "📁 处理分组: {$groupName}\n";
    
    $files = scandir($subDir);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if (!in_array($ext, $videoExtensions)) continue;
        
        $videoPath = $subDir . '/' . $file;
        $baseName = pathinfo($file, PATHINFO_FILENAME);
        $thumbnailPath = $subDir . '/' . $baseName . '.jpg';
        
        $totalProcessed++;
        
        // 检查是否已有缩略图
        $hadThumbnail = file_exists($thumbnailPath);
        
        // 删除旧缩略图（如果存在）
        if ($hadThumbnail) {
            unlink($thumbnailPath);
            echo "  🗑️  删除旧缩略图: {$baseName}.jpg\n";
        }
        
        // 使用智能提取生成新缩略图
        echo "  🎨 生成缩略图: {$file}";
        
        // 获取视频时长用于显示
        $duration = VideoThumbnailGenerator::getVideoDuration($videoPath);
        if ($duration !== false) {
            echo " [时长: " . round($duration, 1) . "秒]";
        }
        
        if (VideoThumbnailGenerator::generateSmartThumbnail($videoPath, $thumbnailPath, 320)) {
            echo " ✅\n";
            $totalRegenerated++;
        } else {
            echo " ❌\n";
            $totalFailed++;
        }
    }
    
    echo "\n";
}

echo "========================================\n";
echo "处理完成\n";
echo "========================================\n\n";
echo "总视频数: {$totalProcessed}\n";
echo "成功生成: {$totalRegenerated}\n";
echo "失败数量: {$totalFailed}\n\n";

if ($totalRegenerated > 0) {
    echo "✅ 已使用智能提取重新生成所有缩略图\n";
    echo "   提取策略: 自动跳过开头黑屏，在视频25%位置提取\n\n";
}

if ($totalFailed > 0) {
    echo "⚠️  部分视频缩略图生成失败，请检查视频文件是否损坏\n\n";
}

echo "下一步: 运行扫描工具更新数据库\n";
echo "  php app/Console/ScanVideos.php -f\n\n";
