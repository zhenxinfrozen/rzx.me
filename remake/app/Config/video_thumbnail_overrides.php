<?php
/**
 * 视频特定缩略图配置
 * Video-Specific Thumbnail Configuration
 * 
 * 为某些特殊视频自定义缩略图提取位置
 */

return [
    // 配置格式：
    // '视频文件名（不含扩展名）' => [
    //     'position' => 提取位置（秒数或百分比）,
    //     'type' => 'seconds' 或 'percentage',
    //     'reason' => '原因说明'
    // ]
    
    'Gunman' => [
        'position' => 10,
        'type' => 'seconds',
        'reason' => '视频整体较暗，10秒位置画面最清晰'
    ],
    
    // 示例：使用百分比
    // 'some-dark-video' => [
    //     'position' => 50,  // 50%位置
    //     'type' => 'percentage',
    //     'reason' => '视频前半部分全黑'
    // ],
    
    // 示例：使用固定秒数
    // 'intro-video' => [
    //     'position' => 3,
    //     'type' => 'seconds',
    //     'reason' => '3秒后才进入主画面'
    // ],
];
