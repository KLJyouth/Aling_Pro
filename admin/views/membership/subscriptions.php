<?php
/**
 * 会员订阅管理页面
 *
 * 用于管理系统中的会员订阅
 */

// 安全检查
if (!defined("ADMIN_ACCESS")) {
    exit("无权访问");
}

$pageTitle = "会员订阅管理";
$breadcrumbs = [
    ["name" => "首页", "url" => "index.php"],
    ["name" => "会员管理", "url" => "index.php?module=membership"],
    ["name" => "订阅管理", "url" => "#"]
];

// 包含头部
include(ADMIN_PATH . "/views/common/header.php");
?>

<div class="container-fluid px-4">
    <h1 class="mt-4"><?php echo $pageTitle; ?></h1>
    
    <!-- 面包屑导航 -->
    <ol class="breadcrumb mb-4">
        <?php foreach ($breadcrumbs as $index => $item): ?>
            <?php if ($index === count($breadcrumbs) - 1): ?>
                <li class="breadcrumb-item active"><?php echo $item["name"]; ?></li>
            <?php else: ?>
                <li class="breadcrumb-item"><a href="<?php echo $item["url"]; ?>"><?php echo $item["name"]; ?></a></li>
            <?php endif; ?>
        <?php endforeach; ?>
    </ol>
    
    <!-- 操作提示框 -->
    <?php if (isset($_SESSION["admin_message"])): ?>
        <div class="alert alert-<?php echo $_SESSION["admin_message_type"]; ?> alert-dismissible fade show" role="alert">
            <?php echo $_SESSION["admin_message"]; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="关闭"></button>
        </div>
        <?php unset($_SESSION["admin_message"], $_SESSION["admin_message_type"]); ?>
    <?php endif; ?>
    
    <!-- 卡片容器 -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-table me-1"></i>
                会员订阅列表
            </div>
            <div>
                <a href="index.php?module=membership&action=subscription_edit" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> 添加订阅
                </a>
            </div>
        </div>
        <div class="card-body">
            <table id="subscriptionTable" class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th width="5%">ID</th>
                        <th width="15%">用户</th>
                        <th width="15%">会员等级</th>
                        <th width="10%">开始日期</th>
                        <th width="10%">结束日期</th>
                        <th width="10%">剩余天数</th>
                        <th width="10%">支付金额</th>
                        <th width="10%">自动续费</th>
                        <th width="10%">状态</th>
                        <th width="10%">操作</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- 数据将通过AJAX加载 -->
                </tbody>
            </table>
        </div>
    </div>
</div>


<!-- 取消订阅的模态框 -->
<div class="modal fade" id="cancelSubscriptionModal" tabindex="-1" aria-labelledby="cancelSubscriptionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cancelSubscriptionModalLabel">取消会员订阅</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="关闭"></button>
            </div>
            <div class="modal-body">
                <form id="cancelSubscriptionForm">
                    <input type="hidden" id="cancel_subscription_id" name="id">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION["csrf_token"]; ?>">
                    <div class="mb-3">
                        <label for="cancel_reason" class="form-label">取消原因</label>
                        <textarea class="form-control" id="cancel_reason" name="reason" rows="3" placeholder="请输入取消原因"></textarea>
                    </div>
                </form>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> 警告：取消订阅后，用户将无法继续使用会员功能。
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">关闭</button>
                <button type="button" class="btn btn-danger" id="confirmCancelSubscription">确认取消</button>
            </div>
        </div>
    </div>
</div>

<!-- 会员订阅管理的JavaScript代码 -->
<script>
$(document).ready(function() {
    // 初始化DataTables
    var subscriptionTable = $("#subscriptionTable").DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "index.php?module=membership&action=subscription_data",
            "type": "POST"
        },
        "columns": [
            { "data": "id" },
            { 
                "data": null, 
                "render": function(data) {
                    return "<div>" + data.user_name + "</div><small class=\"text-muted\">" + data.user_email + "</small>";
                }
            },
            { "data": "level_name" },
            { "data": "start_date" },
            { "data": "end_date" },
            { 
                "data": "days_remaining", 
                "render": function(data, type, row) {
                    if (row.status === "cancelled") {
                        return "<span class=\"badge bg-danger\">已取消</span>";
                    } else if (row.status === "expired" || data <= 0) {
                        return "<span class=\"badge bg-secondary\">已过期</span>";
                    } else if (data <= 7) {
                        return "<span class=\"badge bg-warning\">" + data + " 天</span>";
                    } else {
                        return "<span class=\"badge bg-success\">" + data + " 天</span>";
                    }
                }
            },
            { 
                "data": "price_paid", 
                "render": function(data) {
                    return parseFloat(data).toFixed(2) + " 元";
                }
            },
            { 
                "data": "auto_renew", 
                "render": function(data) {
                    if (data) {
                        return "<span class=\"badge bg-success\">是</span>";
                    } else {
                        return "<span class=\"badge bg-secondary\">否</span>";
                    }
                }
            },
            { 
                "data": "status", 
                "render": function(data) {
                    var statusMap = {
                        "active": "<span class=\"badge bg-success\">有效</span>",
                        "pending": "<span class=\"badge bg-warning\">待处理</span>",
                        "cancelled": "<span class=\"badge bg-danger\">已取消</span>",
                        "expired": "<span class=\"badge bg-secondary\">已过期</span>"
                    };
                    return statusMap[data] || data;
                }
            },
            { 
                "data": null, 
                "render": function(data, type, row) {
                    var actions = "<div class=\"btn-group btn-group-sm\" role=\"group\">";
                    actions += "<a href=\"index.php?module=membership&action=subscription_edit&id=" + row.id + "\" class=\"btn btn-primary\"><i class=\"fas fa-edit\"></i></a>";
                    
                    if (row.status === "active") {
                        actions += "<button type=\"button\" class=\"btn btn-danger cancel-subscription\" data-id=\"" + row.id + "\"><i class=\"fas fa-ban\"></i></button>";
                    }
                    
                    actions += "</div>";
                    return actions;
                }
            }
        ],
        "order": [[0, "desc"]],
        "language": {
            "url": "assets/js/plugins/datatables/zh-CN.json"
        }
    });

    
    // 打开取消订阅模态框
    $(document).on("click", ".cancel-subscription", function() {
        var id = $(this).data("id");
        $("#cancel_subscription_id").val(id);
        $("#cancel_reason").val("");
        $("#cancelSubscriptionModal").modal("show");
    });
    
    // 确认取消订阅
    $("#confirmCancelSubscription").on("click", function() {
        var id = $("#cancel_subscription_id").val();
        var reason = $("#cancel_reason").val();
        
        $.ajax({
            url: "index.php?module=membership&action=subscription_cancel",
            type: "POST",
            data: {
                id: id,
                reason: reason,
                csrf_token: $("#cancelSubscriptionForm input[name=csrf_token]").val()
            },
            success: function(response) {
                try {
                    var data = JSON.parse(response);
                    if (data.success) {
                        $("#cancelSubscriptionModal").modal("hide");
                        Swal.fire(
                            "已取消!",
                            data.message,
                            "success"
                        );
                        subscriptionTable.ajax.reload();
                    } else {
                        Swal.fire(
                            "错误!",
                            data.message,
                            "error"
                        );
                    }
                } catch(e) {
                    Swal.fire(
                        "错误!",
                        "操作失败，请重试",
                        "error"
                    );
                }
            },
            error: function() {
                Swal.fire(
                    "错误!",
                    "服务器错误，请稍后重试",
                    "error"
                );
            }
        });
    });
});
</script>

<?php
// 包含底部
include(ADMIN_PATH . "/views/common/footer.php");
?>
