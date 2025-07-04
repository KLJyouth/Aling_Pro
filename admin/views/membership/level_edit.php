<?php
/**
 * 会员等级编辑页面
 *
 * 用于添加或编辑会员等级
 */

// 安全检查
if (!defined("ADMIN_ACCESS")) {
    exit("无权访问");
}

// 获取会员等级ID
$id = isset($_GET["id"]) ? intval($_GET["id"]) : 0;
$isEdit = $id > 0;

// 如果是编辑模式，获取会员等级信息
$level = [];
if ($isEdit) {
    $levelModel = new \App\Models\Membership\MembershipLevel();
    $level = $levelModel->find($id);
    
    if (!$level) {
        $_SESSION["admin_message"] = "会员等级不存在";
        $_SESSION["admin_message_type"] = "danger";
        header("Location: index.php?module=membership&action=levels");
        exit;
    }
    
    // 转换benefits为数组
    if (isset($level->benefits) && is_string($level->benefits)) {
        $level->benefits = json_decode($level->benefits, true);
    }
}

$pageTitle = $isEdit ? "编辑会员等级" : "添加会员等级";
$breadcrumbs = [
    ["name" => "首页", "url" => "index.php"],
    ["name" => "会员管理", "url" => "index.php?module=membership"],
    ["name" => "等级设置", "url" => "index.php?module=membership&action=levels"],
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
            <form id="levelForm" method="post" action="index.php?module=membership&action=level_save" enctype="multipart/form-data">
                <?php if ($isEdit): ?>
                <input type="hidden" name="id" value="<?php echo $level->id; ?>">
                <?php endif; ?>
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION["csrf_token"]; ?>">
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="name" class="form-label">等级名称 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" required 
                                value="<?php echo $isEdit ? htmlspecialchars($level->name) : ""; ?>">
                            <div class="form-text">会员等级的显示名称，如"黄金会员"、"钻石会员"等</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="code" class="form-label">等级代码 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="code" name="code" required 
                                value="<?php echo $isEdit ? htmlspecialchars($level->code) : ""; ?>">
                            <div class="form-text">等级的唯一标识符，如"gold"、"diamond"等</div>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="price" class="form-label">价格 <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" required 
                                    value="<?php echo $isEdit ? $level->price : "0.00"; ?>">
                                <span class="input-group-text">元</span>
                            </div>
                            <div class="form-text">会员等级的价格，0表示免费</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="duration_days" class="form-label">有效期(天) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="duration_days" name="duration_days" min="1" required 
                                value="<?php echo $isEdit ? $level->duration_days : "30"; ?>">
                            <div class="form-text">会员等级的有效期天数，如30、90、365等</div>
                        </div>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="icon" class="form-label">等级图标</label>
                            <input type="file" class="form-control" id="icon" name="icon" accept="image/*">
                            <div class="form-text">推荐尺寸：64x64像素，支持PNG、JPG、SVG格式</div>
                            <?php if ($isEdit && !empty($level->icon)): ?>
                            <div class="mt-2">
                                <img src="<?php echo $level->icon; ?>" alt="当前图标" class="img-thumbnail" style="max-width: 64px; max-height: 64px;">
                                <div class="form-check mt-1">
                                    <input class="form-check-input" type="checkbox" id="remove_icon" name="remove_icon">
                                    <label class="form-check-label" for="remove_icon">
                                        删除当前图标
                                    </label>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="color" class="form-label">等级颜色</label>
                            <input type="color" class="form-control form-control-color" id="color" name="color" 
                                value="<?php echo $isEdit && !empty($level->color) ? $level->color : "#007bff"; ?>">
                            <div class="form-text">会员等级的标识颜色，用于前端显示</div>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="api_quota" class="form-label">API配额</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="api_quota" name="api_quota" min="0" 
                                    value="<?php echo $isEdit ? $level->api_quota : "1000"; ?>">
                                <span class="input-group-text">次/月</span>
                            </div>
                            <div class="form-text">每月可使用的API调用次数，0表示无限制</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="ai_quota" class="form-label">AI配额</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="ai_quota" name="ai_quota" min="0" 
                                    value="<?php echo $isEdit ? $level->ai_quota : "10000"; ?>">
                                <span class="input-group-text">tokens/月</span>
                            </div>
                            <div class="form-text">每月可使用的AI tokens数量，0表示无限制</div>
                        </div>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="storage_quota" class="form-label">存储配额</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="storage_quota" name="storage_quota" min="0" 
                                    value="<?php echo $isEdit ? $level->storage_quota : "100"; ?>">
                                <span class="input-group-text">MB</span>
                            </div>
                            <div class="form-text">可使用的存储空间大小，0表示无限制</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="bandwidth_quota" class="form-label">带宽配额</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="bandwidth_quota" name="bandwidth_quota" min="0" 
                                    value="<?php echo $isEdit ? $level->bandwidth_quota : "1000"; ?>">
                                <span class="input-group-text">MB/月</span>
                            </div>
                            <div class="form-text">每月可使用的带宽流量，0表示无限制</div>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="discount_percent" class="form-label">折扣比例</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="discount_percent" name="discount_percent" min="0" max="100" 
                                    value="<?php echo $isEdit ? $level->discount_percent : "0"; ?>">
                                <span class="input-group-text">%</span>
                            </div>
                            <div class="form-text">购买其他服务时的折扣比例，0表示无折扣</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">优先支持</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="priority_support" name="priority_support" value="1" 
                                    <?php echo ($isEdit && $level->priority_support) ? "checked" : ""; ?>>
                                <label class="form-check-label" for="priority_support">启用优先支持</label>
                            </div>
                            <div class="form-text">是否享有优先客服支持</div>
                        </div>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">特色等级</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" value="1" 
                                    <?php echo ($isEdit && $level->is_featured) ? "checked" : ""; ?>>
                                <label class="form-check-label" for="is_featured">设为特色等级</label>
                            </div>
                            <div class="form-text">特色等级将在会员页面突出显示</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="sort_order" class="form-label">排序</label>
                            <input type="number" class="form-control" id="sort_order" name="sort_order" min="0" 
                                value="<?php echo $isEdit ? $level->sort_order : "0"; ?>">
                            <div class="form-text">数字越小排序越靠前</div>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-12">
                        <div class="mb-3">
                            <label for="benefits" class="form-label">会员特权</label>
                            <div id="benefits-container">
                                <?php if ($isEdit && !empty($level->benefits)): ?>
                                    <?php foreach ($level->benefits as $index => $benefit): ?>
                                        <div class="input-group mb-2 benefit-item">
                                            <input type="text" class="form-control" name="benefits[]" value="<?php echo htmlspecialchars($benefit); ?>">
                                            <button type="button" class="btn btn-outline-danger remove-benefit"><i class="fas fa-times"></i></button>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="input-group mb-2 benefit-item">
                                        <input type="text" class="form-control" name="benefits[]" placeholder="输入会员特权">
                                        <button type="button" class="btn btn-outline-danger remove-benefit"><i class="fas fa-times"></i></button>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="add-benefit">
                                <i class="fas fa-plus"></i> 添加特权
                            </button>
                            <div class="form-text">会员等级包含的特权，如"专属客服"、"高级模型访问"等</div>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="description" class="form-label">等级描述</label>
                    <textarea class="form-control" id="description" name="description" rows="4"><?php echo $isEdit ? htmlspecialchars($level->description) : ""; ?></textarea>
                    <div class="form-text">会员等级的详细描述，支持简单的HTML标签</div>
                </div>
                
                <div class="mb-3">
                    <label for="status" class="form-label">状态</label>
                    <select class="form-select" id="status" name="status">
                        <option value="1" <?php echo ($isEdit && $level->status == 1) ? "selected" : ""; ?>>启用</option>
                        <option value="0" <?php echo ($isEdit && $level->status == 0) ? "selected" : ""; ?>>禁用</option>
                    </select>
                </div>
                
                <div class="d-flex justify-content-between">
                    <a href="index.php?module=membership&action=levels" class="btn btn-secondary">返回列表</a>
                    <button type="submit" class="btn btn-primary">保存设置</button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- 会员等级编辑的JavaScript代码 -->
<script>
$(document).ready(function() {
    // 添加特权
    $("#add-benefit").on("click", function() {
        var benefitItem = `
            <div class="input-group mb-2 benefit-item">
                <input type="text" class="form-control" name="benefits[]" placeholder="输入会员特权">
                <button type="button" class="btn btn-outline-danger remove-benefit"><i class="fas fa-times"></i></button>
            </div>
        `;
        $("#benefits-container").append(benefitItem);
    });
    
    // 删除特权
    $(document).on("click", ".remove-benefit", function() {
        // 如果只有一个特权项，则清空内容而不是删除
        if ($(".benefit-item").length <= 1) {
            $(this).closest(".benefit-item").find("input").val("");
        } else {
            $(this).closest(".benefit-item").remove();
        }
    });
    
    // 表单验证
    $("#levelForm").on("submit", function(e) {
        var name = $("#name").val().trim();
        var code = $("#code").val().trim();
        
        if (!name) {
            e.preventDefault();
            Swal.fire({
                title: "验证错误",
                text: "请输入等级名称",
                icon: "error"
            });
            return false;
        }
        
        if (!code) {
            e.preventDefault();
            Swal.fire({
                title: "验证错误",
                text: "请输入等级代码",
                icon: "error"
            });
            return false;
        }
        
        // 验证代码格式（只允许字母、数字和下划线）
        var codeRegex = /^[a-zA-Z0-9_]+$/;
        if (!codeRegex.test(code)) {
            e.preventDefault();
            Swal.fire({
                title: "验证错误",
                text: "等级代码只能包含字母、数字和下划线",
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
