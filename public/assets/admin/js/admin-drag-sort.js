/**
 * Admin 拖拽排序组件
 * 提供列表项和图片的拖拽排序功能
 *
 * @version 1.0.0
 * @date 2026-01-21
 */

class AdminDragSort {
    constructor(containerId, options = {}) {
        this.container = document.getElementById(containerId);
        if (!this.container) {
            console.error(`Container #${containerId} not found`);
            return;
        }

        this.options = {
            itemSelector: '.admin-list-item',
            handleSelector: '.admin-drag-handle',
            onReorder: null,
            ...options
        };

        this.draggedItem = null;
        this.init();
    }

    init() {
        const items = this.container.querySelectorAll(this.options.itemSelector);

        items.forEach(item => {
            item.setAttribute('draggable', 'true');

            // 拖拽开始
            item.addEventListener('dragstart', (e) => {
                this.draggedItem = item;
                item.classList.add('dragging');

                // 只在拖拽手柄上允许拖拽
                const handle = item.querySelector(this.options.handleSelector);
                if (handle) {
                    handle.classList.add('grabbing');
                }

                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('text/html', item.innerHTML);
            });

            // 拖拽结束
            item.addEventListener('dragend', (e) => {
                item.classList.remove('dragging');

                const handle = item.querySelector(this.options.handleSelector);
                if (handle) {
                    handle.classList.remove('grabbing');
                }

                // 移除所有拖拽悬停状态
                const allItems = this.container.querySelectorAll(this.options.itemSelector);
                allItems.forEach(i => i.classList.remove('drag-over'));

                this.draggedItem = null;

                // 触发重排回调
                if (this.options.onReorder) {
                    this.options.onReorder(this.getOrder());
                }
            });

            // 拖拽经过
            item.addEventListener('dragover', (e) => {
                e.preventDefault();

                if (this.draggedItem === item) return;

                const bounding = item.getBoundingClientRect();
                const offset = e.clientY - bounding.top;

                if (offset > bounding.height / 2) {
                    item.parentNode.insertBefore(this.draggedItem, item.nextSibling);
                } else {
                    item.parentNode.insertBefore(this.draggedItem, item);
                }
            });

            // 拖拽进入
            item.addEventListener('dragenter', (e) => {
                if (this.draggedItem === item) return;
                item.classList.add('drag-over');
            });

            // 拖拽离开
            item.addEventListener('dragleave', (e) => {
                item.classList.remove('drag-over');
            });
        });
    }

    /**
     * 获取当前排序
     */
    getOrder() {
        const items = this.container.querySelectorAll(this.options.itemSelector);
        return Array.from(items).map((item, index) => ({
            id: item.dataset.category || item.dataset.id,
            position: index + 1
        }));
    }

    /**
     * 重新初始化（当列表内容变化时调用）
     */
    refresh() {
        this.init();
    }
}

/**
 * 图片拖拽排序（与列表拖拽类似但针对图片网格优化）
 */
class AdminImageDragSort {
    constructor(containerId, options = {}) {
        this.container = document.getElementById(containerId);
        if (!this.container) {
            console.error(`Container #${containerId} not found`);
            return;
        }

        this.options = {
            itemSelector: '.admin-image-item',
            onReorder: null,
            ...options
        };

        this.draggedItem = null;
        this.init();
    }

    init() {
        const items = this.container.querySelectorAll(this.options.itemSelector);

        items.forEach(item => {
            item.setAttribute('draggable', 'true');

            item.addEventListener('dragstart', (e) => {
                this.draggedItem = item;
                item.classList.add('dragging');
                e.dataTransfer.effectAllowed = 'move';
            });

            item.addEventListener('dragend', (e) => {
                item.classList.remove('dragging');

                const allItems = this.container.querySelectorAll(this.options.itemSelector);
                allItems.forEach(i => i.classList.remove('drag-over'));

                this.draggedItem = null;

                if (this.options.onReorder) {
                    this.options.onReorder(this.getOrder());
                }
            });

            item.addEventListener('dragover', (e) => {
                e.preventDefault();

                if (this.draggedItem === item) return;

                const bounding = item.getBoundingClientRect();
                const offsetX = e.clientX - bounding.left;

                if (offsetX > bounding.width / 2) {
                    item.parentNode.insertBefore(this.draggedItem, item.nextSibling);
                } else {
                    item.parentNode.insertBefore(this.draggedItem, item);
                }
            });

            item.addEventListener('dragenter', (e) => {
                if (this.draggedItem === item) return;
                item.classList.add('drag-over');
            });

            item.addEventListener('dragleave', (e) => {
                item.classList.remove('drag-over');
            });
        });
    }

    getOrder() {
        const items = this.container.querySelectorAll(this.options.itemSelector);
        return Array.from(items).map((item, index) => ({
            id: item.dataset.imageId || item.dataset.id,
            position: index
        }));
    }

    refresh() {
        this.init();
    }
}

// 导出到全局
window.AdminDragSort = AdminDragSort;
window.AdminImageDragSort = AdminImageDragSort;
