<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    $category = $_GET['category'] ?? '';
    if (empty($category)) {
        throw new Exception('分组名称不能为空');
    }
    
    // 安全检查，防止目录遍历
    $category = basename($category);
    
    // 图片目录路径
    $imageDir = "../../assets/images/single-works/$category";
    $thumbDir = "$imageDir/thumbs";
    
    $images = [];
    $supportedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    if (is_dir($imageDir)) {
        $files = scandir($imageDir);
        
        foreach ($files as $file) {
            if ($file === '.' || $file === '..' || $file === 'thumbs') continue;
            
            $filePath = "$imageDir/$file";
            $fileInfo = pathinfo($file);
            
            // 检查是否为支持的图片格式
            if (!isset($fileInfo['extension']) || 
                !in_array(strtolower($fileInfo['extension']), $supportedExtensions)) {
                continue;
            }
            
            // 检查缩略图是否存在
            $thumbPath = "$thumbDir/$file";
            $thumbUrl = "/assets/images/single-works/$category/thumbs/$file";
            
            // 如果缩略图不存在，使用原图
            if (!file_exists($thumbPath)) {
                $thumbUrl = "/assets/images/single-works/$category/$file";
            }
            
            $images[] = [
                'name' => $file,
                'path' => "/assets/images/single-works/$category/$file",
                'thumb_path' => $thumbUrl,
                'size' => filesize($filePath),
                'modified' => filemtime($filePath)
            ];
        }
    }
    
    // 按修改时间排序（最新的在前）
    usort($images, function($a, $b) {
        return $b['modified'] - $a['modified'];
    });
    
    echo json_encode([
        'success' => true,
        'category' => $category,
        'images' => $images,
        'count' => count($images)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'images' => [],
        'count' => 0
    ]);
}
?>