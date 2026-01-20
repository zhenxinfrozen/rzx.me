<?php
/**
 * Admin 后台管理系统入口控制器
 * 
 * 统一处理所有 /admin 路由请求，根据 page 参数加载对应的控制器和视图
 * 新架构：app/Controllers/Admin/ 和 app/Views/Admin/
 */

class AdminIndexController
{
    /**
     * 处理 admin 请求的主方法
     */
    public static function handle()
    {
        // 获取请求的页面
        $page = $_GET['page'] ?? 'dashboard';
        
        // 定义可用的页面和对应的控制器
        $pages = [
            'dashboard' => [
                'view' => 'body-dashboard',
                'controller' => null, // dashboard 只需要视图
                'title' => '控制台',
                'subtitle' => '管理和监控您的网站'
            ],
            'single-works' => [
                'view' => 'body-single-works',
                'controller' => 'single-works',  // 需要控制器处理逻辑
                'title' => 'Single-Works 管理',
                'subtitle' => '管理 Single-Works 页面分组与图片'
            ],
            'sketchbook' => [
                'view' => 'body-sketchbook',
                'controller' => 'sketchbook',  // 需要控制器处理逻辑
                'title' => 'Sketchbook 管理',
                'subtitle' => '管理 Sketchbook 页面图片集'
            ],
            'comics' => [
                'view' => 'body-comics',
                'controller' => 'comics',  // 需要控制器处理逻辑
                'title' => '漫画管理',
                'subtitle' => '管理漫画分组和图片'
            ],
            'video-gallery' => [
                'view' => 'body-video-gallery',
                'controller' => 'video-gallery',  // 需要控制器处理逻辑
                'title' => 'Video Gallery 管理',
                'subtitle' => '管理视频集合'
            ],
            'gallery-manager' => [
                'view' => null,
                'controller' => 'gallery-manager',
                'title' => '画廊管理',
                'subtitle' => '管理网站的图片画廊'
            ],
            'cache-manager' => [
                'view' => null,
                'controller' => 'cache-manager',
                'title' => '缓存管理',
                'subtitle' => '管理和优化网站缓存'
            ],
            'trash' => [
                'view' => null,
                'controller' => 'trash',
                'title' => '回收站',
                'subtitle' => '管理已删除的内容'
            ],
            'site-config' => [
                'view' => null,
                'controller' => 'site-config',
                'title' => '网站配置',
                'subtitle' => '管理网站的全局配置'
            ],
            'tools' => [
                'view' => 'tools',
                'controller' => null,
                'title' => '管理工具',
                'subtitle' => '网站管理工具集'
            ],
            'thumbnail-center' => [
                'view' => 'thumbnail-center',
                'controller' => 'thumbnail-center',
                'title' => '缩略图中心',
                'subtitle' => '管理和生成缩略图'
            ],
            'system-info' => [
                'view' => null,
                'controller' => 'system-info',
                'title' => '系统信息',
                'subtitle' => '查看服务器和PHP环境信息'
            ],
            'docs' => [
                'view' => 'body-docs',
                'controller' => 'docs-handler',
                'title' => '项目文档',
                'subtitle' => '查看项目文档和开发指南'
            ]
        ];
        
        // 检查页面是否存在
        if (!isset($pages[$page])) {
            // 默认重定向到 dashboard
            $page = 'dashboard';
        }
        
        $pageConfig = $pages[$page];
        
        // 设置页面信息（供 header.php 使用）
        $GLOBALS['page_title'] = $pageConfig['title'];
        $GLOBALS['page_subtitle'] = $pageConfig['subtitle'];
        $GLOBALS['current_page'] = $page;
        
        // 也设置为局部变量（兼容旧代码）
        $page_title = $pageConfig['title'];
        $page_subtitle = $pageConfig['subtitle'];
        $_GET['page'] = $page;
        
        // 如果是 AJAX 请求 docs 页面，特殊处理
        if ($page === 'docs' && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            require_once __DIR__ . '/docs-handler.php';
            exit;
        }
        
        // 加载控制器（如果有的话，控制器可能会设置变量或处理表单）
        if ($pageConfig['controller']) {
            $controllerFile = __DIR__ . '/' . $pageConfig['controller'] . '.php';
            if (file_exists($controllerFile)) {
                // 某些控制器可能需要 bootstrap
                if (in_array($page, ['thumbnail-center', 'video-gallery', 'sketchbook', 'single-works'])) {
                    require_once __DIR__ . '/../../bootstrap.php';
                }
                
                // comics 需要 Models
                if ($page === 'comics') {
                    define('ADMIN_ACCESS', true);
                    require_once __DIR__ . '/../../Models/comic_data.php';
                }
                
                // 包含控制器（但不输出，只执行逻辑）
                ob_start();
                require_once $controllerFile;
                $controller_output = ob_get_clean();
                
                // 如果控制器已经完全渲染（包含 header/footer），直接输出并退出
                if (strpos($controller_output, '</html>') !== false) {
                    echo $controller_output;
                    return;
                }
            }
        }
        
        // 标准布局：header + content + footer
        require_once __DIR__ . '/../../Views/Admin/layouts/header.php';
        
        // 加载视图内容
        if ($pageConfig['view']) {
            $viewFile = __DIR__ . '/../../Views/Admin/pages/' . $pageConfig['view'] . '.php';
            if (file_exists($viewFile)) {
                require_once $viewFile;
            } else {
                echo '<div class="admin-page-content">';
                echo '<div class="alert alert-error">视图文件不存在: ' . htmlspecialchars($pageConfig['view']) . '</div>';
                echo '</div>';
            }
        }
        
        require_once __DIR__ . '/../../Views/Admin/layouts/footer.php';
    }
}
