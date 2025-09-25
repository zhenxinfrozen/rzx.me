<?php
/**
 * 缩略图生成工具
 * 为single-works目录生成缩略图
 */

function generateThumbnail($sourcePath, $destPath, $maxSize = 200) {
    $imageInfo = getimagesize($sourcePath);
    if (!$imageInfo) {
        echo "无法读取图片信息: $sourcePath\n";
        return false;
    }

    $sourceWidth = $imageInfo[0];
    $sourceHeight = $imageInfo[1];
    $imageType = $imageInfo[2];

    // 计算缩略图尺寸，保持比例
    if ($sourceWidth > $sourceHeight) {
        $thumbWidth = min($maxSize, $sourceWidth);
        $thumbHeight = intval(($sourceHeight / $sourceWidth) * $thumbWidth);
    } else {
        $thumbHeight = min($maxSize, $sourceHeight);
        $thumbWidth = intval(($sourceWidth / $sourceHeight) * $thumbHeight);
    }

    // 创建源图片资源
    switch ($imageType) {
        case IMAGETYPE_JPEG:
            $sourceImage = imagecreatefromjpeg($sourcePath);
            break;
        case IMAGETYPE_PNG:
            $sourceImage = imagecreatefrompng($sourcePath);
            break;
        case IMAGETYPE_GIF:
            $sourceImage = imagecreatefromgif($sourcePath);
            break;
        default:
            echo "不支持的图片格式: $sourcePath\n";
            return false;
    }

    if (!$sourceImage) {
        echo "无法创建源图片资源: $sourcePath\n";
        return false;
    }

    // 创建缩略图
    $thumbImage = imagecreatetruecolor($thumbWidth, $thumbHeight);
    
    // 处理透明背景（PNG和GIF）
    if ($imageType == IMAGETYPE_PNG || $imageType == IMAGETYPE_GIF) {
        imagealphablending($thumbImage, false);
        imagesavealpha($thumbImage, true);
        $transparent = imagecolorallocatealpha($thumbImage, 255, 255, 255, 127);
        imagefill($thumbImage, 0, 0, $transparent);
    }

    // 重新采样
    imagecopyresampled($thumbImage, $sourceImage, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $sourceWidth, $sourceHeight);

    // 保存缩略图
    $success = false;
    switch ($imageType) {
        case IMAGETYPE_JPEG:
            $success = imagejpeg($thumbImage, $destPath, 85);
            break;
        case IMAGETYPE_PNG:
            $success = imagepng($thumbImage, $destPath, 6);
            break;
        case IMAGETYPE_GIF:
            $success = imagegif($thumbImage, $destPath);
            break;
    }

    // 清理资源
    imagedestroy($sourceImage);
    imagedestroy($thumbImage);

    if ($success) {
        echo "已生成缩略图: $destPath\n";
        return true;
    } else {
        echo "无法保存缩略图: $destPath\n";
        return false;
    }
}

// 扫描single-works目录并生成缩略图
$baseDir = dirname(__DIR__) . '/public/assets/images/single-works';

if (!is_dir($baseDir)) {
    echo "single-works目录不存在: $baseDir\n";
    exit(1);
}

$categories = scandir($baseDir);
foreach ($categories as $category) {
    if ($category === '.' || $category === '..') continue;
    
    $categoryPath = $baseDir . '/' . $category;
    if (!is_dir($categoryPath)) continue;
    
    echo "处理分类: $category\n";
    
    // 创建thumbs目录
    $thumbsDir = $categoryPath . '/thumbs';
    if (!is_dir($thumbsDir)) {
        mkdir($thumbsDir, 0755, true);
        echo "创建thumbs目录: $thumbsDir\n";
    }
    
    // 扫描图片文件
    $files = scandir($categoryPath);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..' || $file === 'thumbs') continue;
        
        $filePath = $categoryPath . '/' . $file;
        if (!is_file($filePath)) continue;
        
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) continue;
        
        $thumbPath = $thumbsDir . '/' . $file;
        
        // 如果缩略图不存在或源文件更新，则生成缩略图
        if (!file_exists($thumbPath) || filemtime($filePath) > filemtime($thumbPath)) {
            generateThumbnail($filePath, $thumbPath);
        } else {
            echo "跳过已存在的缩略图: $thumbPath\n";
        }
    }
}

echo "缩略图生成完成！\n";
?>