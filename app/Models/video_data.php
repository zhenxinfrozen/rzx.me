<?php
/**
 * Video Data Model
 * 视频数据管理模型
 * 
 * 负责视频分组和视频数据的读取、存储和管理
 */

// 视频数据文件路径
define('VIDEO_DATA_FILE', __DIR__ . '/../storage/data/video-gallery.json');
// 视频文件基础目录
define('VIDEO_GALLERY_DIR', __DIR__ . '/../../public/assets/videos/video-gallery');

// 加载自动缩略图生成器
require_once __DIR__ . '/../Utils/AutoThumbnailGenerator.php';

/**
 * 获取所有视频分组数据（带自动缩略图检查）
 * 
 * @param bool $autoCheckThumbnails 是否自动检查并生成缺失的缩略图（默认true）
 * @return array 所有视频分组数据
 */
function get_all_video_groups($autoCheckThumbnails = true) {
    // 自动检查并生成缺失的缩略图（仅在检测到新文件时）
    if ($autoCheckThumbnails) {
        $stats = smart_thumbnail_check();
        if ($stats['generated'] > 0) {
            error_log("[VideoData] 自动生成了 {$stats['generated']} 个缩略图");
            // 如果生成了新缩略图，重新扫描并更新数据库
            $groups = scan_videos_from_directory(VIDEO_GALLERY_DIR, false); // false避免重复生成
            if (!empty($groups)) {
                save_video_groups($groups);
                return $groups;
            }
        }
    }
    
    if (!file_exists(VIDEO_DATA_FILE)) {
        // 如果文件不存在，扫描目录并生成
        $groups = scan_videos_from_directory(VIDEO_GALLERY_DIR, true);
        if (!empty($groups)) {
            save_video_groups($groups);
            return $groups;
        }
        return get_default_video_groups();
    }
    
    $jsonContent = file_get_contents(VIDEO_DATA_FILE);
    $data = json_decode($jsonContent, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log('视频数据JSON解析错误: ' . json_last_error_msg());
        return get_default_video_groups();
    }
    
    return $data ?? [];
}

/**
 * 获取默认视频分组数据（从现有视频迁移）
 * 
 * @return array 默认视频分组
 */
function get_default_video_groups() {
    return [
        'action-scenes' => [
            'title' => '动作场景',
            'description' => '动作相关的动画分镜和视频',
            'status' => 'active',
            'order' => 1,
            'videos' => [
                [
                    'title' => 'biu biu biu',
                    'description' => '',
                    'poster' => '/assets/videos/shooting-01.jpg',
                    'sources' => [
                        'mp4' => '/assets/videos/shooting-01.mp4',
                        'webm' => '/assets/videos/shooting-01.webm'
                    ]
                ],
                [
                    'title' => 'Gunman',
                    'description' => '',
                    'poster' => '/assets/videos/Gunman.jpg',
                    'sources' => [
                        'mp4' => '/assets/videos/Gunman.mp4',
                        'webm' => '/assets/videos/Gunman.webm'
                    ]
                ],
                [
                    'title' => 'gun shooting',
                    'description' => '',
                    'poster' => '/assets/videos/gun-shooting.jpg',
                    'sources' => [
                        'mp4' => '/assets/videos/gun-shooting.mp4',
                        'webm' => '/assets/videos/gun-shooting.webm'
                    ]
                ]
            ]
        ],
        'nature-effects' => [
            'title' => '自然特效',
            'description' => '自然元素和特效动画',
            'status' => 'active',
            'order' => 2,
            'videos' => [
                [
                    'title' => 'flower',
                    'description' => '',
                    'poster' => '/assets/videos/flower.jpg',
                    'sources' => [
                        'mp4' => '/assets/videos/flower.mp4',
                        'webm' => '/assets/videos/flower.webm'
                    ]
                ],
                [
                    'title' => 'dragonfire',
                    'description' => '',
                    'poster' => '/assets/videos/dragonfire.jpg',
                    'sources' => [
                        'mp4' => '/assets/videos/dragonfire.mp4',
                        'webm' => '/assets/videos/dragonfire.webm'
                    ]
                ]
            ]
        ],
        'story-clips' => [
            'title' => '故事片段',
            'description' => '动画故事片段',
            'status' => 'active',
            'order' => 3,
            'videos' => [
                [
                    'title' => '动画片段 1',
                    'description' => '',
                    'poster' => '/assets/videos/begin-01.jpg',
                    'sources' => [
                        'mp4' => '/assets/videos/begin-01.mp4',
                        'webm' => '/assets/videos/begin-01.webm'
                    ]
                ]
            ]
        ]
    ];
}

/**
 * 保存视频分组数据
 * 
 * @param array $groups 视频分组数据
 * @return bool 是否保存成功
 */
function save_video_groups($groups) {
    // 确保存储目录存在
    $dir = dirname(VIDEO_DATA_FILE);
    if (!is_dir($dir)) {
        if (!mkdir($dir, 0755, true)) {
            error_log('无法创建视频数据目录: ' . $dir);
            return false;
        }
    }
    
    $jsonContent = json_encode($groups, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    
    if ($jsonContent === false) {
        error_log('视频数据JSON编码错误: ' . json_last_error_msg());
        return false;
    }
    
    $result = file_put_contents(VIDEO_DATA_FILE, $jsonContent);
    
    if ($result === false) {
        error_log('无法保存视频数据文件: ' . VIDEO_DATA_FILE);
        return false;
    }
    
    return true;
}

/**
 * 获取单个视频分组数据
 * 
 * @param string $groupId 分组ID
 * @return array|null 分组数据，不存在时返回null
 */
function get_video_group($groupId) {
    $groups = get_all_video_groups();
    return $groups[$groupId] ?? null;
}

/**
 * 添加视频分组
 * 
 * @param string $groupId 分组ID
 * @param array $groupData 分组数据
 * @return bool 是否添加成功
 */
function add_video_group($groupId, $groupData) {
    $groups = get_all_video_groups();
    
    if (isset($groups[$groupId])) {
        error_log('视频分组已存在: ' . $groupId);
        return false;
    }
    
    $groups[$groupId] = array_merge([
        'title' => '',
        'description' => '',
        'status' => 'active',
        'order' => count($groups) + 1,
        'videos' => []
    ], $groupData);
    
    return save_video_groups($groups);
}

/**
 * 更新视频分组
 * 
 * @param string $groupId 分组ID
 * @param array $groupData 分组数据
 * @return bool 是否更新成功
 */
function update_video_group($groupId, $groupData) {
    $groups = get_all_video_groups();
    
    if (!isset($groups[$groupId])) {
        error_log('视频分组不存在: ' . $groupId);
        return false;
    }
    
    $groups[$groupId] = array_merge($groups[$groupId], $groupData);
    
    return save_video_groups($groups);
}

/**
 * 删除视频分组
 * 
 * @param string $groupId 分组ID
 * @return bool 是否删除成功
 */
function delete_video_group($groupId) {
    $groups = get_all_video_groups();
    
    if (!isset($groups[$groupId])) {
        error_log('视频分组不存在: ' . $groupId);
        return false;
    }
    
    unset($groups[$groupId]);
    
    return save_video_groups($groups);
}

/**
 * 添加视频到分组
 * 
 * @param string $groupId 分组ID
 * @param array $videoData 视频数据
 * @return bool 是否添加成功
 */
function add_video_to_group($groupId, $videoData) {
    $groups = get_all_video_groups();
    
    if (!isset($groups[$groupId])) {
        error_log('视频分组不存在: ' . $groupId);
        return false;
    }
    
    $groups[$groupId]['videos'][] = $videoData;
    
    return save_video_groups($groups);
}

/**
 * 从目录扫描视频文件并创建分组
 * 
 * @param string $baseDir 视频文件基础目录（默认使用 VIDEO_GALLERY_DIR）
 * @param bool $autoGenerateThumbnails 是否自动生成缩略图（默认true）
 * @return array 扫描到的视频分组
 */
function scan_videos_from_directory($baseDir = null, $autoGenerateThumbnails = true) {
    if ($baseDir === null) {
        $baseDir = VIDEO_GALLERY_DIR;
    }
    
    if (!is_dir($baseDir)) {
        error_log('视频目录不存在: ' . $baseDir);
        return [];
    }
    
    // 加载缩略图生成器
    if ($autoGenerateThumbnails) {
        require_once __DIR__ . '/../Utils/VideoThumbnailGenerator.php';
    }
    
    $groups = [];
    // 获取支持的视频格式
    $videoExtensions = require_once __DIR__ . '/../Utils/VideoThumbnailGenerator.php';
    $videoExtensions = VideoThumbnailGenerator::getSupportedFormats();
    $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    // 扫描子目录作为分组
    $subDirs = array_filter(glob($baseDir . '/*'), 'is_dir');
    
    $orderIndex = 0;
    foreach ($subDirs as $subDir) {
        $groupName = basename($subDir);
        $videos = [];
        
        // 扫描视频文件
        $files = scandir($subDir);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;
            
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (!in_array($ext, $videoExtensions)) continue;
            
            $filePath = $subDir . '/' . $file;
            $relativePath = '/assets/videos/video-gallery/' . $groupName . '/' . $file;
            
            // 查找对应的预览图
            $posterPath = '';
            $baseName = pathinfo($file, PATHINFO_FILENAME);
            
            // 1. 首先查找同名预览图
            foreach ($imageExtensions as $imgExt) {
                $posterFile = $subDir . '/' . $baseName . '.' . $imgExt;
                if (file_exists($posterFile)) {
                    $posterPath = '/assets/videos/video-gallery/' . $groupName . '/' . $baseName . '.' . $imgExt;
                    break;
                }
            }
            
            // 2. 如果没有找到同名预览图，且启用自动生成
            if (empty($posterPath) && $autoGenerateThumbnails) {
                $thumbnailFile = $subDir . '/' . $baseName . '.jpg';
                $thumbnailRelativePath = '/assets/videos/video-gallery/' . $groupName . '/' . $baseName . '.jpg';
                
                // 尝试生成缩略图
                if (VideoThumbnailGenerator::generateOrDefault(
                    $filePath,
                    $thumbnailFile,
                    $baseName
                )) {
                    $posterPath = $thumbnailRelativePath;
                }
            }
            
            // 3. 如果仍然没有预览图，查找目录中的第一个图片
            if (empty($posterPath)) {
                foreach ($files as $imgFile) {
                    $imgExt = strtolower(pathinfo($imgFile, PATHINFO_EXTENSION));
                    if (in_array($imgExt, $imageExtensions)) {
                        $posterPath = '/assets/videos/video-gallery/' . $groupName . '/' . $imgFile;
                        break;
                    }
                }
            }
            
            $videos[] = [
                'title' => str_replace(['-', '_'], ' ', $baseName),
                'description' => '',
                'poster' => $posterPath,
                'sources' => [
                    $ext => $relativePath
                ]
            ];
        }
        
        if (!empty($videos)) {
            $orderIndex++;
            $groups[$groupName] = [
                'title' => ucfirst(str_replace(['-', '_'], ' ', $groupName)),
                'description' => '',
                'status' => 'active',
                'order' => $orderIndex,
                'videos' => $videos
            ];
        }
    }
    
    return $groups;
}

/**
 * 获取视频统计信息
 * 
 * @return array 统计信息
 */
function get_video_stats() {
    $groups = get_all_video_groups();
    
    $stats = [
        'total_groups' => count($groups),
        'active_groups' => 0,
        'total_videos' => 0
    ];
    
    foreach ($groups as $group) {
        if (($group['status'] ?? 'active') === 'active') {
            $stats['active_groups']++;
        }
        $stats['total_videos'] += count($group['videos'] ?? []);
    }
    
    return $stats;
}

/**
 * 完整同步目录与数据库（添加新文件、删除不存在的文件）
 * 
 * @param array $existingGroups 现有的视频分组数据
 * @return array 同步后的视频分组数据
 */
function sync_videos_with_directory($existingGroups) {
    $scannedGroups = scan_videos_from_directory();
    
    if (empty($scannedGroups)) {
        // 如果扫描结果为空，清空所有分组
        return [];
    }
    
    $updated = false;
    
    // 第一步：删除数据库中不存在于文件系统的视频和分组
    foreach ($existingGroups as $groupId => $existingGroup) {
        // 如果整个分组在文件系统中不存在，删除它
        if (!isset($scannedGroups[$groupId])) {
            unset($existingGroups[$groupId]);
            $updated = true;
            continue;
        }
        
        // 检查该分组中的视频，删除不存在的
        $existingVideos = $existingGroup['videos'] ?? [];
        $scannedVideos = $scannedGroups[$groupId]['videos'] ?? [];
        
        // 建立扫描到的视频路径集合
        $scannedPaths = [];
        foreach ($scannedVideos as $video) {
            foreach ($video['sources'] ?? [] as $source) {
                $scannedPaths[$source] = true;
            }
        }
        
        // 过滤掉不存在的视频
        $filteredVideos = [];
        foreach ($existingVideos as $video) {
            $exists = false;
            foreach ($video['sources'] ?? [] as $source) {
                if (isset($scannedPaths[$source])) {
                    $exists = true;
                    break;
                }
            }
            if ($exists) {
                $filteredVideos[] = $video;
            } else {
                $updated = true;
            }
        }
        
        $existingGroups[$groupId]['videos'] = $filteredVideos;
    }
    
    // 第二步：添加新分组和新视频
    foreach ($scannedGroups as $groupId => $scannedGroup) {
        // 如果分组不存在，直接添加
        if (!isset($existingGroups[$groupId])) {
            $existingGroups[$groupId] = $scannedGroup;
            $updated = true;
            continue;
        }
        
        // 检查该分组中的新视频
        $existingVideos = $existingGroups[$groupId]['videos'] ?? [];
        $scannedVideos = $scannedGroup['videos'] ?? [];
        
        // 建立现有视频的快速查找表
        $existingPaths = [];
        foreach ($existingVideos as $video) {
            foreach ($video['sources'] ?? [] as $source) {
                $existingPaths[$source] = true;
            }
        }
        
        // 检查并添加新视频
        foreach ($scannedVideos as $scannedVideo) {
            $isNew = true;
            foreach ($scannedVideo['sources'] ?? [] as $source) {
                if (isset($existingPaths[$source])) {
                    $isNew = false;
                    break;
                }
            }
            
            if ($isNew) {
                $existingGroups[$groupId]['videos'][] = $scannedVideo;
                $updated = true;
            }
        }
    }
    
    // 如果有更新，保存到文件
    if ($updated) {
        save_video_groups($existingGroups);
    }
    
    return $existingGroups;
}

/**
 * 从目录扫描并自动更新视频数据（每次都检查新文件和删除的文件）
 * 
 * @param bool $forceRescan 是否强制完全重新扫描（默认false，使用增量同步）
 * @return array 视频分组数据
 */
function get_videos_with_auto_scan($forceRescan = false) {
    $groups = get_all_video_groups();
    
    // 如果强制重新扫描，则完全从目录重建
    if ($forceRescan) {
        $scannedGroups = scan_videos_from_directory();
        if (!empty($scannedGroups)) {
            save_video_groups($scannedGroups);
            return $scannedGroups;
        }
    }
    
    // 如果数据为空，则从目录加载
    if (empty($groups)) {
        $scannedGroups = scan_videos_from_directory();
        if (!empty($scannedGroups)) {
            save_video_groups($scannedGroups);
            return $scannedGroups;
        }
        return [];
    }
    
    // 完整同步：检查新文件和删除的文件
    return sync_videos_with_directory($groups);
}

/**
 * 获取应用了配置的视频分组数据（用于前台显示）
 * 
 * @return array 应用了配置的视频分组数据
 */
function get_videos_for_display() {
    $groups = get_videos_with_auto_scan();
    
    // 加载配置
    $configPath = __DIR__ . '/../Config/video_gallery_sort.php';
    if (file_exists($configPath)) {
        $config = include $configPath;
        
        // 应用显示名称和描述
        foreach ($groups as $groupId => &$group) {
            if (isset($config['display_names'][$groupId]) && !empty($config['display_names'][$groupId])) {
                $group['title'] = $config['display_names'][$groupId];
            }
            if (isset($config['descriptions'][$groupId]) && !empty($config['descriptions'][$groupId])) {
                $group['description'] = $config['descriptions'][$groupId];
            }
        }
        
        // 按自定义顺序排序
        if (!empty($config['custom_order'])) {
            $orderedGroups = [];
            foreach ($config['custom_order'] as $groupId) {
                if (isset($groups[$groupId])) {
                    $orderedGroups[$groupId] = $groups[$groupId];
                }
            }
            // 添加未在自定义顺序中的分组
            foreach ($groups as $groupId => $group) {
                if (!isset($orderedGroups[$groupId])) {
                    $orderedGroups[$groupId] = $group;
                }
            }
            $groups = $orderedGroups;
        }
    }
    
    return $groups;
}
