<?php
// 顶部导航组件（ray-top-nav.php）
// 说明：这是页面顶栏的导航渲染片段。
// 期望变量：
// - $navItems: 关联数组，label => href
// - $path: 当前请求路径，用于判断哪个链接为激活状态
// 功能：遍历 $navItems 输出一个带有 active 类（site-nav__link--active）的导航列表。
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
