
<?php $currentPage = 'raylab-gpt-time03'; ?>

<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>24小时倒计时</title>
    <style>
        body {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            font-family: Arial, sans-serif;
        }
        .countdown {
            font-size: 48px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .input-container {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 10px;
        }
        .input-container input {
            width: 50px;
            margin: 0 5px;
            text-align: center;
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
    <button id="resetButton">重置</button>

    <script>
        let countdownElement = document.getElementById("countdown");
        let hoursInput = document.getElementById("hoursInput");
        let minutesInput = document.getElementById("minutesInput");
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
                    let hoursLeft = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    let minutesLeft = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    let secondsLeft = Math.floor((distance % (1000 * 60)) / 1000);
                    countdownElement.innerHTML = (hoursLeft < 10 ? "0" : "") + hoursLeft + ":" + (minutesLeft < 10 ? "0" : "") + minutesLeft + ":" + (secondsLeft < 10 ? "0" : "") + secondsLeft;
                }
            }, 1000);
        }

        hoursInput.addEventListener("change", startCountdown);
        minutesInput.addEventListener("change", startCountdown);

        resetButton.addEventListener("click", function () {
            clearInterval(interval);
            countdownElement.innerHTML = "00:00:00";
hoursInput.value = "";
minutesInput.value = "";
});
</script>

</body>
</html>
