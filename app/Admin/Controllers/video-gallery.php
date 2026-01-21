<?php
/**
 * Video Gallery 管理控制器
 * 负责视频分组排序、视频管理及相关 AJAX 操作
 */

define('ADMIN_ACCESS', true);

require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/../../Models/video_data.php';

// 开发模式跳过认证检查
if (!isset($_GET['dev'])) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['admin_authenticated'])) {
        header('Location: ../login.php');
        exit;
    }
} else {
    // 开发模式：模拟已登录状态
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['admin_authenticated'] = true;
}

$ajaxAction = $_GET['ajax'] ?? ($_POST['ajax_action'] ?? null);
$configPath = __DIR__ . '/../../storage/data/video-gallery-sort.json';
$videosRoot = __DIR__ . '/../../../public/assets/videos/video-gallery';

if ($ajaxAction) {
    handleVideoGalleryAjax($ajaxAction, $configPath, $videosRoot);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    handleVideoGalleryFormSubmission($configPath);
    header('Location: ' . ($_SERVER['REQUEST_URI'] ?? './video-gallery.php'));
    exit;
}

$currentConfig = loadVideoGalleryConfig($configPath);
$videoGroups = get_all_video_groups(false);

// 获取所有实际存在的分组
$categories = array_keys($videoGroups);
$orderedCategories = reorderCategories($categories, $currentConfig['custom_order'] ?? []);

$categoryData = [];
foreach ($orderedCategories as $index => $category) {
    $groupData = $videoGroups[$category] ?? [];
    $videoCount = count($groupData['videos'] ?? []);

    $firstThumb = null;
    if (!empty($groupData['videos'][0]['poster'])) {
        $firstThumb = $groupData['videos'][0]['poster'];
    }

    $categoryData[] = [
        'id' => $category,
        'display_name' => $currentConfig['display_names'][$category] ?? $category,
        'description' => $currentConfig['descriptions'][$category] ?? '',
        'video_count' => $videoCount,
        'position' => $index + 1,
        'thumbnail' => $firstThumb,
        'first_image_thumb' => $firstThumb
    ];
}

$flashMessage = $_SESSION['video_gallery_flash'] ?? null;
unset($_SESSION['video_gallery_flash']);

$page_title = '🎬 Video Gallery 管理';
$page_subtitle = '管理视频分组与文件';
$_GET['page'] = 'video-gallery';

// 控制器逻辑完成，返回给 AdminIndexController 渲染视图

/**
 * 处理表单提交
 */
function handleVideoGalleryFormSubmission(string $configPath): void
{
    try {
        $config = [
            'sort_method' => $_POST['sort_method'] ?? 'custom_order',
            'custom_order' => [],
            'display_names' => $_POST['display_names'] ?? [],
            'descriptions' => $_POST['descriptions'] ?? [],
        ];

        if (!empty($_POST['category_order'])) {
            $config['custom_order'] = array_values(array_filter(array_map('trim', explode(',', $_POST['category_order']))));
        }

        if (saveVideoGalleryConfig($configPath, $config)) {
            $_SESSION['video_gallery_flash'] = ['type' => 'success', 'text' => '配置保存成功！'];
        } else {
            throw new RuntimeException('配置保存失败，请检查文件权限');
        }
    } catch (Throwable $e) {
        $_SESSION['video_gallery_flash'] = ['type' => 'error', 'text' => '保存失败：' . $e->getMessage()];
    }
}

/**
 * 加载配置
 */
function loadVideoGalleryConfig(string $path): array
{
    if (!file_exists($path)) {
        return [
            'sort_method' => 'custom_order',
            'custom_order' => [],
            'display_names' => [],
            'descriptions' => [],
        ];
    }
    $config = include $path;
    return is_array($config) ? $config : [];
}

/**
 * 保存配置
 */
function saveVideoGalleryConfig(string $path, array $config): bool
{
    $json = json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    return file_put_contents($path, $json) !== false;
}

/**
 * 重新排序分类
 */
function reorderCategories(array $categories, array $customOrder): array
{
    $ordered = [];
    $remaining = array_combine($categories, $categories);

    foreach ($customOrder as $cat) {
        if (in_array($cat, $categories)) {
            $ordered[] = $cat;
            unset($remaining[$cat]);
        }
    }

    return array_merge($ordered, array_values($remaining));
}

/**
 * 处理 AJAX 请求
 */
function handleVideoGalleryAjax(string $action, string $configPath, string $videosRoot): void
{
    header('Content-Type: application/json; charset=utf-8');

    try {
        switch ($action) {
            case 'scan_directory':
                $videoGroups = get_all_video_groups(true);
                echo json_encode(['success' => true, 'groups' => count($videoGroups), 'data' => $videoGroups]);
                break;

            case 'get_group_videos':
                $groupId = $_GET['group_id'] ?? $_POST['group_id'] ?? '';
                $videoGroups = get_all_video_groups(false);

                if (isset($videoGroups[$groupId])) {
                    echo json_encode(['success' => true, 'videos' => $videoGroups[$groupId]['videos']]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Group not found']);
                }
                break;

            case 'videos':
                $category = $_GET['category'] ?? '';
                if (!$category) {
                    echo json_encode(['success' => false, 'message' => 'Category required']);
                    break;
                }

                $videoGroups = get_all_video_groups(false);
                if (!isset($videoGroups[$category])) {
                    echo json_encode(['success' => false, 'message' => 'Category not found']);
                    break;
                }

                $videos = $videoGroups[$category]['videos'] ?? [];
                echo json_encode([
                    'success' => true,
                    'videos' => $videos,
                    'current_thumbnail' => null // 视频分组没有单一缩略图概念
                ]);
                break;

            case 'category_thumbnail':
                $category = $_GET['category'] ?? '';
                if (!$category) {
                    echo json_encode(['success' => false, 'message' => 'Category required']);
                    break;
                }

                $videoGroups = get_all_video_groups(false);
                $thumbnail = null;
                $firstVideoThumb = null;

                if (isset($videoGroups[$category]['videos']) && !empty($videoGroups[$category]['videos'])) {
                    $firstVideo = $videoGroups[$category]['videos'][0];
                    $firstVideoThumb = $firstVideo['poster'] ?? null;
                }

                echo json_encode([
                    'success' => true,
                    'thumbnail' => $thumbnail,
                    'first_video_thumb' => $firstVideoThumb
                ]);
                break;

            case 'save_category':
                $data = json_decode(file_get_contents('php://input'), true);
                $category = $data['category'] ?? '';
                $displayName = $data['displayName'] ?? '';
                $description = $data['description'] ?? '';

                if (!$category) {
                    echo json_encode(['success' => false, 'message' => 'Category required']);
                    break;
                }

                // 更新配置
                $config = loadVideoGalleryConfig($configPath);
                $config['display_names'][$category] = $displayName;
                $config['descriptions'][$category] = $description;
                saveVideoGalleryConfig($configPath, $config);

                // 同时更新实际数据文件中的title字段，使前台生效
                $videoGroups = get_all_video_groups(false);
                if (isset($videoGroups[$category])) {
                    $videoGroups[$category]['title'] = $displayName;
                    $videoGroups[$category]['description'] = $description;
                    save_video_groups($videoGroups);
                }

                echo json_encode(['success' => true]);
                break;

            case 'delete_category':
                $data = json_decode(file_get_contents('php://input'), true);
                $category = $data['category'] ?? '';

                if (!$category) {
                    echo json_encode(['success' => false, 'message' => 'Category required']);
                    break;
                }

                // 删除分组目录和文件
                $categoryPath = $videosRoot . '/' . $category;
                if (is_dir($categoryPath)) {
                    deleteDirectory($categoryPath);
                }

                // 从视频数据中完全移除分组
                $allGroups = get_all_video_groups(false);
                if (isset($allGroups[$category])) {
                    unset($allGroups[$category]);
                    save_video_groups($allGroups);
                    error_log("Video Gallery: Removed category '$category' from video data");
                }

                // 更新配置
                $config = loadVideoGalleryConfig($configPath);
                unset($config['display_names'][$category]);
                unset($config['descriptions'][$category]);
                if (($key = array_search($category, $config['custom_order'])) !== false) {
                    unset($config['custom_order'][$key]);
                    $config['custom_order'] = array_values($config['custom_order']);
                }
                saveVideoGalleryConfig($configPath, $config);

                echo json_encode(['success' => true, 'message' => '分组已完全删除']);
                break;

            case 'save_order':
                $data = json_decode(file_get_contents('php://input'), true);
                $order = $data['order'] ?? '';

                $config = loadVideoGalleryConfig($configPath);
                $config['custom_order'] = array_filter(explode(',', $order));

                if (saveVideoGalleryConfig($configPath, $config)) {
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['success' => false, 'message' => '保存失败']);
                }
                break;

            case 'upload_videos':
                $category = $_POST['category'] ?? '';
                if (!$category) {
                    echo json_encode(['success' => false, 'message' => 'Category required']);
                    break;
                }

                $categoryPath = $videosRoot . '/' . $category;
                if (!is_dir($categoryPath)) {
                    mkdir($categoryPath, 0755, true);
                }

                $uploaded = 0;
                $errors = [];

                if (!empty($_FILES['videos']['name'])) {
                    $files = $_FILES['videos'];
                    $fileCount = count($files['name']);

                    for ($i = 0; $i < $fileCount; $i++) {
                        if ($files['error'][$i] === UPLOAD_ERR_OK) {
                            $tmpName = $files['tmp_name'][$i];
                            $originalName = $files['name'][$i];
                            $filePath = $categoryPath . '/' . $originalName;

                            if (move_uploaded_file($tmpName, $filePath)) {
                                $uploaded++;
                            } else {
                                $errors[] = "上传失败: $originalName";
                            }
                        }
                    }
                }

                if ($uploaded > 0) {
                    // 重新扫描以更新数据
                    get_all_video_groups(true);
                    echo json_encode([
                        'success' => true,
                        'message' => "成功上传 $uploaded 个视频",
                        'total_count' => count(get_all_video_groups(false)[$category]['videos'] ?? [])
                    ]);
                } else {
                    echo json_encode(['success' => false, 'message' => '没有文件被上传']);
                }
                break;

            case 'delete_video':
                $data = json_decode(file_get_contents('php://input'), true);
                $category = $data['category'] ?? '';
                $videoTitle = $data['video'] ?? '';

                if (!$category || !$videoTitle) {
                    echo json_encode(['success' => false, 'message' => 'Category and video required']);
                    break;
                }

                $categoryPath = $videosRoot . '/' . $category;
                $videoPath = $categoryPath . '/' . $videoTitle;

                error_log("Video Gallery: Attempting to delete video - Category: $category, Video: $videoTitle");
                error_log("Video Gallery: Video path: $videoPath");

                if (file_exists($videoPath)) {
                    // 删除视频文件
                    $videoDeleted = unlink($videoPath);
                    error_log("Video Gallery: Video file deletion result: " . ($videoDeleted ? 'SUCCESS' : 'FAILED'));

                    // 删除对应的缩略图文件
                    $baseName = pathinfo($videoTitle, PATHINFO_FILENAME);
                    $thumbnailExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                    $thumbnailsDeleted = 0;

                    foreach ($thumbnailExtensions as $ext) {
                        $thumbnailPath = $categoryPath . '/' . $baseName . '.' . $ext;
                        if (file_exists($thumbnailPath)) {
                            if (unlink($thumbnailPath)) {
                                $thumbnailsDeleted++;
                                error_log("Video Gallery: Deleted thumbnail: $thumbnailPath");
                            }
                        }
                    }

                    error_log("Video Gallery: Total thumbnails deleted: $thumbnailsDeleted");

                    // 直接从数据中移除视频，而不是依赖重新扫描
                    $allGroups = get_all_video_groups(false); // 不自动生成缩略图
                    if (isset($allGroups[$category]['videos'])) {
                        // 找到并移除匹配的视频
                        $videos = &$allGroups[$category]['videos'];
                        foreach ($videos as $key => $video) {
                            // 检查视频的sources中是否包含要删除的文件名
                            $shouldRemove = false;
                            if (isset($video['sources'])) {
                                foreach ($video['sources'] as $format => $sourcePath) {
                                    $sourceFileName = basename($sourcePath);
                                    if ($sourceFileName === $videoTitle) {
                                        $shouldRemove = true;
                                        break;
                                    }
                                }
                            }
                            // 或者检查title是否匹配
                            if ($video['title'] === $videoTitle) {
                                $shouldRemove = true;
                            }

                            if ($shouldRemove) {
                                unset($videos[$key]);
                                error_log("Video Gallery: Removed video from data: {$video['title']}");
                                break;
                            }
                        }
                        // 重新索引数组
                        $allGroups[$category]['videos'] = array_values($videos);
                        save_video_groups($allGroups);
                        error_log("Video Gallery: Updated video data saved");
                    }

                    $updatedCount = count($allGroups[$category]['videos'] ?? []);

                    echo json_encode([
                        'success' => true,
                        'video_deleted' => $videoDeleted,
                        'thumbnails_deleted' => $thumbnailsDeleted,
                        'total_count' => $updatedCount,
                        'message' => $videoDeleted ? '视频及缩略图已删除' : '视频删除失败'
                    ]);
                } else {
                    error_log("Video Gallery: Video file not found: $videoPath");
                    echo json_encode(['success' => false, 'message' => '视频文件不存在']);
                }
                break;

            case 'upload_thumbnail':
                $category = $_POST['category'] ?? '';
                if (!$category) {
                    echo json_encode(['success' => false, 'message' => 'Category required']);
                    break;
                }

                if (!empty($_FILES['thumbnail'])) {
                    $file = $_FILES['thumbnail'];
                    if ($file['error'] === UPLOAD_ERR_OK) {
                        $thumbnailDir = __DIR__ . '/../../../public/assets/videos/thumbnails';
                        if (!is_dir($thumbnailDir)) {
                            mkdir($thumbnailDir, 0755, true);
                        }

                        $thumbnailPath = $thumbnailDir . '/' . $category . '.jpg';
                        if (move_uploaded_file($file['tmp_name'], $thumbnailPath)) {
                            echo json_encode([
                                'success' => true,
                                'thumbnail_url' => '/assets/videos/thumbnails/' . $category . '.jpg'
                            ]);
                        } else {
                            echo json_encode(['success' => false, 'message' => '缩略图保存失败']);
                        }
                    } else {
                        echo json_encode(['success' => false, 'message' => '缩略图上传失败']);
                    }
                } else {
                    echo json_encode(['success' => false, 'message' => '没有缩略图文件']);
                }
                break;

            case 'delete_thumbnail':
                $data = json_decode(file_get_contents('php://input'), true);
                $category = $data['category'] ?? '';

                if (!$category) {
                    echo json_encode(['success' => false, 'message' => 'Category required']);
                    break;
                }

                $thumbnailDir = __DIR__ . '/../../../public/assets/videos/thumbnails';
                $thumbnailPath = $thumbnailDir . '/' . $category . '.jpg';
                $fileDeleted = false;

                // 确保目录存在（虽然正常情况下应该存在）
                if (!is_dir($thumbnailDir)) {
                    error_log("Video Gallery: Thumbnails directory not found for category '$category'");
                } elseif (file_exists($thumbnailPath)) {
                    $fileDeleted = unlink($thumbnailPath);
                    error_log("Video Gallery: Deleted custom thumbnail for category '$category': " . ($fileDeleted ? 'SUCCESS' : 'FAILED'));
                } else {
                    error_log("Video Gallery: Custom thumbnail file not found for category '$category' at: $thumbnailPath");
                }

                // 返回第一张视频的缩略图
                $videoGroups = get_all_video_groups(false);
                $newThumbnailUrl = null;
                if (isset($videoGroups[$category]['videos']) && !empty($videoGroups[$category]['videos'])) {
                    $firstVideo = $videoGroups[$category]['videos'][0];
                    $newThumbnailUrl = $firstVideo['poster'] ?? null;
                    error_log("Video Gallery: New thumbnail URL for category '$category': " . ($newThumbnailUrl ?: 'NONE'));
                }

                echo json_encode([
                    'success' => true,
                    'file_deleted' => $fileDeleted,
                    'new_thumbnail_url' => $newThumbnailUrl,
                    'message' => $fileDeleted ? '自定义缩略图已删除' : '缩略图文件不存在或已删除'
                ]);
                break;

            case 'create_category':
                $category = $_POST['category'] ?? '';
                $displayName = $_POST['displayName'] ?? '';
                $description = $_POST['description'] ?? '';
                $position = $_POST['position'] ?? 'last';

                if (!$category) {
                    echo json_encode(['success' => false, 'message' => 'Category required']);
                    break;
                }

                // 创建目录
                $categoryPath = $videosRoot . '/' . $category;
                if (!is_dir($categoryPath)) {
                    mkdir($categoryPath, 0755, true);
                }

                // 更新配置
                $config = loadVideoGalleryConfig($configPath);
                $config['display_names'][$category] = $displayName;
                $config['descriptions'][$category] = $description;

                if ($position === 'first') {
                    array_unshift($config['custom_order'], $category);
                } else {
                    $config['custom_order'][] = $category;
                }

                saveVideoGalleryConfig($configPath, $config);

                // 处理上传的视频文件
                $thumbnailInfo = null;
                if (!empty($_FILES['videos']['name'])) {
                    $files = $_FILES['videos'];
                    $fileCount = count($files['name']);

                    for ($i = 0; $i < $fileCount; $i++) {
                        if ($files['error'][$i] === UPLOAD_ERR_OK) {
                            $tmpName = $files['tmp_name'][$i];
                            $originalName = $files['name'][$i];
                            $filePath = $categoryPath . '/' . $originalName;
                            move_uploaded_file($tmpName, $filePath);
                        }
                    }

                    // 重新扫描以生成缩略图
                    get_all_video_groups(true);

                    // 获取第一张视频缩略图
                    $videoGroups = get_all_video_groups(false);
                    if (isset($videoGroups[$category]['videos']) && !empty($videoGroups[$category]['videos'])) {
                        $firstVideo = $videoGroups[$category]['videos'][0];
                        $thumbnailInfo = [
                            'first_video_thumb' => $firstVideo['poster'] ?? null
                        ];
                    }
                }

                echo json_encode([
                    'success' => true,
                    'category' => $category,
                    'thumbnail_info' => $thumbnailInfo
                ]);
                break;

            case 'reorder_videos':
                $data = json_decode(file_get_contents('php://input'), true);
                $category = $data['category'] ?? '';
                $order = $data['order'] ?? [];

                if (!$category || empty($order)) {
                    echo json_encode(['success' => false, 'message' => 'Category and order required']);
                    break;
                }

                // 重新排序视频：order是视频索引数组
                $videoGroups = get_all_video_groups(false);
                if (isset($videoGroups[$category]['videos'])) {
                    $videos = $videoGroups[$category]['videos'];
                    $orderedVideos = [];

                    foreach ($order as $index) {
                        if (isset($videos[$index])) {
                            $orderedVideos[] = $videos[$index];
                        }
                    }

                    $videoGroups[$category]['videos'] = $orderedVideos;
                    save_video_groups($videoGroups);
                }

                echo json_encode(['success' => true]);
                break;

            case 'php_config':
                $config = [
                    'upload_max_filesize' => ini_get('upload_max_filesize'),
                    'post_max_size' => ini_get('post_max_size'),
                    'max_file_uploads' => ini_get('max_file_uploads'),
                    'max_execution_time' => ini_get('max_execution_time'),
                ];

                $issues = [];
                $recommendations = [];

                if (str_replace('M', '', $config['upload_max_filesize']) < 50) {
                    $issues[] = '单文件大小限制过小';
                    $recommendations[] = '建议设置 upload_max_filesize = 500M';
                }
                if (str_replace('M', '', $config['post_max_size']) < 500) {
                    $issues[] = 'POST数据大小限制过小';
                    $recommendations[] = '建议设置 post_max_size = 1000M';
                }
                if ($config['max_execution_time'] < 300) {
                    $issues[] = '执行时间限制过短';
                    $recommendations[] = '建议设置 max_execution_time = 300';
                }

                echo json_encode([
                    'config' => $config,
                    'issues' => $issues,
                    'recommendations' => $recommendations,
                    'status' => empty($issues) ? 'ok' : 'warning'
                ]);
                break;

            default:
                echo json_encode(['success' => false, 'message' => 'Unknown action: ' . $action]);
        }
    } catch (Throwable $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

/**
 * 递归删除目录
 */
function deleteDirectory(string $dir): bool
{
    if (!is_dir($dir)) {
        return false;
    }

    $files = array_diff(scandir($dir), ['.', '..']);
    foreach ($files as $file) {
        $path = $dir . '/' . $file;
        if (is_dir($path)) {
            deleteDirectory($path);
        } else {
            unlink($path);
        }
    }

    return rmdir($dir);
}
