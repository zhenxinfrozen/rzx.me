<?php
// public/admin/controllers/thumbnail-config-manager.php - 缩略图配置管理页面
header('Content-Type: text/html; charset=UTF-8');
require_once '../../../app/bootstrap.php';
require_once '../../../app/Services/ThumbnailService.php';

// 简单的认证检查
session_start();
if (!isset($_SESSION['admin_authenticated']) && !isset($_GET['dev'])) {
    header('Location: ../login.php');
    exit;
}

// 设置页面信息
$page_title = '🎛️ 缩略图配置管理';
$page_subtitle = '统一的缩略图配置管理，支持预设管理、自定义配置、实时测试和预览功能';
$_GET['page'] = 'thumbnail-config-manager';

// 获取配置信息（来自合并后的服务）
$builtin = ThumbnailService::getBuiltinConfigs();
$custom = ThumbnailService::getCustomConfigs();
$configs = ThumbnailService::getAllConfigs();

// 处理表单提交
$message = '';
$messageType = 'info';

// 测试结果容器
// 测试结果容器
$testResult = null;

// 编辑回填
$editId = null;
$editConfig = null;

if ($_POST) {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'test_generate':
            $configId = $_POST['config_id'] ?? '';
            $testImageRel = trim($_POST['test_image'] ?? '');
            if (!$configId) {
                $message = '请选择配置';
                $messageType = 'danger';
                break;
            }
            if ($testImageRel === '') {
                $testImageRel = '000-gaodale/20230727_002747775_iOS.jpg';
            }
            $sourcePath = realpath(__DIR__ . '/../../assets/images/' . $testImageRel);
            if (!$sourcePath || !file_exists($sourcePath)) {
                $message = '测试图片不存在：' . htmlspecialchars($testImageRel);
                $messageType = 'danger';
                break;
            }
            $testResult = ThumbnailService::generate($sourcePath, $configId);
            if ($testResult['success']) {
                $message = '测试成功：已生成缩略图';
                $messageType = 'success';
            } else {
                $message = '生成失败：' . ($testResult['error'] ?? '未知错误');
                $messageType = 'danger';
            }
            break;

        case 'add_config':
            try {
                $configId = trim($_POST['config_id'] ?? '');
                if ($configId === '') throw new Exception('配置ID不能为空');
                $conf = [
                    'name' => trim($_POST['name'] ?? ''),
                    'width' => (int)($_POST['width'] ?? 0),
                    'height' => (int)($_POST['height'] ?? 0),
                    'quality' => (int)($_POST['quality'] ?? 80),
                    'format' => strtolower(trim($_POST['format'] ?? 'jpg')),
                    'directory' => trim($_POST['directory'] ?? 'thumbs'),
                    'suffix' => trim($_POST['suffix'] ?? '_thumb'),
                    'crop' => isset($_POST['crop']) ? true : false,
                ];
                ThumbnailService::addCustomConfig($configId, $conf);
                $message = '自定义配置已添加'; $messageType = 'success';
                $custom = ThumbnailService::getCustomConfigs(); $configs = ThumbnailService::getAllConfigs();
            } catch (Exception $e) {
                $message = '添加失败：' . $e->getMessage(); $messageType = 'danger';
            }
            break;

        case 'load_edit':
            $editId = trim($_POST['config_id'] ?? '');
            if ($editId !== '' && isset($custom[$editId])) {
                $editConfig = $custom[$editId];
            } else {
                $message = '加载失败：未找到该自定义配置'; $messageType = 'warning';
            }
            break;

        case 'update_config':
            try {
                $configId = trim($_POST['config_id'] ?? '');
                if ($configId === '') throw new Exception('配置ID不能为空');
                $conf = [
                    'name' => trim($_POST['name'] ?? ''),
                    'width' => (int)($_POST['width'] ?? 0),
                    'height' => (int)($_POST['height'] ?? 0),
                    'quality' => (int)($_POST['quality'] ?? 80),
                    'format' => strtolower(trim($_POST['format'] ?? 'jpg')),
                    'directory' => trim($_POST['directory'] ?? 'thumbs'),
                    'suffix' => trim($_POST['suffix'] ?? '_thumb'),
                    'crop' => isset($_POST['crop']) ? true : false,
                ];
                ThumbnailService::updateCustomConfig($configId, $conf);
                $message = '更新成功'; $messageType = 'success';
                $custom = ThumbnailService::getCustomConfigs(); $configs = ThumbnailService::getAllConfigs();
                $editId = null; $editConfig = null;
            } catch (Exception $e) {
                $message = '更新失败：' . $e->getMessage(); $messageType = 'danger';
            }
            break;

        case 'delete_config':
            try {
                $configId = trim($_POST['config_id'] ?? '');
                if ($configId === '') throw new Exception('配置ID不能为空');
                ThumbnailService::deleteCustomConfig($configId);
                $message = '删除成功'; $messageType = 'success';
                $custom = ThumbnailService::getCustomConfigs(); $configs = ThumbnailService::getAllConfigs();
            } catch (Exception $e) {
                $message = '删除失败：' . $e->getMessage(); $messageType = 'danger';
            }
            break;

        default:
            $message = '未知操作'; $messageType = 'danger';
    }
}

// 包含头部
require_once '../views/layouts/header.php';
?>

<!-- 缩略图配置管理页面 -->
<div class="container-fluid">
    
    <!-- 页面头部 -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <p>头部占位信息</p>
    </div>

    <!-- 统计卡片 -->
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card border-0" style="background: linear-gradient(135deg, #0ea5e9 0%, #06b6d4 100%); color: white;">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="bi bi-hdd-stack fs-1 opacity-75"></i>
                        </div>
                        <div class="ms-3">
                            <h5 class="mb-1">统一缩略图服务</h5>
                            <p class="mb-0 opacity-90">支持自定义配置的统一缩略图生成管理</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white;">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="bi bi-check2-square fs-1 opacity-75"></i>
                        </div>
                        <div class="ms-3">
                            <h5 class="mb-1">可用预设配置</h5>
                            <p class="mb-0 opacity-90"><?= count($builtin) ?> 个内置配置，<?= count($custom) ?> 个自定义配置</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 消息提示 -->
    <?php if ($message): ?>
    <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
        <?= $message ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <div class="row">
        <!-- 配置列表栏 -->
        <div class="card mt-3">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-list"></i> 所有配置 (<?= count($configs) ?>)
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>配置ID</th>
                                    <th>适用场景</th>
                                    <th>尺寸</th>
                                    <th>质量</th>
                                    <th>格式</th>
                                    <th>存放目录</th>
                                    <th>文件后缀</th>
                                    <th class="text-end">操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($configs as $id => $config): ?>
                                <tr>
                                    <td><code><?= $id ?></code></td>
                                    <td>
                                        <span class="text-muted"><?= $config['name'] ?></span>
                                        <?php if (!empty($config['builtin'])): ?>
                                            <span class="badge bg-info ms-1">内置</span>
                                        <?php else: ?>
                                            <span class="badge bg-success ms-1">自定义</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><strong><?= $config['width'] ?>×<?= $config['height'] ?></strong>px</td>
                                    <td><?= $config['quality'] ?>%</td>
                                    <td><span class="badge bg-secondary"><?= strtoupper($config['format']) ?></span></td>
                                    <td><code><?= $config['directory'] ?>/</code></td>
                                    <td><code><?= $config['suffix'] ?></code></td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-outline-primary" disabled>
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-success test-config" data-id="<?= $id ?>">
                                            <i class="bi bi-play"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

            <!-- 已保存的自定义预设 -->
            <div class="card mt-3">
                <div class="card-header" style="background:#ffc107;">
                    <h6 class="mb-0"><i class="bi bi-star-fill"></i> 已保存的自定义预设 (<?= count($custom) ?>)</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>预设ID</th>
                                    <th>名称</th>
                                    <th>尺寸</th>
                                    <th>质量</th>
                                    <th>格式</th>
                                    <th>目录</th>
                                    <th>后缀</th>
                                    <th class="text-end">操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($custom): foreach ($custom as $cid => $c): ?>
                                <tr>
                                    <td><code><?= htmlspecialchars($cid) ?></code></td>
                                    <td><?= htmlspecialchars($c['name'] ?? '') ?></td>
                                    <td><strong><?= (int)($c['width'] ?? 0) ?>×<?= (int)($c['height'] ?? 0) ?></strong></td>
                                    <td><?= (int)($c['quality'] ?? 0) ?>%</td>
                                    <td><span class="badge bg-secondary"><?= strtoupper($c['format'] ?? 'jpg') ?></span></td>
                                    <td><code><?= htmlspecialchars($c['directory'] ?? '') ?></code></td>
                                    <td><code><?= htmlspecialchars($c['suffix'] ?? '') ?></code></td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-outline-success test-config" data-id="<?= htmlspecialchars($cid) ?>">
                                            <i class="bi bi-play"></i>
                                        </button>
                                        <form method="post" class="d-inline">
                                            <input type="hidden" name="action" value="load_edit">
                                            <input type="hidden" name="config_id" value="<?= htmlspecialchars($cid) ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-warning">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                        </form>
                                        <form method="post" class="d-inline" onsubmit="return confirm('确定删除该配置吗？');">
                                            <input type="hidden" name="action" value="delete_config">
                                            <input type="hidden" name="config_id" value="<?= htmlspecialchars($cid) ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; else: ?>
                                <tr><td colspan="8" class="text-center text-muted py-3">暂无自定义预设</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        <!-- 右侧：测试区域 -->
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-play-circle"></i> 配置测试
                    </h5>
                </div>
                <div class="card-body">
                    <form method="post" id="test-form">
                        <input type="hidden" name="action" value="test_generate">
                        
                        <div class="mb-3">
                            <label for="config_id" class="form-label">选择配置</label>
                            <select class="form-select" id="config_id" name="config_id" required>
                                <option value="">请选择配置...</option>
                                <?php foreach ($configs as $id => $config): ?>
                                <option value="<?= $id ?>"><?= $config['name'] ?> (<?= $id ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="test_image" class="form-label">测试图片路径</label>
                            <input type="text" class="form-control" id="test_image" name="test_image" 
                                   placeholder="例如: galleries/test/image.jpg">
                            <div class="form-text">相对于 assets/images/ 的路径</div>
                        </div>
                        
                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-play"></i> 测试生成
                        </button>
                    </form>
                    
                    <?php if ($testResult): ?>
                    <hr>
                    <div class="alert alert-<?= $testResult['success'] ? 'success' : 'danger' ?>">
                        <div class="d-flex align-items-start gap-2">
                            <i class="bi <?= $testResult['success'] ? 'bi-check-circle' : 'bi-x-circle' ?> fs-5"></i>
                            <div>
                                <div class="fw-bold mb-1">测试结果</div>
                                <?php if ($testResult['success']): ?>
                                    <div class="small text-muted">原图片：<?= str_replace(realpath(__DIR__ . '/..'), '', $testResult['original_path']) ?></div>
                                    <div class="small text-muted">输出路径：<?= str_replace(realpath(__DIR__ . '/..'), '', $testResult['output_path']) ?></div>
                                    <?php if (!empty($testResult['file_size'])): ?>
                                        <div class="small text-muted">文件大小：<?= number_format($testResult['file_size']/1024, 2) ?> KB</div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div class="small text-muted">错误：<?= htmlspecialchars($testResult['error'] ?? '未知错误') ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <hr>
                    
                    <div class="mb-3">
                        <h6><i class="bi bi-info-circle text-info me-1"></i>使用说明</h6>
                        <ul class="small text-muted">
                            <li>选择一个配置方案</li>
                            <li>输入要测试的图片路径</li>
                            <li>点击测试生成按钮</li>
                            <li>查看生成结果和参数</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- 快速操作 -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-lightning"></i> 快速操作</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="tools.php<?= isset($_GET['dev']) ? '?dev' : '' ?>" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-tools"></i> 返回工具页面
                        </a>
                        <a href="../thumbnail-config-demo.php<?= isset($_GET['dev']) ? '?dev' : '' ?>" class="btn btn-outline-info btn-sm">
                            <i class="bi bi-eye"></i> 查看演示
                        </a>
                        <a href="thumbnail-manager.php<?= isset($_GET['dev']) ? '?dev' : '' ?>" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-image"></i> 缩略图管理
                        </a>
                    </div>
                </div>
            </div>

            <!-- 自定义预设管理 -->
            <div class="card mt-3">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="bi bi-sliders"></i> 自定义预设管理</h6>
                </div>
                <div class="card-body">
                    <form method="post">
                        <input type="hidden" name="action" value="<?= $editConfig ? 'update_config' : 'add_config' ?>">

                        <div class="mb-3">
                            <label class="form-label">配置ID *</label>
                            <input type="text" class="form-control" name="config_id" placeholder="例: my-custom-config" value="<?= htmlspecialchars($editId ?? '') ?>" <?= $editConfig ? 'readonly' : '' ?> required>
                            <div class="form-text">只能包含小写字母、数字、下划线和短横线</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">配置名称 *</label>
                            <input type="text" class="form-control" name="name" placeholder="例：我的自定义配置" value="<?= htmlspecialchars($editConfig['name'] ?? '') ?>" required>
                        </div>
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label">宽度 (px)</label>
                                <input type="number" class="form-control" name="width" value="<?= (int)($editConfig['width'] ?? 200) ?>" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label">高度 (px)</label>
                                <input type="number" class="form-control" name="height" value="<?= (int)($editConfig['height'] ?? 200) ?>" required>
                            </div>
                        </div>
                        <div class="row g-2 mt-2">
                            <div class="col-6">
                                <label class="form-label">质量 (%)</label>
                                <input type="number" class="form-control" name="quality" value="<?= (int)($editConfig['quality'] ?? 80) ?>" min="1" max="100" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label">格式</label>
                                <select class="form-select" name="format">
                                    <?php $fmt = strtolower($editConfig['format'] ?? 'jpg'); ?>
                                    <option value="jpg" <?= $fmt==='jpg'?'selected':'' ?>>JPG</option>
                                    <option value="png" <?= $fmt==='png'?'selected':'' ?>>PNG</option>
                                    <option value="webp" <?= $fmt==='webp'?'selected':'' ?>>WEBP</option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-2">
                            <label class="form-label">存放目录</label>
                            <input type="text" class="form-control" name="directory" value="<?= htmlspecialchars($editConfig['directory'] ?? 'thumbs') ?>">
                        </div>
                        <div class="mt-2">
                            <label class="form-label">文件后缀</label>
                            <input type="text" class="form-control" name="suffix" value="<?= htmlspecialchars($editConfig['suffix'] ?? '_thumb') ?>">
                        </div>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" value="1" id="cropCheck" name="crop" <?= !empty($editConfig['crop']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="cropCheck">
                                使用裁剪模式（保持长宽比并居中裁剪）
                            </label>
                        </div>
                        <div class="d-grid mt-3">
                            <button type="submit" class="btn btn-<?= $editConfig ? 'warning' : 'success' ?>">
                                <?= $editConfig ? '保存更新' : '添加配置' ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
                    <!-- 测试结果 -->
                    <?php if ($testResult): ?>
                    <hr>
                    <div class="alert alert-<?= $testResult['success'] ? 'success' : 'danger' ?>">
                        <div class="d-flex align-items-start gap-2">
                            <i class="bi <?= $testResult['success'] ? 'bi-check-circle' : 'bi-x-circle' ?> fs-5"></i>
                            <div>
                                <div class="fw-bold mb-1">测试结果</div>
                                <?php if ($testResult['success']): ?>
                                    <div class="small text-muted">原图片：<?= str_replace(realpath(__DIR__ . '/..'), '', $testResult['original_path']) ?></div>
                                    <div class="small text-muted">输出路径：<?= str_replace(realpath(__DIR__ . '/..'), '', $testResult['output_path']) ?></div>
                                    <?php if (!empty($testResult['file_size'])): ?>
                                        <div class="small text-muted">文件大小：<?= number_format($testResult['file_size']/1024, 2) ?> KB</div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div class="small text-muted">错误：<?= htmlspecialchars($testResult['error'] ?? '未知错误') ?></div>
                                <?php endif; ?>
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


    <!-- 配置说明 -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-book"></i> 配置说明
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <h6><strong>Gallery页面配置</strong></h6>
                            <p class="text-muted small">
                                适用于作品集展示页面，使用较大尺寸和高质量，
                                保证图片展示效果。
                            </p>
                        </div>
                        <div class="col-md-4">
                            <h6><strong>Single-Works页面配置</strong></h6>
                            <p class="text-muted small">
                                适用于单个作品详情页，中等尺寸平衡加载速度
                                和显示质量。
                            </p>
                        </div>
                        <div class="col-md-4">
                            <h6><strong>Sketch页面配置</strong></h6>
                            <p class="text-muted small">
                                适用于草图展示，较小尺寸加快页面加载，
                                适合批量浏览。
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// 测试配置按钮功能
document.addEventListener('DOMContentLoaded', function() {
    const testButtons = document.querySelectorAll('.test-config');
    const configSelect = document.getElementById('config_id');
    
    testButtons.forEach(button => {
        button.addEventListener('click', function() {
            const configId = this.getAttribute('data-id');
            configSelect.value = configId;
            
            // 滚动到测试表单
            document.getElementById('test-form').scrollIntoView({
                behavior: 'smooth'
            });
        });
    });
});
</script>

<?php include '../views/layouts/footer.php'; ?>
