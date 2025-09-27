<div class="comic_title">
    <span class="comic-title-text"><b>漫画 Comic</b></span>
</div>
<a href="#"><div class="comic_dog">
</div></a>
<p align="center"><span style="font-family: 'Comic Sans MS', cursive; color:#999999; font-size:1rem;"> Test page<span style="font-size:0.8rem">（<b>测试</b>）</span> </span></p>

<div id="ray-comic-menu">
    <a href="/sketch-dream" ><div id="c1" class="ray-comic-menu-icon"></div></a>
    <a href="#" data-comic-id="gzjy"><div id="c2" class="ray-comic-menu-icon"></div></a>
    <a href="#" data-comic-id="wine"><div id="c3" class="ray-comic-menu-icon"></div></a>
    <a href="#" data-comic-id="MagicUbuntu"><div id="c4" class="ray-comic-menu-icon"></div></a>
    <a href="#" data-comic-id="icefire">
    <div id="c5" class="ray-comic-menu-icon">
        <img class="default-img" src="/assets/images/ray-comic-icefire-01.png" alt="comic 5">
        <img class="hover-img" src="/assets/images/ray-comic-icefire-02.png" alt="comic 5 hover">
    </div>
    </a>
</div>

<div id="comic_show">
    
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const comicLinks = document.querySelectorAll('#ray-comic-menu a');
    const comicShowContainer = document.getElementById('comic_show');

    comicLinks.forEach(link => {
        link.addEventListener('click', function(event) {
            const comicId = this.dataset.comicId;
            if (!comicId) {
                // 如果没有 comic-id，说明是外部链接（如 sketch-dream），允许正常跳转
                return;
            }
            
            // 只有有 comic-id 的链接才阻止默认行为并进行AJAX处理
            event.preventDefault();

            // 使用新的统一API路径
            fetch(`api?id=${comicId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.error) {
                        throw new Error(data.error);
                    }

                                        const htmlContent = `
                                                <div id="comic-wrapper">
                                                    <div id="comic-titlebox">
                                                        <h1>${data.title}</h1>
                                                        <p><strong>${data.subtitle}<br />
                                                        ${data.lines}</strong></p>
                                                    </div>
                                                </div>
                                                <div class="comic-item">
                                                    <img src="${data.image}" alt="${data.alt}" />
                                                </div>
                                        `;
                    comicShowContainer.innerHTML = htmlContent;
                })
                .catch(error => {
                    console.error('Error fetching or processing comic data:', error);
                    comicShowContainer.innerHTML = '<p style="text-align:center; color:red;">内容加载失败，请稍后再试。</p>';
                });
        });
    });
});
</script>