<?php
// app/Controllers/page_data_handler.php

function get_page_specific_data($viewFile) {
    // 加载页面配置
    $pageConfigs = require __DIR__ . '/../Config/page_config.php';
    
    // 获取默认配置
    $defaultConfig = $pageConfigs['default'];
    
    // 获取特定页面配置，如果不存在则使用默认配置
    $pageConfig = $pageConfigs[$viewFile] ?? $defaultConfig;
    
    // 合并默认配置和页面特定配置
    $data = array_merge($defaultConfig, $pageConfig);
    
    return $data;
}
