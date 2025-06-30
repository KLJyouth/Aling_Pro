<?php
/**
 * 403 禁止访问错误页面
 */
?>

<div class="container-fluid">
    <div class="error-page text-center py-5">
        <div class="error-code mb-4">
            <h1 class="display-1 fw-bold text-muted">403</h1>
        </div>
        <div class="error-icon mb-4">
            <i class="bi bi-shield-lock text-warning" style="font-size: 5rem;"></i>
        </div>
        <div class="error-message mb-4">
            <h2>禁止访问</h2>
            <p class="lead text-muted">
                抱歉，您没有权限访问此页面。
            </p>
        </div>
        <div class="error-actions">
            <a href="/admin" class="btn btn-primary btn-lg">
                <i class="bi bi-house-door"></i> 返回首页
            </a>
            <button onclick="history.back()" class="btn btn-outline-secondary btn-lg ms-2">
                <i class="bi bi-arrow-left"></i> 返回上一页
            </button>
        </div>
    </div>
</div>

<style>
    .error-page {
        max-width: 600px;
        margin: 0 auto;
        padding: 2rem;
    }
    
    .error-code {
        opacity: 0.8;
    }
    
    .error-icon {
        animation: bounce 2s infinite;
    }
    
    @keyframes bounce {
        0%, 20%, 50%, 80%, 100% {
            transform: translateY(0);
        }
        40% {
            transform: translateY(-20px);
        }
        60% {
            transform: translateY(-10px);
        }
    }
</style> 