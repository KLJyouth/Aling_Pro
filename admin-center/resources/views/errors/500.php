<?php
/**
 * 500 服务器错误页面
 */
?>

<div class="container-fluid">
    <div class="error-page text-center py-5">
        <div class="error-code mb-4">
            <h1 class="display-1 fw-bold text-muted">500</h1>
        </div>
        <div class="error-icon mb-4">
            <i class="bi bi-exclamation-triangle text-danger" style="font-size: 5rem;"></i>
        </div>
        <div class="error-message mb-4">
            <h2>服务器内部错误</h2>
            <p class="lead text-muted">
                抱歉，服务器遇到了一个错误，无法完成您的请求。
            </p>
        </div>
        <div class="error-actions">
            <a href="/admin" class="btn btn-primary btn-lg">
                <i class="bi bi-house-door"></i> 返回首页
            </a>
            <button onclick="location.reload()" class="btn btn-outline-secondary btn-lg ms-2">
                <i class="bi bi-arrow-clockwise"></i> 刷新页面
            </button>
        </div>
        
        <?php if ($isDebug && isset($exception)): ?>
            <div class="error-details mt-5">
                <div class="card">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0">错误详情</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning">
                            <strong>注意：</strong> 这些错误详情仅在调试模式下显示。在生产环境中，请关闭调试模式以保护敏感信息。
                        </div>
                        
                        <h6 class="mb-2">错误消息:</h6>
                        <pre class="bg-light p-3 rounded mb-3"><?= htmlspecialchars($exception->getMessage()) ?></pre>
                        
                        <h6 class="mb-2">文件:</h6>
                        <pre class="bg-light p-3 rounded mb-3"><?= htmlspecialchars($exception->getFile()) ?> (行: <?= $exception->getLine() ?>)</pre>
                        
                        <h6 class="mb-2">堆栈跟踪:</h6>
                        <pre class="bg-light p-3 rounded mb-3 overflow-auto" style="max-height: 300px;"><?= htmlspecialchars($exception->getTraceAsString()) ?></pre>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
    .error-page {
        max-width: 800px;
        margin: 0 auto;
        padding: 2rem;
    }
    
    .error-code {
        opacity: 0.8;
    }
    
    .error-icon {
        animation: shake 1s ease-in-out;
    }
    
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
        20%, 40%, 60%, 80% { transform: translateX(5px); }
    }
</style> 