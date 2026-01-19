<div id="ray-about-bg-wallpaper"><img src="assets/images/about-left-hand-try.jpg" alt="left-hand"></div>
<div id="ray-about-wrapper">
    <div id="ray-about-connectbox">
        <!-- <div id="email"></div> 旧图片占位已移除 -->
        
        <!-- 使用 JS 动态生成邮箱，提高安全性 -->
        <div id="ray-contact-email" onclick="window.location.href='mailto:' + document.getElementById('email-safe').innerText">
            <span class="email-label">Email: </span> <span id="email-safe">Loading...</span>
        </div>
        <script>
            // 简单的防爬虫混淆: 页面加载后再拼接邮箱
            (function(){
                var u = 'i';
                var d = 'rzx.me';
                var email = u + '@' + d;
                document.getElementById('email-safe').innerText = ' ' + email; // 增加空格以匹配原图间距
            })();
        </script>

        <div id="ray-about-center-follow-icons">
            <a href="https://plus.google.com/105722500346157763322" target="_blank" >
            <div id="follow-google" class="follow-icon"></div>
            </a>
            <a href="http://www.twitter.com/rzxme" target="_blank">
            <div id="follow-twitter" class="follow-icon"></div>
            </a>
            <a href="http://weibo.com/zhenxinfrzen" target="_blank">
            <div id="follow-weibo" class="follow-icon"></div>
            </a>
            <a href="http://wpa.qq.com/msgrd?v=3&uin=327456910&site=qq&menu=yes" target="_blank" >
            <div id="follow-qq" class="follow-icon"></div>
            </a>
        </div>
    <div id="ray-about-note">
            闲,忙,无聊,<font color="#CC3300">受不了</font>....<br /><br />
            Beta:2.3 _ 2011.07.15
    </div>        
    </div>
    
        
</div>