<?php
/**
 * 主站顶部菜单栏 - 基于 shared-header 公共组件渲染
 *
 * 组件位置: shared-header/src/SiteHeader.php
 * 样式文件: shared-header/assets/css/header.css (经 public/shared-header junction 暴露)
 * 其它项目接入方式见 shared-header/README.md
 */

require_once __DIR__ . '/../../../shared-header/src/SiteHeader.php';

echo \SharedHeader\SiteHeader::render([
    'brand'       => 'RZX.ME',
    'tagline'     => '颓废动画人',
    'home_url'    => '/',
    'avatar'      => '/assets/images/Avatar100X100.jpg',
    'avatar_file' => __DIR__ . '/../../../public/assets/images/Avatar100X100.jpg',
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
    ],
    // LAB 菜单同时覆盖 /lab 实验子目录下的页面
    'active_map'   => ['LAB' => ['/lab']],
    // 伪静态/非伪静态链接由主站 url() 决定
    'url_rewriter' => 'url',
]);