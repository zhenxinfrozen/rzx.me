
<?php $currentPage = 'raylab-gpt-time02'; ?>

<!DOCTYPE html>
<html>
<head>
    <title>倒计时</title>
    <style type="text/css">
        .countdown {
            font-size: 300px;
            text-align: center;
            margin-top: 100px;
        }
        input[type="text"] {
            padding: 10px;
            font-size: 24px;
            margin-right: 10px;
        }
        input[type="submit"] {
            padding: 10px 20px;
            font-size: 24px;
            background-color: #333;
            color: #fff;
            border: none;
            cursor: pointer;
        }
        .result {
            font-size: 36px;
            text-align: center;
            color: red;
            margin-top: 30px;
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

        $countdown_time = "";

        if (isset($_POST['countdown_time'])) {
            $countdown_time = $_POST['countdown_time'];
        }

        if (empty($countdown_time)) {
            echo "<form method='post'><input type='text' name='countdown_time' placeholder='请输入倒计时时间，如2021-12-31 23:59:59' /><input type='submit' value='开始倒计时' /></form>";
        } else {
            $end_time = strtotime($countdown_time); // 设定倒计时时间
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
        }
        ?>
    </div>
    <?php
    if (!empty($countdown_time)) {
        echo "<div class='result'>您设置的倒计时时间为：" . $countdown_time . "</div>";
    }
    ?>
</body>
</html>