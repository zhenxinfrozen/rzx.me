<?php
/**
 * 通用助手函数集合
 * 整合常用的工具函数，避免代码重复
 */

/**
 * 调试输出函数
 * @param mixed $var 要输出的变量
 */
function debug_dump($var) {
    echo '<pre>'; 
    var_dump($var); 
    echo '</pre>';
}

/**
 * 安全地获取数组项
 * @param array $arr 数组
 * @param string $key 键名
 * @param mixed $default 默认值
 * @return mixed
 */
function arr_get(array $arr, $key, $default = null) {
    return array_key_exists($key, $arr) ? $arr[$key] : $default;
}

/**
 * 格式化文件大小
 * @param int $bytes 字节数
 * @param int $precision 精度
 * @return string
 */
function format_bytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    
    for ($i = 0; $bytes >= 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, $precision) . ' ' . $units[$i];
}

/**
 * 安全的路径拼接
 * @param string ...$paths 路径片段
 * @return string
 */
function safe_path_join(...$paths) {
    $result = '';
    foreach ($paths as $path) {
        if ($path !== '') {
            if ($result !== '') {
                $result .= DIRECTORY_SEPARATOR;
            }
            $result .= trim($path, DIRECTORY_SEPARATOR);
        }
    }
    return $result;
}

/**
 * 生成链接 URL
 * 根据配置决定是否使用伪静态链接
 * 
 * @param string $path 路径，例如 '/about'
 * @param array $params 查询参数
 * @return string
 */
function url($path = '', array $params = []) {
    // 处理空路径
    if (empty($path)) {
        $path = '/';
    }

    // 确保 path 以 / 开头
    if (strpos($path, '/') !== 0) {
        $path = '/' . $path;
    }

    // 获取配置，默认开启伪静态
    $usePrettyUrls = config('app.use_pretty_urls', true);

    // 构建基础 URL
    if ($usePrettyUrls) {
        $url = $path;
    } else {
        // 使用查询参数模式
        // 在非伪静态模式下，所有请求都通过入口文件 index.php
        $base = '/index.php'; 
        
        if ($path === '/') {
            $url = $base;
        } else {
            $url = $base . '?route=' . $path;
        }
    }

    // 添加额外的查询参数
    if (!empty($params)) {
        // 检查是否已经有 ?
        $separator = (strpos($url, '?') !== false) ? '&' : '?';
        $queryString = http_build_query($params);
        $url .= $separator . $queryString;
    }

    return $url;
}

/**
 * 检查文件是否为图片
 * @param string $filePath 文件路径
 * @return bool
 */
function is_image($filePath) {
    $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'];
    $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    return in_array($extension, $imageExtensions);
}

/**
 * 生成安全的文件名
 * @param string $filename 原文件名
 * @return string
 */
function sanitize_filename($filename) {
    // 移除或替换不安全的字符
    $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
    // 移除多余的下划线
    $filename = preg_replace('/_+/', '_', $filename);
    // 移除开头和结尾的下划线和点号
    $filename = trim($filename, '_.');
    
    return $filename;
}

/**
 * 获取随机壁纸信息（从WallpaperHelper迁移的功能）
 * @param array $opts 选项数组
 * @return array|null
 */
function get_random_wallpaper(array $opts = []) {
    // 调用WallpaperHelper类的方法
    require_once __DIR__ . '/WallpaperHelper.php';
    return WallpaperHelper::getRandomWallpaper($opts);
}