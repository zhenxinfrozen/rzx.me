<!-- app/Views/layouts/header.php - copied from includes/views/header.php -->
<header class="site-header" role="banner">
    <div class="site-header__inner">
        <a class="site-brand" href="<?php echo htmlspecialchars(url('/'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>">
            <?php if (file_exists(__DIR__ . '/../../../public/assets/images/Avatar100X100.jpg')): ?>
                <img src="/assets/images/Avatar100X100.jpg" alt="Ray avatar">
            <?php endif; ?>
            RZX.ME <span class="site-tagline">颓废动画人</span>
        </a>

        <input id="nav-toggle" class="nav-toggle" type="checkbox" aria-hidden="true">
        <label for="nav-toggle" class="nav-toggle-label" aria-hidden="true">
            <span aria-hidden="true"></span>
        </label>

        <nav class="site-nav" role="navigation" aria-label="Primary">
            <?php
            // 获取当前逻辑路径，用于高亮导航
            $currentPath = $_SERVER['REQUEST_URI'] ?? '/';
            // 优先检测 route 参数 (兼容非伪静态模式)
            if (isset($_GET['route'])) {
                $currentPath = $_GET['route'];
            } else {
                // 伪静态模式
                $parsedPath = parse_url($currentPath, PHP_URL_PATH);
                if ($parsedPath !== '/' && $parsedPath) {
                    $currentPath = rtrim($parsedPath, '/');
                } else {
                    $currentPath = $parsedPath ?: '/';
                }
            }
            
            // 确保以 / 开头
            if (strpos($currentPath, '/') !== 0) {
                $currentPath = '/' . $currentPath;
            }

            $navItems = [
                'Home' => '/',
                'Animation' => '/animation',
                'Videos' => '/videos',
                'Latest' => '/latest',
                'Comic' => '/comic',
                'Gallery' => '/galleries',
                'Blogs' => '/sites',
                'Sketch' => '/sketch',
                'Single-Works' => '/single-works',
                'About' => '/about',
            ];
            ?>
            <ul class="site-nav__list">
                <?php foreach ($navItems as $label => $href):
                    // 简单匹配：当前路径等于菜单路径，或者当前路径以菜单路径开头（对于/除外）
                    if ($href === '/') {
                        $isActive = ($currentPath === '/');
                    } else {
                        $isActive = ($currentPath === $href) || (strpos($currentPath, $href . '/') === 0);
                    }
                    
                    $class = 'site-nav__link' . ($isActive ? ' site-nav__link--active' : '');
                ?>
                <li class="site-nav__item"><a class="<?php echo $class ?>" href="<?php echo htmlspecialchars(url($href), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>"><?php echo htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></a></li>
                <?php endforeach; ?>
            </ul>
        </nav>
    </div>
</header>

<?php
    $publicCss = __DIR__ . '/../../../public/assets/css/header.css';
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
// header scroll script
(function(){
    var header = document.querySelector('.site-header');
    if (!header) return;
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
    onScroll();
})();
</script>
