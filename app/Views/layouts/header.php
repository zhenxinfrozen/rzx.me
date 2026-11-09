<?php
/**
 * 主站顶部菜单栏 - 基于 shared-layout 公共组件渲染
 *
 * 组件位置: shared-layout/src/SiteHeader.php
 * 样式文件: shared-layout/assets/css/header.css (经 public/shared-layout junction 暴露)
 * 其它项目接入方式见 shared-layout/README.md
 */

require_once __DIR__ . '/../../../shared-layout/src/SiteHeader.php';

// 读取网站配置 (图标/名称/副标题)
$siteConfig = __DIR__ . '/../../../app/Config/site_settings.php';
$ss = file_exists($siteConfig) ? require $siteConfig : [];
$logoUrl = $ss['site_icon'] ?? '/assets/images/Avatar100X100.jpg';
$logoPath = __DIR__ . '/../../../public' . parse_url($logoUrl, PHP_URL_PATH);
$brandText = $ss['site_name'] ?? 'RZX.ME';
$taglineText = $ss['site_description'] ?? '颓废动画人';

echo \SharedLayout\SiteHeader::render([
    'brand'       => $brandText,
    'tagline'     => $taglineText,
    'home_url'    => '/',
    'logo'      => $logoUrl,
    'logo_file' => $logoPath,
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