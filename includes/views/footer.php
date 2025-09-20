<!-- includes/views/footer.php - 简洁现代的页脚片段 -->
<footer class="site-footer">
    <div class="site-footer__inner">
        <div class="site-footer__left">
            <small class="site-footer__meta">v0.7.4:2013-07-22</small>
        </div>
        <div class="site-footer__center">
            <a class="site-footer__link" href="https://rzx.me" target="_blank" rel="noopener">About: rzx.me</a>
        </div>
        <div class="site-footer__right">
            <small class="site-footer__copy">&copy; <span id="footer-year"><?php echo date('Y'); ?></span> Ray</small>
        </div>
    </div>
</footer>

<?php
// Inline centralized footer CSS as a fallback if public asset isn't loaded.
// This inlines the single source of truth from includes/css/footer.css so there's
// no duplication between include and public asset.
// Prefer public asset for caching; fallback to includes inline if missing.
$publicCss = __DIR__ . '/../../public/assets/css/footer.css';
if (file_exists($publicCss)) {
    echo '<link rel="stylesheet" href="/assets/css/footer.css?v=' . filemtime($publicCss) . '">' . PHP_EOL;
} else {
    $cssPath = __DIR__ . '/../css/footer.css';
    if (file_exists($cssPath)) {
        echo "<style>\n" . file_get_contents($cssPath) . "\n</style>\n";
    }
}
?>


<!-- Footer is purely CSS-driven fixed footer now. -->
<script>
// Load jQuery from CDN with local fallback to /assets/js/jquery-3.7.1.min.js.
// This avoids document.write and only injects the local script if the CDN is blocked.
(function(){
    // Insert CDN script tag
    var cdn = document.createElement('script');
    cdn.src = 'https://code.jquery.com/jquery-3.7.1.min.js';
    cdn.integrity = 'sha256-3gJwYp8U+Y3f9C5v+XgYh2i1Yl3M7u1l9+8n8F0Y3Yg=';
    cdn.crossOrigin = 'anonymous';
    cdn.onload = function(){ /* CDN loaded successfully */ };
    cdn.onerror = function(){ loadLocal(); };
    document.head.appendChild(cdn);

    // If CDN is blocked or slow, fall back to local copy.
    function loadLocal(){
        if (window.jQuery) return;
        var s = document.createElement('script');
        // Use filemtime query string for cache busting when available
        s.src = '/assets/js/jquery-3.7.1.min.js';
        document.head.appendChild(s);
    }

    // In case the CDN script doesn't fire error (e.g. blocked silently), check after short timeout
    setTimeout(function(){ if (!window.jQuery) loadLocal(); }, 3000);
})();
</script>

<script>
// Small vanilla JS to trigger click feedback for menu boxes (no jQuery)
// Menu click feedback script moved to homepage (index.php) because menu exists only there.
</script>
</script>
