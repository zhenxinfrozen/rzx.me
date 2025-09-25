<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    if (!isset($_POST['category']) || !isset($_FILES['images'])) {
        throw new Exception('缺少必要参数');
    }
    
    $category = $_POST['category'];
    $files = $_FILES['images'];
    
    // 验证分组名称
    if (!preg_match('/^[a-zA-Z0-9_-]+$/', $category)) {
        throw new Exception('无效的分组名称');
    }
    
    // 检查目录
    $categoryDir = "../../assets/images/single-works/$category";
    $thumbsDir = "$categoryDir/thumbs";
    
    if (!is_dir($categoryDir)) {
        throw new Exception('分组目录不存在');
    }
    
    if (!is_dir($thumbsDir)) {
        mkdir($thumbsDir, 0755, true);
    }
    
    $uploadedCount = 0;
    $errors = [];
    
    // 处理多文件上传
    $fileCount = is_array($files['name']) ? count($files['name']) : 1;
    
    for ($i = 0; $i < $fileCount; $i++) {
        $fileName = is_array($files['name']) ? $files['name'][$i] : $files['name'];
        $fileTmpName = is_array($files['tmp_name']) ? $files['tmp_name'][$i] : $files['tmp_name'];
        $fileError = is_array($files['error']) ? $files['error'][$i] : $files['error'];
        $fileSize = is_array($files['size']) ? $files['size'][$i] : $files['size'];
        
        // 检查上传错误
        if ($fileError !== UPLOAD_ERR_OK) {
            $errors[] = "文件 $fileName 上传失败";
            continue;
        }
        
        // 检查文件大小 (50MB限制)
        if ($fileSize > 50 * 1024 * 1024) {
            $errors[] = "文件 $fileName 太大（超过50MB）";
            continue;
        }
        
        // 检查文件类型
        $imageInfo = getimagesize($fileTmpName);
        if (!$imageInfo) {
            $errors[] = "文件 $fileName 不是有效的图片";
            continue;
        }
        
        $allowedTypes = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF, IMAGETYPE_WEBP];
        if (!in_array($imageInfo[2], $allowedTypes)) {
            $errors[] = "文件 $fileName 格式不支持";
            continue;
        }
        
        // 生成安全的文件名
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $safeFileName = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($fileName, PATHINFO_FILENAME));
        $finalFileName = $safeFileName . '.' . $fileExt;
        
        // 避免文件名冲突
        $counter = 1;
        while (file_exists("$categoryDir/$finalFileName")) {
            $finalFileName = $safeFileName . '_' . $counter . '.' . $fileExt;
            $counter++;
        }
        
        // 移动文件
        if (move_uploaded_file($fileTmpName, "$categoryDir/$finalFileName")) {
            // 生成缩略图
            if (generateThumbnail("$categoryDir/$finalFileName", "$thumbsDir/$finalFileName", 200, 200)) {
                $uploadedCount++;
            } else {
                $errors[] = "文件 $fileName 缩略图生成失败";
            }
        } else {
            $errors[] = "文件 $fileName 保存失败";
        }
    }
    
    echo json_encode([
        'success' => $uploadedCount > 0,
        'uploaded' => $uploadedCount,
        'errors' => $errors,
        'message' => $uploadedCount > 0 ? "成功上传 $uploadedCount 张图片" : "上传失败"
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'uploaded' => 0
    ]);
}

// 生成缩略图函数
function generateThumbnail($sourcePath, $destPath, $maxWidth, $maxHeight) {
    try {
        $imageInfo = getimagesize($sourcePath);
        if (!$imageInfo) return false;
        
        $sourceWidth = $imageInfo[0];
        $sourceHeight = $imageInfo[1];
        $imageType = $imageInfo[2];
        
        // 计算缩略图尺寸
        $ratio = min($maxWidth / $sourceWidth, $maxHeight / $sourceHeight);
        $thumbWidth = round($sourceWidth * $ratio);
        $thumbHeight = round($sourceHeight * $ratio);
        
        // 创建源图像
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
            case IMAGETYPE_WEBP:
                $sourceImage = imagecreatefromwebp($sourcePath);
                break;
            default:
                return false;
        }
        
        if (!$sourceImage) return false;
        
        // 创建缩略图
        $thumbImage = imagecreatetruecolor($thumbWidth, $thumbHeight);
        
        // 保持透明度
        if ($imageType === IMAGETYPE_PNG || $imageType === IMAGETYPE_GIF) {
            imagealphablending($thumbImage, false);
            imagesavealpha($thumbImage, true);
            $transparent = imagecolorallocatealpha($thumbImage, 255, 255, 255, 127);
            imagefilledrectangle($thumbImage, 0, 0, $thumbWidth, $thumbHeight, $transparent);
        }
        
        // 重采样
        imagecopyresampled($thumbImage, $sourceImage, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $sourceWidth, $sourceHeight);
        
        // 保存缩略图
        $result = false;
        switch ($imageType) {
            case IMAGETYPE_JPEG:
                $result = imagejpeg($thumbImage, $destPath, 85);
                break;
            case IMAGETYPE_PNG:
                $result = imagepng($thumbImage, $destPath, 6);
                break;
            case IMAGETYPE_GIF:
                $result = imagegif($thumbImage, $destPath);
                break;
            case IMAGETYPE_WEBP:
                $result = imagewebp($thumbImage, $destPath, 85);
                break;
        }
        
        // 清理内存
        imagedestroy($sourceImage);
        imagedestroy($thumbImage);
        
        return $result;
        
    } catch (Exception $e) {
        return false;
    }
}
?>