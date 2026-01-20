<?php
// public/admin/controllers/comics.php

// 开发模式下显示错误
if (isset($_GET['dev'])) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    // 记录错误到文件
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../../../debug.log');
}

define('ADMIN_ACCESS', true);
require_once __DIR__ . '/../../Models/comic_data.php';

// 处理POST请求
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add':
            $comicData = [
                'title' => trim($_POST['title'] ?? ''),
                'subtitle' => trim($_POST['subtitle'] ?? ''),
                'lines' => trim($_POST['lines'] ?? ''),
                'alt' => trim($_POST['alt'] ?? ''),
                'status' => $_POST['status'] ?? 'active'
            ];
            
            // 处理图片上传
            $uploadDir = __DIR__ . '/../../assets/images/comic/';
            $thumbsDir = $uploadDir . 'thumbs/';
            
            // 确保目录存在
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            if (!is_dir($thumbsDir)) mkdir($thumbsDir, 0755, true);
            
            $images = [];
            $icons = [];
            
            // 处理主图片上传
            if (!empty($_FILES['images']['name'][0])) {
                foreach ($_FILES['images']['name'] as $key => $filename) {
                    if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                        // 使用原文件名
                        $originalName = pathinfo($filename, PATHINFO_FILENAME);
                        $ext = pathinfo($filename, PATHINFO_EXTENSION);
                        $newFilename = $originalName . '.' . $ext;
                        
                        // 如果文件已存在，添加数字后缀
                        $counter = 1;
                        while (file_exists($uploadDir . $newFilename)) {
                            $newFilename = $originalName . '_' . $counter . '.' . $ext;
                            $counter++;
                        }
                        
                        $uploadPath = $uploadDir . $newFilename;
                        
                        if (move_uploaded_file($_FILES['images']['tmp_name'][$key], $uploadPath)) {
                            $images[] = '/assets/images/comic/' . $newFilename;
                        }
                    }
                }
            }
            
            // 处理图标上传
            if (!empty($_FILES['icon_default']['name'])) {
                $originalName = pathinfo($_FILES['icon_default']['name'], PATHINFO_FILENAME);
                $ext = pathinfo($_FILES['icon_default']['name'], PATHINFO_EXTENSION);
                $iconDefault = $originalName . '-icon-default.' . $ext;
                
                // 如果文件已存在，添加数字后缀
                $counter = 1;
                while (file_exists($thumbsDir . $iconDefault)) {
                    $iconDefault = $originalName . '-icon-default_' . $counter . '.' . $ext;
                    $counter++;
                }
                
                if (move_uploaded_file($_FILES['icon_default']['tmp_name'], $thumbsDir . $iconDefault)) {
                    $comicData['icon_default'] = '/assets/images/comic/thumbs/' . $iconDefault;
                }
            }
            
            if (!empty($_FILES['icon_hover']['name'])) {
                $originalName = pathinfo($_FILES['icon_hover']['name'], PATHINFO_FILENAME);
                $ext = pathinfo($_FILES['icon_hover']['name'], PATHINFO_EXTENSION);
                $iconHover = $originalName . '-icon-hover.' . $ext;
                
                // 如果文件已存在，添加数字后缀
                $counter = 1;
                while (file_exists($thumbsDir . $iconHover)) {
                    $iconHover = $originalName . '-icon-hover_' . $counter . '.' . $ext;
                    $counter++;
                }
                
                if (move_uploaded_file($_FILES['icon_hover']['tmp_name'], $thumbsDir . $iconHover)) {
                    $comicData['icon_hover'] = '/assets/images/comic/thumbs/' . $iconHover;
                }
            }
            
            $comicData['images'] = $images;
            // 如果没有传入 order_id，则为新条目分配一个自增长的 order_id
            if (empty($comicData['order_id'])) {
                $allExist = get_all_comics_data();
                $max = 0;
                foreach ($allExist as $ex) {
                    if (!empty($ex['order_id'])) $max = max($max, (int)$ex['order_id']);
                }
                $comicData['order_id'] = $max + 1;
            }
            
            try {
                if (add_comic($comicData)) {
                    $message = '漫画添加成功！';
                    $messageType = 'success';
                } else {
                    $message = '添加失败，请重试。';
                    $messageType = 'danger';
                }
            } catch (Exception $e) {
                $message = '添加失败：' . $e->getMessage();
                $messageType = 'danger';
            }
            break;
            
        case 'update':
            $id = $_POST['id'] ?? '';
            $comicData = [
                'title' => trim($_POST['title'] ?? ''),
                'subtitle' => trim($_POST['subtitle'] ?? ''),
                'lines' => trim($_POST['lines'] ?? ''),
                'alt' => trim($_POST['alt'] ?? ''),
                'status' => $_POST['status'] ?? 'active'
            ];
            // 接收可选的 order_id 字段
            if (isset($_POST['order_id']) && $_POST['order_id'] !== '') {
                $comicData['order_id'] = (int)$_POST['order_id'];
            }
            
            if (update_comic($id, $comicData)) {
                $message = '漫画更新成功！';
                $messageType = 'success';
            } else {
                $message = '更新失败，请重试。';
                $messageType = 'danger';
            }
            break;
            
        case 'delete':
            $id = $_POST['id'] ?? '';
            $result = delete_comic($id);
            if ($result['success']) {
                $message = $result['message'];
                $messageType = 'success';
            } else {
                $message = $result['error'] ?? '删除失败，请重试。';
                $messageType = 'danger';
            }
            break;
    }
}

// 获取所有漫画
$comics = get_all_comics_data();

// 处理AJAX图片操作（单图上传/删除/排序）
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_action'])) {
    $ajax = $_POST['ajax_action'];
    header('Content-Type: application/json');

    if ($ajax === 'upload_image') {
        $comicId = $_POST['comic_id'] ?? '';
        $field = $_POST['field'] ?? '';
        if (!$comicId || !isset($_FILES['image'])) {
            echo json_encode(['ok' => false, 'error' => '参数缺失']); exit;
        }
        // 如果上传用于 icon_default/icon_hover，保存到 thumbs 目录
        if ($field === 'icon_default' || $field === 'icon_hover') {
            $uploadDir = __DIR__ . '/../../assets/images/comic/thumbs/';
        } else {
            $uploadDir = __DIR__ . '/../../assets/images/comic/';
        }
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        $file = $_FILES['image'];
        if ($file['error'] !== UPLOAD_ERR_OK) { echo json_encode(['ok'=>false,'error'=>'上传失败']); exit; }
        $orig = pathinfo($file['name'], PATHINFO_FILENAME);
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $new = $orig . '.' . $ext;
        
        // 如果文件已存在，添加数字后缀
        $counter = 1;
        while (file_exists($uploadDir . $new)) {
            $new = $orig . '_' . $counter . '.' . $ext;
            $counter++;
        }
        if (move_uploaded_file($file['tmp_name'], $uploadDir . $new)) {
            $url = ($field === 'icon_default' || $field === 'icon_hover') ? '/assets/images/comic/thumbs/' . $new : '/assets/images/comic/' . $new;
            // 更新数据
            $all = get_all_comics_data();
            if (isset($all[$comicId])) {
                if ($field === 'icon_default' || $field === 'icon_hover') {
                    $message = '图标上传成功';
                    // 删除旧图标文件
                    if (isset($all[$comicId][$field]) && $all[$comicId][$field]) {
                        // 修复路径构造：直接使用项目根目录
                        $oldPath = dirname(dirname(dirname(__DIR__))) . '/public' . $all[$comicId][$field];
                        error_log("[图标上传] 尝试删除旧文件: $oldPath");
                        if (file_exists($oldPath)) {
                            $deleted = @unlink($oldPath);
                            if ($deleted) {
                                $message .= '，旧图标已清理';
                                error_log("[图标上传] 旧图标删除成功: $oldPath");
                            } else {
                                $message .= '，但旧图标清理失败';
                                error_log("[图标上传] 旧图标删除失败: $oldPath");
                            }
                        } else {
                            $message .= ' (旧文件不存在)';
                            error_log("[图标上传] 旧图标文件不存在: $oldPath");
                        }
                    }
                    $all[$comicId][$field] = $url;
                } else {
                    $message = '图片上传成功';
                    $all[$comicId]['images'][] = $url;
                }
                save_comics_data($all);
            }
            echo json_encode(['ok' => true, 'url' => $url, 'message' => $message]); exit;
        }
        echo json_encode(['ok'=>false,'error'=>'保存失败']); exit;
    }

    if ($ajax === 'delete_image') {
        $comicId = $_POST['comic_id'] ?? '';
        $imageUrl = $_POST['image_url'] ?? '';
        $field = $_POST['field'] ?? '';
        if (!$comicId || !$imageUrl) { echo json_encode(['ok'=>false,'error'=>'参数缺失']); exit; }
        $all = get_all_comics_data();
        if (!isset($all[$comicId])) { echo json_encode(['ok'=>false,'error'=>'未找到漫画']); exit; }
        
        // 构建完整的文件路径 - 修复路径构造
        $projectRoot = dirname(dirname(dirname(__DIR__)));
        $localPath = $projectRoot . '/public' . $imageUrl;
        error_log("[删除请求] 路径: $localPath, URL: $imageUrl");
        
        if ($field === 'icon_default' || $field === 'icon_hover') {
            // 清空对应字段
            if (isset($all[$comicId][$field]) && $all[$comicId][$field] === $imageUrl) {
                $all[$comicId][$field] = '';
                $saved = save_comics_data($all);
                
                // 删除物理文件
                $fileDeleted = false;
                $deleteError = '';
                error_log("[图标删除] 尝试删除文件: {$localPath}");
                
                if (file_exists($localPath)) {
                    $fileDeleted = @unlink($localPath);
                    if ($fileDeleted) {
                        error_log("[图标删除] 文件删除成功: {$localPath}");
                    } else {
                        $deleteError = '文件删除失败，可能权限不足';
                        error_log("[图标删除] 文件删除失败: {$localPath}");
                    }
                } else {
                    $deleteError = '文件不存在';
                    error_log("[图标删除] 文件不存在: {$localPath}");
                }
                
                if ($saved) {
                    if ($fileDeleted) {
                        $msg = "图标和文件已删除: {$imageUrl}";
                    } else {
                        $msg = "图标已删除，但文件删除失败: {$deleteError}";
                    }
                    echo json_encode(['ok'=>true, 'message'=>$msg]); exit;
                } else {
                    echo json_encode(['ok'=>false,'error'=>'数据保存失败']); exit;
                }
            }
            echo json_encode(['ok'=>false,'error'=>'图标未找到']); exit;
        } else {
            $idx = array_search($imageUrl, $all[$comicId]['images'] ?? []);
            if ($idx !== false && $idx !== null) {
                array_splice($all[$comicId]['images'], $idx, 1);
                $saved = save_comics_data($all);
                
                // 删除物理文件
                $fileDeleted = false;
                $deleteError = '';
                error_log("[图片删除] 尝试删除文件: {$localPath}");
                
                if (file_exists($localPath)) {
                    $fileDeleted = @unlink($localPath);
                    if ($fileDeleted) {
                        error_log("[图片删除] 文件删除成功: {$localPath}");
                    } else {
                        $deleteError = '文件删除失败，可能权限不足';
                        error_log("[图片删除] 文件删除失败: {$localPath}");
                    }
                } else {
                    $deleteError = '文件不存在';
                    error_log("[图片删除] 文件不存在: {$localPath}");
                }
                
                if ($saved) {
                    if ($fileDeleted) {
                        $msg = "图片和文件已删除: {$imageUrl}";
                    } else {
                        $msg = "图片已删除，但文件删除失败: {$deleteError}";
                    }
                    echo json_encode(['ok'=>true, 'message'=>$msg]); exit;
                } else {
                    echo json_encode(['ok'=>false,'error'=>'数据保存失败']); exit;
                }
            }
            echo json_encode(['ok'=>false,'error'=>'图片未找到']); exit;
        }
    }

    if ($ajax === 'reorder_images') {
        $comicId = $_POST['comic_id'] ?? '';
        $order = $_POST['order'] ?? '';
        if (!$comicId || $order === '') { echo json_encode(['ok'=>false,'error'=>'参数缺失']); exit; }
        $orderArr = json_decode($order, true);
        if (!is_array($orderArr)) { echo json_encode(['ok'=>false,'error'=>'参数格式错误']); exit; }
        $all = get_all_comics_data();
        if (!isset($all[$comicId])) { echo json_encode(['ok'=>false,'error'=>'未找到漫画']); exit; }
        $all[$comicId]['images'] = $orderArr;
        save_comics_data($all);
        echo json_encode(['ok'=>true]); exit;
    }
    if ($ajax === 'add_group') {
        // 创建一个空的分组条目并保存
        $all = get_all_comics_data();
        // 生成唯一ID
        $newId = 'g' . time();
        $title = 'New Group';
        // 分配自增长的 order_id
        $max = 0; foreach ($all as $k=>$v) { if (!empty($v['order_id'])) $max = max($max, (int)$v['order_id']); }
        $all[$newId] = [
            'title' => $title,
            'subtitle' => '',
            'lines' => '',
            'alt' => '',
            'status' => 'inactive',
            'order_id' => $max + 1,
            'images' => []
        ];
        if (save_comics_data($all)) {
            echo json_encode(['ok'=>true, 'id'=>$newId, 'title'=>$title, 'order_id' => $all[$newId]['order_id']]); exit;
        }
        echo json_encode(['ok'=>false,'error'=>'创建失败']); exit;
    }
    if ($ajax === 'reorder_comics') {
        $order = $_POST['order'] ?? '';
        $orderArr = json_decode($order, true);
        if (!is_array($orderArr)) { echo json_encode(['ok'=>false,'error'=>'参数格式错误']); exit; }
        // 把 order 写入到每个条目的 order_id 字段（以1起始），并删除旧的 comic_order.json
        $all = get_all_comics_data();
        foreach ($orderArr as $idx => $cid) {
            if (isset($all[$cid])) {
                $all[$cid]['order_id'] = $idx + 1;
            }
        }
        $saved = save_comics_data($all);
        $orderFile = __DIR__ . '/../../../storage/comic_order.json';
        if (file_exists($orderFile)) @unlink($orderFile);
        if ($saved !== false) { echo json_encode(['ok'=>true]); exit; }
        echo json_encode(['ok'=>false,'error'=>'写入失败']); exit;
    }
    if ($ajax === 'reindex_order_id') {
        // 接收可选的 id 列表，按顺序重写 order_id 为 1..N
        $order = $_POST['order'] ?? '';
        $orderArr = json_decode($order, true);
        $all = get_all_comics_data();
        if (is_array($orderArr) && count($orderArr) > 0) {
            foreach ($orderArr as $idx => $cid) {
                if (isset($all[$cid])) {
                    $all[$cid]['order_id'] = $idx + 1;
                }
            }
        } else {
            // 使用当前 keys 顺序
            $i = 1;
            foreach ($all as $cid => $rec) { $all[$cid]['order_id'] = $i++; }
        }
        $saved = save_comics_data($all);
        if ($saved !== false) { echo json_encode(['ok'=>true]); exit; }
        echo json_encode(['ok'=>false,'error'=>'写入失败']); exit;
    }

    echo json_encode(['ok'=>false,'error'=>'未知操作']); exit;
}
// 设置页面信息
$page_title = '🛠️ 漫画配置';
$page_subtitle = '管理漫画分组和图片';
$_GET['page'] = 'comics';

require_once __DIR__ . '/../index.php';