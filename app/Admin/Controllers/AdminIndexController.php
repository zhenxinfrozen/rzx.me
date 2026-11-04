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

        if ($page === 'login') {
            require_once __DIR__ . '/../Views/login.php';
            return;
        }

        // 定义可用的页面和对应的控制器
        $pages = [
            'dashboard' => [
                'view' => 'admin-dashboard',
                'controller' => null, // dashboard 只需要视图
                'title' => '控制台',
                'subtitle' => '管理和监控您的网站'
            ],
            'drafts' => [
                'view' => 'admin-drafts',
                'controller' => 'drafts',
                'title' => 'Drafts 草稿管理',
                'subtitle' => '管理草稿分类与图片'
            ],
            'sketchbook' => [
                'view' => 'admin-sketchbook',
                'controller' => 'sketchbook',
                'title' => 'Sketchbook 管理',
                'subtitle' => '管理 Sketchbook 页面图片集'
            ],
            'galleries' => [
                'view' => 'admin-galleries',
                'controller' => null,
                'title' => 'Galleries 画廊管理',
                'subtitle' => '管理前台 Galleries 页面显示的画廊集合'
            ],
            'comics' => [
                'view' => 'admin-comics',
                'controller' => 'comics',
                'title' => '漫画管理',
                'subtitle' => '管理漫画分组和图片'
            ],
            'videos' => [
                'view' => 'admin-videos',
                'controller' => null,
                'title' => 'Videos 管理',
                'subtitle' => '管理视频集合'
            ],
            'galleries-manager' => [
                'view' => null,
                'controller' => 'galleries-manager',
                'title' => 'Galleries 画廊管理',
                'subtitle' => '管理前台画廊页面显示的作品集'
            ],
            'thumbnail-manager' => [
                'view' => null,
                'controller' => 'thumbnail-manager',
                'title' => '整站缩略图管理',
                'subtitle' => '统计和管理整站的缩略图资源'
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
                'view' => 'admin-tools',
                'controller' => null,
                'title' => '管理工具',
                'subtitle' => '网站管理工具集'
            ],
            'thumbnail-center' => [
                'view' => 'admin-thumbnail-center',
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
                'view' => 'admin-docs',
                'controller' => 'docs-handler',
                'title' => '项目文档',
                'subtitle' => '查看项目文档和开发指南'
            ],
            'template-new' => [
                'view' => 'admin-template-new',
                'controller' => null,
                'title' => '📐 标准模板',
                'subtitle' => 'New 架构页面开发模板 - 仅供参考'
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
                if (in_array($page, ['thumbnail-center', 'videos', 'sketchbook', 'drafts', 'galleries'])) {
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
        require_once __DIR__ . '/../Views/layouts/admin-header.php';

        // 加载视图内容
        if ($pageConfig['view']) {
            $viewFile = __DIR__ . '/../Views/pages/' . $pageConfig['view'] . '.php';
            if (file_exists($viewFile)) {
                require_once $viewFile;
            } else {
                echo '<div class="admin-page-content">';
                echo '<div class="alert alert-error">视图文件不存在: ' . htmlspecialchars($pageConfig['view']) . '</div>';
                echo '</div>';
            }
        }

        require_once __DIR__ . '/../Views/layouts/admin-footer.php';
    }
}
