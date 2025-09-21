<?php require_once __DIR__ . '/../includes/bootstrap.php'; ?>
<!--
  ray-pictures-backup.php
  这是 `public/ray-pictures.php` 在创建备份时的完整拷贝。
  如果需要恢复，请将此文件的内容复制回 `public/ray-pictures.php`。
-->
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="description" content="备份：图片页" />
    <title>ray-pictures backup</title>
</head>
<body>
<?php
// 下面直接嵌入原始页面内容以便备份查看（注意：这是备份文件，不会在站点中被包含）
echo "<!-- 原始页面内容开始（仅供备份） -->\n";
readfile(__DIR__ . '/ray-pictures.php');
echo "\n<!-- 原始页面内容结束 -->";
?>
</body>
</html>
