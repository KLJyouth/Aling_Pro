<?php
/**
 * 会员订阅编辑页面
 *
 * 用于添加或编辑会员订阅
 */

// 安全检查
if (!defined("ADMIN_ACCESS")) {
    exit("无权访问");
}

// 获取会员订阅ID
$id = isset($_GET["id"]) ? intval($_GET["id"]) : 0;
$isEdit = $id > 0;

// 如果是编辑模式，获取会员订阅信息
$subscription = [];
$user = null;
$level = null;
if ($isEdit) {
    $subscriptionModel = new \App\Models\Membership\MembershipSubscription();
    $subscription = $subscriptionModel->with(["user", "level"])->find($id);
    
    if (!$subscription) {
        $_SESSION["admin_message"] = "会员订阅不存在";
        $_SESSION["admin_message_type"] = "danger";
        header("Location: index.php?module=membership&action=subscriptions");
        exit;
    }
    
    $user = $subscription->user;
    $level = $subscription->level;
}

// 获取所有用户和会员等级
$userModel = new \App\Models\User();
$users = $userModel->orderBy("name")->get();

$levelModel = new \App\Models\Membership\MembershipLevel();
$levels = $levelModel->where("status", 1)->orderBy("sort_order")->orderBy("name")->get();

$pageTitle = $isEdit ? "编辑会员订阅" : "添加会员订阅";
$breadcrumbs = [
    ["name" => "首页", "url" => "index.php"],
    ["name" => "会员管理", "url" => "index.php?module=membership"],
    ["name" => "订阅管理", "url" => "index.php?module=membership&action=subscriptions"],
    ["name" => $pageTitle, "url" => "#"]
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
    
    <!-- 表单卡片 -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-edit me-1"></i>
            <?php echo $pageTitle; ?>
        </div>
        <div class="card-body">
            <form id="subscriptionForm" method="post" action="index.php?module=membership&action=subscription_save">
                <?php if ($isEdit): ?>
                <input type="hidden" name="id" value="<?php echo $subscription->id; ?>">
                <?php endif; ?>
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION["csrf_token"]; ?>">
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="user_id" class="form-label">用户 <span class="text-danger">*</span></label>
                            <select class="form-select" id="user_id" name="user_id" required <?php echo $isEdit ? "disabled" : ""; ?>>
                                <option value="">选择用户</option>
                                <?php foreach ($users as $u): ?>
                                    <option value="<?php echo $u->id; ?>" <?php echo ($isEdit && $user->id == $u->id) ? "selected" : ""; ?>>
                                        <?php echo htmlspecialchars($u->name); ?> (<?php echo htmlspecialchars($u->email); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if ($isEdit): ?>
                            <input type="hidden" name="user_id" value="<?php echo $user->id; ?>">
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="membership_level_id" class="form-label">会员等级 <span class="text-danger">*</span></label>
                            <select class="form-select" id="membership_level_id" name="membership_level_id" required>
                                <option value="">选择会员等级</option>
                                <?php foreach ($levels as $l): ?>
                                    <option value="<?php echo $l->id; ?>" 
                                        data-price="<?php echo $l->price; ?>" 
                                        data-duration="<?php echo $l->duration_days; ?>" 
                                        <?php echo ($isEdit && $level->id == $l->id) ? "selected" : ""; ?>>
                                        <?php echo htmlspecialchars($l->name); ?> (<?php echo $l->price; ?> 元 / <?php echo $l->duration_days; ?> 天)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="start_date" class="form-label">开始日期 <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="start_date" name="start_date" required 
                                value="<?php echo $isEdit ? date("Y-m-d", strtotime($subscription->start_date)) : date("Y-m-d"); ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="end_date" class="form-label">结束日期 <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="end_date" name="end_date" required 
                                value="<?php echo $isEdit ? date("Y-m-d", strtotime($subscription->end_date)) : date("Y-m-d", strtotime("+30 days")); ?>">
                        </div>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="price_paid" class="form-label">支付金额 <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="price_paid" name="price_paid" step="0.01" min="0" required 
                                    value="<?php echo $isEdit ? $subscription->price_paid : "0.00"; ?>">
                                <span class="input-group-text">元</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">自动续费</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="auto_renew" name="auto_renew" value="1" 
                                    <?php echo ($isEdit && $subscription->auto_renew) ? "checked" : ""; ?>>
                                <label class="form-check-label" for="auto_renew">启用自动续费</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="status" class="form-label">状态 <span class="text-danger">*</span></label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="active" <?php echo ($isEdit && $subscription->status == "active") ? "selected" : ""; ?>>有效</option>
                                <option value="pending" <?php echo ($isEdit && $subscription->status == "pending") ? "selected" : ""; ?>>待处理</option>
                                <option value="cancelled" <?php echo ($isEdit && $subscription->status == "cancelled") ? "selected" : ""; ?>>已取消</option>
                                <option value="expired" <?php echo ($isEdit && $subscription->status == "expired") ? "selected" : ""; ?>>已过期</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="order_id" class="form-label">关联订单ID</label>
                            <input type="text" class="form-control" id="order_id" name="order_id" 
                                value="<?php echo $isEdit && $subscription->order_id ? $subscription->order_id : ""; ?>">
                        </div>
                    </div>
                </div>
                
                <div class="mb-3" id="cancellation_reason_container" style="<?php echo ($isEdit && $subscription->status == "cancelled") ? "" : "display: none;"; ?>">
                    <label for="cancellation_reason" class="form-label">取消原因</label>
                    <textarea class="form-control" id="cancellation_reason" name="cancellation_reason" rows="3"><?php echo $isEdit && $subscription->cancellation_reason ? htmlspecialchars($subscription->cancellation_reason) : ""; ?></textarea>
                </div>
                
                <?php if ($isEdit): ?>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">订阅编号</label>
                            <p class="form-control-plaintext"><?php echo $subscription->subscription_no; ?></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">创建时间</label>
                            <p class="form-control-plaintext"><?php echo $subscription->created_at; ?></p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="d-flex justify-content-between">
                    <a href="index.php?module=membership&action=subscriptions" class="btn btn-secondary">返回列表</a>
                    <button type="submit" class="btn btn-primary">保存设置</button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- 会员订阅编辑的JavaScript代码 -->
<script>
$(document).ready(function() {
    // 会员等级变化时自动计算价格和结束日期
    $("#membership_level_id").on("change", function() {
        var selectedOption = $(this).find("option:selected");
        if (selectedOption.val()) {
            var price = parseFloat(selectedOption.data("price"));
            var duration = parseInt(selectedOption.data("duration"));
            
            // 设置价格
            $("#price_paid").val(price.toFixed(2));
            
            // 计算结束日期
            var startDate = new Date($("#start_date").val());
            if (!isNaN(startDate.getTime())) {
                var endDate = new Date(startDate);
                endDate.setDate(endDate.getDate() + duration);
                
                // 格式化为YYYY-MM-DD
                var endDateStr = endDate.toISOString().split("T")[0];
                $("#end_date").val(endDateStr);
            }
        }
    });
    
    // 开始日期变化时更新结束日期
    $("#start_date").on("change", function() {
        // 触发会员等级变化事件，重新计算结束日期
        $("#membership_level_id").trigger("change");
    });
    
    // 状态变化时显示/隐藏取消原因
    $("#status").on("change", function() {
        if ($(this).val() === "cancelled") {
            $("#cancellation_reason_container").show();
        } else {
            $("#cancellation_reason_container").hide();
        }
    });
    
    // 表单验证
    $("#subscriptionForm").on("submit", function(e) {
        var userId = $("#user_id").val();
        var levelId = $("#membership_level_id").val();
        var startDate = $("#start_date").val();
        var endDate = $("#end_date").val();
        
        if (!userId) {
            e.preventDefault();
            Swal.fire({
                title: "验证错误",
                text: "请选择用户",
                icon: "error"
            });
            return false;
        }
        
        if (!levelId) {
            e.preventDefault();
            Swal.fire({
                title: "验证错误",
                text: "请选择会员等级",
                icon: "error"
            });
            return false;
        }
        
        if (!startDate) {
            e.preventDefault();
            Swal.fire({
                title: "验证错误",
                text: "请选择开始日期",
                icon: "error"
            });
            return false;
        }
        
        if (!endDate) {
            e.preventDefault();
            Swal.fire({
                title: "验证错误",
                text: "请选择结束日期",
                icon: "error"
            });
            return false;
        }
        
        // 验证结束日期必须晚于开始日期
        if (new Date(endDate) <= new Date(startDate)) {
            e.preventDefault();
            Swal.fire({
                title: "验证错误",
                text: "结束日期必须晚于开始日期",
                icon: "error"
            });
            return false;
        }
        
        return true;
    });
});
</script>

<?php
// 包含底部
include(ADMIN_PATH . "/views/common/footer.php");
?>
