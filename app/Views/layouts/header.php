<!-- app/Views/layouts/header.php - copied from includes/views/header.php -->
<header class="site-header" role="banner">
    <div class="site-header__inner">
        <a class="site-brand" href="/">
            <?php if (file_exists(__DIR__ . '/../../../public/assets/images/Avatar100X100.jpg')): ?>
                <img src="/assets/images/Avatar100X100.jpg" alt="Ray avatar">
            <?php endif; ?>
            rzx.me
        </a>

        <input id="nav-toggle" class="nav-toggle" type="checkbox" aria-hidden="true">
        <label for="nav-toggle" class="nav-toggle-label" aria-hidden="true">
            <span aria-hidden="true"></span>
        </label>

        <nav class="site-nav" role="navigation" aria-label="Primary">
            <?php
            $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
            $path = parse_url($requestUri, PHP_URL_PATH);
            if ($path !== '/') {
                $path = rtrim($path, '/');
            }

            $navItems = [
                'Home' => '/',
                'Animation' => '/animation',
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
                    $isActive = ($href === $path) || ($href === '/' && $path === '/');
                    $class = 'site-nav__link' . ($isActive ? ' site-nav__link--active' : '');
                ?>
                <li class="site-nav__item"><a class="<?php echo $class ?>" href="<?php echo htmlspecialchars($href, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>"><?php echo htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></a></li>
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
