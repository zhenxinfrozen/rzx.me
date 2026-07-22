<?php
/**
 * 视频目录扫描工具
 * 
 * 用法：
 * php app/Console/ScanVideos.php
 * php app/Console/ScanVideos.php --force              # 强制重新扫描
 * php app/Console/ScanVideos.php --generate-thumbs    # 生成缺失的缩略图
 * php app/Console/ScanVideos.php -f -t               # 强制扫描并生成缩略图
 */

require_once __DIR__ . '/../Models/video_data.php';

echo "\n========================================\n";
echo "视频目录扫描工具\n";
echo "========================================\n\n";

// 检查命令行参数
$force = in_array('--force', $argv) || in_array('-f', $argv);
$generateThumbs = in_array('--generate-thumbs', $argv) || in_array('-t', $argv);

if ($generateThumbs) {
    echo "🎨 自动生成缩略图模式已启用\n";
}

if ($force) {
    echo "🔄 强制重新扫描模式\n\n";
} else {
    echo "📂 检查现有数据...\n\n";
    $existingGroups = get_all_video_groups();
    if (!empty($existingGroups)) {
        echo "⚠️  发现现有视频数据 (" . count($existingGroups) . " 个分组)\n";
        echo "如需重新扫描，请使用 --force 参数\n";
        if ($generateThumbs) {
            echo "将继续生成缺失的缩略图...\n";
        }
        echo "\n";
        
        foreach ($existingGroups as $groupId => $group) {
            $videoCount = count($group['videos'] ?? []);
            echo "  - {$group['title']} ({$groupId}): {$videoCount} 个视频\n";
        }
        
        if (!$generateThumbs) {
            exit(0);
        }
    }
}

// 开始扫描
echo "🔍 开始扫描视频目录...\n";
echo "目录: " . VIDEO_GALLERY_DIR . "\n";
if ($generateThumbs) {
    echo "缩略图生成: 启用\n";
}
echo "\n";

if (!is_dir(VIDEO_GALLERY_DIR)) {
    echo "❌ 错误: 视频目录不存在！\n";
    echo "请确保目录存在: " . VIDEO_GALLERY_DIR . "\n";
    exit(1);
}

$scannedGroups = scan_videos_from_directory(null, $generateThumbs);

if (empty($scannedGroups)) {
    echo "⚠️  未找到任何视频文件\n";
    echo "请确保视频文件放置在正确的子目录中\n";
    exit(0);
}

echo "✅ 扫描完成！找到 " . count($scannedGroups) . " 个分组:\n\n";

$totalVideos = 0;
foreach ($scannedGroups as $groupId => $group) {
    $videoCount = count($group['videos'] ?? []);
    $totalVideos += $videoCount;
    echo "📁 {$group['title']} ({$groupId})\n";
    echo "   状态: {$group['status']}\n";
    echo "   视频数量: {$videoCount}\n";
    
    if (!empty($group['videos'])) {
        echo "   视频列表:\n";
        foreach ($group['videos'] as $video) {
            echo "     - {$video['title']}\n";
            if (!empty($video['poster'])) {
                echo "       预览图: {$video['poster']}\n";
            }
            foreach ($video['sources'] as $format => $path) {
                echo "       {$format}: {$path}\n";
            }
        }
    }
    echo "\n";
}

echo "========================================\n";
echo "总计: {$totalVideos} 个视频\n";
echo "========================================\n\n";

// 保存到JSON文件
echo "💾 保存数据到 JSON 文件...\n";
if (save_video_groups($scannedGroups)) {
    echo "✅ 数据已保存到: " . VIDEO_DATA_FILE . "\n";
} else {
    echo "❌ 保存失败！请检查文件权限\n";
    exit(1);
}

echo "\n🎉 完成！\n\n";
