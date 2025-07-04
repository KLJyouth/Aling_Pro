<?php
/**
 * 页面内容管理
 * 
 * 用于管理网站页面内容
 */

// 设置页面标题
$pageTitle = "页面管理";
$currentPage = "pages";

// 包含头部
require_once __DIR__ . "/../layouts/header.php";
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">页面管理</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="/admin">首页</a></li>
        <li class="breadcrumb-item"><a href="/admin/content">内容管理</a></li>
        <li class="breadcrumb-item active">页面管理</li>
    </ol>
    
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <i class="bi bi-file-earmark-text me-1"></i>
                页面列表
            </div>
            <div>
                <a href="/admin/content/pages/create" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-circle me-1"></i>
                    创建新页面
                </a>
            </div>
        </div>
        <div class="card-body">
            <!-- 筛选器 -->
            <div class="row mb-3">
                <div class="col-md-3 mb-2">
                    <select id="typeFilter" class="form-select form-select-sm">
                        <option value="">所有类型</option>
                        <option value="standard">标准页面</option>
                        <option value="landing">落地页</option>
                        <option value="product">产品页面</option>
                        <option value="solution">解决方案</option>
                        <option value="legal">法律页面</option>
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <select id="statusFilter" class="form-select form-select-sm">
                        <option value="">所有状态</option>
                        <option value="published">已发布</option>
                        <option value="draft">草稿</option>
                    </select>
                </div>
                <div class="col-md-4 mb-2">
                    <div class="input-group input-group-sm">
                        <input type="text" id="searchInput" class="form-control" placeholder="搜索标题或URL...">
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
            
            <!-- 页面列表 -->
            <div class="table-responsive">
                <table class="table table-striped table-hover" id="pagesTable">
                    <thead>
                        <tr>
                            <th style="width: 50px;">ID</th>
                            <th>标题</th>
                            <th>URL</th>
                            <th>类型</th>
                            <th>状态</th>
                            <th>最后更新</th>
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
            <nav aria-label="页面列表分页" id="pagination" class="d-flex justify-content-center mt-4">
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
                确定要删除页面 <span id="deleteItemTitle" class="fw-bold"></span> 吗？此操作无法撤销。
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
        type: "",
        status: "",
        search: ""
    };
    let deleteItemId = null;
    
    // 加载页面列表
    loadPagesList();
    
    // 绑定筛选事件
    document.getElementById("typeFilter").addEventListener("change", function() {
        currentFilters.type = this.value;
        currentPage = 1;
        loadPagesList();
    });
    
    document.getElementById("statusFilter").addEventListener("change", function() {
        currentFilters.status = this.value;
        currentPage = 1;
        loadPagesList();
    });
    
    document.getElementById("searchButton").addEventListener("click", function() {
        currentFilters.search = document.getElementById("searchInput").value;
        currentPage = 1;
        loadPagesList();
    });
    
    document.getElementById("searchInput").addEventListener("keypress", function(e) {
        if (e.key === "Enter") {
            currentFilters.search = this.value;
            currentPage = 1;
            loadPagesList();
        }
    });
    
    document.getElementById("resetFilters").addEventListener("click", function() {
        document.getElementById("typeFilter").value = "";
        document.getElementById("statusFilter").value = "";
        document.getElementById("searchInput").value = "";
        
        currentFilters = {
            type: "",
            status: "",
            search: ""
        };
        currentPage = 1;
        loadPagesList();
    });
    
    // 删除确认
    const deleteModal = new bootstrap.Modal(document.getElementById("deleteModal"));
    
    document.getElementById("confirmDelete").addEventListener("click", function() {
        if (deleteItemId) {
            deletePage(deleteItemId);
            deleteModal.hide();
        }
    });
    
    // 加载页面列表
    function loadPagesList() {
        const tableBody = document.querySelector("#pagesTable tbody");
        tableBody.innerHTML = "<tr><td colspan=\"8\" class=\"text-center\">加载中...</td></tr>";
        
        let url = `/api/admin/content/pages?page=${currentPage}`;
        
        if (currentFilters.type) {
            url += `&type=${currentFilters.type}`;
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
                    tableBody.innerHTML = "<tr><td colspan=\"8\" class=\"text-center\">没有找到符合条件的页面</td></tr>";
                    return;
                }
                
                data.items.forEach(item => {
                    const row = document.createElement("tr");
                    
                    const statusBadge = getStatusBadge(item.status);
                    const typeBadge = getTypeBadge(item.type);
                    
                    row.innerHTML = `
                        <td>${item.id}</td>
                        <td>
                            <div class="fw-bold">${item.title}</div>
                        </td>
                        <td><code>${item.url_path}</code></td>
                        <td>${typeBadge}</td>
                        <td>${statusBadge}</td>
                        <td>${formatDate(item.updated_at)}</td>
                        <td>${item.views_count}</td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="/admin/content/pages/edit/${item.id}" class="btn btn-outline-primary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="${item.url_path}" target="_blank" class="btn btn-outline-info">
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
                console.error("加载页面列表失败:", error);
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
                loadPagesList();
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
                    loadPagesList();
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
                loadPagesList();
            });
        }
        
        ul.appendChild(nextLi);
    }
    
    // 删除页面
    function deletePage(id) {
        fetch(`/api/admin/content/pages/${id}`, {
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
            loadPagesList();
        })
        .catch(error => {
            console.error("删除页面失败:", error);
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
            default:
                return "<span class=\"badge bg-light text-dark\">" + status + "</span>";
        }
    }
    
    function getTypeBadge(type) {
        switch (type) {
            case "standard":
                return "<span class=\"badge bg-primary\">标准页面</span>";
            case "landing":
                return "<span class=\"badge bg-info\">落地页</span>";
            case "product":
                return "<span class=\"badge bg-warning\">产品页面</span>";
            case "solution":
                return "<span class=\"badge bg-success\">解决方案</span>";
            case "legal":
                return "<span class=\"badge bg-dark\">法律页面</span>";
            default:
                return "<span class=\"badge bg-light text-dark\">" + type + "</span>";
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
