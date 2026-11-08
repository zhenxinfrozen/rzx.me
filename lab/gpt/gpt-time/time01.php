<?php $currentPage = 'raylab-gpt-time01'; ?>

<!DOCTYPE html>
<html>
<head>
    <title>24小时倒计时</title>
    <style type="text/css">
        .countdown {
            font-size: 300px;
            text-align: center;
            margin-top: 100px;
        }
    </style>
</head>
<body>
    <!-- raylab-挂载页眉 -->
<?php
require_once __DIR__ . '/../../lab-plugin/raylab-header.php';
?>
<!-- raylab-挂载页眉结束 -->
    <div class="countdown">
        <?php 
        date_default_timezone_set('Asia/Shanghai');
        $end_time = strtotime("+1 day 00:00:00"); // 设定倒计时时间
        $now_time = time();
        $left_time = $end_time - $now_time;
        $left_hour = floor($left_time / 3600);
        $left_minute = floor(($left_time % 3600) / 60);
        $left_second = $left_time % 60;
        if ($left_time > 0) {
            echo $left_hour . ":" . $left_minute . ":" . $left_second;
        } else {
            echo "时间到了！";
        }
        ?>
    </div>
</body>
</html>