/**
 * Admin 图片管理器组件
 * 提供图片上传、预览、星标、删除等功能
 *
 * @version 1.0.0
 * @date 2026-01-21
 */

class AdminImageManager {
    constructor(containerId, options = {}) {
        this.container = document.getElementById(containerId);
        if (!this.container) {
            console.error(`Container #${containerId} not found`);
            return;
        }

        this.options = {
            allowDrag: true,
            allowStar: true,
            allowDelete: true,
            thumbnailSize: '80x80',
            uploadUrl: '/admin/upload-image',
            onImageAdded: null,
            onImageDeleted: null,
            onThumbnailSet: null,
            ...options
        };

        this.images = [];
        this.dragSort = null;

        this.init();
    }

    init() {
        this.render();

        if (this.options.allowDrag) {
            this.enableDragSort();
        }
    }

    /**
     * 渲染图片网格
     */
    render() {
        this.container.innerHTML = '';

        // 添加图片按钮
        const addBtn = document.createElement('div');
        addBtn.className = 'admin-add-image-btn';
        addBtn.innerHTML = '+';
        addBtn.onclick = () => this.selectImages();
        this.container.appendChild(addBtn);

        // 渲染已有图片
        this.images.forEach((image, index) => {
            const item = this.createImageItem(image, index);
            this.container.appendChild(item);
        });
    }

    /**
     * 创建图片项
     */
    createImageItem(image, index) {
        const item = document.createElement('div');
        item.className = 'admin-image-item';
        if (image.isThumbnail) {
            item.classList.add('is-thumbnail');
        }
        item.dataset.imageId = image.id;
        item.dataset.index = index;

        // 图片
        const img = document.createElement('img');
        img.src = image.thumbPath || image.path;
        img.alt = image.name || 'Image';
        item.appendChild(img);

        // 星标角标
        if (image.isThumbnail) {
            const badge = document.createElement('div');
            badge.className = 'admin-image-star-badge';
            badge.innerHTML = '★';
            item.appendChild(badge);
        }

        // 操作按钮
        const actions = document.createElement('div');
        actions.className = 'admin-image-actions';

        if (this.options.allowStar) {
            const starBtn = document.createElement('button');
            starBtn.className = 'admin-image-action-btn star';
            if (image.isThumbnail) starBtn.classList.add('active');
            starBtn.innerHTML = '★';
            starBtn.title = '设为封面';
            starBtn.onclick = (e) => {
                e.stopPropagation();
                this.setAsThumbnail(image.id);
            };
            actions.appendChild(starBtn);
        }

        if (this.options.allowDelete) {
            const deleteBtn = document.createElement('button');
            deleteBtn.className = 'admin-image-action-btn delete';
            deleteBtn.innerHTML = '×';
            deleteBtn.title = '删除图片';
            deleteBtn.onclick = (e) => {
                e.stopPropagation();
                this.deleteImage(image.id);
            };
            actions.appendChild(deleteBtn);
        }

        item.appendChild(actions);

        return item;
    }

    /**
     * 选择图片文件
     */
    selectImages() {
        const input = document.createElement('input');
        input.type = 'file';
        input.multiple = true;
        input.accept = 'image/*';
        input.onchange = (e) => {
            const files = Array.from(e.target.files);
            this.uploadImages(files);
        };
        input.click();
    }

    /**
     * 上传图片
     */
    async uploadImages(files) {
        for (const file of files) {
            try {
                const formData = new FormData();
                formData.append('image', file);

                const response = await fetch(this.options.uploadUrl, {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    const newImage = {
                        id: result.id || Date.now() + Math.random(),
                        name: file.name,
                        path: result.path,
                        thumbPath: result.thumbPath,
                        isThumbnail: false
                    };

                    this.images.push(newImage);

                    if (this.options.onImageAdded) {
                        this.options.onImageAdded(newImage);
                    }
                }
            } catch (error) {
                console.error('Upload failed:', error);
            }
        }

        this.render();
        if (this.options.allowDrag) {
            this.enableDragSort();
        }
    }

    /**
     * 设为封面图
     */
    setAsThumbnail(imageId) {
        this.images.forEach(img => {
            img.isThumbnail = (img.id === imageId);
        });

        this.render();

        if (this.options.onThumbnailSet) {
            this.options.onThumbnailSet(imageId);
        }
    }

    /**
     * 删除图片
     */
    deleteImage(imageId) {
        if (!confirm('确定要删除这张图片吗？')) return;

        this.images = this.images.filter(img => img.id !== imageId);

        this.render();

        if (this.options.onImageDeleted) {
            this.options.onImageDeleted(imageId);
        }
    }

    /**
     * 启用拖拽排序
     */
    enableDragSort() {
        if (this.dragSort) {
            this.dragSort.refresh();
        } else {
            this.dragSort = new AdminImageDragSort(this.container.id, {
                onReorder: (order) => {
                    // 重新排序 images 数组
                    const newImages = [];
                    order.forEach(item => {
                        const img = this.images.find(i => i.id == item.id);
                        if (img) newImages.push(img);
                    });
                    this.images = newImages;
                }
            });
        }
    }

    /**
     * 加载图片数据
     */
    loadImages(images) {
        this.images = images || [];
        this.render();
        if (this.options.allowDrag) {
            this.enableDragSort();
        }
    }

    /**
     * 获取图片数据
     */
    getImages() {
        return this.images;
    }
}

// 导出到全局
window.AdminImageManager = AdminImageManager;
