// 现代化画廊 JavaScript
class ModernGallery {
    constructor() {
        this.images = [];
        this.currentIndex = 0;
        this.isPlaying = false;
        this.playInterval = null;
        this.displayTime = 3000; // 默认3秒

        this.elements = {
            image: document.getElementById('currentImage'),
            caption: document.getElementById('imageCaption'),
            counter: document.getElementById('counter'),
            prevBtn: document.getElementById('prevBtn'),
            nextBtn: document.getElementById('nextBtn'),
            playBtn: document.getElementById('playBtn'),
            playIcon: document.getElementById('playIcon'),
            pauseIcon: document.getElementById('pauseIcon'),
            thumbnailStrip: document.getElementById('thumbnailStrip'),
            spinner: document.querySelector('.loading-spinner')
        };

        this.init();
    }

    async init() {
        try {
            await this.loadGalleryData();
            this.setupEventListeners();
            this.createThumbnails();
            this.showImage(0);
            this.startAutoPlay();
        } catch (error) {
            console.error('初始化画廊失败:', error);
            this.showError('加载画廊数据失败');
        }
    }

    async loadGalleryData() {
        try {
            const response = await fetch('gallery.xml');
            const xmlText = await response.text();
            const parser = new DOMParser();
            const xmlDoc = parser.parseFromString(xmlText, 'text/xml');

            const gallery = xmlDoc.querySelector('gallery');
            const title = gallery.getAttribute('title');
            const displayTime = parseInt(gallery.getAttribute('displayTime')) || 3;
            this.displayTime = displayTime * 1000;

            const imageNodes = xmlDoc.querySelectorAll('image');
            this.images = Array.from(imageNodes).map(node => ({
                url: node.querySelector('url').textContent,
                caption: node.querySelector('caption').textContent,
                width: parseInt(node.querySelector('width').textContent),
                height: parseInt(node.querySelector('height').textContent)
            }));

            document.querySelector('.gallery-title').textContent = title || 'Dream-颓废动画人';
        } catch (error) {
            console.error('加载 XML 失败:', error);
            throw error;
        }
    }

    setupEventListeners() {
        // 按钮事件
        this.elements.prevBtn.addEventListener('click', () => this.prev());
        this.elements.nextBtn.addEventListener('click', () => this.next());
        this.elements.playBtn.addEventListener('click', () => this.togglePlay());

        // 键盘事件
        document.addEventListener('keydown', (e) => {
            switch(e.key) {
                case 'ArrowLeft':
                    this.prev();
                    break;
                case 'ArrowRight':
                    this.next();
                    break;
                case ' ':
                    e.preventDefault();
                    this.togglePlay();
                    break;
                case 'Home':
                    this.showImage(0);
                    break;
                case 'End':
                    this.showImage(this.images.length - 1);
                    break;
            }
        });

        // 触摸滑动支持
        let touchStartX = 0;
        let touchEndX = 0;

        this.elements.image.addEventListener('touchstart', (e) => {
            touchStartX = e.changedTouches[0].screenX;
        });

        this.elements.image.addEventListener('touchend', (e) => {
            touchEndX = e.changedTouches[0].screenX;
            this.handleSwipe(touchStartX, touchEndX);
        });

        // 图片加载事件
        this.elements.image.addEventListener('load', () => {
            this.hideSpinner();
        });

        this.elements.image.addEventListener('error', () => {
            this.hideSpinner();
            this.showError('图片加载失败');
        });
    }

    handleSwipe(startX, endX) {
        const threshold = 50;
        const diff = startX - endX;

        if (Math.abs(diff) > threshold) {
            if (diff > 0) {
                this.next();
            } else {
                this.prev();
            }
        }
    }

    createThumbnails() {
        this.elements.thumbnailStrip.innerHTML = '';

        this.images.forEach((image, index) => {
            const thumb = document.createElement('img');
            thumb.src = image.url;
            thumb.alt = `缩略图 ${index + 1}`;
            thumb.className = 'thumbnail';
            thumb.dataset.index = index;

            if (index === 0) {
                thumb.classList.add('active');
            }

            thumb.addEventListener('click', () => {
                this.showImage(index);
                if (this.isPlaying) {
                    this.resetAutoPlay();
                }
            });

            this.elements.thumbnailStrip.appendChild(thumb);
        });
    }

    showImage(index) {
        if (index < 0 || index >= this.images.length) return;

        this.currentIndex = index;
        const image = this.images[index];

        // 显示加载动画
        this.showSpinner();

        // 加载图片
        this.elements.image.src = image.url;
        this.elements.caption.textContent = image.caption || '';
        this.updateCounter();
        this.updateThumbnails();
    }

    updateCounter() {
        this.elements.counter.textContent = `${this.currentIndex + 1} / ${this.images.length}`;
    }

    updateThumbnails() {
        const thumbnails = this.elements.thumbnailStrip.querySelectorAll('.thumbnail');
        thumbnails.forEach((thumb, index) => {
            thumb.classList.toggle('active', index === this.currentIndex);
        });

        // 滚动到当前缩略图
        const currentThumb = thumbnails[this.currentIndex];
        if (currentThumb) {
            currentThumb.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
        }
    }

    next() {
        const nextIndex = (this.currentIndex + 1) % this.images.length;
        this.showImage(nextIndex);
    }

    prev() {
        const prevIndex = (this.currentIndex - 1 + this.images.length) % this.images.length;
        this.showImage(prevIndex);
    }

    togglePlay() {
        if (this.isPlaying) {
            this.stopAutoPlay();
        } else {
            this.startAutoPlay();
        }
    }

    startAutoPlay() {
        this.isPlaying = true;
        this.elements.playIcon.style.display = 'none';
        this.elements.pauseIcon.style.display = 'block';

        this.playInterval = setInterval(() => {
            this.next();
        }, this.displayTime);
    }

    stopAutoPlay() {
        this.isPlaying = false;
        this.elements.playIcon.style.display = 'block';
        this.elements.pauseIcon.style.display = 'none';

        if (this.playInterval) {
            clearInterval(this.playInterval);
            this.playInterval = null;
        }
    }

    resetAutoPlay() {
        if (this.isPlaying) {
            this.stopAutoPlay();
            this.startAutoPlay();
        }
    }

    showSpinner() {
        this.elements.spinner.classList.add('active');
    }

    hideSpinner() {
        this.elements.spinner.classList.remove('active');
    }

    showError(message) {
        this.elements.caption.textContent = message;
        this.elements.caption.style.color = '#ff6b6b';
    }
}

// 页面加载完成后初始化画廊
document.addEventListener('DOMContentLoaded', () => {
    const gallery = new ModernGallery();
});

// 服务工作线程注册（可选，用于离线支持）
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        // navigator.serviceWorker.register('/sw.js').catch(() => {});
    });
}
