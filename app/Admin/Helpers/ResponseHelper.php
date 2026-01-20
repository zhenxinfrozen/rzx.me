<?php
/**
 * 后台响应助手函数
 * 
 * 提供统一的JSON响应、错误处理等公共功能
 */

namespace App\Admin\Helpers;

class ResponseHelper
{
    /**
     * 发送JSON响应并终止执行
     *
     * @param array $payload 响应数据
     * @param int $status HTTP状态码
     * @return never
     */
    public static function json(array $payload, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * 发送成功响应
     *
     * @param mixed $data 响应数据
     * @param string|null $message 成功消息
     * @return never
     */
    public static function success($data = null, ?string $message = null): never
    {
        $payload = ['success' => true];
        
        if ($message !== null) {
            $payload['message'] = $message;
        }
        
        if ($data !== null) {
            $payload['data'] = $data;
        }
        
        self::json($payload);
    }

    /**
     * 发送错误响应
     *
     * @param string $message 错误消息
     * @param int $status HTTP状态码
     * @param array $extra 额外数据
     * @return never
     */
    public static function error(string $message, int $status = 400, array $extra = []): never
    {
        $payload = [
            'success' => false,
            'message' => $message,
        ];
        
        if (!empty($extra)) {
            $payload = array_merge($payload, $extra);
        }
        
        self::json($payload, $status);
    }

    /**
     * 发送异常响应
     *
     * @param \Throwable $e 异常对象
     * @param bool $debug 是否显示详细错误信息
     * @return never
     */
    public static function exception(\Throwable $e, bool $debug = false): never
    {
        $payload = [
            'success' => false,
            'message' => $e->getMessage(),
        ];
        
        if ($debug) {
            $payload['debug'] = [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ];
        }
        
        $status = ($e->getCode() >= 400 && $e->getCode() < 600) ? $e->getCode() : 500;
        self::json($payload, $status);
    }

    /**
     * 验证必需参数
     *
     * @param array $params 参数数组 ($_GET, $_POST等)
     * @param array $required 必需参数列表
     * @param string $source 参数来源（用于错误消息）
     * @throws \InvalidArgumentException
     */
    public static function validateRequired(array $params, array $required, string $source = 'request'): void
    {
        $missing = [];
        
        foreach ($required as $key) {
            if (!isset($params[$key]) || $params[$key] === '') {
                $missing[] = $key;
            }
        }
        
        if (!empty($missing)) {
            self::error(
                "缺少必需参数: " . implode(', ', $missing),
                400,
                ['missing_params' => $missing]
            );
        }
    }
}
