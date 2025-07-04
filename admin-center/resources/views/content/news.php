<?php
/**
 * 新闻管理页面
 * 
 * 用于管理新闻文章
 */

// 设置页面标题
$pageTitle = "新闻管理";
$currentPage = "news";

// 包含头部
require_once __DIR__ . "/../layouts/header.php";
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">新闻管理</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="/admin">首页</a></li>
        <li class="breadcrumb-item"><a href="/admin/content">内容管理</a></li>
        <li class="breadcrumb-item active">新闻管理</li>
    </ol>
    
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <i class="bi bi-newspaper me-1"></i>
                新闻文章列表
            </div>
            <div>
                <a href="/admin/content/news/create" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-circle me-1"></i>
                    添加新文章
                </a>
            </div>
        </div>
        <div class="card-body">
            <!-- 筛选器 -->
            <div class="row mb-3">
                <div class="col-md-3 mb-2">
                    <select id="categoryFilter" class="form-select form-select-sm">
                        <option value="">所有分类</option>
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <select id="statusFilter" class="form-select form-select-sm">
                        <option value="">所有状态</option>
                        <option value="published">已发布</option>
                        <option value="draft">草稿</option>
                        <option value="scheduled">计划发布</option>
                    </select>
                </div>
                <div class="col-md-4 mb-2">
                    <div class="input-group input-group-sm">
                        <input type="text" id="searchInput" class="form-control" placeholder="搜索标题或内容...">
                        <button class="btn btn-outline-secondary" type="button" id="searchButton">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-2 mb-2">
                    <button id="resetFilters" class="btn btn-outline-secondary btn-sm w-100">
                        重置筛选
                    </button>
                </div>
            </div>
            
            <!-- 文章列表 -->
            <div class="table-responsive">
                <table class="table table-striped table-hover" id="newsTable">
                    <thead>
                        <tr>
                            <th style="width: 50px;">ID</th>
                            <th>标题</th>
                            <th>分类</th>
                            <th>作者</th>
                            <th>状态</th>
                            <th>发布日期</th>
                            <th>浏览量</th>
                            <th style="width: 150px;">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="8" class="text-center">加载中...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- 分页 -->
            <nav aria-label="新闻列表分页" id="pagination" class="d-flex justify-content-center mt-4">
                <ul class="pagination">
                    <li class="page-item disabled">
                        <a class="page-link" href="#" tabindex="-1" aria-disabled="true">上一页</a>
                    </li>
                    <li class="page-item active" aria-current="page">
                        <a class="page-link" href="#">1</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="#">下一页</a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</div>

<!-- 删除确认对话框 -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">确认删除</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                确定要删除文章 <span id="deleteItemTitle" class="fw-bold"></span> 吗？此操作无法撤销。
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">确认删除</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    let currentPage = 1;
    let totalPages = 1;
    let currentFilters = {
        category: "",
        status: "",
        search: ""
    };
    let deleteItemId = null;
    
    // 加载分类列表
    loadCategories();
    
    // 加载新闻列表
    loadNewsList();
    
    // 绑定筛选事件
    document.getElementById("categoryFilter").addEventListener("change", function() {
        currentFilters.category = this.value;
        currentPage = 1;
        loadNewsList();
    });
    
    document.getElementById("statusFilter").addEventListener("change", function() {
        currentFilters.status = this.value;
        currentPage = 1;
        loadNewsList();
    });
    
    document.getElementById("searchButton").addEventListener("click", function() {
        currentFilters.search = document.getElementById("searchInput").value;
        currentPage = 1;
        loadNewsList();
    });
    
    document.getElementById("searchInput").addEventListener("keypress", function(e) {
        if (e.key === "Enter") {
            currentFilters.search = this.value;
            currentPage = 1;
            loadNewsList();
        }
    });
    
    document.getElementById("resetFilters").addEventListener("click", function() {
        document.getElementById("categoryFilter").value = "";
        document.getElementById("statusFilter").value = "";
        document.getElementById("searchInput").value = "";
        
        currentFilters = {
            category: "",
            status: "",
            search: ""
        };
        currentPage = 1;
        loadNewsList();
    });
    
    // 删除确认
    const deleteModal = new bootstrap.Modal(document.getElementById("deleteModal"));
    
    document.getElementById("confirmDelete").addEventListener("click", function() {
        if (deleteItemId) {
            deleteNewsItem(deleteItemId);
            deleteModal.hide();
        }
    });
    
    // 加载分类列表
    function loadCategories() {
        fetch("/api/admin/content/categories?type=news")
            .then(response => response.json())
            .then(data => {
                const select = document.getElementById("categoryFilter");
                
                data.forEach(category => {
                    const option = document.createElement("option");
                    option.value = category.id;
                    option.textContent = category.name;
                    select.appendChild(option);
                });
            })
            .catch(error => {
                console.error("加载分类失败:", error);
            });
    }
    
    // 加载新闻列表
    function loadNewsList() {
        const tableBody = document.querySelector("#newsTable tbody");
        tableBody.innerHTML = "<tr><td colspan=\"8\" class=\"text-center\">加载中...</td></tr>";
        
        let url = `/api/admin/content/news?page=${currentPage}`;
        
        if (currentFilters.category) {
            url += `&category=${currentFilters.category}`;
        }
        
        if (currentFilters.status) {
            url += `&status=${currentFilters.status}`;
        }
        
        if (currentFilters.search) {
            url += `&search=${encodeURIComponent(currentFilters.search)}`;
        }
        
        fetch(url)
            .then(response => response.json())
            .then(data => {
                tableBody.innerHTML = "";
                
                if (data.items.length === 0) {
                    tableBody.innerHTML = "<tr><td colspan=\"8\" class=\"text-center\">没有找到符合条件的文章</td></tr>";
                    return;
                }
                
                data.items.forEach(item => {
                    const row = document.createElement("tr");
                    
                    const statusBadge = getStatusBadge(item.status);
                    
                    row.innerHTML = `
                        <td>${item.id}</td>
                        <td>
                            <div class="fw-bold">${item.title}</div>
                            <div class="small text-muted">${item.slug}</div>
                        </td>
                        <td>${item.category_name}</td>
                        <td>${item.author_name}</td>
                        <td>${statusBadge}</td>
                        <td>${formatDate(item.published_at)}</td>
                        <td>${item.views_count}</td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="/admin/content/news/edit/${item.id}" class="btn btn-outline-primary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="/news/${item.slug}" target="_blank" class="btn btn-outline-info">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <button type="button" class="btn btn-outline-danger delete-btn" data-id="${item.id}" data-title="${item.title}">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    `;
                    
                    tableBody.appendChild(row);
                });
                
                // 绑定删除按钮事件
                document.querySelectorAll(".delete-btn").forEach(btn => {
                    btn.addEventListener("click", function() {
                        deleteItemId = this.getAttribute("data-id");
                        document.getElementById("deleteItemTitle").textContent = this.getAttribute("data-title");
                        deleteModal.show();
                    });
                });
                
                // 更新分页
                totalPages = data.total_pages;
                updatePagination();
            })
            .catch(error => {
                console.error("加载新闻列表失败:", error);
                tableBody.innerHTML = "<tr><td colspan=\"8\" class=\"text-center\">加载失败</td></tr>";
            });
    }
    
    // 更新分页
    function updatePagination() {
        const pagination = document.getElementById("pagination");
        const ul = pagination.querySelector("ul");
        ul.innerHTML = "";
        
        // 上一页
        const prevLi = document.createElement("li");
        prevLi.className = `page-item ${currentPage === 1 ? "disabled" : ""}`;
        prevLi.innerHTML = `<a class="page-link" href="#" ${currentPage === 1 ? "tabindex=\"-1\" aria-disabled=\"true\"" : ""}>上一页</a>`;
        
        if (currentPage > 1) {
            prevLi.querySelector("a").addEventListener("click", function(e) {
                e.preventDefault();
                currentPage--;
                loadNewsList();
            });
        }
        
        ul.appendChild(prevLi);
        
        // 页码
        const startPage = Math.max(1, currentPage - 2);
        const endPage = Math.min(totalPages, startPage + 4);
        
        for (let i = startPage; i <= endPage; i++) {
            const li = document.createElement("li");
            li.className = `page-item ${i === currentPage ? "active" : ""}`;
            li.innerHTML = `<a class="page-link" href="#">${i}</a>`;
            
            if (i !== currentPage) {
                li.querySelector("a").addEventListener("click", function(e) {
                    e.preventDefault();
                    currentPage = i;
                    loadNewsList();
                });
            }
            
            ul.appendChild(li);
        }
        
        // 下一页
        const nextLi = document.createElement("li");
        nextLi.className = `page-item ${currentPage === totalPages ? "disabled" : ""}`;
        nextLi.innerHTML = `<a class="page-link" href="#" ${currentPage === totalPages ? "tabindex=\"-1\" aria-disabled=\"true\"" : ""}>下一页</a>`;
        
        if (currentPage < totalPages) {
            nextLi.querySelector("a").addEventListener("click", function(e) {
                e.preventDefault();
                currentPage++;
                loadNewsList();
            });
        }
        
        ul.appendChild(nextLi);
    }
    
    // 删除新闻
    function deleteNewsItem(id) {
        fetch(`/api/admin/content/news/${id}`, {
            method: "DELETE",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector("meta[name=\"csrf-token\"]").getAttribute("content")
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error("删除失败");
            }
            return response.json();
        })
        .then(data => {
            showToast("删除成功", "success");
            loadNewsList();
        })
        .catch(error => {
            console.error("删除文章失败:", error);
            showToast("删除失败: " + error.message, "danger");
        });
    }
    
    // 辅助函数
    function getStatusBadge(status) {
        switch (status) {
            case "published":
                return "<span class=\"badge bg-success\">已发布</span>";
            case "draft":
                return "<span class=\"badge bg-secondary\">草稿</span>";
            case "scheduled":
                return "<span class=\"badge bg-info\">计划发布</span>";
            default:
                return "<span class=\"badge bg-light text-dark\">" + status + "</span>";
        }
    }
    
    function formatDate(dateString) {
        if (!dateString) return "未设置";
        const date = new Date(dateString);
        return date.toLocaleString("zh-CN", {
            year: "numeric",
            month: "2-digit",
            day: "2-digit",
            hour: "2-digit",
            minute: "2-digit"
        });
    }
    
    function showToast(message, type = "info") {
        const toastContainer = document.getElementById("toastContainer");
        
        const toast = document.createElement("div");
        toast.className = `toast align-items-center text-white bg-${type} border-0`;
        toast.setAttribute("role", "alert");
        toast.setAttribute("aria-live", "assertive");
        toast.setAttribute("aria-atomic", "true");
        
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        `;
        
        toastContainer.appendChild(toast);
        
        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();
        
        toast.addEventListener("hidden.bs.toast", function() {
            toast.remove();
        });
    }
});
</script>

<?php
// 包含页脚
require_once __DIR__ . "/../layouts/footer.php";
?>
