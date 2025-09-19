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
$cssPath = __DIR__ . '/../css/footer.css';
if (file_exists($cssPath)) {
    echo "<style>\n" . file_get_contents($cssPath) . "\n</style>\n";
}
?>


<!-- Footer is purely CSS-driven fixed footer now. -->
