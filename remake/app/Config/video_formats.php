<?php
/**
 * 视频格式配置
 * Video Format Configuration
 * 
 * 定义系统支持的视频格式
 */

return [
    // 常见视频格式扩展名
    'extensions' => [
        // 最常用格式
        'mp4',
        'mkv',    // Matroska (常用于高质量视频)
        'webm',   // Web视频
        'avi',    // 传统格式
        'mov',    // QuickTime
        
        // 其他支持格式
        'flv',    // Flash Video
        'm4v',    // iTunes视频
        'wmv',    // Windows Media
        'mpg',    // MPEG
        'mpeg',   // MPEG
        'ogv',    // Ogg Video
        '3gp',    // 移动设备
        'ts',     // Transport Stream
        'mts',    // AVCHD
        'vob',    // DVD
        
        // 大写变体（Windows不区分，但Linux需要）
        'MP4', 'MKV', 'WEBM', 'AVI', 'MOV',
        'FLV', 'M4V', 'WMV', 'MPG', 'MPEG',
    ],
    
    // 格式说明
    'formats' => [
        'mp4' => 'H.264/H.265视频，最通用',
        'mkv' => 'Matroska容器，支持高质量编码',
        'webm' => 'Web优化格式',
        'avi' => '传统Windows格式',
        'mov' => 'QuickTime格式',
        'flv' => 'Flash视频（已过时但仍常见）',
    ],
];
