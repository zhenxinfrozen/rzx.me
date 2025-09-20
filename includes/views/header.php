<!-- includes/views/header.php - semantic header + responsive nav -->
<header class="site-header" role="banner">
    <div class="site-header__inner">
        <a class="site-brand" href="/">
            <!-- optional logo image if available -->
            <?php if (file_exists(__DIR__ . '/../../public/assets/images/Avatar100X100.jpg')): ?>
                <img src="/assets/images/Avatar100X100.jpg" alt="Ray avatar">
            <?php endif; ?>
            rzx.me
        </a>

        <!-- checkbox toggle for small screens (no JS required) -->
        <input id="nav-toggle" class="nav-toggle" type="checkbox" aria-hidden="true">
        <label for="nav-toggle" class="nav-toggle-label" aria-hidden="true">
            <span aria-hidden="true"></span>
        </label>

        <nav class="site-nav" role="navigation" aria-label="Primary">
            <?php
            // Determine current request path for active state detection.
            $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
            $path = parse_url($requestUri, PHP_URL_PATH);
            if ($path !== '/') {
                $path = rtrim($path, '/');
            }

            // Nav items: label => path
            $navItems = [
                'Home' => '/',
                'Animation' => '/ray-animation.php',
                'Latest' => '/ray-latest.php',
                'Comic' => '/ray-comic.php',
                'Blogs' => '/ray-sites.php',
                'Sketch' => '/ray-sketch.php',
                'Pictures' => '/ray-pictures.php',
                'About' => '/ray-contact.php',
            ];

            // Render nav component via render_template when available, fallback to inline list
            $navComponent = __DIR__ . '/components/nav.php';
            if (function_exists('render_template')) {
                echo render_template($navComponent, ['navItems' => $navItems, 'path' => $path]);
            } else {
                // fallback: render inline list
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
// Inline header CSS like footer.php to keep a single source of truth under includes/css
// Prefer public asset for caching; fallback to includes inline if missing.
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
// Make header fixed and toggle scrolled opacity on scroll (lightweight, no dependencies)
(function(){
    var header = document.querySelector('.site-header');
    if (!header) return;
    // mark header as fixed and ensure body has top padding to avoid content jump
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
    // run once to set initial state
    onScroll();
})();
</script>
