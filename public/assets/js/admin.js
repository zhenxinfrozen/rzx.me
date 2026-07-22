/**
 * RZX.ME 后台管理系统 JavaScript
 * 提供交互功能和用户体验增强
 */

class AdminDashboard {
    constructor() {
        this.init();
    }
    
    init() {
        // 初始化图标
        feather.replace();
        
        // 绑定事件
        this.bindEvents();
        
        // 初始化组件
        this.initComponents();
        
        // 加载完成动画
        this.showLoadAnimation();
    }
    
    bindEvents() {
        // 用户菜单切换
        const userMenuTrigger = document.querySelector('.user-menu-trigger');
        if (userMenuTrigger) {
            userMenuTrigger.addEventListener('click', this.toggleUserMenu.bind(this));
        }
        
        // 移动端侧边栏切换
        this.initMobileSidebar();
        
        // 快速操作卡片交互
        this.initQuickActions();
        
        // 统计卡片动画
        this.initStatCards();
    }
    
    initComponents() {
        // 检查系统状态
        this.checkSystemStatus();
        
        // 初始化通知系统
        this.initNotifications();
    }
    
    showLoadAnimation() {
        const cards = document.querySelectorAll('.stat-card, .dashboard-card');
        cards.forEach((card, index) => {
            setTimeout(() => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                card.style.transition = 'all 0.5s ease';
                
                setTimeout(() => {
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, 50);
            }, index * 100);
        });
    }
    
    toggleUserMenu() {
        // 用户菜单切换逻辑（待实现下拉菜单）
        console.log('用户菜单切换');
    }
    
    initMobileSidebar() {
        // 移动端响应式处理
        const sidebar = document.querySelector('.admin-sidebar');
        let touchStartX = 0;
        
        // 触摸滑动检测
        document.addEventListener('touchstart', (e) => {
            touchStartX = e.touches[0].clientX;
        });
        
        document.addEventListener('touchmove', (e) => {
            const touchX = e.touches[0].clientX;
            const diff = touchX - touchStartX;
            
            if (Math.abs(diff) > 50) {
                if (diff > 0 && touchStartX < 50) {
                    // 向右滑动，显示侧边栏
                    sidebar.classList.add('open');
                } else if (diff < 0 && sidebar.classList.contains('open')) {
                    // 向左滑动，隐藏侧边栏
                    sidebar.classList.remove('open');
                }
            }
        });
        
        // 点击主内容区域关闭侧边栏
        document.querySelector('.admin-main').addEventListener('click', () => {
            if (window.innerWidth <= 768) {
                sidebar.classList.remove('open');
            }
        });
    }
    
    initQuickActions() {
        const quickActions = document.querySelectorAll('.quick-action');
        quickActions.forEach(action => {
            action.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-2px)';
                this.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)';
            });
            
            action.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = 'none';
            });
        });
    }
    
    initStatCards() {
        const statCards = document.querySelectorAll('.stat-card');
        
        // 数字动画效果
        statCards.forEach(card => {
            const number = card.querySelector('h3');
            const finalNumber = parseInt(number.textContent);
            
            if (!isNaN(finalNumber)) {
                this.animateNumber(number, 0, finalNumber, 1000);
            }
        });
    }
    
    animateNumber(element, start, end, duration) {
        const range = end - start;
        const startTime = performance.now();
        
        const animate = (currentTime) => {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            
            // 缓动函数
            const easeOutQuart = 1 - Math.pow(1 - progress, 4);
            const current = Math.floor(start + (range * easeOutQuart));
            
            element.textContent = current;
            
            if (progress < 1) {
                requestAnimationFrame(animate);
            } else {
                element.textContent = end;
            }
        };
        
        requestAnimationFrame(animate);
    }
    
    checkSystemStatus() {
        // 模拟系统状态检查
        const statusItems = document.querySelectorAll('.status-item');
        
        statusItems.forEach((item, index) => {
            setTimeout(() => {
                const indicator = item.querySelector('.status-indicator');
                indicator.style.opacity = '0.5';
                
                setTimeout(() => {
                    indicator.style.opacity = '1';
                    indicator.style.transform = 'scale(1.2)';
                    
                    setTimeout(() => {
                        indicator.style.transform = 'scale(1)';
                    }, 200);
                }, 300);
            }, index * 200);
        });
    }
    
    initNotifications() {
        // 通知系统初始化
        this.notifications = [];
        this.createNotificationContainer();
    }
    
    createNotificationContainer() {
        const container = document.createElement('div');
        container.id = 'notification-container';
        container.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 10000;
            max-width: 350px;
        `;
        document.body.appendChild(container);
    }
    
    showNotification(message, type = 'info', duration = 5000) {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.style.cssText = `
            background: white;
            padding: 16px;
            margin-bottom: 10px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            border-left: 4px solid ${this.getNotificationColor(type)};
            transform: translateX(100%);
            transition: transform 0.3s ease;
        `;
        
        notification.innerHTML = `
            <div style="display: flex; align-items: center; gap: 8px;">
                <i data-feather="${this.getNotificationIcon(type)}"></i>
                <span>${message}</span>
                <button onclick="this.parentElement.parentElement.remove()" 
                        style="margin-left: auto; background: none; border: none; cursor: pointer;">
                    <i data-feather="x"></i>
                </button>
            </div>
        `;
        
        document.getElementById('notification-container').appendChild(notification);
        feather.replace();
        
        // 显示动画
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 100);
        
        // 自动移除
        if (duration > 0) {
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.style.transform = 'translateX(100%)';
                    setTimeout(() => notification.remove(), 300);
                }
            }, duration);
        }
        
        return notification;
    }
    
    getNotificationColor(type) {
        const colors = {
            info: '#0073aa',
            success: '#00a32a',
            warning: '#dba617',
            error: '#d63638'
        };
        return colors[type] || colors.info;
    }
    
    getNotificationIcon(type) {
        const icons = {
            info: 'info',
            success: 'check-circle',
            warning: 'alert-triangle',
            error: 'alert-circle'
        };
        return icons[type] || icons.info;
    }
}

// 工具函数
class AdminUtils {
    static formatFileSize(bytes) {
        const sizes = ['B', 'KB', 'MB', 'GB'];
        if (bytes === 0) return '0 B';
        const i = Math.floor(Math.log(bytes) / Math.log(1024));
        return (bytes / Math.pow(1024, i)).toFixed(2) + ' ' + sizes[i];
    }
    
    static formatDate(date) {
        return new Intl.DateTimeFormat('zh-CN', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        }).format(new Date(date));
    }
    
    static debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
    
    static throttle(func, limit) {
        let inThrottle;
        return function(...args) {
            if (!inThrottle) {
                func.apply(this, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    }
}

// 页面加载完成后初始化
document.addEventListener('DOMContentLoaded', () => {
    window.adminDashboard = new AdminDashboard();
    
    // 全局快捷键
    document.addEventListener('keydown', (e) => {
        // Ctrl/Cmd + K: 快速搜索（待实现）
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            console.log('打开快速搜索');
        }
        
        // ESC: 关闭弹窗/侧边栏
        if (e.key === 'Escape') {
            const sidebar = document.querySelector('.admin-sidebar');
            if (sidebar && sidebar.classList.contains('open')) {
                sidebar.classList.remove('open');
            }
        }
    });
    
    // 开发模式下显示欢迎消息
    if (window.location.search.includes('dev')) {
        setTimeout(() => {
            window.adminDashboard.showNotification(
                '欢迎使用 RZX.ME 后台管理系统！', 
                'success'
            );
        }, 1000);
    }
});

// 导出到全局
window.AdminUtils = AdminUtils;