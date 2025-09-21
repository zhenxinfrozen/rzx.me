<!-- includes/views/header.php - 语义化页眉 + 响应式导航 -->
<header class="site-header" role="banner">
    <div class="site-header__inner">
        <a class="site-brand" href="/">
            <!-- 可选的 logo 图片（如果存在） -->
            <?php if (file_exists(__DIR__ . '/../../public/assets/images/Avatar100X100.jpg')): ?>
                <img src="/assets/images/Avatar100X100.jpg" alt="Ray avatar">
            <?php endif; ?>
            rzx.me
        </a>

    <!-- 用于小屏幕的复选切换（无须 JS） -->
        <input id="nav-toggle" class="nav-toggle" type="checkbox" aria-hidden="true">
        <label for="nav-toggle" class="nav-toggle-label" aria-hidden="true">
            <span aria-hidden="true"></span>
        </label>

        <nav class="site-nav" role="navigation" aria-label="Primary">
            <?php
            // 确定当前请求路径，用于导航高亮（active）判断。
            
            
            $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
            $path = parse_url($requestUri, PHP_URL_PATH);
            if ($path !== '/') {
                $path = rtrim($path, '/');
            }

            // 导航项： 标签 => 路径
            $navItems = [
                'Home' => '/',
                'Animation' => '/ray-animation.php',
                'Latest' => '/ray-latest.php',
                'Comic' => '/ray-comic.php',
                'Blogs' => '/ray-sites.php',
                'Sketch' => '/ray-sketch.php',
                'Pictures' => '/ray-pictures.php',
                'About' => '/ray-about.php',
            ];

            // 优先通过 render_template 渲染导航组件；若不可用则回退到内联列表渲染
            // 现在使用更明确命名的组件 ray-top-nav.php（顶部导航）
            $navComponent = __DIR__ . '/components/ray-top-nav.php';
            if (function_exists('render_template')) {
                echo render_template($navComponent, ['navItems' => $navItems, 'path' => $path]);
            } else {
                // 回退：直接渲染内联列表
                ?>
                <ul class="site-nav__list">
                    <?php foreach ($navItems as $label => $href):
                        $isActive = ($href === $path) || ($href === '/' && $path === '/');
                        $class = 'site-nav__link' . ($isActive ? ' site-nav__link--active' : '');
                    ?>
                    <li class="site-nav__item"><a class="<?php echo $class ?>" href="<?php echo $href ?>"><?php echo $label ?></a></li>
                    <?php endforeach; ?>
                </ul>
                <?php
            }
            ?>
        </nav>
    </div>
</header>

<?php
// 与 footer.php 一样尝试使用公共样式文件以保持单一样式来源
    $publicCss = __DIR__ . '/../../public/assets/css/header.css';
    if (file_exists($publicCss)) {
        echo '<link rel="stylesheet" href="' . htmlspecialchars(rtrim(ASSET_URL, '/'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '/css/header.css?v=' . filemtime($publicCss) . '">' . PHP_EOL;
    } else {
        $cssPath = __DIR__ . '/../css/header.css';
        if (file_exists($cssPath)) {
            echo "<style>\n" . file_get_contents($cssPath) . "\n</style>\n";
        }
    }
?>
<script>
// 使页眉固定，并在滚动时切换 .scrolled 类以调整透明度（轻量实现，无依赖）
(function(){
    // 如果没有找到页眉则直接返回
    var header = document.querySelector('.site-header');
    if (!header) return;

    // 将页眉标记为固定，并为 body 添加顶部内边距以避免内容跳动
    header.classList.add('fixed');
    document.body.classList.add('has-fixed-header');

    var ticking = false;
    function onScroll(){
        if (!ticking){
            window.requestAnimationFrame(function(){
                var y = window.scrollY || window.pageYOffset;
                if (y > 10){
                    header.classList.add('scrolled');
                } else {
                    header.classList.remove('scrolled');
                }
                ticking = false;
            });
            ticking = true;
        }
    }

    window.addEventListener('scroll', onScroll, {passive:true});
    // 运行一次以设置初始状态
    onScroll();
})();
</script>
