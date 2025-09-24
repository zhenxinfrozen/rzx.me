<?php
// app/Config/page_config.php
// 页面配置文件 - 集中管理所有页面的元数据
// 注意：只需要配置pages/目录下的文件，不需要重复配置

return [
    'pages/home.php' => [
        'page_id' => 'home',
        'page_title' => 'rzx.me - 首页',
        'css_file' => '/assets/css/home_style.css',
        'meta_keywords' => '动画，原画，速写，分镜头，颓废动画人，内真心，Comic，sketches，Story board，Digital painting，Concept art，games',
        'meta_description' => '颓废动画人的个人网站，涂鸦和作品练习，扯淡的东西。This site is a portfolio of Ray art. Hope you enjoy it!',
    ],
    
    'pages/comic.php' => [
        'page_id' => 'comic',
        'page_title' => 'Comic - rzx.me',
        'css_file' => '/assets/css/comic.css',
        'meta_description' => '漫画作品展示，包括原创漫画和分镜头设计',
        'meta_keywords' => '漫画，Comic，分镜头，Story board，原创漫画，漫画设计',
    ],
    
    'pages/comic-gallery.php' => [
        'page_id' => 'comic-reader',
        'page_title' => 'Dream Gallery - rzx.me',
        'css_file' => null,
        'meta_description' => 'Dream速写画廊，沉浸式浏览体验，配有背景音乐的图片幻灯片',
        'meta_keywords' => '速写画廊，Dream，幻灯片，艺术欣赏，沉浸式体验，sketch gallery',
    ],
    
    'pages/animation.php' => [
        'page_id' => 'animation',
        'page_title' => 'Animation - rzx.me',
        'css_file' => '/assets/css/animation.css',
        'meta_description' => '动画作品展示，包括原创动画短片和练习作品',
        'meta_keywords' => '动画，Flash动画，原创动画，动画短片，Motion Graphics',
    ],
    
    'pages/latest.php' => [
        'page_id' => 'latest',
        'page_title' => 'Latest - rzx.me',
        'css_file' => '/assets/css/latest.css',
        'meta_description' => '最新作品和动态，了解Ray的最新创作',
        'meta_keywords' => '最新作品，动态更新，创作日志，艺术动态',
    ],
    
    'pages/pictures.php' => [
        'page_id' => 'pictures',
        'page_title' => 'Pictures - rzx.me',
        'css_file' => '/assets/css/pictures.css',
        'meta_description' => '图片作品展示，包括插画、概念设计和数字绘画',
        'meta_keywords' => '图片，插画，概念设计，数字绘画，Digital Art',
    ],
    
    'pages/sketch.php' => [
        'page_id' => 'sketch',
        'page_title' => 'Sketch - rzx.me',
        'css_file' => '/assets/css/sketch.css',
        'meta_description' => '速写作品展示，包括人物速写、场景练习和创意草图',
        'meta_keywords' => '速写，Sketch，人物速写，场景练习，创意草图',
    ],
    
    'pages/sketch-test.php' => [
        'page_id' => 'test-sketch-test',
        'page_title' => 'SketchBooks (jQuery Test) - rzx.me',
        'css_file' => '/assets/css/sketch.css',
        'meta_description' => '速写本和练习作品，记录日常的绘画练习和创意草图（jQuery测试版本）',
        'meta_keywords' => '速写，草图，练习作品，sketchbook，drawing，concept sketches',
    ],
    
    'pages/sites.php' => [
        'page_id' => 'sites',
        'page_title' => 'Sites - rzx.me',
        'css_file' => '/assets/css/sites.css',
        'meta_description' => '相关链接和推荐网站，发现更多有趣的创意资源',
        'meta_keywords' => '链接，推荐网站，Portal，相关资源，友情链接',
    ],
    
    'pages/about.php' => [
        'page_id' => 'about',
        'page_title' => '关于我 - rzx.me',
        'css_file' => '/assets/css/about.css',
        'meta_description' => '个人简介和联系方式，了解更多关于Ray的信息',
        'meta_keywords' => '关于我，个人简介，联系方式，动画师，分镜师，原画师',
    ],
    
    'pages/galleries.php' => [
        'page_id' => 'galleries',
        'page_title' => '画廊 - rzx.me',
        'css_file' => null,
        'meta_description' => '图片画廊，展示各种艺术作品和创作',
        'meta_keywords' => '画廊，图片展示，艺术作品，创作展示，Gallery',
    ],
    
    'pages/gallery.php' => [
        'page_id' => 'gallery',
        'page_title' => '画廊 - rzx.me',
        'css_file' => null,
        'meta_description' => '画廊图片浏览，沉浸式艺术作品欣赏体验',
        'meta_keywords' => '画廊浏览，图片欣赏，艺术作品，Gallery viewer',
    ],
    
    'comic-gallery.php' => [
        'page_id' => 'comic-reader',
        'page_title' => 'Dream Gallery - rzx.me',
        'css_file' => null,
        'meta_description' => 'Dream速写画廊，沉浸式浏览体验，配有背景音乐的图片幻灯片',
        'meta_keywords' => '速写画廊，Dream，幻灯片，艺术欣赏，沉浸式体验，sketch gallery',
    ],
    
    'pages/comic-gallery.php' => [
        'page_id' => 'comic-reader',
        'page_title' => 'Dream Gallery - rzx.me',
        'css_file' => null,
        'meta_description' => 'Dream速写画廊，沉浸式浏览体验，配有背景音乐的图片幻灯片',
        'meta_keywords' => '速写画廊，Dream，幻灯片，艺术欣赏，沉浸式体验，sketch gallery',
    ],
    
    'animation.php' => [
        'page_id' => 'animation',
        'page_title' => 'Animation - rzx.me',
        'css_file' => '/assets/css/animation.css',
        'meta_description' => '动画作品展示，包括原创动画短片和练习作品',
        'meta_keywords' => '动画，Flash动画，原创动画，动画短片，Motion Graphics',
    ],
    
    'pages/animation.php' => [
        'page_id' => 'animation',
        'page_title' => 'Animation - rzx.me',
        'css_file' => '/assets/css/animation.css',
        'meta_description' => '动画作品展示，包括原创动画短片和练习作品',
        'meta_keywords' => '动画，Flash动画，原创动画，动画短片，Motion Graphics',
    ],
    
    'latest.php' => [
        'page_id' => 'latest',
        'page_title' => 'Latest - rzx.me',
        'css_file' => '/assets/css/latest.css',
        'meta_description' => '最新作品和动态，了解Ray的最新创作',
        'meta_keywords' => '最新作品，动态更新，创作日志，艺术动态',
    ],
    
    'pages/latest.php' => [
        'page_id' => 'latest',
        'page_title' => 'Latest - rzx.me',
        'css_file' => '/assets/css/latest.css',
        'meta_description' => '最新作品和动态，了解Ray的最新创作',
        'meta_keywords' => '最新作品，动态更新，创作日志，艺术动态',
    ],
    
    'pictures.php' => [
        'page_id' => 'pictures',
        'page_title' => 'Pictures - rzx.me',
        'css_file' => '/assets/css/pictures.css',
        'meta_description' => '图片作品展示，包括插画、概念设计和数字绘画',
        'meta_keywords' => '图片，插画，概念设计，数字绘画，Digital Art',
    ],
    
    'pages/pictures.php' => [
        'page_id' => 'pictures',
        'page_title' => 'Pictures - rzx.me',
        'css_file' => '/assets/css/pictures.css',
        'meta_description' => '图片作品展示，包括插画、概念设计和数字绘画',
        'meta_keywords' => '图片，插画，概念设计，数字绘画，Digital Art',
    ],
    
    'sketch.php' => [
        'page_id' => 'sketch',
        'page_title' => 'Sketch - rzx.me',
        'css_file' => '/assets/css/sketch.css',
        'meta_description' => '速写作品展示，包括人物速写、场景练习和创意草图',
        'meta_keywords' => '速写，Sketch，人物速写，场景练习，创意草图',
    ],
    
    'pages/sketch.php' => [
        'page_id' => 'sketch',
        'page_title' => 'Sketch - rzx.me',
        'css_file' => '/assets/css/sketch.css',
        'meta_description' => '速写作品展示，包括人物速写、场景练习和创意草图',
        'meta_keywords' => '速写，Sketch，人物速写，场景练习，创意草图',
    ],
    
    'sketch-test.php' => [
        'page_id' => 'test-sketch-test',
        'page_title' => 'SketchBooks (jQuery Test) - rzx.me',
        'css_file' => '/assets/css/sketch.css',
        'meta_description' => '速写本和练习作品，记录日常的绘画练习和创意草图（jQuery测试版本）',
        'meta_keywords' => '速写，草图，练习作品，sketchbook，drawing，concept sketches',
    ],
    
    'pages/sketch-test.php' => [
        'page_id' => 'test-sketch-test',
        'page_title' => 'SketchBooks (jQuery Test) - rzx.me',
        'css_file' => '/assets/css/sketch.css',
        'meta_description' => '速写本和练习作品，记录日常的绘画练习和创意草图（jQuery测试版本）',
        'meta_keywords' => '速写，草图，练习作品，sketchbook，drawing，concept sketches',
    ],
    
    'sites.php' => [
        'page_id' => 'sites',
        'page_title' => 'Sites - rzx.me',
        'css_file' => '/assets/css/sites.css',
        'meta_description' => '相关链接和推荐网站，发现更多有趣的创意资源',
        'meta_keywords' => '链接，推荐网站，Portal，相关资源，友情链接',
    ],
    
    'pages/sites.php' => [
        'page_id' => 'sites',
        'page_title' => 'Sites - rzx.me',
        'css_file' => '/assets/css/sites.css',
        'meta_description' => '相关链接和推荐网站，发现更多有趣的创意资源',
        'meta_keywords' => '链接，推荐网站，Portal，相关资源，友情链接',
    ],
    
    'about.php' => [
        'page_id' => 'about',
        'page_title' => '关于我 - rzx.me',
        'css_file' => '/assets/css/about.css',
        'meta_description' => '个人简介和联系方式，了解更多关于Ray的信息',
        'meta_keywords' => '关于我，个人简介，联系方式，动画师，分镜师，原画师',
    ],
    
    'pages/about.php' => [
        'page_id' => 'about',
        'page_title' => '关于我 - rzx.me',
        'css_file' => '/assets/css/about.css',
        'meta_description' => '个人简介和联系方式，了解更多关于Ray的信息',
        'meta_keywords' => '关于我，个人简介，联系方式，动画师，分镜师，原画师',
    ],
    
    'galleries.php' => [
        'page_id' => 'galleries',
        'page_title' => '画廊 - rzx.me',
        'css_file' => null,
        'meta_description' => '图片画廊，展示各种艺术作品和创作',
        'meta_keywords' => '画廊，图片展示，艺术作品，创作展示，Gallery',
    ],
    
    'pages/galleries.php' => [
        'page_id' => 'galleries',
        'page_title' => '画廊 - rzx.me',
        'css_file' => null,
        'meta_description' => '图片画廊，展示各种艺术作品和创作',
        'meta_keywords' => '画廊，图片展示，艺术作品，创作展示，Gallery',
    ],
    
    'gallery.php' => [
        'page_id' => 'gallery',
        'page_title' => '画廊 - rzx.me',
        'css_file' => null,
        'meta_description' => '画廊图片浏览，沉浸式艺术作品欣赏体验',
        'meta_keywords' => '画廊浏览，图片欣赏，艺术作品，Gallery viewer',
    ],
    
    'pages/gallery.php' => [
        'page_id' => 'gallery',
        'page_title' => '画廊 - rzx.me',
        'css_file' => null,
        'meta_description' => '画廊图片浏览，沉浸式艺术作品欣赏体验',
        'meta_keywords' => '画廊浏览，图片欣赏，艺术作品，Gallery viewer',
    ],
    
    // 默认配置
    'default' => [
        'page_id' => 'default',
        'page_title' => 'rzx.me',
        'css_file' => null,
        'meta_keywords' => '动画，原画，速写，分镜头，颓废动画人，内真心，Comic，sketches，Story board，Digital painting，Concept art，games',
        'meta_description' => '颓废动画人的个人网站，涂鸦和作品练习，扯淡的东西。This site is a portfolio of Ray art. Hope you enjoy it!',
        'meta_copyright' => '站点所有原创图片版权归作者所有，转载注明出处......',
        'meta_author' => 'ray,ruizhenxin,rzx.me',
    ],
];