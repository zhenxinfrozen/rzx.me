<?php
// public/admin/controllers/thumbnail-config-demo.php - 缩略图配置演示
require_once '../../../app/bootstrap.php';
require_once '../../../app/Services/ThumbnailService.php';
require_once '../../../app/Config/ThumbnailConfig.php';

// 简单的认证检查（开发模式允许访问）
session_start();
if (!isset($_SESSION['admin_authenticated']) && !isset($_GET['dev']) && !isset($_GET['debug'])) {
    // 开发环境临时允许直接访问
    if (in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost', '127.0.0.1']) || 
        strpos($_SERVER['HTTP_HOST'] ?? '', 'localhost:') === 0) {
        // 本地开发环境，允许访问
    } else {
        header('Location: ../login.php');
        exit;
    }
}

$pageTitle = "缩略图配置演示";
$currentPage = 'thumbnail-config-demo';

// 应用自定义配置
ThumbnailConfig::applyCustomConfigs();

// 处理表单提交
$testResults = [];
if ($_POST) {
    $action = $_POST['action'] ?? '';
    $imagePath = $_POST['image_path'] ?? '';
    $pageType = $_POST['page_type'] ?? 'gallery';
    
    if ($action === 'test_generate' && $imagePath) {
        // 测试生成缩略图
        $fullPath = __DIR__ . '/../../assets/images/' . $imagePath;
        if (file_exists($fullPath)) {
            $thumbnailPath = ThumbnailService::generateForPage($fullPath, $pageType);
            $testResults['generate'] = [
                'success' => $thumbnailPath !== false,
                'path' => $thumbnailPath ? str_replace(__DIR__ . '/../../', '', $thumbnailPath) : null,
                'config' => ThumbnailService::getAllPageConfigs()[$pageType] ?? []
            ];
        }
    }
    
    if ($action === 'test_custom') {
        // 测试自定义配置
        $customConfig = [
            'width' => (int)($_POST['width'] ?? 200),
            'height' => (int)($_POST['height'] ?? 200),
            'quality' => (int)($_POST['quality'] ?? 80),
            'directory' => $_POST['directory'] ?? 'custom-thumbs',
            'suffix' => $_POST['suffix'] ?? '_custom'
        ];
        
        if ($imagePath) {
            $fullPath = __DIR__ . '/../../assets/images/' . $imagePath;
            if (file_exists($fullPath)) {
                $thumbnailPath = ThumbnailService::generateForPage($fullPath, $pageType, $customConfig);
                $testResults['custom'] = [
                    'success' => $thumbnailPath !== false,
                    'path' => $thumbnailPath ? str_replace(__DIR__ . '/../../', '', $thumbnailPath) : null,
                    'config' => $customConfig
                ];
            }
        }
    }
}

// 获取所有可用配置
$allConfigs = ThumbnailService::getAllPageConfigs();
$customConfigs = ThumbnailConfig::getCustomConfigs();

include '../views/layouts/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-cogs"></i> 缩略图配置系统演示
                    </h3>
                </div>
                
                <div class="card-body">
                    
                    <!-- 配置说明 -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="info-box bg-info">
                                <span class="info-box-icon"><i class="fas fa-info-circle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">统一缩略图服务</span>
                                    <span class="info-box-number">
                                        支持页面自定义尺寸<br>
                                        支持存放目录自定义<br>
                                        支持质量和格式配置
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box bg-success">
                                <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">预设配置类型</span>
                                    <span class="info-box-number"><?php echo count($allConfigs); ?> 种页面类型</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- 预设配置展示 -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h4><i class="fas fa-list"></i> 预设页面配置</h4>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>页面类型</th>
                                            <th>尺寸</th>
                                            <th>质量</th>
                                            <th>格式</th>
                                            <th>存放目录</th>
                                            <th>文件后缀</th>
                                            <th>裁剪模式</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($allConfigs as $type => $config): ?>
                                        <tr>
                                            <td><code><?php echo $type; ?></code></td>
                                            <td><?php echo $config['width']; ?>×<?php echo $config['height']; ?>px</td>
                                            <td><?php echo $config['quality']; ?>%</td>
                                            <td><?php echo strtoupper($config['format']); ?></td>
                                            <td><code><?php echo $config['directory']; ?>/</code></td>
                                            <td><code><?php echo $config['suffix']; ?></code></td>
                                            <td><?php echo $config['crop'] ? '✅ 裁剪' : '❌ 缩放'; ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <!-- 测试工具 -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card card-primary">
                                <div class="card-header">
                                    <h4 class="card-title"><i class="fas fa-test-tube"></i> 预设配置测试</h4>
                                </div>
                                <form method="post" class="card-body">
                                    <input type="hidden" name="action" value="test_generate">
                                    
                                    <div class="form-group">
                                        <label>图片路径（相对于assets/images/）</label>
                                        <input type="text" name="image_path" class="form-control" 
                                               placeholder="例如: galleries/AAA/image.jpg" 
                                               value="<?php echo $_POST['image_path'] ?? ''; ?>">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>页面类型</label>
                                        <select name="page_type" class="form-control">
                                            <?php foreach ($allConfigs as $type => $config): ?>
                                            <option value="<?php echo $type; ?>" 
                                                    <?php echo ($_POST['page_type'] ?? '') === $type ? 'selected' : ''; ?>>
                                                <?php echo $type; ?> (<?php echo $config['width']; ?>×<?php echo $config['height']; ?>)
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
                        
                        <div class="col-md-6">
                            <div class="card card-warning">
                                <div class="card-header">
                                    <h4 class="card-title"><i class="fas fa-wrench"></i> 自定义配置测试</h4>
                                </div>
                                <form method="post" class="card-body">
                                    <input type="hidden" name="action" value="test_custom">
                                    
                                    <div class="form-group">
                                        <label>图片路径（相对于assets/images/）</label>
                                        <input type="text" name="image_path" class="form-control" 
                                               placeholder="例如: galleries/AAA/image.jpg" 
                                               value="<?php echo $_POST['image_path'] ?? ''; ?>">
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>宽度 (px)</label>
                                                <input type="number" name="width" class="form-control" 
                                                       value="<?php echo $_POST['width'] ?? 250; ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>高度 (px)</label>
                                                <input type="number" name="height" class="form-control" 
                                                       value="<?php echo $_POST['height'] ?? 250; ?>">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>质量 (%)</label>
                                                <input type="number" name="quality" class="form-control" 
                                                       min="1" max="100" value="<?php echo $_POST['quality'] ?? 85; ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>存放目录</label>
                                                <input type="text" name="directory" class="form-control" 
                                                       value="<?php echo $_POST['directory'] ?? 'custom-thumbs'; ?>">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>文件后缀</label>
                                        <input type="text" name="suffix" class="form-control" 
                                               value="<?php echo $_POST['suffix'] ?? '_custom'; ?>">
                                    </div>
                                    
                                    <button type="submit" class="btn btn-warning">
                                        <i class="fas fa-magic"></i> 自定义生成
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- 测试结果 -->
                    <?php if (!empty($testResults)): ?>
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card card-success">
                                <div class="card-header">
                                    <h4 class="card-title"><i class="fas fa-check-circle"></i> 测试结果</h4>
                                </div>
                                <div class="card-body">
                                    <?php foreach ($testResults as $testType => $result): ?>
                                    <div class="alert <?php echo $result['success'] ? 'alert-success' : 'alert-danger'; ?>">
                                        <h5><?php echo $testType === 'generate' ? '预设配置测试' : '自定义配置测试'; ?></h5>
                                        <p><strong>状态：</strong> <?php echo $result['success'] ? '✅ 成功' : '❌ 失败'; ?></p>
                                        <?php if ($result['success'] && $result['path']): ?>
                                        <p><strong>缩略图路径：</strong> <code><?php echo $result['path']; ?></code></p>
                                        <p><strong>使用配置：</strong></p>
                                        <pre><?php echo json_encode($result['config'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE); ?></pre>
                                        <?php endif; ?>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- 使用示例代码 -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card card-info">
                                <div class="card-header">
                                    <h4 class="card-title"><i class="fas fa-code"></i> 代码使用示例</h4>
                                </div>
                                <div class="card-body">
                                    <pre><code class="php">
                                        <?php 
                                        echo htmlspecialchars('// 1. 基本使用 - 页面预设配置
ThumbnailService::generateForPage($imagePath, \'gallery\');
ThumbnailService::generateForPage($imagePath, \'single-works\');
ThumbnailService::generateForPage($imagePath, \'sketch\');

// 2. 自定义配置
$customConfig = [
    \'width\' => 250,
    \'height\' => 250,
    \'quality\' => 90,
    \'directory\' => \'custom-thumbs\',
    \'suffix\' => \'_my_thumb\'
];
ThumbnailService::generateForPage($imagePath, \'gallery\', $customConfig);

// 3. 批量生成
ThumbnailService::generateBatchForPage($directoryPath, \'single-works\');

// 4. 获取缩略图路径
$thumbPath = ThumbnailService::getThumbnailPath($imagePath, \'gallery\');

// 5. 响应式配置
$mobileConfig = ThumbnailConfig::getResponsiveConfig(\'mobile\');
ThumbnailService::generateForPage($imagePath, \'gallery\', $mobileConfig);'); ?></code></pre>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../views/layouts/footer.php'; ?>