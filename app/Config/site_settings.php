<?php
// app/Config/site_settings.php
// 网站全局配置 - 驱动 SharedAdmin site-settings 模板

return [
    // 基本信息
    'site_name' => 'RZX.ME',
    'site_description' => '颓废动画人的个人网站，涂鸦和作品练习',
    'site_icon' => '/assets/img/icon/INV_Scroll_02.png',

    // Header 配色板
    'primary_color' => '#4e73df',
    'header_bg' => 'rgba(255,255,255,0.5)',
    'brand_color' => '#333333',
    'nav_text_color' => '#777777',

    // 版本/Git 分支
    'show_version' => true,
    'version_text' => 'v0.8.1',
    'use_git_branch' => true,
    'git_branch_cache' => '',
];
