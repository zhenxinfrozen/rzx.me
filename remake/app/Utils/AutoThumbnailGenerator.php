<?php
/**
 * 自动缩略图生成系统
 * Auto Thumbnail Generation System
 * 
 * 在加载视频数据时自动检测并生成缺失的缩略图
 */

require_once __DIR__ . '/../Utils/VideoThumbnailGenerator.php';

/**
 * 自动检测并生成缺失的视频缩略图
 * 
 * @param string $videoGalleryDir 视频画廊目录
 * @return array 包含生成结果的数组 ['generated' => 数量, 'skipped' => 数量]
 */
function auto_generate_missing_thumbnails($videoGalleryDir = null) {
    if ($videoGalleryDir === null) {
        $videoGalleryDir = __DIR__ . '/../../public/assets/videos/video-gallery';
    }
    
    $stats = [
        'generated' => 0,
        'skipped' => 0
    ];
    
    // 检查FFmpeg是否可用
    $ffmpegAvailable = VideoThumbnailGenerator::isFFmpegAvailable();
    
    if (!is_dir($videoGalleryDir)) {
        error_log("[AutoThumbnail] 视频目录不存在: {$videoGalleryDir}");
        return $stats;
    }
    
    // 获取支持的视频格式
    $videoExtensions = VideoThumbnailGenerator::getSupportedFormats();
    
    // 扫描所有子目录
    $subDirs = array_filter(glob($videoGalleryDir . '/*'), 'is_dir');
    
    foreach ($subDirs as $subDir) {
        $groupName = basename($subDir);
        
        // 扫描视频文件
        $files = scandir($subDir);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;
            
            $ext = pathinfo($file, PATHINFO_EXTENSION);
            if (!in_array($ext, $videoExtensions)) continue;
            
            $videoPath = $subDir . '/' . $file;
            $baseName = pathinfo($file, PATHINFO_FILENAME);
            $thumbnailPath = $subDir . '/' . $baseName . '.jpg';
            
            // 如果缩略图已存在，跳过（不管质量如何）
            if (file_exists($thumbnailPath)) {
                $stats['skipped']++;
                continue;
            }
            
            // 缩略图不存在，生成新的
            $success = VideoThumbnailGenerator::generateOrDefault(
                $videoPath,
                $thumbnailPath,
                $baseName,
                320
            );
            
            if ($success) {
                $stats['generated']++;
                error_log("[AutoThumbnail] ✓ 生成缩略图: {$groupName}/{$baseName}.jpg");
            }
        }
    }
    
    return $stats;
}

/**
 * 智能缩略图检查：仅在检测到新文件时才生成
 * 
 * @return array 生成统计信息
 */
function smart_thumbnail_check() {
    $cacheFile = __DIR__ . '/../storage/cache/thumbnail_check.json';
    $videoGalleryDir = __DIR__ . '/../../public/assets/videos/video-gallery';
    
    // 获取当前视频文件的修改时间戳
    $currentState = get_video_directory_state($videoGalleryDir);
    
    // 读取上次检查的状态
    $lastState = [];
    if (file_exists($cacheFile)) {
        $lastState = json_decode(file_get_contents($cacheFile), true) ?? [];
    }
    
    // 比较状态，找出新增或修改的视频
    $newVideos = [];
    foreach ($currentState as $videoFile => $timestamp) {
        if (!isset($lastState[$videoFile])) {
            $newVideos[] = $videoFile;
        }
    }
    
    $stats = [
        'generated' => 0,
        'skipped' => 0,
        'new_videos' => count($newVideos)
    ];
    
    // 如果有新视频，只为新视频生成缩略图
    if (count($newVideos) > 0) {
        error_log("[AutoThumbnail] 检测到 " . count($newVideos) . " 个新视频");
        
        // 运行缩略图生成（只处理缺失的）
        $stats = auto_generate_missing_thumbnails($videoGalleryDir);
        $stats['new_videos'] = count($newVideos);
        
        // 保存新状态
        $cacheDir = dirname($cacheFile);
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }
        file_put_contents($cacheFile, json_encode($currentState, JSON_PRETTY_PRINT));
    } else {
        // 没有新视频，跳过
        $stats['skipped'] = count($currentState);
    }
    
    return $stats;
}

/**
 * 获取视频目录的当前状态（文件列表和修改时间）
 * 
 * @param string $videoGalleryDir 视频目录
 * @return array 文件路径 => 修改时间戳
 */
function get_video_directory_state($videoGalleryDir) {
    $state = [];
    // 获取支持的视频格式
    $videoExtensions = VideoThumbnailGenerator::getSupportedFormats();
    
    if (!is_dir($videoGalleryDir)) {
        return $state;
    }
    
    $subDirs = array_filter(glob($videoGalleryDir . '/*'), 'is_dir');
    
    foreach ($subDirs as $subDir) {
        $files = scandir($subDir);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;
            
            $ext = pathinfo($file, PATHINFO_EXTENSION);
            if (!in_array($ext, $videoExtensions)) continue;
            
            $videoPath = $subDir . '/' . $file;
            $relativePath = str_replace($videoGalleryDir . '/', '', $videoPath);
            $state[$relativePath] = filemtime($videoPath);
        }
    }
    
    return $state;
}
