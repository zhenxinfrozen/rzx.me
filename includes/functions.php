<?php
// functions.php
// 放置应用级的函数或简单的业务逻辑函数。

// 示例占位：安全地获取数组项
function arr_get(array $arr, $key, $default = null) {
    return array_key_exists($key, $arr) ? $arr[$key] : $default;
}

