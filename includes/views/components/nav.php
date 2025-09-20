<?php
// expects $navItems (assoc label => href) and $path (current path)
if (!isset($navItems) || !is_array($navItems)) {
    $navItems = [];
}
?>
<ul class="site-nav__list">
    <?php foreach ($navItems as $label => $href):
        $isActive = ($href === $path) || ($href === '/' && $path === '/');
        $class = 'site-nav__link' . ($isActive ? ' site-nav__link--active' : '');
    ?>
    <li class="site-nav__item"><a class="<?php echo $class ?>" href="<?php echo htmlspecialchars($href, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>"><?php echo htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></a></li>
    <?php endforeach; ?>
</ul>
