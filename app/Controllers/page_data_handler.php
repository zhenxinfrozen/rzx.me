<?php
// app/Controllers/page_data_handler.php

function get_page_specific_data($viewFile) {
    // 默认 meta 信息
    $data = [
        'page_id' => '',
        'page_title' => 'rzx.me',
        'css_file' => null,
        'meta_keywords' => '动画，原画，速写，分镜头，颓废动画人，内真心，Comic，sketches，Story board，Digital painting，Concept art，games',
        'meta_description' => '颓废动画人的个人网站，涂鸦和作品练习，扯淡的东西。This site is a portfolio of Ray art. Hope you enjoy it!',
        'meta_copyright' => '站点所有原创图片版权归作者所有，转载注明出处......',
        'meta_author' => 'ray,ruizhenxin,rzx.me'
    ];
    
    // 为不同页面提供特定数据
    if ($viewFile === 'index-body.php' || $viewFile === 'pages/index-body.php') {
        $data['page_id'] = 'home';
        $data['page_title'] = 'rzx.me - 首页';
        $data['css_file'] = '/assets/css/home_style.css';
        // 使用默认的 meta 信息
    } elseif ($viewFile === 'ray-comic-body.php' || $viewFile === 'pages/ray-comic-body.php') {
        $data['page_id'] = 'comic';
        $data['page_title'] = 'Comic - rzx.me';
        $data['css_file'] = '/assets/css/comic.css';
        $data['meta_description'] = '漫画作品展示，包括原创漫画和分镜头设计';
    } elseif ($viewFile === 'ray-comic-body.php' || $viewFile === 'pages/ray-comic-body.php') {
        $data['page_id'] = 'comic';
        $data['page_title'] = 'Comic - rzx.me';
        $data['css_file'] = '/assets/css/comic.css';
        $data['meta_description'] = '漫画作品展示，包括原创漫画和分镜头设计';
        $data['meta_keywords'] = '漫画，Comic，分镜头，Story board，原创漫画，漫画设计';
    } elseif ($viewFile === 'ray-comic-reader-body.php' || $viewFile === 'pages/ray-comic-reader-body.php') {
        $data['page_id'] = 'comic-reader';
        $data['page_title'] = 'Dream Gallery - rzx.me';
        $data['css_file'] = null; // CSS内嵌在模板中
        $data['meta_description'] = 'Dream速写画廊，沉浸式浏览体验，配有背景音乐的图片幻灯片';
    } elseif ($viewFile === 'ray-comic-reader-body.php' || $viewFile === 'pages/ray-comic-reader-body.php') {
        $data['page_id'] = 'comic-reader';
        $data['page_title'] = 'Dream Gallery - rzx.me';
        $data['css_file'] = null; // CSS内嵌在模板中
        $data['meta_description'] = 'Dream速写画廊，沉浸式浏览体验，配有背景音乐的图片幻灯片';
        $data['meta_keywords'] = '速写画廊，Dream，幻灯片，艺术欣赏，沉浸式体验，sketch gallery';
    } elseif ($viewFile === 'ray-animation-body.php' || $viewFile === 'pages/ray-animation-body.php') {
        $data['page_id'] = 'animation';
        $data['page_title'] = 'Animation - rzx.me';
        $data['css_file'] = '/assets/css/animation.css';
        $data['meta_description'] = '动画作品展示，包括原创动画短片和练习作品';
        $data['meta_keywords'] = '动画，Flash动画，原创动画，动画短片，Motion Graphics';
    } elseif ($viewFile === 'ray-latest-body.php' || $viewFile === 'pages/ray-latest-body.php') {
        $data['page_id'] = 'latest';
        $data['page_title'] = 'Latest - rzx.me';
        $data['css_file'] = '/assets/css/latest.css';
        $data['meta_description'] = '最新作品和动态更新，关注最新的创作进展';
        $data['meta_keywords'] = '最新作品，最新动态，作品更新，创作进展';
    } elseif ($viewFile === 'ray-pictures-body.php' || $viewFile === 'pages/ray-pictures-body.php') {
        $data['page_id'] = 'pictures';
        $data['page_title'] = 'Pictures - rzx.me';
        $data['css_file'] = '/assets/css/pictures.css';
        $data['meta_description'] = '图片作品集，包括插画、概念设计和数字绘画作品';
        $data['meta_keywords'] = '插画，数字绘画，概念设计，艺术作品，Digital Art，Illustration';
    } elseif ($viewFile === 'ray-sketch-body.php' || $viewFile === 'pages/ray-sketch-body.php') {
        $data['page_id'] = 'sketch';
        $data['page_title'] = 'SketchBooks - rzx.me';
        $data['css_file'] = '/assets/css/sketch.css';
        $data['meta_description'] = '速写本和练习作品，记录日常的绘画练习和创意草图';
        $data['meta_keywords'] = '速写，草图，练习作品，sketchbook，drawing，concept sketches';
    } elseif ($viewFile === 'ray-sketch-test-body.php' || $viewFile === 'pages/ray-sketch-test-body.php') {
        // jQuery 测试版本（与 ray-sketch 共享样式和meta）
        $data['page_id'] = 'test-sketch-test';
        $data['page_title'] = 'SketchBooks (jQuery Test) - rzx.me';
        $data['css_file'] = '/assets/css/sketch.css';
        $data['meta_description'] = '速写本和练习作品，记录日常的绘画练习和创意草图（jQuery测试版本）';
        $data['meta_keywords'] = '速写，草图，练习作品，sketchbook，drawing，concept sketches';
    } elseif ($viewFile === 'ray-sites-body.php' || $viewFile === 'pages/ray-sites-body.php') {
        $data['page_id'] = 'sites';
        $data['page_title'] = 'Portal - rzx.me';
        $data['css_file'] = '/assets/css/sites.css';
        $data['meta_description'] = '相关链接和推荐网站，发现更多有趣的内容';
        $data['meta_keywords'] = '链接，推荐网站，Portal，相关资源，友情链接';
    } elseif ($viewFile === 'ray-about-body.php' || $viewFile === 'pages/ray-about-body.php') {
        $data['page_id'] = 'about';
        $data['page_title'] = '关于我 - rzx.me';
        $data['css_file'] = '/assets/css/about.css';
        $data['meta_description'] = '个人简介和联系方式，了解更多关于Ray的信息';
        $data['meta_keywords'] = '关于我，个人简介，联系方式，动画师，分镜师，原画师';
    }
    
    return $data;
}
