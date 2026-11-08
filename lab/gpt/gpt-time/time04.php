
<?php $currentPage = 'raylab-gpt-time04'; ?>

<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>24小时倒计时</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
        }
        .countdown {
            font-size: calc(12px + 8vmin);
            font-weight: bold;
            color: #333;
            margin-bottom: 20px;
        }
        .input-container {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 10px;
        }
        .input-container input {
            width: 70px;
            height: 40px;
            font-size: 18px;
            margin: 0 5px;
            text-align: center;
            border: 2px solid #333;
            border-radius: 5px;
            background-color: #fff;
        }
        button {
            padding: 10px 20px;
            font-size: 18px;
            font-weight: bold;
            color: #fff;
            background-color: #333;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #555;
        }
    </style>
</head>
<body>
<!-- raylab-挂载页眉 -->
<?php
require_once __DIR__ . '/../../lab-plugin/raylab-header.php';
?>
<!-- raylab-挂载页眉结束 -->
    <div class="countdown" id="countdown">00:00:00</div>
    <div class="input-container">
        <input type="number" min="0" max="23" step="1" id="hoursInput" placeholder="小时">
        <span>:</span>
        <input type="number" min="0" max="59" step="1" id="minutesInput" placeholder="分钟">
    </div>
    <button id="startButton">开始</button>
    <button id="resetButton" style="margin-left: 10px;">重置</button>

    <script>
        let countdownElement = document.getElementById("countdown");
        let hoursInput = document.getElementById("hoursInput");
        let minutesInput = document.getElementById("minutesInput");
        let startButton = document.getElementById("startButton");
        let resetButton = document.getElementById("resetButton");
        let interval;

        function startCountdown() {
            clearInterval(interval);
            let hours = parseInt(hoursInput.value) || 0;
            let minutes = parseInt(minutesInput.value) || 0;
            let currentTime = new Date();
            let endTime = new Date(currentTime.getTime() + (hours * 60 + minutes) * 60 * 1000);

            interval = setInterval(function () {
                let now = new Date();
                let distance = endTime - now;
                if (distance <= 0) {
                    clearInterval(interval);
                    countdownElement.innerHTML = "00:00:00";
                } else {
                    let hoursLeft = Math.floor((distance % (1000 * 60 * 60 * 24))/ (1000 * 60 * 60));
let minutesLeft = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
let secondsLeft = Math.floor((distance % (1000 * 60)) / 1000);
countdownElement.innerHTML = (hoursLeft < 10 ? "0" : "") + hoursLeft + ":" + (minutesLeft < 10 ? "0" : "") + minutesLeft + ":" + (secondsLeft < 10 ? "0" : "") + secondsLeft;
}
}, 1000);
}
    startButton.addEventListener("click", startCountdown);

    resetButton.addEventListener("click", function () {
        clearInterval(interval);
        countdownElement.innerHTML = "00:00:00";
        hoursInput.value = "";
        minutesInput.value = "";
    });
</script>
</body>
</html>