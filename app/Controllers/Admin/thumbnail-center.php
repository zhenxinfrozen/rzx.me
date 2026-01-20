<?php
/**
 * 缩略图中心 控制器
 */
require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/../../Services/ThumbnailService.php';
require_once __DIR__ . '/../../Utils/GalleryManager.php';

// 简单的认证检查
session_start();
if (!isset($_SESSION['admin_authenticated']) && !isset($_GET['dev'])) {
    header('Location: ../login.php');
    exit;
}

// 实例化服务
$galleryManager = new GalleryManager();

$isAjaxRequest = isset($_POST['action']) && isset($_POST['ajax']);
if ($isAjaxRequest) {
    unset($_POST['ajax']);
    header('Content-Type: application/json');
    try {
        switch ($_POST['action']) {
            case 'scan_galleries':
                $galleries = [];
                $imagesPath = realpath(__DIR__ . '/../../assets/images');
                if (is_dir($imagesPath)) {
                    $dirs = scandir($imagesPath);
                    foreach ($dirs as $dir) {
                        if ($dir[0] === '.' || !is_dir($imagesPath . '/' . $dir)) continue;
                        $categories = $galleryManager->getGalleryCategories($dir);
                        $galleries[] = [
                            'name' => $dir,
                            'display_name' => ucfirst(str_replace('-', ' ', $dir)),
                            'categories' => $categories,
                            'category_count' => count($categories)
                        ];
                    }
                }
                echo json_encode(['success' => true, 'galleries' => $galleries]);
                exit;

            case 'generate_thumbnails':
                $gallery = $_POST['gallery'] ?? '';
                $category = $_POST['category'] ?? '';
                if (empty($gallery)) throw new Exception('Gallery参数不能为空');
                $path = $gallery . (!empty($category) ? '/' . $category : '');
                $result = $galleryManager->generateThumbnails($path);
                echo json_encode(['success' => true, 'message' => '缩略图生成完成', 'results' => $result, 'count' => count($result)]);
                exit;

            case 'clean_thumbnails':
                $gallery = $_POST['gallery'] ?? '';
                $category = $_POST['category'] ?? '';
                if (empty($gallery)) throw new Exception('Gallery参数不能为空');
                $thumbsDir = realpath(__DIR__ . '/../../assets/images/' . $gallery) . (!empty($category) ? '/' . $category : '') . '/thumbs';
                $count = 0;
                if (is_dir($thumbsDir)) {
                    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($thumbsDir, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
                    foreach ($iterator as $file) {
                        if ($file->isFile()) {
                            unlink($file->getRealPath());
                            $count++;
                        }
                    }
                }
                echo json_encode(['success' => true, 'message' => "已清理 $count 个缩略图文件", 'count' => $count]);
                exit;
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit;
    }
}

// --- 页面加载和表单处理 ---

// 设置页面信息
$page_title = '🖼️ 缩略图中心';
$page_subtitle = '统一管理缩略图的批量操作、配置预设和功能测试。';
$_GET['page'] = 'thumbnail-center';

// 初始化变量
$message = $_SESSION['flash_message'] ?? '';
$message_type = $_SESSION['flash_message_type'] ?? 'info';
unset($_SESSION['flash_message'], $_SESSION['flash_message_type']);

$test_result = null;
$edit_id = null;
$edit_config = null;

// 处理非AJAX的POST请求 (主要来自配置管理)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['ajax'])) {
    $action = $_POST['action'] ?? '';
    try {
        switch ($action) {
            case 'test_generate':
                $configId = $_POST['config_id'] ?? '';
                $testImageRel = trim($_POST['test_image'] ?? '');
                if (!$configId) throw new Exception('请选择配置');
                if ($testImageRel === '') $testImageRel = 'single-works/Animals/20230727_002747775_iOS.jpg'; // 默认图片
                $sourcePath = realpath(__DIR__ . '/../../assets/images/' . $testImageRel);
                if (!$sourcePath || !file_exists($sourcePath)) throw new Exception('测试图片不存在：' . htmlspecialchars($testImageRel));
                
                $test_result = ThumbnailService::generate($sourcePath, $configId);
                if ($test_result['success']) {
                    $message = '测试成功'; $message_type = 'success';
                } else {
                    throw new Exception($test_result['error'] ?? '未知错误');
                }
                break;

            case 'add_config':
            case 'update_config':
                $configId = trim($_POST['config_id'] ?? '');
                if (empty($configId)) throw new Exception('配置ID不能为空');
                $conf = [
                    'name' => trim($_POST['name'] ?? ''),
                    'width' => (int)($_POST['width'] ?? 0),
                    'height' => (int)($_POST['height'] ?? 0),
                    'quality' => (int)($_POST['quality'] ?? 80),
                    'format' => strtolower(trim($_POST['format'] ?? 'jpg')),
                    'directory' => trim($_POST['directory'] ?? 'thumbs'),
                    'suffix' => trim($_POST['suffix'] ?? '_thumb'),
                    'crop' => isset($_POST['crop']),
                ];
                if ($action === 'add_config') {
                    ThumbnailService::addCustomConfig($configId, $conf);
                    $_SESSION['flash_message'] = '自定义配置已添加';
                } else {
                    ThumbnailService::updateCustomConfig($configId, $conf);
                    $_SESSION['flash_message'] = '配置已更新';
                }
                $_SESSION['flash_message_type'] = 'success';
                header('Location: thumbnail-center.php#config-manager-tab');
                exit;

            case 'load_edit':
                $edit_id = trim($_POST['config_id'] ?? '');
                $custom_configs = ThumbnailService::getCustomConfigs();
                if ($edit_id !== '' && isset($custom_configs[$edit_id])) {
                    $edit_config = $custom_configs[$edit_id];
                } else {
                    throw new Exception('加载失败：未找到该自定义配置');
                }
                break;

            case 'delete_config':
                $configId = trim($_POST['config_id'] ?? '');
                if (empty($configId)) throw new Exception('配置ID不能为空');
                ThumbnailService::deleteCustomConfig($configId);
                $_SESSION['flash_message'] = '配置已删除';
                $_SESSION['flash_message_type'] = 'success';
                header('Location: thumbnail-center.php#config-manager-tab');
                exit;
        }
    } catch (Exception $e) {
        $message = $e->getMessage();
        $message_type = 'danger';
    }
}

// 准备视图所需数据
$dev_query = isset($_GET['dev']) ? '?dev' : '';
$builtin_configs = ThumbnailService::getBuiltinConfigs();
$custom_configs = ThumbnailService::getCustomConfigs();
$all_configs = ThumbnailService::getAllConfigs();
$galleries = []; // 初始为空，由JS加载

// 包含布局和视图
require_once __DIR__ . '/../../Views/Admin/layouts/header.php';
require_once __DIR__ . '/../../Views/Admin/pages/thumbnail-center.php';
require_once __DIR__ . '/../../Views/Admin/layouts/footer.php';
