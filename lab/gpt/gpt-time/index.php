
<?php $currentPage = 'raylab-gpt-time'; ?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>倒计时</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<!-- raylab-挂载页眉 -->
<?php
require_once __DIR__ . '/../../lab-plugin/raylab-header.php';
?>
<!-- raylab-挂载页眉结束 -->
    <div class="container">
        <h1>倒计时</h1>
        <input type="datetime-local" id="countdownTime" />
        <button id="startTimer">开始倒计时</button>
        <div id="timerDisplay"></div>
        <input type="text" id="noteInput" placeholder="输入备注" />
        <button id="markTime">标记时间</button>
        <div id="markedPoints"></div>
    </div>
    <script src="script.js"></script>
</body>
</html>
