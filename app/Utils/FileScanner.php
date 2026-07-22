<?php
// app/Utils/FileScanner.php
// 文件扫描工具类 - 用于扫描指定目录下的图片文件

class FileScanner {
    
    /**
     * 支持的图片格式
     */
    private static $supportedFormats = [
        'jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'
    ];
    
    /**
     * 扫描目录获取所有图片文件
     * 
     * @param string $directory 要扫描的目录路径
     * @param bool $recursive 是否递归扫描子目录
     * @return array 图片文件信息数组
     */
    public static function scanImages($directory, $recursive = false) {
        $images = [];
        
        // 确保目录存在
        if (!is_dir($directory)) {
            error_log("FileScanner: Directory does not exist: $directory");
            return $images;
        }
        
        try {
            $iterator = $recursive ? 
                new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory)) : 
                new DirectoryIterator($directory);
            
            foreach ($iterator as $file) {
                if ($file->isDot()) continue;
                
                if ($file->isFile() && self::isImageFile($file->getFilename())) {
                    $images[] = [
                        'filename' => $file->getFilename(),
                        'basename' => $file->getBasename('.' . $file->getExtension()),
                        'extension' => strtolower($file->getExtension()),
                        'path' => $file->getPathname(),
                        'relative_path' => str_replace($_SERVER['DOCUMENT_ROOT'], '', $file->getPathname()),
                        'size' => $file->getSize(),
                        'modified' => $file->getMTime(),
                        'directory' => dirname($file->getPathname())
                    ];
                }
            }
        } catch (Exception $e) {
            error_log("FileScanner: Error scanning directory $directory - " . $e->getMessage());
        }
        
        // 按文件名排序
        usort($images, function($a, $b) {
            return strcmp($a['filename'], $b['filename']);
        });
        
        return $images;
    }
    
    /**
     * 检查文件是否为支持的图片格式
     * 
     * @param string $filename 文件名
     * @return bool
     */
    public static function isImageFile($filename) {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        return in_array($extension, self::$supportedFormats);
    }
    
    /**
     * 获取目录信息
     * 
     * @param string $directory 目录路径
     * @return array|null 目录信息
     */
    public static function getDirectoryInfo($directory) {
        if (!is_dir($directory)) {
            return null;
        }
        
        $images = self::scanImages($directory);
        
        return [
            'path' => $directory,
            'name' => basename($directory),
            'image_count' => count($images),
            'total_size' => array_sum(array_column($images, 'size')),
            'last_modified' => max(array_column($images, 'modified')),
            'images' => $images
        ];
    }
    
    /**
     * 扫描多个目录
     * 
     * @param array $directories 目录数组
     * @return array 所有目录的图片信息
     */
    public static function scanMultipleDirectories($directories) {
        $result = [];
        
        foreach ($directories as $directory) {
            $info = self::getDirectoryInfo($directory);
            if ($info) {
                $result[] = $info;
            }
        }
        
        return $result;
    }
    
    /**
     * 获取支持的图片格式列表
     * 
     * @return array
     */
    public static function getSupportedFormats() {
        return self::$supportedFormats;
    }
}