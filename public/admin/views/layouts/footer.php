        </div>
        <!-- 页面内容区域结束 -->
    </main>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Feather Icons -->
    <script src="https://unpkg.com/feather-icons@4.29.0/dist/feather.min.js"></script>
    <!-- 自定义JS -->
    <script src="../assets/js/admin.js"></script>
    <script>
        // 初始化页面
        document.addEventListener('DOMContentLoaded', function() {
            // 初始化图标
            feather.replace();
            
            // 添加页面特定的JavaScript逻辑
            if (typeof pageInit === 'function') {
                pageInit();
            }
        });
    </script>
</body>
</html>