<?php
// 缩略图配置演示页面 - 独立版本
require_once __DIR__ . '/../../app/Services/ThumbnailService.php';

$pageTitle = "缩略图配置演示";

// 获取所有配置（内置 + 自定义）
$allConfigs = ThumbnailService::getAllPageConfigs();

// 分离内置配置和自定义配置
$builtinConfigs = [];
$customConfigs = [];

foreach ($allConfigs as $configId => $config) {
    if (isset($config['builtin']) && $config['builtin']) {
        $builtinConfigs[$configId] = $config;
    } else {
        $customConfigs[$configId] = $config;
    }
}

// 通用缩略图预设配置（为了显示目的，实际使用$allConfigs）
$configs = $builtinConfigs;

// 处理自定义预设保存
if ($_POST && ($_POST['action'] ?? '') === 'save_preset') {
    $presetName = trim($_POST['preset_name'] ?? '');
    $presetScene = trim($_POST['preset_scene'] ?? '');
    $presetConfig = [
        'name' => $presetScene ?: $presetName,
        'width' => (int)($_POST['preset_width'] ?? 200),
        'height' => (int)($_POST['preset_height'] ?? 200),
        'quality' => (int)($_POST['preset_quality'] ?? 80),
        'format' => $_POST['preset_format'] ?? 'jpg',
        'directory' => $_POST['preset_directory'] ?? 'custom',
        'suffix' => $_POST['preset_suffix'] ?? '_custom',
        'crop' => isset($_POST['preset_crop']),
        'builtin' => false
    ];
    
    if ($presetName && !empty($presetName)) {
        try {
            // 使用ThumbnailConfigManager保存配置
            require_once __DIR__ . '/../../app/Services/ThumbnailConfigManager.php';
            $success = ThumbnailConfigManager::addCustomConfig($presetName, $presetConfig);
            
            if ($success) {
                $result = [
                    'success' => true,
                    'message' => '自定义预设 "' . $presetName . '" 保存成功！',
                    'preset_saved' => true
                ];
                // 重新加载配置
                $allConfigs = ThumbnailService::getAllPageConfigs();
                $builtinConfigs = [];
                $customConfigs = [];
                foreach ($allConfigs as $configId => $config) {
                    if (isset($config['builtin']) && $config['builtin']) {
                        $builtinConfigs[$configId] = $config;
                    } else {
                        $customConfigs[$configId] = $config;
                    }
                }
                $configs = $builtinConfigs;
            } else {
                $result = [
                    'success' => false,
                    'message' => '预设保存失败',
                    'preset_saved' => false
                ];
            }
        } catch (Exception $e) {
            $result = [
                'success' => false,
                'message' => '保存错误: ' . $e->getMessage(),
                'preset_saved' => false
            ];
        }
    } else {
        $result = [
            'success' => false,
            'message' => '预设名称不能为空',
            'preset_saved' => false
        ];
    }
}

// 处理自定义预设删除
if ($_POST && ($_POST['action'] ?? '') === 'delete_preset') {
    $presetName = $_POST['preset_to_delete'] ?? '';
    if ($presetName && isset($customConfigs[$presetName])) {
        try {
            require_once __DIR__ . '/../../app/Services/ThumbnailConfigManager.php';
            $success = ThumbnailConfigManager::deleteCustomConfig($presetName);
            
            if ($success) {
                $result = [
                    'success' => true,
                    'message' => '自定义预设 "' . $presetName . '" 删除成功！',
                    'preset_deleted' => true
                ];
                // 重新加载配置
                $allConfigs = ThumbnailService::getAllPageConfigs();
                $builtinConfigs = [];
                $customConfigs = [];
                foreach ($allConfigs as $configId => $config) {
                    if (isset($config['builtin']) && $config['builtin']) {
                        $builtinConfigs[$configId] = $config;
                    } else {
                        $customConfigs[$configId] = $config;
                    }
                }
                $configs = $builtinConfigs;
            } else {
                $result = [
                    'success' => false,
                    'message' => '预设删除失败',
                    'preset_deleted' => false
                ];
            }
        } catch (Exception $e) {
            $result = [
                'success' => false,
                'message' => '删除错误: ' . $e->getMessage(),
                'preset_deleted' => false
            ];
        }
    }
}

// 初始化变量
$result = null;
$customPresets = $customConfigs;

// 处理测试请求
if ($_POST) {
    $action = $_POST['action'] ?? '';
    $imagePath = $_POST['image_path'] ?? '';
    
    if ($action === 'test' && $imagePath) {
        $pageType = $_POST['page_type'] ?? 'gallery';
        $config = $allConfigs[$pageType] ?? null;
        
        // 构建完整的源图片路径
        $fullImagePath = __DIR__ . '/../../public/assets/images/' . $imagePath;
        $fullImagePath = str_replace(['\\', '//'], '/', $fullImagePath);
        
        // 检查源文件是否存在
        if (file_exists($fullImagePath)) {
            try {
                // 调用真实的缩略图生成服务
                $thumbnailPath = ThumbnailService::generateForPage($fullImagePath, $pageType);
                
                if ($thumbnailPath) {
                    $relativePath = str_replace(__DIR__ . '/../../public/', '', $thumbnailPath);
                    $result = [
                        'success' => true,
                        'message' => '缩略图生成成功！',
                        'image_path' => $imagePath,
                        'page_type' => $pageType,
                        'config_used' => ThumbnailService::getAllPageConfigs()[$pageType] ?? $config,
                        'thumbnail_path' => $relativePath,
                        'full_source_path' => $fullImagePath,
                        'full_thumbnail_path' => $thumbnailPath
                    ];
                } else {
                    $result = [
                        'success' => false,
                        'message' => '缩略图生成失败：ThumbnailService返回空值',
                        'image_path' => $imagePath,
                        'page_type' => $pageType,
                        'config_used' => $config,
                        'error' => '生成服务返回失败'
                    ];
                }
            } catch (Exception $e) {
                $result = [
                    'success' => false,
                    'message' => '缩略图生成异常',
                    'image_path' => $imagePath,
                    'page_type' => $pageType,
                    'config_used' => $config,
                    'error' => $e->getMessage()
                ];
            }
        } else {
            $result = [
                'success' => false,
                'message' => '源文件不存在',
                'image_path' => $imagePath,
                'page_type' => $pageType,
                'config_used' => $config,
                'error' => '源文件路径: ' . $fullImagePath
            ];
        }
    } elseif ($action === 'test_custom_preset' && $imagePath) {
        // 测试自定义预设
        $presetName = $_POST['custom_preset'] ?? '';
        if ($presetName && isset($allConfigs[$presetName])) {
            $config = $allConfigs[$presetName];
            
            // 构建完整的源图片路径
            $fullImagePath = __DIR__ . '/../../public/assets/images/' . $imagePath;
            $fullImagePath = str_replace(['\\', '//'], '/', $fullImagePath);
            
            // 检查源文件是否存在
            if (file_exists($fullImagePath)) {
                try {
                    // 直接使用预设名称作为页面类型
                    $thumbnailPath = ThumbnailService::generateForPage($fullImagePath, $presetName);
                    
                    if ($thumbnailPath) {
                        $relativePath = str_replace(__DIR__ . '/../../public/', '', $thumbnailPath);
                        $result = [
                            'success' => true,
                            'message' => '自定义预设 "' . $presetName . '" 测试成功！',
                            'image_path' => $imagePath,
                            'preset_name' => $presetName,
                            'config_used' => $config,
                            'thumbnail_path' => $relativePath,
                            'full_source_path' => $fullImagePath,
                            'full_thumbnail_path' => $thumbnailPath
                        ];
                    } else {
                        $result = [
                            'success' => false,
                            'message' => '自定义预设缩略图生成失败',
                            'image_path' => $imagePath,
                            'preset_name' => $presetName,
                            'config_used' => $config,
                            'error' => 'ThumbnailService返回空值'
                        ];
                    }
                } catch (Exception $e) {
                    $result = [
                        'success' => false,
                        'message' => '自定义预设缩略图生成异常',
                        'image_path' => $imagePath,
                        'preset_name' => $presetName,
                        'config_used' => $config,
                        'error' => $e->getMessage()
                    ];
                }
            } else {
                $result = [
                    'success' => false,
                    'message' => '源文件不存在',
                    'image_path' => $imagePath,
                    'preset_name' => $presetName,
                    'config_used' => $config,
                    'error' => '源文件路径: ' . $fullImagePath
                ];
            }
        } else {
            $result = [
                'success' => false,
                'message' => '自定义预设不存在',
                'error' => '预设名称: ' . $presetName
            ];
        }
    } elseif ($action === 'custom' && $imagePath) {
        $customConfig = [
            'width' => (int)($_POST['width'] ?? 200),
            'height' => (int)($_POST['height'] ?? 200),
            'quality' => (int)($_POST['quality'] ?? 80),
            'format' => $_POST['format'] ?? 'jpg',
            'directory' => $_POST['directory'] ?? 'custom-thumbs',
            'suffix' => $_POST['suffix'] ?? '_custom',
            'crop' => isset($_POST['crop'])
        ];
        
        // 构建完整的源图片路径
        $fullImagePath = __DIR__ . '/../../public/assets/images/' . $imagePath;
        $fullImagePath = str_replace(['\\', '//'], '/', $fullImagePath);
        
        // 检查源文件是否存在
        if (file_exists($fullImagePath)) {
            try {
                // 使用gallery作为基础页面类型，但应用自定义配置
                $thumbnailPath = ThumbnailService::generateForPage($fullImagePath, 'gallery', $customConfig);
                
                if ($thumbnailPath) {
                    $relativePath = str_replace(__DIR__ . '/../../public/', '', $thumbnailPath);
                    $result = [
                        'success' => true,
                        'message' => '自定义配置缩略图生成成功！',
                        'image_path' => $imagePath,
                        'config_used' => $customConfig,
                        'thumbnail_path' => $relativePath,
                        'full_source_path' => $fullImagePath,
                        'full_thumbnail_path' => $thumbnailPath
                    ];
                } else {
                    $result = [
                        'success' => false,
                        'message' => '自定义缩略图生成失败',
                        'image_path' => $imagePath,
                        'config_used' => $customConfig,
                        'error' => 'ThumbnailService返回空值'
                    ];
                }
            } catch (Exception $e) {
                $result = [
                    'success' => false,
                    'message' => '自定义缩略图生成异常',
                    'image_path' => $imagePath,
                    'config_used' => $customConfig,
                    'error' => $e->getMessage()
                ];
            }
        } else {
            $result = [
                'success' => false,
                'message' => '源文件不存在',
                'image_path' => $imagePath,
                'config_used' => $customConfig,
                'error' => '源文件路径: ' . $fullImagePath
            ];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .card { border: none; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .info-card { color: white; padding: 1.5rem; border-radius: 8px; }
        .bg-info-custom { background: linear-gradient(135deg, #17a2b8, #138496); }
        .bg-success-custom { background: linear-gradient(135deg, #28a745, #20c997); }
        .col-lg-1-5 { flex: 0 0 auto; width: 20%; }
        @media (max-width: 992px) {
            .col-lg-1-5 { width: 50%; }
        }
        @media (max-width: 576px) {
            .col-lg-1-5 { width: 100%; }
        }
        pre { background: #f8f9fa; padding: 1rem; border-radius: 4px; border: 1px solid #dee2e6; }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <nav class="navbar navbar-expand-lg navbar-dark bg-dark rounded mb-4">
                    <div class="container-fluid">
                        <a class="navbar-brand" href="#">
                            <i class="fas fa-cogs"></i> 缩略图配置演示
                        </a>
                        <div class="navbar-nav ms-auto">
                            <a class="nav-link" href="thumbnail-config-manager.php">
                                <i class="fas fa-cog"></i> 配置管理
                            </a>
                        </div>
                    </div>
                </nav>
            </div>
        </div>

        <!-- 信息卡片 -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="info-card bg-info-custom">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-info-circle fa-3x me-3"></i>
                        <div>
                            <h5 class="mb-1">统一缩略图服务</h5>
                            <small>支持页面自定义尺寸和存放目录配置</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="info-card bg-success-custom">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-check-circle fa-3x me-3"></i>
                        <div>
                            <h5 class="mb-1">可用预设配置</h5>
                            <small><?= count($configs) ?> 种通用预设 + <?= count($customPresets) ?> 种自定义预设</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 通用预设配置表格 -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-table"></i> 通用缩略图预设</h5>
                        <small>常用的缩略图规格，适用于不同场景</small>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>预设名称</th>
                                        <th>适用场景</th>
                                        <th>尺寸</th>
                                        <th>质量</th>
                                        <th>格式</th>
                                        <th>存放目录</th>
                                        <th>文件后缀</th>
                                        <th>裁剪模式</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    foreach ($configs as $type => $config): ?>
                                    <tr>
                                        <td><code><?= $type ?></code></td>
                                        <td><small class="text-muted"><?= $config['name'] ?? $type ?></small></td>
                                        <td><strong><?= $config['width'] ?>×<?= $config['height'] ?></strong>px</td>
                                        <td><?= $config['quality'] ?>%</td>
                                        <td><span class="badge bg-secondary"><?= strtoupper($config['format']) ?></span></td>
                                        <td><code><?= $config['directory'] ?>/</code></td>
                                        <td><code><?= $config['suffix'] ?></code></td>
                                        <td>
                                            <?php if ($config['crop']): ?>
                                                <span class="badge bg-success">✓ 裁剪</span>
                                            <?php else: ?>
                                                <span class="badge bg-info">缩放</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 已保存的自定义预设 -->
        <?php if (!empty($customConfigs)): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-user-cog"></i> 已保存的自定义预设</h5>
                        <small>您创建的自定义缩略图配置</small>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>预设名称</th>
                                        <th>适用场景</th>
                                        <th>尺寸</th>
                                        <th>质量</th>
                                        <th>格式</th>
                                        <th>存放目录</th>
                                        <th>文件后缀</th>
                                        <th>裁剪模式</th>
                                        <th>操作</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($customConfigs as $type => $config): ?>
                                    <tr>
                                        <td><code><?= $type ?></code></td>
                                        <td><small class="text-muted"><?= $config['name'] ?? $type ?></small></td>
                                        <td><strong><?= $config['width'] ?>×<?= $config['height'] ?></strong>px</td>
                                        <td><?= $config['quality'] ?>%</td>
                                        <td><span class="badge bg-secondary"><?= strtoupper($config['format']) ?></span></td>
                                        <td><code><?= $config['directory'] ?>/</code></td>
                                        <td><code><?= $config['suffix'] ?></code></td>
                                        <td>
                                            <?php if ($config['crop']): ?>
                                                <span class="badge bg-success">✓ 裁剪</span>
                                            <?php else: ?>
                                                <span class="badge bg-info">缩放</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <form method="post" class="d-inline" onsubmit="return confirm('确定要删除预设 <?= htmlspecialchars($type) ?> 吗？')">
                                                <input type="hidden" name="action" value="delete_preset">
                                                <input type="hidden" name="preset_to_delete" value="<?= htmlspecialchars($type) ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="fas fa-trash"></i> 删除
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- 自定义预设管理 -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-info">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-user-cog"></i> 自定义预设管理</h5>
                        <small>创建和管理您自己的缩略图预设配置</small>
                    </div>
                    <div class="card-body">
                        
                        <!-- 添加新预设表单 -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="border rounded p-3 bg-light">
                                    <h6 class="mb-3"><i class="fas fa-plus-circle"></i> 创建新预设</h6>
                                    <form method="post">
                                        <input type="hidden" name="action" value="save_preset">
                                        
                                        <div class="row">
                                            <div class="col-lg-3">
                                                <div class="mb-3">
                                                    <label for="preset_name" class="form-label">预设名称 <span class="text-danger">*</span></label>
                                                    <input type="text" name="preset_name" id="preset_name" class="form-control" 
                                                           placeholder="例如: my-gallery-thumb" required>
                                                    <div class="form-text">用作配置ID，需唯一</div>
                                                </div>
                                            </div>
                                            <div class="col-lg-3">
                                                <div class="mb-3">
                                                    <label for="preset_scene" class="form-label">适用场景</label>
                                                    <input type="text" name="preset_scene" id="preset_scene" class="form-control" 
                                                           placeholder="例如: 商品展示缩略图">
                                                    <div class="form-text">描述该预设的用途</div>
                                                </div>
                                            </div>
                                            <div class="col-lg-1-5">
                                                <div class="mb-3">
                                                    <label for="preset_width" class="form-label">宽度</label>
                                                    <input type="number" name="preset_width" id="preset_width" class="form-control" 
                                                           min="1" max="2000" value="400">
                                                </div>
                                            </div>
                                            <div class="col-lg-1-5">
                                                <div class="mb-3">
                                                    <label for="preset_height" class="form-label">高度</label>
                                                    <input type="number" name="preset_height" id="preset_height" class="form-control" 
                                                           min="1" max="2000" value="400">
                                                </div>
                                            </div>
                                            <div class="col-lg-1-5">
                                                <div class="mb-3">
                                                    <label for="preset_quality" class="form-label">质量%</label>
                                                    <input type="number" name="preset_quality" id="preset_quality" class="form-control" 
                                                           min="1" max="100" value="85">
                                                </div>
                                            </div>
                                            <div class="col-lg-1-5">
                                                <div class="mb-3">
                                                    <label for="preset_format" class="form-label">格式</label>
                                                    <select name="preset_format" id="preset_format" class="form-select">
                                                        <option value="jpg">JPG</option>
                                                        <option value="png">PNG</option>
                                                        <option value="webp">WebP</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-lg-4">
                                                <div class="mb-3">
                                                    <label for="preset_directory" class="form-label">存放目录</label>
                                                    <input type="text" name="preset_directory" id="preset_directory" class="form-control" 
                                                           value="custom" placeholder="相对于assets/images/">
                                                </div>
                                            </div>
                                            <div class="col-lg-4">
                                                <div class="mb-3">
                                                    <label for="preset_suffix" class="form-label">文件后缀</label>
                                                    <input type="text" name="preset_suffix" id="preset_suffix" class="form-control" 
                                                           value="_custom" placeholder="添加到文件名后">
                                                </div>
                                            </div>
                                            <div class="col-lg-4">
                                                <div class="mb-3">
                                                    <div class="form-check mt-4">
                                                        <input class="form-check-input" type="checkbox" name="preset_crop" id="preset_crop">
                                                        <label class="form-check-label" for="preset_crop">
                                                            裁剪模式
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <button type="submit" class="btn btn-info">
                                            <i class="fas fa-save"></i> 保存预设
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        
                    </div>
                </div>
            </div>
        </div>

        <!-- 测试表单 -->
        <div class="row">
            <div class="col-lg-4 mb-4">
                <div class="card border-primary">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-play"></i> 通用预设测试</h5>
                    </div>
                    <form method="post" class="card-body">
                        <input type="hidden" name="action" value="test">
                        
                        <div class="mb-3">
                            <label for="image_path1" class="form-label">图片路径</label>
                            <input type="text" name="image_path" id="image_path1" class="form-control" 
                                   placeholder="例如: galleries/AAA/image.jpg" 
                                   value="<?= $_POST['image_path'] ?? 'test-image.jpg' ?>">
                            <div class="form-text">相对于 assets/images/ 的路径</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="page_type" class="form-label">通用预设</label>
                            <select name="page_type" id="page_type" class="form-select">
                                <?php foreach ($configs as $type => $config): ?>
                                <option value="<?= $type ?>" <?= ($_POST['page_type'] ?? '') === $type ? 'selected' : '' ?>>
                                    <?= $sceneNames[$type] ?? $type ?> (<?= $config['width'] ?>×<?= $config['height'] ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-play"></i> 测试生成
                        </button>
                    </form>
                </div>
            </div>

            <!-- 自定义预设测试 -->
            <?php if (!empty($customPresets)): ?>
            <div class="col-lg-4 mb-4">
                <div class="card border-info">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-user-cog"></i> 自定义预设测试</h5>
                    </div>
                    <form method="post" class="card-body">
                        <input type="hidden" name="action" value="test_custom_preset">
                        
                        <div class="mb-3">
                            <label for="image_path_preset" class="form-label">图片路径</label>
                            <input type="text" name="image_path" id="image_path_preset" class="form-control" 
                                   placeholder="例如: galleries/AAA/image.jpg" 
                                   value="<?= $_POST['image_path'] ?? 'preset-test.jpg' ?>">
                            <div class="form-text">相对于 assets/images/ 的路径</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="custom_preset" class="form-label">选择预设</label>
                            <select name="custom_preset" id="custom_preset" class="form-select" required>
                                <option value="">-- 选择自定义预设 --</option>
                                <?php foreach ($customPresets as $presetName => $preset): ?>
                                <option value="<?= htmlspecialchars($presetName) ?>" 
                                        <?= ($_POST['custom_preset'] ?? '') === $presetName ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($presetName) ?> (<?= $preset['width'] ?>×<?= $preset['height'] ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-info">
                            <i class="fas fa-play"></i> 测试预设
                        </button>
                    </form>
                </div>
            </div>
            <?php endif; ?>

            <div class="col-lg-4 mb-4">
                <div class="card border-warning">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="fas fa-magic"></i> 完全自定义测试</h5>
                    </div>
                    <form method="post" class="card-body">
                        <input type="hidden" name="action" value="custom">
                        
                        <div class="mb-3">
                            <label for="image_path2" class="form-label">图片路径</label>
                            <input type="text" name="image_path" id="image_path2" class="form-control" 
                                   placeholder="例如: galleries/AAA/image.jpg" 
                                   value="<?= $_POST['image_path'] ?? 'custom-test.jpg' ?>">
                            <div class="form-text">相对于 assets/images/ 的路径</div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="width" class="form-label">宽度 (px)</label>
                                    <input type="number" name="width" id="width" class="form-control" min="1" max="2000"
                                           value="<?= $_POST['width'] ?? 250 ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="height" class="form-label">高度 (px)</label>
                                    <input type="number" name="height" id="height" class="form-control" min="1" max="2000"
                                           value="<?= $_POST['height'] ?? 250 ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="quality" class="form-label">质量 (%)</label>
                                    <input type="number" name="quality" id="quality" class="form-control" min="1" max="100"
                                           value="<?= $_POST['quality'] ?? 85 ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="format" class="form-label">格式</label>
                                    <select name="format" id="format" class="form-select">
                                        <option value="jpg" <?= ($_POST['format'] ?? 'jpg') === 'jpg' ? 'selected' : '' ?>>JPG</option>
                                        <option value="png" <?= ($_POST['format'] ?? '') === 'png' ? 'selected' : '' ?>>PNG</option>
                                        <option value="webp" <?= ($_POST['format'] ?? '') === 'webp' ? 'selected' : '' ?>>WebP</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="directory" class="form-label">存放目录</label>
                                    <input type="text" name="directory" id="directory" class="form-control" 
                                           value="<?= $_POST['directory'] ?? 'custom-thumbs' ?>">
                                    <div class="form-text">相对于 assets/images/ 的子目录</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="suffix" class="form-label">文件后缀</label>
                                    <input type="text" name="suffix" id="suffix" class="form-control" 
                                           value="<?= $_POST['suffix'] ?? '_custom' ?>">
                                    <div class="form-text">添加到原文件名后</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="crop" id="crop" 
                                       <?= isset($_POST['crop']) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="crop">
                                    <strong>裁剪模式</strong>
                                    <small class="text-muted d-block">启用后将裁剪图片以完全填充目标尺寸，禁用则按比例缩放</small>
                                </label>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-magic"></i> 自定义生成
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- 测试结果 -->
        <?php if ($result): ?>
        <div class="row">
            <div class="col-12">
                <div class="card <?= $result['success'] ? 'border-success' : 'border-danger' ?>">
                    <div class="card-header <?= $result['success'] ? 'bg-success' : 'bg-danger' ?> text-white">
                        <h5 class="mb-0">
                            <i class="fas <?= $result['success'] ? 'fa-check-circle' : 'fa-exclamation-triangle' ?>"></i> 
                            测试结果
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="alert <?= $result['success'] ? 'alert-success' : 'alert-danger' ?>">
                            <h6>
                                <i class="fas <?= $result['success'] ? 'fa-thumbs-up' : 'fa-thumbs-down' ?>"></i> 
                                <?= $result['message'] ?>
                            </h6>
                            
                            <div class="mt-3">
                                <p><strong>原始图片：</strong> <code><?= htmlspecialchars($result['image_path']) ?></code></p>
                                
                                <?php if ($result['success'] && isset($result['thumbnail_path'])): ?>
                                    <p><strong>缩略图路径：</strong> <code><?= htmlspecialchars($result['thumbnail_path']) ?></code></p>
                                    
                                    <?php if (isset($result['full_source_path'])): ?>
                                    <p><strong>源文件完整路径：</strong> <small><code><?= htmlspecialchars($result['full_source_path']) ?></code></small></p>
                                    <?php endif; ?>
                                    
                                    <?php if (isset($result['full_thumbnail_path'])): ?>
                                    <p><strong>缩略图完整路径：</strong> <small><code><?= htmlspecialchars($result['full_thumbnail_path']) ?></code></small></p>
                                    <?php endif; ?>
                                    
                                <?php elseif (!$result['success'] && isset($result['error'])): ?>
                                    <p><strong>错误信息：</strong> <span class="text-danger"><?= htmlspecialchars($result['error']) ?></span></p>
                                <?php endif; ?>
                                
                                <?php if (isset($result['page_type'])): ?>
                                <p><strong>页面类型：</strong> <span class="badge bg-info"><?= $result['page_type'] ?></span></p>
                                <?php endif; ?>
                                
                                <?php if (isset($result['preset_name'])): ?>
                                <p><strong>使用预设：</strong> <span class="badge bg-info"><?= htmlspecialchars($result['preset_name']) ?></span></p>
                                <?php endif; ?>
                                
                                <p><strong>使用的配置：</strong></p>
                                <pre class="bg-light border"><?= json_encode($result['config_used'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?></pre>
                                
                                <?php if ($result['success'] && isset($result['thumbnail_path'])): ?>
                                <div class="mt-3">
                                    <h6>✨ 生成成功提示：</h6>
                                    <ul class="list-unstyled mb-0">
                                        <li>✅ 缩略图文件已生成</li>
                                        <li>📁 检查目标目录中的文件</li>
                                        <li>🔍 可在文件系统中验证结果</li>
                                    </ul>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- 代码示例 -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card border-info">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-code"></i> 使用示例代码</h5>
                    </div>
                    <div class="card-body">
                        <pre><code>// 1. 使用预设配置
ThumbnailService::generateForPage($imagePath, 'gallery');
ThumbnailService::generateForPage($imagePath, 'single-works');
ThumbnailService::generateForPage($imagePath, 'sketch');

// 2. 自定义配置覆盖
$customConfig = [
    'width' => 250,
    'height' => 250,
    'quality' => 90,
    'directory' => 'my-thumbs',
    'suffix' => '_custom'
];
ThumbnailService::generateForPage($imagePath, 'gallery', $customConfig);

// 3. 批量生成
ThumbnailService::generateBatchForPage($directoryPath, 'single-works');

// 4. 获取缩略图路径
$thumbPath = ThumbnailService::getThumbnailPath($imagePath, 'gallery');</code></pre>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>