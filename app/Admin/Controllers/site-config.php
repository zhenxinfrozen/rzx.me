<?php
/**
 * 网站配置管理 - 基于 SharedAdmin site-settings 模板
 */

if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['admin_authenticated']) && !isset($_GET['dev'])) {
    header('Location: /admin?page=login');
    exit;
}

// 读取配置
$configFile = __DIR__ . '/../../Config/site_settings.php';
$site_settings = require $configFile;

// 保存回调：写入 config 文件
$site_settings_save_cb = function(array $newSettings) use ($configFile) {
    $existing = require $configFile;
    $merged = array_merge($existing, $newSettings);

    $content = "<?php\n";
    $content .= "// app/Config/site_settings.php\n";
    $content .= "// 网站全局配置 - 驱动 SharedAdmin site-settings 模板\n\n";
    $content .= "return [\n";
    foreach ($merged as $key => $value) {
        if (is_bool($value)) {
            $content .= "    '$key' => " . ($value ? 'true' : 'false') . ",\n";
        } elseif (is_int($value)) {
            $content .= "    '$key' => $value,\n";
        } else {
            $content .= "    '$key' => '" . addslashes($value) . "',\n";
        }
    }
    $content .= "];\n";

    return file_put_contents($configFile, $content) !== false;
};

// Git 分支信息
$git_branch_display = null;
$git_branch_cache = $site_settings['git_branch_cache'] ?? '';

// 图标上传配置
$icon_upload_dir = dirname(__DIR__, 3) . '/public/assets/img/icon/';
$icon_upload_url_prefix = '/assets/img/icon/';

// 页面标题
$page_title = '网站配置';
$page_subtitle = '自定义网站基本信息与视觉风格';
$_GET['page'] = 'site-config';

// 引入 shared-admin 布局
require_once __DIR__ . '/../Views/layouts/admin-header.php';

// 引入 shared-admin site-settings 模板
$sharedSiteSettings = dirname(__DIR__, 3) . '/shared-admin/src/views/pages/site-settings.php';
if (file_exists($sharedSiteSettings)) {
    require_once $sharedSiteSettings;
} else {
    echo "<div class='content-card'><h3>SharedAdmin site-settings 模板未找到</h3></div>";
}

// 引入底部
require_once __DIR__ . '/../Views/layouts/admin-footer.php';
