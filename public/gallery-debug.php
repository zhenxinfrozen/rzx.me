<!DOCTYPE html>
<html>
<head>
    <title>Gallery System Debug - RZX.ME</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; line-height: 1.6; }
        .debug { background: #f5f5f5; padding: 15px; margin: 10px 0; border-left: 4px solid #007cba; }
        .error { background: #ffe6e6; border-left-color: #dc3545; }
        .success { background: #e6ffe6; border-left-color: #28a745; }
        pre { background: #f8f9fa; padding: 10px; overflow-x: auto; }
        .test-link { display: inline-block; margin: 5px 10px 5px 0; padding: 8px 12px; 
                     background: #007cba; color: white; text-decoration: none; border-radius: 4px; }
    </style>
</head>
<body>
    <h1>Gallery System Debug</h1>
    
    <div class="debug">
        <h3>Quick Tests</h3>
        <a href="/galleries" class="test-link">画廊列表</a>
        <a href="/gallery-test-gallery" class="test-link">测试画廊</a>
        <a href="/" class="test-link">返回首页</a>
    </div>
    
    <?php
    // 检查文件结构
    echo '<div class="debug">';
    echo '<h3>Directory Structure Check</h3>';
    
    $galleries_dir = '../public/assets/images/galleries';
    $test_gallery = '../public/assets/images/galleries/test-gallery';
    
    echo '<pre>';
    echo "Gallery Base Dir: " . (is_dir($galleries_dir) ? 'EXISTS' : 'MISSING') . "\n";
    echo "Test Gallery Dir: " . (is_dir($test_gallery) ? 'EXISTS' : 'MISSING') . "\n";
    
    if (is_dir($test_gallery)) {
        $files = glob($test_gallery . '/*.{jpg,jpeg,png,gif}', GLOB_BRACE);
        echo "Test Gallery Files: " . count($files) . " images found\n";
        foreach ($files as $file) {
            echo "  - " . basename($file) . "\n";
        }
    }
    echo '</pre>';
    echo '</div>';
    
    // 测试Gallery类
    echo '<div class="debug">';
    echo '<h3>Gallery Classes Test</h3>';
    echo '<pre>';
    
    try {
        // 尝试加载bootstrap
        if (file_exists('../app/bootstrap.php')) {
            require_once '../app/bootstrap.php';
            echo "Bootstrap loaded successfully\n";
            
            // 测试GalleryManager
            if (class_exists('GalleryManager')) {
                echo "GalleryManager class available\n";
                $gm = new GalleryManager();
                
                $galleries = $gm->scanGalleries();
                echo "Found galleries: " . count($galleries) . "\n";
                foreach ($galleries as $gallery) {
                    echo "  - $gallery\n";
                }
                
                if (in_array('test-gallery', $galleries)) {
                    $images = $gm->getGalleryImages('test-gallery');
                    echo "Test gallery images: " . count($images) . "\n";
                }
            } else {
                echo "GalleryManager class NOT available\n";
            }
        } else {
            echo "Bootstrap file not found\n";
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
    
    echo '</pre>';
    echo '</div>';
    
    // 测试路由
    echo '<div class="debug">';
    echo '<h3>Router Test</h3>';
    echo '<pre>';
    
    try {
        if (class_exists('Router')) {
            $router = new Router();
            
            $test_routes = ['/galleries', '/gallery-test-gallery', '/gallery-abc'];
            foreach ($test_routes as $test_route) {
                $result = $router->match($test_route);
                if ($result) {
                    echo "$test_route -> {$result['view']}\n";
                } else {
                    echo "$test_route -> NO MATCH\n";
                }
            }
        } else {
            echo "Router class not available\n";
        }
    } catch (Exception $e) {
        echo "Router error: " . $e->getMessage() . "\n";
    }
    
    echo '</pre>';
    echo '</div>';
    ?>
</body>
</html>