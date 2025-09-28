<?php
// app/Models/comic_data.php

function get_all_comics_data() {
    $dataFile = __DIR__ . '/../../storage/comic_data.json';

    if (file_exists($dataFile)) {
        $jsonData = file_get_contents($dataFile);
        $data = json_decode($jsonData, true);
        if (!is_array($data)) return get_default_comics_data();

        // 如果每条记录内带有 'order_id' 字段，则按该字段排序
        $haveOrder = false;
        foreach ($data as $cid => $rec) {
            if (isset($rec['order_id'])) { $haveOrder = true; break; }
        }
        if ($haveOrder) {
            uasort($data, function($a, $b) {
                $ao = isset($a['order_id']) ? (int)$a['order_id'] : PHP_INT_MAX;
                $bo = isset($b['order_id']) ? (int)$b['order_id'] : PHP_INT_MAX;
                if ($ao === $bo) return 0;
                return ($ao < $bo) ? -1 : 1;
            });
            return $data;
        }

        return $data;
    }

    return get_default_comics_data();
}

function get_default_comics_data() {
    return [
        'wine' => [
            'title' => '又到一年醉酒时',
            'subtitle' => '生日扎堆,节日扎堆,庆祝扎堆',
            'lines' => "躁动的时节.... <br />疯狂终于找到了理由", 
            'images' => ['/assets/images/comic/ray-comic-img-wine350px.jpg'],
            'alt' => '又到一年醉酒时',
            'icon_default' => '/assets/images/comic/thumbs/ray-comic-wine-icon-default.png',
            'icon_hover' => '/assets/images/comic/thumbs/ray-comic-wine-icon-hover.png',
            'created_at' => '2024-01-01',
            'status' => 'active'
        ],
        'gzjy' => [
            'title' => '经验,经验从何而来？',
            'subtitle' => '都要有经验的, 都要有经验的，初始经验从何而来?',
            'lines' => "这又不是魔兽世界，遍地的狗头人给你开始提升经验～～",
            'images' => ['/assets/images/comic/ray-comic-img-gzjy.jpg'],
            'alt' => '工作经验',
            'icon_default' => '/assets/images/comic/thumbs/ray-comic-gzjy-icon-default.png',
            'icon_hover' => '/assets/images/comic/thumbs/ray-comic-gzjy-icon-hover.png',
            'created_at' => '2024-01-02',
            'status' => 'active'
        ],
        'MagicUbuntu' => [
            'title' => 'MagicUbuntu',
            'subtitle' => '来自Buzz里的一段对话...',
            'lines' => "如有巧合纯属正常！",
            'images' => ['/assets/images/comic/ray-comic-img-ubuntu.jpg'],
            'alt' => 'MagicUbuntu',
            'icon_default' => '/assets/images/comic/thumbs/ray-comic-ubuntu-icon-default.png',
            'icon_hover' => '/assets/images/comic/thumbs/ray-comic-ubuntu-icon-hover.png',
            'created_at' => '2024-01-03',
            'status' => 'active'
        ],
        'icefire' => [
            'title' => '冰与火',
            'subtitle' => '装逼的下场',
            'lines' => "如有巧合纯属正常！",
            'images' => ['/assets/images/comic/ray-comic-img-icefire.jpg'],
            'alt' => '冰与火之歌',
            'icon_default' => '/assets/images/comic/thumbs/ray-comic-icefire-icon-default.png',
            'icon_hover' => '/assets/images/comic/thumbs/ray-comic-icefire-icon-hover.png',
            'created_at' => '2024-01-04',
            'status' => 'active'
        ]
    ];
}

function get_comic_by_id($id) {
    $comics = get_all_comics_data();
    return $comics[$id] ?? null;
}

function save_comics_data($data) {
    $dataFile = __DIR__ . '/../../storage/comic_data.json';
    $storageDir = dirname($dataFile);

    if (!is_dir($storageDir)) {
        mkdir($storageDir, 0755, true);
    }

    return file_put_contents($dataFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function add_comic($comicData) {
    $comics = get_all_comics_data();
    $id = $comicData['id'] ?? generate_comic_id($comicData['title'] ?? 'untitled');
    unset($comicData['id']); // 移除ID字段，用数组键代替

    // 验证必填字段
    $requiredFields = ['title'];
    foreach ($requiredFields as $field) {
        if (empty($comicData[$field])) {
            throw new InvalidArgumentException("字段 '{$field}' 是必填的");
        }
    }

    $comicData['created_at'] = date('Y-m-d H:i:s');
    $comicData['status'] = $comicData['status'] ?? 'active';
    $comicData['images'] = $comicData['images'] ?? [];

    // 如果未提供 order_id，分配一个自增的 order_id
    if (empty($comicData['order_id'])) {
        $max = 0;
        foreach ($comics as $ex) {
            if (!empty($ex['order_id'])) $max = max($max, (int)$ex['order_id']);
        }
        $comicData['order_id'] = $max + 1;
    }

    // 确保images是数组
    if (!is_array($comicData['images'])) {
        $comicData['images'] = [$comicData['images']];
    }

    $comics[$id] = $comicData;
    return save_comics_data($comics) ? $id : false;
}

function update_comic($id, $comicData) {
    $comics = get_all_comics_data();
    if (!isset($comics[$id])) {
        return false;
    }

    $comicData['updated_at'] = date('Y-m-d H:i:s');
    $comics[$id] = array_merge($comics[$id], $comicData);

    return save_comics_data($comics);
}

function delete_comic($id) {
    $comics = get_all_comics_data();
    if (!isset($comics[$id])) {
        return ['success' => false, 'error' => '漫画条目不存在'];
    }

    // 删除相关的图片文件
    $comic = $comics[$id];
    // 修复路径构造：使用正确的项目根目录
    $projectRoot = dirname(dirname(__DIR__));
    $basePath = $projectRoot . '/public';
    $deletionReport = [
        'images_deleted' => [],
        'images_failed' => [],
        'icons_deleted' => [],
        'icons_failed' => []
    ];
    
    // 删除主图片
    if (isset($comic['images']) && is_array($comic['images'])) {
        foreach ($comic['images'] as $imageUrl) {
            if ($imageUrl && is_string($imageUrl)) {
                $localPath = $basePath . $imageUrl;
                if (file_exists($localPath)) {
                    $deleted = @unlink($localPath);
                    if ($deleted) {
                        $deletionReport['images_deleted'][] = $imageUrl;
                        error_log("[删除条目] 图片删除成功: $localPath");
                    } else {
                        $deletionReport['images_failed'][] = $imageUrl;
                        error_log("[删除条目] 图片删除失败: $localPath");
                    }
                } else {
                    error_log("[删除条目] 图片文件不存在: $localPath");
                }
            }
        }
    }
    
    // 删除图标文件
    if (isset($comic['icon_default']) && $comic['icon_default']) {
        $localPath = $basePath . $comic['icon_default'];
        if (file_exists($localPath)) {
            $deleted = @unlink($localPath);
            if ($deleted) {
                $deletionReport['icons_deleted'][] = $comic['icon_default'];
                error_log("[删除条目] 默认图标删除成功: $localPath");
            } else {
                $deletionReport['icons_failed'][] = $comic['icon_default'];
                error_log("[删除条目] 默认图标删除失败: $localPath");
            }
        } else {
            error_log("[删除条目] 默认图标文件不存在: $localPath");
        }
    }
    
    if (isset($comic['icon_hover']) && $comic['icon_hover']) {
        $localPath = $basePath . $comic['icon_hover'];
        if (file_exists($localPath)) {
            $deleted = @unlink($localPath);
            if ($deleted) {
                $deletionReport['icons_deleted'][] = $comic['icon_hover'];
                error_log("[删除条目] 悬停图标删除成功: $localPath");
            } else {
                $deletionReport['icons_failed'][] = $comic['icon_hover'];
                error_log("[删除条目] 悬停图标删除失败: $localPath");
            }
        } else {
            error_log("[删除条目] 悬停图标文件不存在: $localPath");
        }
    }

    unset($comics[$id]);
    $dataSaved = save_comics_data($comics);
    
    if (!$dataSaved) {
        return ['success' => false, 'error' => '数据保存失败'];
    }
    
    // 生成删除报告
    $totalDeleted = count($deletionReport['images_deleted']) + count($deletionReport['icons_deleted']);
    $totalFailed = count($deletionReport['images_failed']) + count($deletionReport['icons_failed']);
    
    $message = "条目已删除。";
    if ($totalDeleted > 0) {
        $message .= " 成功删除 {$totalDeleted} 个文件";
    }
    if ($totalFailed > 0) {
        $message .= " ({$totalFailed} 个文件删除失败)";
    }
    
    return [
        'success' => true,
        'message' => $message,
        'details' => $deletionReport
    ];
}

function generate_comic_id($title) {
    // 生成基于标题的ID
    $id = strtolower(trim($title));
    $id = preg_replace('/[^a-z0-9\-_]/', '', $id);
    $id = preg_replace('/\s+/', '_', $id);
    $id = substr($id, 0, 20); // 限制长度

    // 如果ID为空或太短，使用时间戳
    if (strlen($id) < 3) {
        $id = 'comic_' . time();
    }

    // 确保ID唯一
    $comics = get_all_comics_data();
    $originalId = $id;
    $counter = 1;
    while (isset($comics[$id])) {
        $id = $originalId . '_' . $counter;
        $counter++;
    }

    return $id;
}

function get_comics_by_status($status = 'active') {
    $comics = get_all_comics_data();
    return array_filter($comics, function($comic) use ($status) {
        return ($comic['status'] ?? 'active') === $status;
    });
}

function search_comics($keyword) {
    $comics = get_all_comics_data();
    $keyword = strtolower($keyword);

    return array_filter($comics, function($comic) use ($keyword) {
        return strpos(strtolower($comic['title'] ?? ''), $keyword) !== false ||
               strpos(strtolower($comic['subtitle'] ?? ''), $keyword) !== false ||
               strpos(strtolower($comic['lines'] ?? ''), $keyword) !== false;
    });
}

function get_comic_stats() {
    $comics = get_all_comics_data();
    $stats = [
        'total' => count($comics),
        'active' => 0,
        'inactive' => 0,
        'with_images' => 0,
        'with_icons' => 0
    ];

    foreach ($comics as $comic) {
        if (($comic['status'] ?? 'active') === 'active') {
            $stats['active']++;
        } else {
            $stats['inactive']++;
        }

        if (!empty($comic['images'])) {
            $stats['with_images']++;
        }

        if (!empty($comic['icon_default']) || !empty($comic['icon_hover'])) {
            $stats['with_icons']++;
        }
    }

    return $stats;
}
