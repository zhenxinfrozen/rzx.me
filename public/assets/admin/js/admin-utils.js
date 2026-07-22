/**
 * Admin 通用工具函数
 * 提供后台管理页面常用的工具函数
 *
 * @version 1.0.0
 * @date 2026-01-21
 */

const AdminUtils = {
    /**
     * 显示提示消息
     */
    showMessage(message, type = 'success', duration = 3000) {
        const alertClass = {
            'success': 'alert-success',
            'error': 'alert-danger',
            'warning': 'alert-warning',
            'info': 'alert-info'
        }[type] || 'alert-info';

        const alert = document.createElement('div');
        alert.className = `alert ${alertClass} alert-dismissible fade show`;
        alert.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        alert.innerHTML = `
            <i data-feather="${type === 'success' ? 'check-circle' : 'alert-circle'}"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        document.body.appendChild(alert);

        // 刷新 feather icons
        if (typeof feather !== 'undefined') {
            feather.replace();
        }

        // 自动关闭
        if (duration > 0) {
            setTimeout(() => {
                alert.remove();
            }, duration);
        }
    },

    /**
     * 确认对话框
     */
    confirm(message, callback) {
        if (confirm(message)) {
            callback();
        }
    },

    /**
     * AJAX 请求封装
     */
    async request(url, options = {}) {
        const defaultOptions = {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            }
        };

        const finalOptions = { ...defaultOptions, ...options };

        try {
            const response = await fetch(url, finalOptions);
            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Request failed');
            }

            return data;
        } catch (error) {
            console.error('Request error:', error);
            this.showMessage(error.message, 'error');
            throw error;
        }
    },

    /**
     * POST 请求
     */
    async post(url, data) {
        return this.request(url, {
            method: 'POST',
            body: JSON.stringify(data)
        });
    },

    /**
     * 表单数据 POST
     */
    async postForm(url, formData) {
        return fetch(url, {
            method: 'POST',
            body: formData
        }).then(res => res.json());
    },

    /**
     * 防抖函数
     */
    debounce(func, wait = 300) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    },

    /**
     * 节流函数
     */
    throttle(func, wait = 300) {
        let inThrottle;
        return function(...args) {
            if (!inThrottle) {
                func.apply(this, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, wait);
            }
        };
    },

    /**
     * 格式化文件大小
     */
    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    },

    /**
     * 格式化日期
     */
    formatDate(date, format = 'YYYY-MM-DD HH:mm:ss') {
        const d = new Date(date);
        const map = {
            'YYYY': d.getFullYear(),
            'MM': String(d.getMonth() + 1).padStart(2, '0'),
            'DD': String(d.getDate()).padStart(2, '0'),
            'HH': String(d.getHours()).padStart(2, '0'),
            'mm': String(d.getMinutes()).padStart(2, '0'),
            'ss': String(d.getSeconds()).padStart(2, '0')
        };

        return format.replace(/YYYY|MM|DD|HH|mm|ss/g, matched => map[matched]);
    },

    /**
     * 图片预加载
     */
    preloadImage(src) {
        return new Promise((resolve, reject) => {
            const img = new Image();
            img.onload = () => resolve(img);
            img.onerror = reject;
            img.src = src;
        });
    },

    /**
     * 批量预加载图片
     */
    async preloadImages(srcs) {
        return Promise.all(srcs.map(src => this.preloadImage(src)));
    },

    /**
     * 复制到剪贴板
     */
    async copyToClipboard(text) {
        try {
            await navigator.clipboard.writeText(text);
            this.showMessage('已复制到剪贴板', 'success');
            return true;
        } catch (error) {
            console.error('Copy failed:', error);
            this.showMessage('复制失败', 'error');
            return false;
        }
    },

    /**
     * 下载文件
     */
    downloadFile(url, filename) {
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    },

    /**
     * 生成唯一ID
     */
    generateId() {
        return Date.now().toString(36) + Math.random().toString(36).substr(2);
    },

    /**
     * URL 编码（支持中文）
     */
    encodeURL(str) {
        return encodeURIComponent(str);
    },

    /**
     * URL 解码
     */
    decodeURL(str) {
        return decodeURIComponent(str);
    },

    /**
     * 获取URL参数
     */
    getUrlParam(name) {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(name);
    },

    /**
     * 设置URL参数（不刷新页面）
     */
    setUrlParam(name, value) {
        const url = new URL(window.location);
        url.searchParams.set(name, value);
        window.history.pushState({}, '', url);
    }
};

// 导出到全局
window.AdminUtils = AdminUtils;
