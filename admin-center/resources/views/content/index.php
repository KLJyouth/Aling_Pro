<?php
/**
 * 内容管理首页
 * 
 * 显示内容管理的主要功能和统计信息
 */

// 设置页面标题
$pageTitle = "内容管理";
$currentPage = "content";

// 包含头部
require_once __DIR__ . "/../layouts/header.php";
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">内容管理</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="/admin">首页</a></li>
        <li class="breadcrumb-item active">内容管理</li>
    </ol>
    
    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">新闻文章</h4>
                            <div class="small">总数: <span id="newsCount">加载中...</span></div>
                        </div>
                        <div class="fs-1">
                            <i class="bi bi-newspaper"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="/admin/content/news">查看详情</a>
                    <div class="small text-white"><i class="bi bi-chevron-right"></i></div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">页面内容</h4>
                            <div class="small">总数: <span id="pagesCount">加载中...</span></div>
                        </div>
                        <div class="fs-1">
                            <i class="bi bi-file-earmark-text"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="/admin/content/pages">查看详情</a>
                    <div class="small text-white"><i class="bi bi-chevron-right"></i></div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card bg-info text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">媒体库</h4>
                            <div class="small">文件: <span id="mediaCount">加载中...</span></div>
                        </div>
                        <div class="fs-1">
                            <i class="bi bi-images"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="/admin/content/media">查看详情</a>
                    <div class="small text-white"><i class="bi bi-chevron-right"></i></div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">评论</h4>
                            <div class="small">待审核: <span id="commentsCount">加载中...</span></div>
                        </div>
                        <div class="fs-1">
                            <i class="bi bi-chat-left-text"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="/admin/content/comments">查看详情</a>
                    <div class="small text-white"><i class="bi bi-chevron-right"></i></div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-clock-history me-1"></i>
                    最近发布的内容
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="recentContentTable">
                            <thead>
                                <tr>
                                    <th>标题</th>
                                    <th>类型</th>
                                    <th>作者</th>
                                    <th>发布时间</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="5" class="text-center">加载中...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-bar-chart-line me-1"></i>
                    内容访问统计
                </div>
                <div class="card-body">
                    <canvas id="contentViewsChart" width="100%" height="40"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <i class="bi bi-lightning-charge me-1"></i>
                        快速操作
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 col-sm-6 mb-3">
                            <a href="/admin/content/news/create" class="btn btn-primary w-100 py-3">
                                <i class="bi bi-plus-circle me-2"></i>
                                发布新闻文章
                            </a>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <a href="/admin/content/pages/create" class="btn btn-success w-100 py-3">
                                <i class="bi bi-plus-circle me-2"></i>
                                创建新页面
                            </a>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <a href="/admin/content/media/upload" class="btn btn-info w-100 py-3">
                                <i class="bi bi-upload me-2"></i>
                                上传媒体文件
                            </a>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <a href="/admin/content/categories" class="btn btn-secondary w-100 py-3">
                                <i class="bi bi-tags me-2"></i>
                                管理分类
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    // 加载统计数据
    fetch("/api/admin/content/stats")
        .then(response => response.json())
        .then(data => {
            document.getElementById("newsCount").textContent = data.news_count || 0;
            document.getElementById("pagesCount").textContent = data.pages_count || 0;
            document.getElementById("mediaCount").textContent = data.media_count || 0;
            document.getElementById("commentsCount").textContent = data.pending_comments_count || 0;
        })
        .catch(error => {
            console.error("加载统计数据失败:", error);
            document.getElementById("newsCount").textContent = "0";
            document.getElementById("pagesCount").textContent = "0";
            document.getElementById("mediaCount").textContent = "0";
            document.getElementById("commentsCount").textContent = "0";
        });
    
    // 加载最近内容
    fetch("/api/admin/content/recent")
        .then(response => response.json())
        .then(data => {
            const tableBody = document.querySelector("#recentContentTable tbody");
            tableBody.innerHTML = "";
            
            if (data.length === 0) {
                tableBody.innerHTML = "<tr><td colspan=\"5\" class=\"text-center\">暂无内容</td></tr>";
                return;
            }
            
            data.forEach(item => {
                const row = document.createElement("tr");
                
                row.innerHTML = `
                    <td>${item.title}</td>
                    <td><span class="badge ${getTypeBadgeClass(item.type)}">${getTypeLabel(item.type)}</span></td>
                    <td>${item.author_name}</td>
                    <td>${formatDate(item.published_at)}</td>
                    <td>
                        <a href="/admin/content/${item.type}s/edit/${item.id}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <a href="${item.url}" target="_blank" class="btn btn-sm btn-outline-info">
                            <i class="bi bi-eye"></i>
                        </a>
                    </td>
                `;
                
                tableBody.appendChild(row);
            });
        })
        .catch(error => {
            console.error("加载最近内容失败:", error);
            const tableBody = document.querySelector("#recentContentTable tbody");
            tableBody.innerHTML = "<tr><td colspan=\"5\" class=\"text-center\">加载失败</td></tr>";
        });
    
    // 绘制访问统计图表
    fetch("/api/admin/content/views-stats")
        .then(response => response.json())
        .then(data => {
            const ctx = document.getElementById("contentViewsChart");
            new Chart(ctx, {
                type: "line",
                data: {
                    labels: data.labels,
                    datasets: [
                        {
                            label: "新闻文章",
                            backgroundColor: "rgba(13, 110, 253, 0.2)",
                            borderColor: "rgba(13, 110, 253, 1)",
                            data: data.news_views,
                            fill: true
                        },
                        {
                            label: "页面内容",
                            backgroundColor: "rgba(25, 135, 84, 0.2)",
                            borderColor: "rgba(25, 135, 84, 1)",
                            data: data.pages_views,
                            fill: true
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: "top"
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });
        })
        .catch(error => {
            console.error("加载访问统计数据失败:", error);
            document.getElementById("contentViewsChart").parentNode.innerHTML = 
                "<div class=\"text-center py-5\">加载统计数据失败</div>";
        });
    
    // 辅助函数
    function getTypeBadgeClass(type) {
        switch (type) {
            case "news": return "bg-primary";
            case "page": return "bg-success";
            default: return "bg-secondary";
        }
    }
    
    function getTypeLabel(type) {
        switch (type) {
            case "news": return "新闻";
            case "page": return "页面";
            default: return type;
        }
    }
    
    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleString("zh-CN", {
            year: "numeric",
            month: "2-digit",
            day: "2-digit",
            hour: "2-digit",
            minute: "2-digit"
        });
    }
});
</script>

<?php
// 包含页脚
require_once __DIR__ . "/../layouts/footer.php";
?>
