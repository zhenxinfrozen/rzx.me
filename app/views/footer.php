<!-- app/views/footer.php - copied from includes/views/footer.php -->
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
    $publicCss = __DIR__ . '/../../public/assets/css/footer.css';
    if (file_exists($publicCss)) {
        echo '<link rel="stylesheet" href="' . htmlspecialchars(rtrim(ASSET_URL, '/'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '/css/footer.css?v=' . filemtime($publicCss) . '">' . PHP_EOL;
    } else {
        $cssPath = __DIR__ . '/../css/footer.css';
        if (file_exists($cssPath)) {
            echo "<style>\n" . file_get_contents($cssPath) . "\n</style>\n";
        }
    }
?>

<script>
// jQuery CDN with local fallback
(function(){
    var cdn = document.createElement('script');
    cdn.src = 'https://code.jquery.com/jquery-3.7.1.min.js';
    cdn.integrity = 'sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=';
    cdn.crossOrigin = 'anonymous';
    cdn.onload = function(){};
    cdn.onerror = function(){ loadLocal(); };
    document.head.appendChild(cdn);
    function loadLocal(){
        if (window.jQuery) return;
        var s = document.createElement('script');
        s.src = '/assets/js/jquery-3.7.1.min.js';
        document.head.appendChild(s);
    }
    setTimeout(function(){ if (!window.jQuery) loadLocal(); }, 3000);
})();
</script>
