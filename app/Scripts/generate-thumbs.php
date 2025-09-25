<?php
/**
 * 缩略图生成脚本
 * 为sketch-dream画廊自动生成缩略图
 */

// 配置
$sourceDir = __DIR__ . '/assets/images/';
$thumbDir = __DIR__ . '/assets/images/thumbs/';
$thumbSize = 88; // 缩略图高度（与CSS匹配）
$quality = 85; // JPEG质量

// 创建thumbs目录
if (!file_exists($thumbDir)) {
    mkdir($thumbDir, 0755, true);
    echo "创建缩略图目录: $thumbDir\n";
}

// 获取所有sketch-dreams图片
$pattern = $sourceDir . 'sketch-dreams-*.jpg';
$images = glob($pattern);

echo "找到 " . count($images) . " 个sketch-dreams图片\n";

foreach ($images as $imagePath) {
    $filename = basename($imagePath);
    $thumbPath = $thumbDir . $filename;
    
    // 检查缩略图是否已存在且较新
    if (file_exists($thumbPath) && filemtime($thumbPath) >= filemtime($imagePath)) {
        echo "跳过 $filename (缩略图已存在且最新)\n";
        continue;
    }
    
    // 生成缩略图
    if (generateThumbnail($imagePath, $thumbPath, $thumbSize, $quality)) {
        echo "✅ 生成缩略图: $filename\n";
    } else {
        echo "❌ 失败: $filename\n";
    }
}

echo "缩略图生成完成！\n";

/**
 * 生成缩略图
 */
function generateThumbnail($sourcePath, $destPath, $maxHeight, $quality = 85) {
    try {
        // 获取原图信息
        $imageInfo = getimagesize($sourcePath);
        if (!$imageInfo) {
            return false;
        }
        
        $originalWidth = $imageInfo[0];
        $originalHeight = $imageInfo[1];
        $imageType = $imageInfo[2];
        
        // 计算缩略图尺寸（保持宽高比）
        $ratio = $maxHeight / $originalHeight;
        $thumbWidth = round($originalWidth * $ratio);
        $thumbHeight = $maxHeight;
        
        // 创建源图像资源
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
                return false;
        }
        
        if (!$sourceImage) {
            return false;
        }
        
        // 创建缩略图画布
        $thumbImage = imagecreatetruecolor($thumbWidth, $thumbHeight);
        
        // 处理透明度（PNG/GIF）
        if ($imageType == IMAGETYPE_PNG || $imageType == IMAGETYPE_GIF) {
            imagealphablending($thumbImage, false);
            imagesavealpha($thumbImage, true);
            $transparent = imagecolorallocatealpha($thumbImage, 255, 255, 255, 127);
            imagefill($thumbImage, 0, 0, $transparent);
        }
        
        // 重新采样生成缩略图
        imagecopyresampled(
            $thumbImage, $sourceImage,
            0, 0, 0, 0,
            $thumbWidth, $thumbHeight,
            $originalWidth, $originalHeight
        );
        
        // 保存缩略图
        $result = false;
        switch ($imageType) {
            case IMAGETYPE_JPEG:
                $result = imagejpeg($thumbImage, $destPath, $quality);
                break;
            case IMAGETYPE_PNG:
                $result = imagepng($thumbImage, $destPath);
                break;
            case IMAGETYPE_GIF:
                $result = imagegif($thumbImage, $destPath);
                break;
        }
        
        // 清理内存
        imagedestroy($sourceImage);
        imagedestroy($thumbImage);
        
        return $result;
        
    } catch (Exception $e) {
        error_log("缩略图生成错误: " . $e->getMessage());
        return false;
    }
}
?>