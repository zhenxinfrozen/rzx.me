<?php
/**
 * 壁纸助手类
 * 提供随机壁纸获取功能
 */
class WallpaperHelper
{
    /**
     * 获取随机壁纸信息
     * @param array $opts 选项数组，支持的键：
     *   - img_folder: public/assets/images下的相对文件夹 (默认: 'home/')
     * @return array|null ['file'=>string,'path'=>string,'url'=>string] 或null（未找到时）
     */
    public static function getRandomWallpaper(array $opts = [])
    {
        $img_folder = isset($opts['img_folder']) ? trim($opts['img_folder'], "/\\") : 'home';

        // 优先使用public/assets/images作为基础文件夹
        $base1 = realpath(__DIR__ . '/../../public/assets/images');
        $base2 = realpath(__DIR__ . '/../../assets/images');

        $base = $base1 ?: $base2 ?: null;
        if (!$base) {
            return null;
        }

        $target = rtrim($base, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $img_folder;
        if (!is_dir($target)) {
            return null;
        }

        $files = array_values(array_filter(scandir($target), function ($f) use ($target) {
            return is_file($target . DIRECTORY_SEPARATOR . $f) && preg_match('/\.(jpg|jpeg|png|gif)$/i', $f);
        }));

        if (count($files) === 0) {
            return null;
        }

        // 随机选择
        $idx = mt_rand(0, count($files) - 1);
        $file = $files[$idx];

        // 构建相对于public/的web访问URL
        $url = 'assets/images/' . $img_folder . '/' . $file;

        return [
            'file' => $file,
            'path' => $target . DIRECTORY_SEPARATOR . $file,
            'url'  => $url,
        ];
    }
}

/**
 * 向后兼容的函数封装
 * @param array $opts 选项数组
 * @return array|null
 */
function get_random_wallpaper(array $opts = [])
{
    return WallpaperHelper::getRandomWallpaper($opts);
}
