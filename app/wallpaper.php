<?php
/**
 * Simple wallpaper helper for this project.
 * Returns a random wallpaper info from public/assets/images/<img_folder>
 *
 * @param array $opts Supported keys:
 *   - img_folder: relative folder under public/assets/images (default: 'home/')
 * @return array|null ['file'=>string,'path'=>string,'url'=>string] or null when not found
 */
function get_random_wallpaper(array $opts = [])
{
    $img_folder = isset($opts['img_folder']) ? trim($opts['img_folder'], "/\\") : 'home';

    // Prefer public/assets/images as the base folder
    $base1 = realpath(__DIR__ . '/../public/assets/images');
    $base2 = realpath(__DIR__ . '/../assets/images');

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

    // pick random
    $idx = mt_rand(0, count($files) - 1);
    $file = $files[$idx];

    // build a web-accessible url relative to public/
    $url = 'assets/images/' . $img_folder . '/' . $file;

    return [
        'file' => $file,
        'path' => $target . DIRECTORY_SEPARATOR . $file,
        'url'  => $url,
    ];
}
