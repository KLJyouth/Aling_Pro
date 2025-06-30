<?php
/**
 * 系统维护中页面
 */
?>

<div class="container-fluid">
    <div class="error-page text-center py-5">
        <div class="error-icon mb-4">
            <i class="bi bi-tools text-primary" style="font-size: 5rem;"></i>
        </div>
        <div class="error-message mb-4">
            <h1 class="display-4 fw-bold">系统维护中</h1>
            <p class="lead text-muted mt-3">
                我们正在进行系统维护和升级，以提供更好的服务体验。
                <br>
                请稍后再试，感谢您的理解与支持。
            </p>
        </div>
        <div class="maintenance-info mb-4">
            <div class="card bg-light">
                <div class="card-body">
                    <h5 class="card-title">预计恢复时间</h5>
                    <p class="card-text">
                        <?php
                        // 获取维护模式预计结束时间
                        $endTime = isset($maintenanceEndTime) ? $maintenanceEndTime : null;
                        
                        if ($endTime) {
                            echo date('Y-m-d H:i:s', strtotime($endTime));
                        } else {
                            echo '暂未确定，请稍后再试';
                        }
                        ?>
                    </p>
                </div>
            </div>
        </div>
        <div class="error-actions">
            <button onclick="location.reload()" class="btn btn-primary btn-lg">
                <i class="bi bi-arrow-clockwise"></i> 刷新页面
            </button>
            <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                <a href="/admin/maintenance/toggle" class="btn btn-outline-danger btn-lg ms-2">
                    <i class="bi bi-toggle-on"></i> 关闭维护模式
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    .error-page {
        max-width: 700px;
        margin: 0 auto;
        padding: 2rem;
    }
    
    .error-icon {
        animation: rotate 5s linear infinite;
    }
    
    @keyframes rotate {
        from {
            transform: rotate(0deg);
        }
        to {
            transform: rotate(360deg);
        }
    }
</style> 