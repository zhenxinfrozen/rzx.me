<?php
require_once __DIR__ . '/../app/bootstrap.php';
require_once __DIR__ . '/../app/view_renderer.php';

$title = 'Art of Ray | Home';
?><!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8" />
    <title><?php echo htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></title>
    <meta name="keywords" content="漫画，原画,电影分镜头，故事板,Comic,Storyboard,Concept art," />
    <meta name="description" content=" This site is a portfolio of Ray art. Hope you enjoy it!" />
    <meta name="author" content="ray,ruizhenxin,rzx.me">
    <meta name="copyright" content="All images copyright Ray zhenxin" />
    <meta http-equiv="refresh" content="600" />
    <link rel="icon" href="favicon.ico" type="image/png" />
    <link rel="shortcut icon" href="favicon.ico" type="image/png" />
    <link href="<?php echo htmlspecialchars(rtrim(ASSET_URL, '/'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>/css/home_style.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo htmlspecialchars(rtrim(ASSET_URL, '/'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>/testcss(1)/style/csslab.css" rel="stylesheet" type="text/css" />
</head>
<body>
    <?php
    // render header
    echo render_template(__DIR__ . '/../app/views/header.php', ['title' => $title]);

    // render main index body
    echo render_template(__DIR__ . '/../app/views/index-body.php');

    // render footer
    echo render_template(__DIR__ . '/../app/views/footer.php');
    ?>
</body>
</html>