<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/view_renderer.php';

$title = 'Comic';

?><!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <title><?php echo htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></title>
    <link href="/assets/css/comic.css" rel="stylesheet" type="text/css" />
    <?php
    // render header include which also injects header CSS
    echo render_template(__DIR__ . '/../includes/views/header.php', ['title' => $title]);
    ?>
</head>
<body>
    <?php
    // main body
    echo render_template(__DIR__ . '/../includes/views/ray-comic-body.php');

    // render footer include (also injects footer CSS)
    echo render_template(__DIR__ . '/../includes/views/footer.php');
    ?>
</body>
</html>
