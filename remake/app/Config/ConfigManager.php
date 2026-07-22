<?php
// app/Config/ConfigManager.php - 配置管理器

class ConfigManager 
{
    private static $instance = null;
    private $configs = [];
    private $configPath;

    private function __construct() 
    {
        $this->configPath = __DIR__;
    }

    public static function getInstance() 
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * 获取配置值
     * @param string $key 配置键，支持点分割如 'app.name'
     * @param mixed $default 默认值
     * @return mixed
     */
    public function get($key, $default = null) 
    {
        $keys = explode('.', $key);
        $configFile = array_shift($keys);
        
        // 加载配置文件
        if (!isset($this->configs[$configFile])) {
            $this->loadConfig($configFile);
        }
        
        $value = $this->configs[$configFile] ?? [];
        
        // 递归获取嵌套值
        foreach ($keys as $k) {
            if (is_array($value) && isset($value[$k])) {
                $value = $value[$k];
            } else {
                return $default;
            }
        }
        
        return $value;
    }

    /**
     * 设置配置值（运行时）
     * @param string $key 配置键
     * @param mixed $value 配置值
     */
    public function set($key, $value) 
    {
        $keys = explode('.', $key);
        $configFile = array_shift($keys);
        
        if (!isset($this->configs[$configFile])) {
            $this->loadConfig($configFile);
        }
        
        $config = &$this->configs[$configFile];
        
        // 递归设置嵌套值
        foreach ($keys as $k) {
            if (!isset($config[$k]) || !is_array($config[$k])) {
                $config[$k] = [];
            }
            $config = &$config[$k];
        }
        
        $config = $value;
    }

    /**
     * 加载配置文件
     * @param string $configFile 配置文件名（不含.php）
     */
    private function loadConfig($configFile) 
    {
        $configPath = $this->configPath . '/' . $configFile . '.php';
        
        if (file_exists($configPath)) {
            $this->configs[$configFile] = require $configPath;
        } else {
            $this->configs[$configFile] = [];
        }
    }

    /**
     * 获取所有配置
     * @param string $configFile 配置文件名
     * @return array
     */
    public function all($configFile = null) 
    {
        if ($configFile) {
            if (!isset($this->configs[$configFile])) {
                $this->loadConfig($configFile);
            }
            return $this->configs[$configFile];
        }
        
        return $this->configs;
    }

    /**
     * 检查配置是否存在
     * @param string $key 配置键
     * @return bool
     */
    public function has($key) 
    {
        return $this->get($key) !== null;
    }
}

// 全局配置访问函数
if (!function_exists('config')) {
    /**
     * 获取配置值的便捷函数
     * @param string $key 配置键
     * @param mixed $default 默认值
     * @return mixed
     */
    function config($key = null, $default = null) 
    {
        $configManager = ConfigManager::getInstance();
        
        if ($key === null) {
            return $configManager;
        }
        
        return $configManager->get($key, $default);
    }
}