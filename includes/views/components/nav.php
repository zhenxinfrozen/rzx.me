<?php
// 期望传入变量：
// - $navItems: 关联数组，格式为 label => href（导航标签与链接）
// - $path: 当前请求路径，用于判断哪个菜单项为激活（高亮）状态
// 如果未传入 $navItems，则默认为空数组，保证模板安全渲染。
if (!isset($navItems) || !is_array($navItems)) {
    $navItems = [];
}
?>
<!-- 以下为导航列表渲染：遍历 $navItems，输出带有 active 类的链接 -->
<ul class="site-nav__list">
    <?php foreach ($navItems as $label => $href):
        $isActive = ($href === $path) || ($href === '/' && $path === '/');
        $class = 'site-nav__link' . ($isActive ? ' site-nav__link--active' : '');
    ?>
    <li class="site-nav__item"><a class="<?php echo $class ?>" href="<?php echo htmlspecialchars($href, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>"><?php echo htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></a></li>
    <?php endforeach; ?>
</ul>
