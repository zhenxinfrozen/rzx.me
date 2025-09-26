<?php
// public/admin/thumbnail-test-simple.php - 简化测试页面，用于调试问题
require_once '../../app/bootstrap.php';
require_once '../../app/Services/ThumbnailService.php';

// 开启错误显示
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$pageTitle = "缩略图测试调试";

// 处理测试请求
$testResult = null;
if ($_POST && isset($_POST['test'])) {
    $imagePath = trim($_POST['image_path']);
    $configType = $_POST['config_type'] ?? 'gallery';
    
    if ($imagePath) {
        $fullPath = __DIR__ . '/../assets/images/' . $imagePath;
        
        echo "<h3>测试详情：</h3>";
        echo "<p><strong>输入路径：</strong> {$imagePath}</p>";
        echo "<p><strong>完整路径：</strong> {$fullPath}</p>";
        echo "<p><strong>文件存在：</strong> " . (file_exists($fullPath) ? '✅ 是' : '❌ 否') . "</p>";
        
        if (file_exists($fullPath)) {
            echo "<p><strong>文件大小：</strong> " . filesize($fullPath) . " bytes</p>";
            echo "<p><strong>文件权限：</strong> " . substr(sprintf('%o', fileperms($fullPath)), -4) . "</p>";
            
            // 测试ImageProcessor
            require_once '../../app/Utils/ImageProcessor.php';
            echo "<p><strong>GD可用：</strong> " . (ImageProcessor::isGdAvailable() ? '✅ 是' : '❌ 否') . "</p>";
            
            $imageInfo = ImageProcessor::getImageInfo($fullPath);
            if ($imageInfo) {
                echo "<p><strong>图片信息：</strong> {$imageInfo['width']}x{$imageInfo['height']}, {$imageInfo['mime']}</p>";
            } else {
                echo "<p><strong>图片信息：</strong> ❌ 获取失败</p>";
            }
            
            // 测试缩略图生成
            echo "<hr><h3>缩略图生成测试：</h3>";
            
            try {
                // 自定义简单配置
                $customConfig = [
                    'width' => 150,
                    'height' => 150,
                    'quality' => 80,
                    'format' => 'jpg',
                    'crop' => false,
                    'suffix' => '_test',
                    'directory' => 'test-thumbs'
                ];
                
                echo "<p><strong>测试配置：</strong></p>";
                echo "<pre>" . json_encode($customConfig, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
                
                $result = ThumbnailService::generateForPage($fullPath, $configType, $customConfig);
                
                if ($result) {
                    $relativePath = str_replace(__DIR__ . '/../', '', $result);
                    echo "<p><strong>生成结果：</strong> ✅ 成功</p>";
                    echo "<p><strong>缩略图路径：</strong> {$relativePath}</p>";
                    echo "<p><strong>缩略图存在：</strong> " . (file_exists($result) ? '✅ 是' : '❌ 否') . "</p>";
                    if (file_exists($result)) {
                        echo "<p><strong>缩略图大小：</strong> " . filesize($result) . " bytes</p>";
                        echo "<img src='/{$relativePath}' style='max-width: 200px; border: 1px solid #ccc;' alt='生成的缩略图'>";
                    }
                } else {
                    echo "<p><strong>生成结果：</strong> ❌ 失败</p>";
                    
                    // 尝试直接使用ThumbnailGenerator
                    require_once '../../app/Utils/ThumbnailGenerator.php';
                    echo "<hr><h4>直接测试ThumbnailGenerator：</h4>";
                    
                    $directResult = ThumbnailGenerator::generate($fullPath, null, $customConfig);
                    echo "<p><strong>直接生成结果：</strong> " . ($directResult ? "✅ 成功: {$directResult}" : "❌ 失败") . "</p>";
                }
                
            } catch (Exception $e) {
                echo "<p><strong>异常：</strong> <span style='color: red;'>" . $e->getMessage() . "</span></p>";
                echo "<p><strong>堆栈跟踪：</strong></p>";
                echo "<pre>" . $e->getTraceAsString() . "</pre>";
            }
        }
    }
}

// 获取一些示例图片路径
$sampleImages = [];
$imageDirs = [
    'galleries',
    'single-works',
    'albums'
];

foreach ($imageDirs as $dir) {
    $fullDir = __DIR__ . "/../assets/images/{$dir}";
    if (is_dir($fullDir)) {
        $subdirs = scandir($fullDir);
        foreach ($subdirs as $subdir) {
            if ($subdir === '.' || $subdir === '..') continue;
            $subdirPath = $fullDir . '/' . $subdir;
            if (is_dir($subdirPath)) {
                $files = scandir($subdirPath);
                foreach ($files as $file) {
                    if (preg_match('/\.(jpg|jpeg|png|gif)$/i', $file)) {
                        $sampleImages[] = "{$dir}/{$subdir}/{$file}";
                        if (count($sampleImages) >= 10) break 3; // 最多10个样例
                    }
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; font-family: monospace; }
        .container { max-width: 1000px; }
        .card { margin-bottom: 20px; }
        pre { font-size: 12px; }
    </style>
</head>
<body>

<div class="container mt-4">
    <h1>🔧 缩略图测试调试页面</h1>
    
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5>测试缩略图生成</h5>
        </div>
        <div class="card-body">
            <form method="post">
                <div class="mb-3">
                    <label class="form-label">图片路径（相对于assets/images/）：</label>
                    <input type="text" name="image_path" class="form-control" 
                           value="<?php echo $_POST['image_path'] ?? ''; ?>" 
                           placeholder="例如: galleries/AAA/image.jpg" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">配置类型：</label>
                    <select name="config_type" class="form-select">
                        <option value="gallery">Gallery (300x300)</option>
                        <option value="single-works">Single Works (200x200)</option>
                        <option value="sketch">Sketch (150x150)</option>
                        <option value="icon">Icon (100x100)</option>
                    </select>
                </div>
                
                <button type="submit" name="test" class="btn btn-primary">开始测试</button>
            </form>
            
            <?php if (!empty($sampleImages)): ?>
            <hr>
            <h6>可用的示例图片路径：</h6>
            <ul class="list-unstyled">
                <?php foreach (array_slice($sampleImages, 0, 5) as $sample): ?>
                <li>
                    <code><?php echo $sample; ?></code>
                    <button class="btn btn-sm btn-outline-secondary ms-2" onclick="document.querySelector('input[name=image_path]').value='<?php echo $sample; ?>'">使用</button>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
        </div>
    </div>
    
    <?php if (isset($_POST['test'])): ?>
    <div class="card">
        <div class="card-header bg-info text-white">
            <h5>测试结果</h5>
        </div>
        <div class="card-body">
            <!-- 测试结果已在上面显示 -->
        </div>
    </div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-header bg-secondary text-white">
            <h5>系统信息</h5>
        </div>
        <div class="card-body">
            <p><strong>PHP版本：</strong> <?php echo PHP_VERSION; ?></p>
            <p><strong>GD扩展：</strong> <?php echo extension_loaded('gd') ? '✅ 已安装' : '❌ 未安装'; ?></p>
            <p><strong>当前工作目录：</strong> <?php echo getcwd(); ?></p>
            <p><strong>错误报告级别：</strong> <?php echo error_reporting(); ?></p>
            <p><strong>assets/images目录：</strong> <?php echo is_dir(__DIR__ . '/../assets/images') ? '✅ 存在' : '❌ 不存在'; ?></p>
        </div>
    </div>
</div>

</body>
</html>