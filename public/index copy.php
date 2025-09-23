<?php
// Front controller - single entry point to the site
require_once __DIR__ . '/../app/bootstrap.php';
require_once __DIR__ . '/../app/view_renderer.php';

// Basic router: map request path to a view template under app/views
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
// Normalize: remove leading/trailing slashes
$path = trim($path, '/');

// Simple routing table (extend as needed)
$routes = [
    '' => 'index-body.php',
    'index.php' => 'index-body.php',
    'ray-comic.php' => 'ray-comic-body.php',
    'ray-pictures.php' => 'ray-pictures-body.php',
    'ray-animation.php' => 'ray-animation-body.php',
    'ray-latest.php' => 'ray-latest-body.php',
    'ray-sites.php' => 'ray-sites-body.php',
    'ray-sketch.php' => 'ray-sketch-body.php',
    'ray-about.php' => 'ray-about-body.php',
    'api.php' => null, // handled separately
];

// If request is to api.php, include the API handler directly
if ($path === 'api.php' || strpos($_SERVER['SCRIPT_NAME'] ?? '', '/api.php') !== false) {
    require_once __DIR__ . '/api.php';
    exit;
}

$viewFile = $routes[$path] ?? null;

// If not found in table but looks like a file under app/views, try to map directly
if ($viewFile === null && $path !== '') {
    $candidate = __DIR__ . '/../app/views/' . basename($path);
    if (file_exists($candidate)) {
        $viewFile = basename($path);
    }
}

// Prepare page title (simple default)
$title = 'rzx.me';

// Render standard page layout
?><!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title><?php echo htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></title>
    <link rel="icon" href="favicon.ico" />
    <link rel="stylesheet" href="<?php echo htmlspecialchars(rtrim(ASSET_URL, '/'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>/css/home_style.css" />
</head>
<body>
<?php
// header
try {
    echo render_template(__DIR__ . '/../app/views/header.php', ['title' => $title]);
} catch (Exception $e) {
    // fail silently and continue
}

// main content
if ($viewFile) {
    try {
        echo render_template(__DIR__ . '/../app/views/' . $viewFile);
    } catch (Exception $e) {
        http_response_code(500);
        echo '<h1>服务器错误</h1><p>无法加载视图。</p>';
    }
} else {
    // fallback: 404
    http_response_code(404);
    echo '<h1>404 Not Found</h1><p>请求的页面不存在。</p>';
}

// footer
try {
    echo render_template(__DIR__ . '/../app/views/footer.php');
} catch (Exception $e) {}
?>
</body>
</html>