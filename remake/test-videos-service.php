<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== Testing CategoryService for Videos ===\n\n";

require_once 'app/Admin/Services/Media/ImageService.php';
require_once 'app/Admin/Services/Media/VideoService.php';
require_once 'app/Admin/Services/Media/ConfigService.php';
require_once 'app/Admin/Services/Media/CategoryService.php';

use App\Admin\Services\Media\ImageService;
use App\Admin\Services\Media\VideoService;
use App\Admin\Services\Media\ConfigService;
use App\Admin\Services\Media\CategoryService;

try {
    echo "✓ All services loaded\n\n";

    $imageService = new ImageService();
    echo "✓ ImageService instantiated\n";

    $videoService = new VideoService();
    echo "✓ VideoService instantiated\n";

    $configService = new ConfigService();
    echo "✓ ConfigService instantiated\n";

    $categoryService = new CategoryService($imageService, $videoService, $configService);
    echo "✓ CategoryService instantiated\n\n";

    $baseDir = __DIR__ . '/public/assets/videos/video-gallery';
    $configPath = __DIR__ . '/app/storage/data/video-gallery-sort.json';

    echo "Base Dir: $baseDir\n";
    echo "Exists: " . (is_dir($baseDir) ? 'YES' : 'NO') . "\n";
    echo "Config Path: $configPath\n";
    echo "Config Exists: " . (file_exists($configPath) ? 'YES' : 'NO') . "\n\n";

    $result = $categoryService->getCategories($baseDir, 'videos', $configPath);

    echo "Success: " . ($result['success'] ? 'YES' : 'NO') . "\n";
    echo "Category Count: " . count($result['categories'] ?? []) . "\n\n";

    if (!empty($result['categories'])) {
        foreach ($result['categories'] as $cat) {
            echo "- {$cat['id']}: {$cat['display_name']} ({$cat['video_count']} videos)\n";
        }
    } else {
        echo "No categories found!\n";
        if (isset($result['error'])) {
            echo "Error: {$result['error']}\n";
        }
    }

} catch (\Exception $e) {
    echo "\n✗ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
