// A new, minimal sites.js (no jQuery needed)

// 等待 HTML 文档完全加载和解析后再执行脚本
document.addEventListener('DOMContentLoaded', function() {

  // 获取需要操作的元素
  const titleButton = document.getElementById('title');
  const contentBox = document.getElementById('box');

  // 确保元素存在，避免在其他页面报错
  if (titleButton && contentBox) {
    // 为标题按钮添加一个点击事件监听器
    titleButton.addEventListener('click', function(event) {
      // 阻止 <a> 标签的默认跳转行为
      event.preventDefault(); 
      
      // 为 #box 元素切换 .is-collapsed 类
      // 如果有这个类，就移除它；如果没有，就添加它。
      // CSS 会自动处理添加/移除这个类时的动画效果。
      contentBox.classList.toggle('is-collapsed');
    });
  }

});