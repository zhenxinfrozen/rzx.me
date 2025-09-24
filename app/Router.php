<?php
// app/Router.php - 现代路由处理器

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
        $path = $_SERVER['REQUEST_URI'] ?? '/';
        
        // 移除查询参数
        if (($pos = strpos($path, '?')) !== false) {
            $path = substr($path, 0, $pos);
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
            $this->currentRoute['type'] = 'api';
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
                $this->currentRoute['type'] = 'page';
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
            // 为正则表达式添加分隔符
            $regex = '/' . $regex . '/';
            return preg_match($regex, $path);
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
}