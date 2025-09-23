<?php
// app/Handlers/page_data_handler.php

function get_page_specific_data($viewFile) {
    $data = [];
    // 为 'ray-about-body.php' 视图提供特定数据
    if ($viewFile === 'ray-about-body.php') {
        // 模拟原始文件中的逻辑
        $data['page_id'] = 'about';
        $data['page_title'] = '关于我 - rzx.me';
        $data['css_file'] = '/assets/css/about.css';
    } elseif ($viewFile === 'index-body.php') {
        $data['page_id'] = 'home';
        $data['page_title'] = 'rzx.me - 首页';
        $data['css_file'] = '/assets/css/home_style.css';
    } elseif ($viewFile === 'ray-animation-body.php') {
        $data['page_id'] = 'animation';
        $data['page_title'] = 'Animation - rzx.me';
        $data['css_file'] = '/assets/css/animation.css';
    } elseif ($viewFile === 'ray-latest-body.php') {
        $data['page_id'] = 'latest';
        $data['page_title'] = 'Latest - rzx.me';
        $data['css_file'] = '/assets/css/latest.css';
    } elseif ($viewFile === 'ray-pictures-body.php') {
        $data['page_id'] = 'pictures';
        $data['page_title'] = 'Pictures - rzx.me';
        $data['css_file'] = '/assets/css/pictures.css';
    } elseif ($viewFile === 'ray-sketch-body.php') {
        $data['page_id'] = 'sketch';
        $data['page_title'] = 'SketchBooks - rzx.me';
        $data['css_file'] = '/assets/css/sketch.css';
    } elseif ($viewFile === 'ray-sketch-test-body.php') {
        // jQuery 测试版本（与 ray-sketch 共享样式）
        $data['page_id'] = 'test-sketch-test';
        $data['page_title'] = 'SketchBooks (jQuery Test) - rzx.me';
        $data['css_file'] = '/assets/css/sketch.css';
    } elseif ($viewFile === 'ray-sites-body.php') {
        $data['page_id'] = 'sites';
        $data['page_title'] = 'Portal - rzx.me';
        $data['css_file'] = '/assets/css/sites.css';
    } elseif ($viewFile === 'ray-comic-body.php') {
        $data['page_id'] = 'comic';
        $data['page_title'] = 'Comic - rzx.me';
        $data['css_file'] = '/assets/css/comic.css';
    }
    // 可以为其他页面添加 else if 分支
    return $data;
}
