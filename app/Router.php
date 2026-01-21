<?php
/**
 * 路由处理引擎 - 现代化路由处理类
 *
 * 功能:
 * - 解析HTTP请求路径
 * - 匹配路由规则(精确匹配 + 正则匹配)
 * - 区分页面/API/静态文件请求类型
 * - 提供路由查询和验证方法
 *
 * 配置: 读取 app/Config/routes.php 配置文件
 * 使用: 由 public/index.php 实例化和调用
 */

class Router
{
    private $routes = [];
    private $currentRoute = null;

    public function __construct()
    {
        // 加载路由配置
        $routeConfig = config('routes');
        if ($routeConfig) {
            $this->routes = $routeConfig;
        }
    }

    /**
     * 解析当前请求路径
     * @return string
     */
    public function getCurrentPath()
    {
        // 1. 优先检查 query string 中的 route 参数 (例如 index.php?route=/about)
        // 即使开启伪静态，也允许通过参数访问，方便调试或兼容
        if (isset($_GET['route']) && !empty($_GET['route'])) {
            $path = $_GET['route'];
            // 确保以/开头
            if (strpos($path, '/') !== 0) {
                $path = '/' . $path;
            }
            return $path;
        }

        $path = $_SERVER['REQUEST_URI'] ?? '/';

        // 移除查询参数
        if (($pos = strpos($path, '?')) !== false) {
            $path = substr($path, 0, $pos);
        }

        // 2. 处理 /index.php 直接访问的情况 (非伪静态模式下的首页)
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
        if ($path === $scriptName) {
            return '/';
        }

        // 确保以/开头
        if (strpos($path, '/') !== 0) {
            $path = '/' . $path;
        }

        return $path;
    }

    /**
     * 路由匹配
     * @param string $path
     * @return array|null
     */
    public function match($path = null)
    {
        if ($path === null) {
            $path = $this->getCurrentPath();
        }

        // 检查页面路由
        if (isset($this->routes['pages'][$path])) {
            $this->currentRoute = $this->routes['pages'][$path];
            $this->currentRoute['type'] = 'page';
            $this->currentRoute['path'] = $path;
            return $this->currentRoute;
        }

        // 检查静态文件路由 (优先检查，避免拦截静态资源)
        foreach ($this->routes['static'] ?? [] as $pattern => $route) {
            if ($this->matchPattern($pattern, $path)) {
                // 静态文件直接返回，不处理
                if ($route['allow_direct'] ?? false) {
                    $this->currentRoute = $route;
                    $this->currentRoute['type'] = 'static';
                    $this->currentRoute['path'] = $path;
                    return $this->currentRoute;
                }
            }
        }

        // 检查API路由
        if (isset($this->routes['api'][$path])) {
            $route = $this->routes['api'][$path];

            // 检查HTTP方法
            $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
            if (isset($route['method'])) {
                $allowedMethods = is_array($route['method']) ? $route['method'] : [$route['method']];
                if (!in_array($method, $allowedMethods)) {
                    return null;
                }
            }

            $this->currentRoute = $route;
            // 使用路由配置中的type，如果没有则默认为'api'
            $this->currentRoute['type'] = $route['type'] ?? 'api';
            $this->currentRoute['path'] = $path;
            return $this->currentRoute;
        }

        // 动态路由匹配（正则表达式路由）
        foreach ($this->routes['pages'] ?? [] as $pattern => $route) {
            // 跳过已经检查过的精确路径
            if ($pattern === $path) {
                continue;
            }

            if ($this->matchPattern($pattern, $path)) {
                $this->currentRoute = $route;
                $this->currentRoute['type'] = $route['type'] ?? 'page';
                $this->currentRoute['path'] = $path;
                $this->currentRoute['pattern'] = $pattern;
                return $this->currentRoute;
            }
        }

        return null;
    }

    /**
     * 模式匹配（支持正则表达式和:param格式）
     * @param string $pattern
     * @param string $path
     * @return bool
     */
    private function matchPattern($pattern, $path)
    {
        // 正则表达式路由 (以~开头和结尾)
        if (preg_match('/^~(.+)~$/', $pattern, $matches)) {
            $regex = $matches[1];
            // 调试输出
            error_log("Matching pattern: $pattern against path: $path");
            error_log("Extracted regex: $regex");

            $result = preg_match($regex, $path);
            error_log("Match result: " . ($result ? 'true' : 'false'));

            return $result;
        }

        // 精确匹配
        return $pattern === $path;
    }

    /**
     * 获取当前路由
     * @return array|null
     */
    public function getCurrentRoute()
    {
        return $this->currentRoute;
    }

    /**
     * 生成404错误路由
     * @return array
     */
    public function get404Route()
    {
        return $this->routes['errors']['404'] ?? [
            'view' => '404.php',
            'title' => '页面未找到 - RZX.ME',
            'type' => 'error'
        ];
    }

    /**
     * 获取页面标题
     * @param array $route
     * @return string
     */
    public function getPageTitle($route = null)
    {
        if ($route === null) {
            $route = $this->currentRoute;
        }

        return $route['title'] ?? config('views.default_title', 'RZX.ME');
    }

    /**
     * 检查是否为API请求
     * @param array $route
     * @return bool
     */
    public function isApiRoute($route = null)
    {
        if ($route === null) {
            $route = $this->currentRoute;
        }

        return ($route['type'] ?? '') === 'api';
    }

    /**
     * 检查是否为页面请求
     * @param array $route
     * @return bool
     */
    public function isPageRoute($route = null)
    {
        if ($route === null) {
            $route = $this->currentRoute;
        }

        return ($route['type'] ?? '') === 'page';
    }

    /**
     * 检查是否为静态文件请求
     * @param array $route
     * @return bool
     */
    public function isStaticRoute($route = null)
    {
        if ($route === null) {
            $route = $this->currentRoute;
        }

        return ($route['type'] ?? '') === 'static';
    }

    /**
     * 检查是否为admin AJAX请求
     * @param array $route
     * @return bool
     */
    public function isAdminAjaxRoute($route = null)
    {
        if ($route === null) {
            $route = $this->currentRoute;
        }

        return ($route['type'] ?? '') === 'admin_ajax';
    }

    /**
     * 检查是否为admin后台请求
     * @param array $route
     * @return bool
     */
    public function isAdminRoute($route = null)
    {
        if ($route === null) {
            $route = $this->currentRoute;
        }

        return ($route['type'] ?? '') === 'admin';
    }
}
