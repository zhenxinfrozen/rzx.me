<?php
/**
 * RayLab 页眉 - 挂载 shared-header 公共顶部菜单组件
 *
 * lab 作为"其它项目"接入: 与主站使用同一套顶部菜单组件与样式,
 * 并在菜单末尾追加 lab 子项目下拉菜单 (LAB 项目)。
 */

// public/lab 是指向 <root>/lab 的联接(junction), PHP 会解析到真实路径,
// 因此兼容两种目录结构: <root>/lab/lab-plugin 与 <root>/public/lab/lab-plugin
$raylabSharedHeader = null;
foreach ([
    __DIR__ . '/../../shared-header/src/SiteHeader.php',
    __DIR__ . '/../../../shared-header/src/SiteHeader.php',
] as $raylabCandidate) {
    if (is_file($raylabCandidate)) {
        $raylabSharedHeader = $raylabCandidate;
        break;
    }
}

// 组件缺失时静默跳过, 不影响页面主体
if ($raylabSharedHeader === null) {
    return;
}

require_once $raylabSharedHeader;

$raylabAvatarFile = null;
foreach ([
    __DIR__ . '/../../public/assets/images/Avatar100X100.jpg',
    __DIR__ . '/../../../assets/images/Avatar100X100.jpg',
] as $raylabAvatarCandidate) {
    if (is_file($raylabAvatarCandidate)) {
        $raylabAvatarFile = $raylabAvatarCandidate;
        break;
    }
}

echo \SharedHeader\SiteHeader::render([
    'brand'       => 'RZX.ME',
    'tagline'     => '颓废动画人',
    'avatar'      => '/assets/images/Avatar100X100.jpg',
    'avatar_file' => $raylabAvatarFile,
    'nav'         => [
        'Home'      => '/',
        'Animation' => '/animation',
        'Videos'    => '/videos',
        'Latest'    => '/latest',
        'Comic'     => '/comic',
        'Gallery'   => '/galleries',
        'LAB'       => '/sites',
        'Sketch'    => '/sketch',
        'Drafts'    => '/drafts',
        'About'     => '/about',
        // lab 子项目下拉菜单
        'LAB 项目'  => [
            'href'     => '/lab/',
            'children' => [
                'RayLab 首页' => '/lab/',
                'girl'        => '/lab/girl/',
                'girl-upload' => '/lab/girl/upload.php',
                'girl-random' => '/lab/girl/random.php',
                'chat-gpt'    => '/lab/chat-gpt/',
                'beforeafter' => '/lab/beforeafter/',
                '漫画'        => '/lab/comic/',
                'gpt-time'    => '/lab/gpt/gpt-time/',
            ],
        ],
    ],
    // LAB 菜单在 /lab 下保持高亮
    'active_map' => ['LAB' => ['/lab']],
]);