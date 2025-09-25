<?php
header('Content-Type: application/json');

// 获取PHP上传配置信息
$uploadConfig = [
    'upload_max_filesize' => ini_get('upload_max_filesize'),
    'post_max_size' => ini_get('post_max_size'),
    'max_file_uploads' => ini_get('max_file_uploads'),
    'max_execution_time' => ini_get('max_execution_time'),
    'memory_limit' => ini_get('memory_limit'),
    'file_uploads' => ini_get('file_uploads') ? 'On' : 'Off'
];

// 转换为字节进行比较
function parseSize($size) {
    $unit = preg_replace('/[^bkmgtpezy]/i', '', $size);
    $size = preg_replace('/[^0-9\.]/', '', $size);
    if ($unit) {
        return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
    } else {
        return round($size);
    }
}

$uploadMaxBytes = parseSize($uploadConfig['upload_max_filesize']);
$postMaxBytes = parseSize($uploadConfig['post_max_size']);

// 检查配置问题
$issues = [];
$recommendations = [];

if ($uploadMaxBytes < 50 * 1024 * 1024) { // 小于50MB
    $issues[] = "upload_max_filesize ({$uploadConfig['upload_max_filesize']}) 小于推荐的50MB";
    $recommendations[] = "建议设置 upload_max_filesize = 50M";
}

if ($postMaxBytes < 60 * 1024 * 1024) { // 小于60MB
    $issues[] = "post_max_size ({$uploadConfig['post_max_size']}) 小于推荐的60MB";
    $recommendations[] = "建议设置 post_max_size = 60M (应大于upload_max_filesize)";
}

if ($uploadConfig['max_file_uploads'] < 20) {
    $issues[] = "max_file_uploads ({$uploadConfig['max_file_uploads']}) 可能限制批量上传";
    $recommendations[] = "建议设置 max_file_uploads = 20";
}

if ($uploadConfig['max_execution_time'] < 300) {
    $issues[] = "max_execution_time ({$uploadConfig['max_execution_time']}秒) 可能不足以处理大文件";
    $recommendations[] = "建议设置 max_execution_time = 300";
}

if ($uploadConfig['file_uploads'] === 'Off') {
    $issues[] = "文件上传功能已禁用";
    $recommendations[] = "必须设置 file_uploads = On";
}

echo json_encode([
    'config' => $uploadConfig,
    'limits' => [
        'upload_max_bytes' => $uploadMaxBytes,
        'post_max_bytes' => $postMaxBytes,
        'upload_max_mb' => round($uploadMaxBytes / (1024 * 1024), 1),
        'post_max_mb' => round($postMaxBytes / (1024 * 1024), 1)
    ],
    'issues' => $issues,
    'recommendations' => $recommendations,
    'status' => empty($issues) ? 'ok' : 'warning'
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>