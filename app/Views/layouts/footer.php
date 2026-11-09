<?php
/**
 * 主站底部 - 基于 shared-layout 公共组件 SiteFooter 渲染
 *
 * 组件位置: shared-layout/src/SiteFooter.php
 * 样式文件: shared-layout/assets/css/footer.css (经 public/shared-layout junction 暴露)
 * 屏蔽逻辑: 由 public/index.php 根据 $hideFooterPages 控制是否渲染本文件
 */

require_once __DIR__ . '/../../../shared-layout/src/SiteFooter.php';

// 读取网站配置
$siteConfig = __DIR__ . '/../../../app/Config/site_settings.php';
$ss = file_exists($siteConfig) ? require $siteConfig : [];
$logoUrl = $ss['site_icon'] ?? '/assets/images/Avatar100X100.jpg';
$logoPath = __DIR__ . '/../../../public' . parse_url($logoUrl, PHP_URL_PATH);

echo \SharedLayout\SiteFooter::render([
    'logo'       => $logoUrl,
    'logo_file'  => $logoPath,
    'brand'      => $ss['site_name'] ?? 'RZX.ME',
    'home_url'   => '/',
    'description' => [
        $ss['site_description'] ?? '颓废动画人的个人网站，涂鸦和作品练习',
    ],
    'nav' => [
        '首页 HOME'   => '/',
        '博客 BLOG'   => '/blog',
        '常见问题'    => '/faq',
        '作者 ABOUT'  => '/about',
    ],
    'copyright'  => '© 2009-' . date('Y') . ' RAY RUI. ALL RIGHTS RESERVED.',
    'powered_by' => 'Powered by Ray',
    // 主站链接走 url() 函数 (支持非伪静态模式)
    'url_rewriter' => 'url',
]);

// jQuery CDN with local fallback; sketch 页面已在视图内加载, 跳过
if (!isset($page_id) || ($page_id !== 'sketch' && $page_id !== 'test-sketch-test')): ?>
<script>
(function(){
    function loadLocal(){
        if (window.jQuery) return;
        var s = document.createElement('script');
        s.src = '/assets/js/jquery-3.7.1.min.js';
        document.head.appendChild(s);
    }
    if (!window.jQuery) {
        var cdn = document.createElement('script');
        cdn.src = 'https://code.jquery.com/jquery-3.7.1.min.js';
        cdn.integrity = 'sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=';
        cdn.crossOrigin = 'anonymous';
        cdn.onerror = function(){ loadLocal(); };
        document.head.appendChild(cdn);
        setTimeout(function(){ if (!window.jQuery) loadLocal(); }, 3000);
    }
})();
</script>
<?php endif; ?>
